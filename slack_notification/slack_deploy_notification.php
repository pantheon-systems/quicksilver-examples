<?php

require __DIR__ . '/slack_lib.inc';

// Find out what tag we are on and get the annotation.
$deploy_tag = `git describe --tags`;
$annotation = `git tag -l -n99 $deploy_tag`;

// Default values for parameters
$defaults = array(
  'slack_channel' => '#quicksilver',
  'slack_username' => 'Pantheon-Quicksilver',
  'always_show_text' => false,
);

// Load our hidden credentials.
// See the README.md for instructions on storing secrets.
$secrets = pantheon_quicksilver_get_secrets(array('slack_url'), $defaults);

$channel = $secrets['slack_channel'];
$username = $secrets['slack_username'];

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
$pantheon_yellow = '#EFD01B';
$attachment = array(
  'fallback' => $text,
  'pretext' => 'Deploying :rocket:',
  'color' => $pantheon_yellow, // Can either be one of 'good', 'warning', 'danger', or any hex color code
  'fields' => $fields
);

pantheon_quicksilver_slack($secrets['slack_url'], $secrets['slack_channel'], $secrets['slack_username'], $text, $attachment, $secrets['always_show_text']);
