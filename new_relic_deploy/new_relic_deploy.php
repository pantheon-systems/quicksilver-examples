<?php
// No need to log this script operation in New Relic's stats.
// PROTIP: you might also want to use this snippet if you have PHP code handling
// very fast things like redirects or the like.
if (extension_loaded('newrelic')) {
  newrelic_ignore_transaction();
}

define("API_KEY_SECRET_NAME", "new_relic_api_key");

$data = get_nr_connection_info(PANTHEON_ENVIRONMENT);
// Fail fast if we're not going to be able to call New Relic.
if ($data == false) {
  echo "\n\nALERT! No New Relic metadata could be found.\n\n";
  exit();
}

// This is one example that handles code pushes, dashboard 
// commits, and deploys between environments. To make sure we 
// have good deploy markers, we gather data differently depending
// on the context.

if (in_array($_POST['wf_type'], ['sync_code','sync_code_with_build'])) {  
  // commit 'subject'
  $description = trim(`git log --pretty=format:"%s" -1`);
  $revision = trim(`git log --pretty=format:"%h" -1`);
  if ($_POST['user_role'] == 'super') {
    // This indicates an in-dashboard SFTP commit.
    $user = trim(`git log --pretty=format:"%ae" -1`);
    $changelog = trim(`git log --pretty=format:"%b" -1`);
    $changelog .= "\n\n" . '(Commit made via Pantheon dashboard.)';
  }
  else {
    $user = $_POST['user_email'];
    $changelog = trim(`git log --pretty=format:"%b" -1`);
    $changelog .= "\n\n" . '(Triggered by remote git push.)';
  }
}
elseif ($_POST['wf_type'] == 'deploy') {
  // Topline description:
  $description = 'Deploy to environment triggered via Pantheon';
  // Find out if there's a deploy tag:
  $revision = `git describe --tags --abbrev=0`;
  // Get the annotation:
  $changelog = `git tag -l -n99 $revision`;
  $user = $_POST['user_email'];
}


// Use New Relic's v1 curl command-line example.
// TODO: update to use v2 API with JSON, plus curl() in PHP.
// Blocked by needing the app_id to use v2 API
$curl = 'curl -H "x-api-key:'. $data['api_key'] .'"';
$curl .= ' -d "deployment[application_id]=' . $data['app_name'] .'"';
$curl .= ' -d "deployment[description]= '. $description .'"';
$curl .= ' -d "deployment[revision]='. $revision .'"';
$curl .= ' -d "deployment[changelog]='. $changelog .'"';
$curl .= ' -d "deployment[user]='. $user .'"';
$curl .= ' https://api.newrelic.com/deployments.xml';
// The below can be helpful debugging.
// echo "\n\nCURLing... \n\n$curl\n\n";

echo "Logging deployment in New Relic...\n";
passthru($curl);
echo "Done!";

/**
 * Gets the New Relic API Key so that further requests can be made.
 *
 * Also gets New Relic's name for the given environment.
 */
function get_nr_connection_info( $env = 'dev' ) {
  $output = array();
  $site_name = $_ENV['PANTHEON_SITE_NAME'];
  $app_name = sprintf( "%s (%s)", $site_name, $env );
  $output['app_name'] = $app_name;

  $output['api_key'] = pantheon_get_secret(API_KEY_SECRET_NAME);

  return $output;
}