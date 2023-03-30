<?php

// Load a secrets file. 
// See the included example.secrets.json and instructions in README.
$secrets = _get_secrets('secrets.json');

//Create curl post request to hit the Jenkins webhook
$curl = curl_init($secrets['jenkins_url']);

//Setup header with authentication
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
    'Authorization: Basic '. base64_encode($secrets['username'] . ":" . $secrets['api_token']),
));

//Declare request as a post and setup the fields
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, array(
  'token' => $secrets->token,
));

//Execute the request 
$response = curl_exec($curl);

// TODO: could produce some richer responses here. 
// Could even chain this to a slack notification. It's up to you! 
if ($response) {
	echo "Build Queued";
}
else {
	echo "Build Failed";
}


/**
 * Get secrets from secrets file.
 *
 * @param string $file path within files/private that has your json
 */
function _get_secrets($file)
{
  $secrets_file = $_SERVER['HOME'] . '/files/private/' . $file;
  if (!file_exists($secrets_file)) {
    die('No secrets file found. Aborting!');
  }
  $secrets_json = file_get_contents($secrets_file);
  $secrets = json_decode($secrets_json, 1);
  if ($secrets == false) {
    die('Could not parse json in secrets file. Aborting!');
  }
  return $secrets;
}
