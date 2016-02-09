<?php

require __DIR__ . '/hipchat_notification.inc';

// Get the hash and message of the most recent commit.
$message = `git log -1 --pretty=%B`;
$hash = trim(`git log -1 --pretty=%h`);

// Default values for parameters
$defaults = array(
  'hipchat_room_id' => 'quicksilver'
);

// Load our hidden credentials.
// See the README.md for instructions on storing secrets.
$secrets = hipchat_notification_get_secrets(array('hipchat_auth_token'), $defaults);

// Prepare the message
$url = 'https://dashboard.pantheon.io/sites/'. PANTHEON_SITE .'#'. PANTHEON_ENVIRONMENT .'/code';
$text = '<b>' . $_POST['user_fullname'] . '</b> committed to
<a href="' . $url . '">' . $_ENV['PANTHEON_SITE_NAME'] . '</a><br />
<b>On branch "' . PANTHEON_ENVIRONMENT . '"</b><br />
- ' . htmlentities($message) . ' (<a href="' . $url . '">' . $hash . '</a>)';

hipchat_notification_send($secrets['hipchat_room_id'], $secrets['hipchat_auth_token'], $text);
