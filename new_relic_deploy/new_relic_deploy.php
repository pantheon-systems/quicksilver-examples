<?php
/**
 * No need to log this script operation in New Relic's stats.
 * PROTIP: you might also want to use this snippet if you have PHP code handling
 * very fast things like redirects or the like.
 */
if (extension_loaded('newrelic')) {
  newrelic_ignore_transaction();
}

// Retrieve the app name and API key from the Pantheon API.
$app = get_nr_connection_info(PANTHEON_ENVIRONMENT);

// Bail if we're not going to be able to call the New Relic API.
if (!$app) {
  echo "\n\nALERT! No New Relic metadata could be found.\n\n";
  exit();
}

// Get the app ID from the New Relic API.
$app_id = get_app_id($app['api_key'], $app['app_name']);

/**
 * Format the Git commit message for use as a deploy marker 
 * in New Relic.
 * 
 * This is one example that handles code pushes, Dashboard
 * commits, and deploys between environments. To make sure we
 * have good deploy markers, we gather data differently depending
 * on the context.
 */
if ($_POST['wf_type'] == 'sync_code') {
  // Commit message subject.
  $description = trim(`git log --pretty=format:"%s" -1`);
  $revision = trim(`git log --pretty=format:"%h" -1`);
  if ($_POST['user_role'] == 'super') {
    // This indicates an in-dashboard SFTP commit.
    $user = trim(`git log --pretty=format:"%ae" -1`);
    $changelog = trim(`git log --pretty=format:"%b" -1`);
    $changelog .= "\n\n" . '(Commit made via Pantheon Dashboard.)';
  }
  else {
    $user = $_POST['user_email'];
    $changelog = trim(`git log --pretty=format:"%b" -1`);
    $changelog .= "\n\n" . '(Triggered by remote git push.)';
  }
}
elseif ($_POST['wf_type'] == 'deploy') {
  $description = 'Deploy to environment triggered via Pantheon';
  // Check if there's a deploy tag.
  $revision = `git describe --tags --abbrev=0`;
  // Get the annotation.
  $changelog = `git tag -l -n99 $deploy_tag`;
  $user = $_POST['user_email'];
}

/**
 * Use New Relic's v2 curl command-line example.
 * https://docs.newrelic.com/docs/apis/rest-api-v2/getting-started/introduction-new-relic-rest-api-v2
 */
$data = [
  'deployment' => [
    'revision' => $revision,
    'changelog' => $changelog,
    'description' => $description,
    'user' => $user
  ]
];
$payload = json_encode($data);

// Set up the cURL request.
$ch = curl_init('https://api.newrelic.com/v2/applications/' . $app_id . '/deployments.json');
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

// Set HTTP Headers for POST request
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  'X-Api-Key:' . $app['api_key'],
  'Content-Type: application/json',
  'Content-Length:' . strlen($payload))
);

print("\n==== Sending Request to New Relic ====\n");

// Submit the POST request
$result = curl_exec($ch);
curl_close($ch);

// Uncomment this line for debugging
// print("RESULT: $result");

print("\n===== Request Complete! =====\n");

/**
 * Retrieve the New Relic API key and app name.
 * 
 * @param string $env
 *   The environment name.
 * @return array $output
 *   An array containing the New Relic API key and app name.
 */
function get_nr_connection_info($env = 'dev') {
  $output = array();
  $req = pantheon_curl('https://api.live.getpantheon.com/sites/self/bindings?type=newrelic', null, 8443);
  $meta = json_decode($req['body'], true);
  foreach ($meta as $data) {
    if ($data['environment'] === $env) {
      if (empty($data['api_key'])) {
        echo "Failed to get API Key\n";
        return;
      }
      $output['api_key'] = $data['api_key'];
      if (empty($data['app_name'])) {
        echo "Failed to get app name\n";
        return;
      }
      $output['app_name'] = $data['app_name'];
    }
  }
  return $output;
}

/**
 * Retrieve the New Relic app ID of the current environment.
 * 
 * @param string $api_key
 * @param string $app_name
 * @return string
 */
function get_app_id($api_key, $app_name) {
  $app_id = '';
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  curl_setopt($ch, CURLOPT_URL, 'https://api.newrelic.com/v2/applications.json');
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-API-KEY:' . $api_key));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($ch);
  curl_close($ch);

  $result = json_decode($result, true);
  foreach ($result['applications'] as $application) {
    if ($application['name'] === $app_name) {
      $app_id = $application['id'];
      break;
    }
  }
  return $app_id;
}
