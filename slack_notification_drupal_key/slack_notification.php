<?php

// Important constants :)
$pantheon_yellow = '#EFD01B';

// Default values for parameters
$defaults = array(
  'slack_channel' => '#quicksilver',
  'slack_username' => 'Pantheon-Quicksilver',
  'always_show_text' => false,
);

// Load our hidden credentials.
// See the README.md for instructions on storing secrets.
$secrets = _get_secrets(array('slack_url_secret'), $defaults);

$secrets['slack_url'] = 'https://hooks.slack.com/services/' . $secrets['slack_url_secret'];

// Build an array of fields to be rendered with Slack Attachments as a table
// attachment-style formatting:
// https://api.slack.com/docs/attachments
$fields = array(
  array(
    'title' => 'Site',
    'value' => $_ENV['PANTHEON_SITE_NAME'],
    'short' => 'true'
  ),
  array( // Render Environment name with link to site, <http://{ENV}-{SITENAME}.pantheon.io|{ENV}>
    'title' => 'Environment',
    'value' => '<http://' . $_ENV['PANTHEON_ENVIRONMENT'] . '-' . $_ENV['PANTHEON_SITE_NAME'] . '.pantheonsite.io|' . $_ENV['PANTHEON_ENVIRONMENT'] . '>',
    'short' => 'true'
  ),
  array( // Render Name with link to Email from Commit message
    'title' => 'By',
    'value' => $_POST['user_email'],
    'short' => 'true'
  ),
  array( // Render workflow phase that the message was sent
    'title' => 'Workflow',
    'value' => ucfirst($_POST['stage']) . ' ' . str_replace('_', ' ',  $_POST['wf_type']),
    'short' => 'true'
  ),
  array(
    'title' => 'View Dashboard',
    'value' => '<https://dashboard.pantheon.io/sites/'. PANTHEON_SITE .'#'. PANTHEON_ENVIRONMENT .'/deploys|View Dashboard>',
    'short' => 'true'
  ),
);

// Customize the message based on the workflow type.  Note that slack_notification.php
// must appear in your pantheon.yml for each workflow type you wish to send notifications on.
switch($_POST['wf_type']) {
  case 'deploy':
    // Find out what tag we are on and get the annotation.
    $deploy_tag = `git describe --tags`;
    $deploy_message = $_POST['deploy_message'];

    // Prepare the slack payload as per:
    // https://api.slack.com/incoming-webhooks
    $text = 'Deploy to the '. $_ENV['PANTHEON_ENVIRONMENT'];
    $text .= ' environment of '. $_ENV['PANTHEON_SITE_NAME'] .' by '. $_POST['user_email'] .' complete!';
    $text .= ' <https://dashboard.pantheon.io/sites/'. PANTHEON_SITE .'#'. PANTHEON_ENVIRONMENT .'/deploys|View Dashboard>';
    $text .= "\n\n*DEPLOY MESSAGE*: $deploy_message";
    // Build an array of fields to be rendered with Slack Attachments as a table
    // attachment-style formatting:
    // https://api.slack.com/docs/attachments
    $fields[] = array(
      'title' => 'Deploy Message',
      'value' => $text,
      'short' => 'false'
    );
    break;

  case 'sync_code':
    // Get the committer, hash, and message for the most recent commit.
    $committer = `git log -1 --pretty=%cn`;
    $email = `git log -1 --pretty=%ce`;
    $message = `git log -1 --pretty=%B`;
    $hash = `git log -1 --pretty=%h`;

    // Prepare the slack payload as per:
    // https://api.slack.com/incoming-webhooks
    $text = 'Code sync to the ' . $_ENV['PANTHEON_ENVIRONMENT'] . ' environment of ' . $_ENV['PANTHEON_SITE_NAME'] . ' by ' . $_POST['user_email'] . "!\n";
    $text .= 'Most recent commit: ' . rtrim($hash) . ' by ' . rtrim($committer) . ': ' . $message;
    // Build an array of fields to be rendered with Slack Attachments as a table
    // attachment-style formatting:
    // https://api.slack.com/docs/attachments
    $fields += array(
      array(
        'title' => 'Commit',
        'value' => rtrim($hash),
        'short' => 'true'
      ),
      array(
        'title' => 'Commit Message',
        'value' => $message,
        'short' => 'false'
      )
    );
    break;

  default:
    $text = $_POST['qs_description'];
    break;
}

$attachment = array(
  'fallback' => $text,
  'pretext' => 'Deploying :rocket:',
  'color' => $pantheon_yellow, // Can either be one of 'good', 'warning', 'danger', or any hex color code
  'fields' => $fields
);

_slack_notification($secrets['slack_url'], $secrets['slack_channel'], $secrets['slack_username'], $text, $attachment, $secrets['always_show_text']);

/**
 * Get secrets from secrets file and Drupal Key if present
 *
 * @param array $requiredKeys  List of keys in secrets file that must exist.
 */
function _get_secrets($requiredKeys, $defaults) {
	$secrets = array();
	$error_message = array();

	//Get our secrets.json file if it exists and pull in any values to the secrets array
  $secretsFile = $_SERVER['HOME'] . '/files/private/secrets.json';
  if (!file_exists($secretsFile)) {
    $error_message[] = 'No secrets file found. ';
  }
  else{
	  $secretsContents = file_get_contents($secretsFile);
	  $secrets = json_decode($secretsContents, 1);
	  if ($secrets == FALSE) {
	    $error_message[] = 'Could not parse json in secrets file. ';
	  }
  }

  $secrets += $defaults;

  $missing = array_diff($requiredKeys, array_keys($secrets));
  if (!empty($missing)) {
	  foreach($missing as $key) {
		  print("\n==== Missing the key $key in the secrets.json so looking for it in Drush ====\n");
		  $key_value = exec("drush key-get-key " . $key);
		  if($key_value != 'Sorry no key found by that name') {
			  $secrets[$key] = $key_value;
		  }
	  }
  }
  $missing = array_diff($requiredKeys, array_keys($secrets));
  if (!empty($missing)) {
    $error_message[] = 'Missing required keys in json secrets file: ' . implode(',', $missing) . '. ';
  }
  else{
	  //Clear out any errors as we have all the required keys
	  $error_message = array();
  }

  //Output any errors and die
  if(!empty($error_message)) {
	  print("\n==== Errors found in getting secrets ====\n");
	  foreach($error_message as $message) {
		  print($message);
	  }
	  die("Aborting!");
  }
  return $secrets;
}

/**
 * Send a notification to slack
 */
function _slack_notification($slack_url, $channel, $username, $text, $attachment, $alwaysShowText = false)
{
  $attachment['fallback'] = $text;
  $post = array(
    'username' => $username,
    'channel' => $channel,
    'icon_emoji' => ':lightning_cloud:',
    'attachments' => array($attachment)
  );
  if ($alwaysShowText) {
    $post['text'] = $text;
  }
  $payload = json_encode($post);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $slack_url);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  // Watch for messages with `terminus workflows watch --site=SITENAME`
  print("\n==== Posting to Slack ====\n");
  $result = curl_exec($ch);
  print("RESULT: $result");
  // $payload_pretty = json_encode($post,JSON_PRETTY_PRINT); // Uncomment to debug JSON
  // print("JSON: $payload_pretty"); // Uncomment to Debug JSON
  print("\n===== Post Complete! =====\n");
  curl_close($ch);
}
