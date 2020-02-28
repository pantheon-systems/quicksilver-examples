<?php

include_once 'get_secrets.inc';

// Important constants :)
$pantheon_yellow = '#EFD01B';
// Default values for parameters - this will assume the channel you define the webhook for.
// The full Slack Message API allows you to specify other channels and enhance the messagge further
// if you like: https://api.slack.com/docs/messages/builder
$defaults = array(
  'slack_username' => 'Pantheon-Quicksilver',
  'always_show_text' => false
);

if (empty($secrets['slack_url'])) {
  $secrets = _get_secrets(array('username', 'password', 'slack_url', 'slack_channel'), $defaults);
}
else {
  $secrets = array_merge($secrets, $defaults);
}

if (empty($action)) {
  $action = 'finish';
}

if (empty($deploy_text)) {
  $deploy_text = '';
}

// Ensure the channel starts with #.
if (substr($secrets['slack_channel'], 0, 1) != '#') {
  $secrets['slack_channel'] = '#' . $secrets['slack_channel'];
}

// Add the user_email to the ENV, if set.
if (!empty($_POST['user_email']) && empty($_ENV['USER_EMAIL'])) {
  putenv('USER_EMAIL=' . $_POST['user_email']);
}
$_ENV['USER_EMAIL'] = getenv('USER_EMAIL');

// Build an array of fields to be rendered with Slack Attachments as a table
// attachment-style formatting:
// https://api.slack.com/docs/attachments
$fields = array(
  array(
    'title' => 'Site',
    'value' => $_ENV['PANTHEON_SITE_NAME'],
    'short' => true,
  ),
  // Render Environment name with link to site, <http://{ENV}-{SITENAME}.pantheon.io|{ENV}>.
  array(
    'title' => 'Environment',
    'value' => '<http://' . $_ENV['PANTHEON_ENVIRONMENT'] . '-' . $_ENV['PANTHEON_SITE_NAME'] . '.pantheonsite.io|' . $_ENV['PANTHEON_ENVIRONMENT'] . '>',
    'short' => true,
  ),
  // Render Name with link to Email from Commit message.
  array(
    'title' => 'By',
    'value' => $_ENV['USER_EMAIL'],
    'short' => false,
  ),
);

// Find out what tag we are on and get the annotation.
$deploy_tag = `git describe --tags`;
// Prepare the slack payload as per:
// https://api.slack.com/incoming-webhooks
$text = ucwords($action) . ' deploy to external server by ' . $_ENV['USER_EMAIL'] . ' completed!';
// Build an array of fields to be rendered with Slack Attachments as a table
// attachment-style formatting:
// https://api.slack.com/docs/attachments
$fields[] = array(
  'title' => 'Details',
  'value' => $text . "\n" . $deploy_text,
  'short' => false,
);
$attachment = array(
  'fallback' => $text,
  'pretext' => 'Deploying :rocket:',
  'color' => $pantheon_yellow, // Can either be one of 'good', 'warning', 'danger', or any hex color code
  'fields' => $fields,
);

_slack_notification($secrets['slack_url'], $secrets['slack_channel'], $secrets['slack_username'], $text, $attachment, $secrets['always_show_text']);


/**
 * Send a notification to slack
 */
function _slack_notification($slack_url, $channel, $username, $text, $attachment, $alwaysShowText = FALSE) {
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
