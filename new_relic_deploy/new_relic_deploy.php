<?php
// No need to log this script operation in New Relic's stats.
// PROTIP: you might also want to use this snippet if you have PHP code handling
// very fast things like redirects or the like.
if (extension_loaded('newrelic')) {
  newrelic_ignore_transaction();
}

define("API_KEY_SECRET_NAME", "new_relic_api_key");

$data = get_nr_connection_info();
// Fail fast if we're not going to be able to call New Relic.
if ($data == false) {
  echo "\n\nALERT! No New Relic metadata could be found.\n\n";
  exit();
}

$app_id = get_app_id($data['api_key'], $data['app_name']);

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

// clean up the git output
$revision = rtrim($revision, "\n");
$changelog = rtrim($changelog, "\n");
$changelog = str_replace('\'','',$changelog);

$deployment_data = [
  "deployment" => [
    "revision" => $revision,
    "changelog" => $changelog,
    "description" => $description,
    "user" => $user,
  ]
];

echo "Logging deployment in New Relic App $app_id...\n";

$json_data = json_encode($deployment_data, JSON_FORCE_OBJECT);
echo "Sending: $json_data\n";

$command = "curl -X POST 'https://api.newrelic.com/v2/applications/$app_id/deployments.json' " .
           "-H 'X-Api-Key: {$data['api_key']}' " .
           "-H 'Content-Type: application/json' " .
           "-H 'Accept: application/json' " .
           "-d '" . $json_data . "'";

echo "Running: $command\n";

passthru($command);

echo "\nDone!\n";

/**
 * Gets the New Relic API Key so that further requests can be made.
 *
 * Also gets New Relic's name for the given environment.
 */
function get_nr_connection_info() {
  $output = array();

  $output['app_name'] = ini_get('newrelic.appname');
  if (function_exists('pantheon_get_secret')) {
    $output['api_key'] = pantheon_get_secret(API_KEY_SECRET_NAME);
  }

  return $output;
}

/**
 * Get the id of the current multidev environment.
 */
function get_app_id( $api_key, $app_name ) {
  $return = '';
  $s      = curl_init();
  curl_setopt( $s, CURLOPT_URL, 'https://api.newrelic.com/v2/applications.json' );
  curl_setopt( $s, CURLOPT_HTTPHEADER, array( 'X-API-KEY:' . $api_key ) );
  curl_setopt( $s, CURLOPT_RETURNTRANSFER, 1 );
  $result = curl_exec( $s );
  curl_close( $s );

  $result = json_decode( $result, true );

  foreach ( $result['applications'] as $application ) {
    if ( $application['name'] === $app_name ) {
      $return = $application['id'];
      break;
    }
  }

  return $return;
}
