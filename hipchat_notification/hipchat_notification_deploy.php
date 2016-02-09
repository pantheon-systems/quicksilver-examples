<?php

require __DIR__ . '/hipchat_notification.inc';

// Find out what tag we are on and get the annotation.
$deploy_tag = `git describe --tags`;
$annotation = str_replace(" ''", '', `git tag -l -n99 $deploy_tag`);

// Default values for parameters
$defaults = array(
  'hipchat_room_id' => 'quicksilver'
);

// Load our hidden credentials.
// See the README.md for instructions on storing secrets.
$secrets = hipchat_notification_get_secrets(array('hipchat_auth_token'), $defaults);

// Prepare the message
$url = 'https://dashboard.pantheon.io/sites/'. PANTHEON_SITE .'#'. PANTHEON_ENVIRONMENT .'/deploy';
$text = '<b>' . $_POST['user_fullname'] . '</b> deployed
<a href="' . $url . '">' . $_ENV['PANTHEON_SITE_NAME'] . '</a><br />
<b>On branch "' . PANTHEON_ENVIRONMENT . '"</b><br />
Deploy Message: ' . htmlentities($annotation);

hipchat_notification_send($secrets['hipchat_room_id'], $secrets['hipchat_auth_token'], $text);
