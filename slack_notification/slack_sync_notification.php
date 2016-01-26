<?php

require __DIR__ . '/slack_lib.inc';

// Get the committer, hash, and message for the most recent commit.
$committer = `git log -1 --pretty=%cn`;
$email = `git log -1 --pretty=%ce`;
$message = `git log -1 --pretty=%B`;
$hash = `git log -1 --pretty=%h`;

// Default values for parameters
$defaults = array(
  'slack_channel' => '#quicksilver',
  'slack_username' => 'Pantheon-Quicksilver',
  'always_show_text' => false,
);

// Load our hidden credentials.
// See the README.md for instructions on storing secrets.
$secrets = pantheon_quicksilver_get_secrets(array('slack_url'), $defaults);

// Prepare the slack payload as per:
// https://api.slack.com/incoming-webhooks
$text = 'Code sync to the ' . $_ENV['PANTHEON_ENVIRONMENT'] . ' environment of ' . $_ENV['PANTHEON_SITE_NAME'] . ' by ' . $_POST['user_email'] . "!\n";
$text .= 'Most recent commit: ' . rtrim($hash) . ' by ' . rtrim($committer) . ': ' . $message;
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
    'value' => '<mailto:' . $email . '|' . rtrim($committer) . '>',
    'short' => 'true'
  ),
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

$pantheon_yellow = '#EFD01B';
$attachment = array(
  'fallback' => $text,
  'pretext' => 'Code syncing :space_invader:',
  'color' => $pantheon_yellow, // Can either be one of 'good', 'warning', 'danger', or any hex color code
  'fields' => $fields
);

pantheon_quicksilver_slack($secrets['slack_url'], $secrets['slack_channel'], $secrets['slack_username'], $text, $attachment, $secrets['always_show_text']);
