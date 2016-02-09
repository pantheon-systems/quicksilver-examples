<?php

// Default values for parameters
$defaults = array(
  'hipchat_url' => 'https://api.hipchat.com/v2/room/',
  'hipchat_room_id' => 'quicksilver'
);

// Load our hidden credentials.
// See the README.md for instructions on storing secrets.
$secrets = _get_secrets(array('hipchat_auth_token'), $defaults);

// Add a slash on the end of the hipchat URL if it does not already have one.
if ($secrets['hipchat_url'][strlen($secrets['hipchat_url']) - 1] != '/') {
  $secrets['hipchat_url'] .= '/';
}

$workflow_description = ucfirst($_POST['stage']) . ' ' . str_replace('_', ' ', $_POST['wf_type']);

// Customize the message based on the workflow type.  Note that hipchat_notification.php
// must appear in your pantheon.yml for each workflow type you wish to send notifications on.
switch($_POST['wf_type']) {
  case 'deploy':
    // Find out what tag we are on and get the annotation.
    $deploy_tag = `git describe --tags`;
    $deploy_message = $_POST['deploy_message'];

    // Prepare the message
    $url = 'https://dashboard.pantheon.io/sites/'. PANTHEON_SITE .'#'. PANTHEON_ENVIRONMENT .'/deploy';
    $text = '<b>' . $_POST['user_fullname'] . '</b> deployed
    <a href="' . $url . '">' . $_ENV['PANTHEON_SITE_NAME'] . '</a><br />
    <b>On branch "' . PANTHEON_ENVIRONMENT . '"</b><br />Workflow: ' . $workflow_description . '<br />
    Deploy Message: ' . htmlentities($deploy_message);

    break;

  case 'sync_code':
    // Get the committer, hash, and message for the most recent commit.
    $committer = `git log -1 --pretty=%cn`;
    $email = `git log -1 --pretty=%ce`;
    $message = `git log -1 --pretty=%B`;
    $hash = `git log -1 --pretty=%h`;

    // Prepare the message
    $url = 'https://dashboard.pantheon.io/sites/'. PANTHEON_SITE .'#'. PANTHEON_ENVIRONMENT .'/code';
    $text = '<b>' . $_POST['user_fullname'] . '</b> committed to
    <a href="' . $url . '">' . $_ENV['PANTHEON_SITE_NAME'] . '</a><br />
    <b>On branch "' . PANTHEON_ENVIRONMENT . '"</b><br />Workflow: ' . $workflow_description . '<br />
    - ' . htmlentities($message) . ' (<a href="' . $url . '">' . $hash . '</a>)';

    break;

  default:
    $text = "Workflow $workflow_description<br />" . $_POST['qs_description'];
    break;
}

_hipchat_notification($secrets['hipchat_url'], $secrets['hipchat_room_id'], $secrets['hipchat_auth_token'], $text);

/**
 * Get secrets from secrets file.
 *
 * @param array $requiredKeys  List of keys in secrets file that must exist.
 */
function _get_secrets($requiredKeys, $defaults)
{
  $secretsFile = $_SERVER['HOME'] . '/files/private/secrets.json';
  if (!file_exists($secretsFile)) {
    die('No secrets file found. Aborting!');
  }
  $secretsContents = file_get_contents($secretsFile);
  $secrets = json_decode($secretsContents, 1);
  if ($secrets == FALSE) {
    die('Could not parse json in secrets file. Aborting!');
  }
  $secrets += $defaults;
  $missing = array_diff($requiredKeys, array_keys($secrets));
  if (!empty($missing)) {
    die('Missing required keys in json secrets file: ' . implode(',', $missing) . '. Aborting!');
  }
  return $secrets;
}

/**
 * Send a notification to hipchat
 */
function _hipchat_notification($hipchat_url, $room_id, $auth_token, $text) {
  $data = array('color' => 'yellow', 'message' => $text);
  $payload = json_encode($data);
  $hipchat_API_URL = $hipchat_url . $room_id . '/notification';
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $hipchat_API_URL);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $auth_token
  ));
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

  // Uncomment this section for debug
/*
  print("\n==== Begin Debug Data ====\n");
  print "URL: " . $hipchat_API_URL . "\n";
  print "Room ID: " . $room_id . "\n";
  print "Auth Token: " . $auth_token . "\n";
  print "Payload: \n";
  print_r($data);
  print("\n==== End Debug Data ====\n");
*/

  // Watch for messages with `terminus workflows watch --site=SITENAME`
  print("\n==== Posting to Hipchat ====\n");
  $result = curl_exec($ch);
  print("RESULT: $result");
  print("\n===== Post Complete! =====\n");
  curl_close($ch);
}
