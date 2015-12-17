<?php
// Get the committer, hash, and message for the most recent commit.
$committer = `git log -1 --pretty=%cn`;
$message = `git log -1 --pretty=%B`;
$hash = `git log -1 --pretty=%h`;
// Load our hidden credentials.
// See the README.md for instructions on storing secrets.
$secrets = json_decode(file_get_contents($_SERVER['HOME'] . '/files/private/secrets.json'), 1);
if ($secrets == FALSE) {
  die('No secrets file found. Aborting!');
}

isset ($secrets['slack_channel']) ? $channel = $secrets['slack_channel'] : $channel = '#quicksilver';

// Prepare the slack payload as per:
// https://api.slack.com/incoming-webhooks
// TODO: use awesome attachment-style formatting.
// https://api.slack.com/docs/attachments
$text = 'Code sync to the ' . $_ENV['PANTHEON_ENVIRONMENT'] . ' environment of ' . $_ENV['PANTHEON_SITE_NAME'] . ' by ' . $_POST['user_email'] . "!\n";
$text .= 'Most recent commit: ' . rtrim($hash) . ' by ' . rtrim($committer) . ': ' . $message;
$post = array(
  'username' => 'Pantheon-Quicksilver',
  'text' => $text,
  'channel' => $channel,
  'icon_emoji' => ':lightning_cloud:'
);
$payload = json_encode($post);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $secrets['slack_url']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
print("\n==== Posting to Slack ====\n");
$result = curl_exec($ch);
print("RESULT: $result");
print("\n===== Post Complete! =====\n");
curl_close($ch);
