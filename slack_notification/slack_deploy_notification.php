<?php
// Find out what tag we are on and get the annotation.
$deploy_tag = `git describe --tags`;
$annotation = `git tag -l -n99 $deploy_tag`;
// Load our hidden credentials.
// See the README.md for instructions on storing secrets.
$secrets = json_decode(file_get_contents($_SERVER['HOME'] . '/files/private/secrets.json'), 1);
if ($secrets == FALSE) {
  die('No secrets file found. Aborting!');
}

isset ($secrets['slack_channel']) ? $channel = $secrets['slack_channel'] : $channel = '#quicksilver';

// Prepare the slack payload as per:
// https://api.slack.com/incoming-webhooks
$text = 'Deploy to the '. $_ENV['PANTHEON_ENVIRONMENT'];
$text .= ' environment of '. $_ENV['PANTHEON_SITE_NAME'] .' by '. $_POST['user_email'] .' complete!';
$text .= ' <https://dashboard.pantheon.io/sites/'. PANTHEON_SITE .'#'. PANTHEON_ENVIRONMENT .'/deploys|View Dashboard>';
$text .= "\n\n*DEPLOY MESSAGE*: $annotation";
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
    'value' => '<http://' . $_ENV['PANTHEON_ENVIRONMENT'] . '-' . $_ENV['PANTHEON_SITE_NAME'] . '.pantheon.io|' . $_ENV['PANTHEON_ENVIRONMENT'] . '>',
    'short' => 'true'
  ),
  array( // Render Name with link to Email from Commit message
    'title' => 'By',
    'value' => $_POST['user_email'],
    'short' => 'true'
  ),
  array(
    'title' => 'View Dashboard',
    'value' => '<https://dashboard.pantheon.io/sites/'. PANTHEON_SITE .'#'. PANTHEON_ENVIRONMENT .'/deploys|View Dashboard>',
    'short' => 'true'
  ),
  array(
    'title' => 'Deploy Message',
    'value' => $annotation,
    'short' => 'false'
  )
);
$attachment = array(
  'fallback' => $text,
  'pretext' => 'Deploying :rocket:',
  'color' => '#EFD01B', // Can either be one of 'good', 'warning', 'danger', or any hex color code, but this is Pantheon Yellow
  'fields' => $fields
);
$post = array(
  'username' => 'Pantheon-Quicksilver',
  // 'text' => $text, // Uncomment if you always want to send a text message, otherwise display attachment->fallback when needed
  'channel' => $channel,
  'icon_emoji' => ':lightning_cloud:',
  'attachments' => array($attachment)
);
$payload = json_encode($post);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $secrets['slack_url']);
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
