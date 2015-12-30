<?php

//Create curl post request to hit the Jenkins webhook
$secrets = json_decode(file_get_contents($_SERVER['HOME'] . '/files/private/secrets.json'));

$curl = curl_init($secrets->jenkins_url);

//Setup header with authentication
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
    'Authorization: Basic '. base64_encode("$secrets->username:$secrets->api_token"),
));

//Declare request as a post and setup the fields
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, array(
  'token' => $secrets->token,
));

//Execute the request (could store response if necessary)
curl_exec($curl);