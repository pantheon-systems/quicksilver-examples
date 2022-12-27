<?php 
error_reporting(-1);

require('twilio-php/Services/Twilio.php'); // load twilio library
$account_sid = '################################'; // Twilio account sid
$auth_token = '################################'; // Twilio auth token
$client = new Services_Twilio($account_sid, $auth_token); 

/*
To = Recipient cellphone number
From = Twilio account number
Body = Commit message, git pretty format placeholders are here https://git-scm.com/docs/pretty-formats
*/

$client->account->messages->create(array( 
// insert recipient cellphone number
	'To' => "+15555555555", 
// insert twilio number
	'From' => "+15555555555", 
// commit message
	'Body' => "\nLatest commit to {$_ENV['PANTHEON_SITE_NAME']}:\n" . `git log -1 --pretty=%an%n%h%n%B`,
));
