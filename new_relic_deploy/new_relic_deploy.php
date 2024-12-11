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

$app_guid = get_app_guid($data['api_key'], $data['app_name']);

if (empty($app_guid)) {
  echo "Error: No New Relic app found with name " . $data['app_name'];
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
    $changelog .= ' (Commit made via Pantheon dashboard.)';
  }
  else {
    $user = $_POST['user_email'];
    $changelog = trim(`git log --pretty=format:"%b" -1`);
    $changelog .= ' (Triggered by remote git push.)';
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

echo "Logging deployment in New Relic App $app_guid...\n";
$response = create_newrelic_deployment_change_tracking($data['api_key'], $app_guid, $user, $revision, $changelog, $description);

echo "\nResponse from New Relic:" . $response;

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

// Get GUID of the current environment.
function get_app_guid(string $api_key, string $app_name): string {
  $url = 'https://api.newrelic.com/graphql';
  $headers = ['Content-Type: application/json', 'API-Key: ' . $api_key];

  // Updated entitySearch query with name filter
  $data = '{ "query": "{ actor { entitySearch(query: \\"(domain = \'APM\' and type = \'APPLICATION\' and name = \'' . $app_name . '\')\\") { count query results { entities { entityType name guid } } } } }" }';

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

  $response = curl_exec($ch);
  curl_close($ch);


  $decoded_response = json_decode($response, true);

  // Error handling for API response.
  if (isset($decoded_response['errors'])) {
    echo "Error: " . $decoded_response['errors'][0]['message'] . "\n";
    return '';
  }
  if (!isset($decoded_response['data']['actor']['entitySearch']['results']['entities']) || !is_array($decoded_response['data']['actor']['entitySearch']['results']['entities'])) {
    echo "Error: No entities found in New Relic response\n";
    return '';
  }

  $entities = $decoded_response['data']['actor']['entitySearch']['results']['entities'];
  // Since we filtered by name, the first entity should be the correct one.
  if (isset($entities[0]['guid'])) {
    return $entities[0]['guid'];
  }
  return '';
}

function create_newrelic_deployment_change_tracking(string $api_key, string $entityGuid, string $user, string $version, string $changelog, string $description): string {
  $url = 'https://api.newrelic.com/graphql';
  $headers = ['Content-Type: application/json', 'API-Key: ' . $api_key];

  $timestamp = round(microtime(true) * 1000);

  // Construct the mutation with dynamic variables
  $data = '{
    "query": "mutation { changeTrackingCreateDeployment(deployment: { version: \\"' . $version . '\\" user: \\"' . $user . '\\" timestamp: ' . $timestamp . ' entityGuid: \\"' . $entityGuid . '\\" description: \\"' . $description . '\\" changelog: \\"' . $changelog . '\\" }) { changelog deploymentId description entityGuid timestamp user version } }"
  }';

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

  $response = curl_exec($ch);
  curl_close($ch);
  return $response;
}
