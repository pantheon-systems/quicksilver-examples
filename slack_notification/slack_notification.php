<?php
header('Content-Type: text/plain; charset=UTF-8');

// Find out what tag we are on and get the annotation.
$deploy_tag = `git describe --tags`;
$annotation = `git tag -l -n99 $deploy_tag`;

// Load our hidden credentials.
// See the README.md for instructions on storing secrets.
$secrets = json_decode(file_get_contents($_SERVER['HOME'] . '/files/private/secrets.json'), 1)

$text = 'Deploy to the '. PANTHEON_ENVIRONMENT;
$text .= '  of '. PANTHEON_SITE_NAME .' by '. $_POST['user_email'] .' complete!';
$text .= ' <https://dashboard.pantheon.io/sites/'. PANTHEON_SITE .'#'. PANTHEON_ENVIRONMENT .'/deploys|View Dashboard>';
$text .= "\n\nDEPLOY MESSAGE: $annotation";

$post = array(
  'text' => $text,
  'channel' => 'quicksilver',
  'icon_emoji' => ':lightning_cloud:'
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $secrets['slack_url']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
print("\n==== Posting to Slack ====\n");
$result = curl_exec($ch);
print($result);
print("\n===== Post Complete! =====\n");
curl_close($ch);