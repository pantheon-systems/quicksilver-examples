<?php

// List of the parameters we need.
$required_parameters = array('circle_username', 'circle_project', 'circle_build');

// Load our parameters file.
// We will allow the user to store parameters in 'files/private' (not committed), or
// next to this script (committed to git).
$parameter_files = array(
  $_SERVER['HOME'] . '/files/private/circle-retry-parameters.json',
  $_SERVER['HOME'] . '/files/private/parameters.json',
  __DIR__ . '/circle-retry-parameters.json',
  __DIR__ . '/parameters.json',
);
$parameters = array();
foreach ($parameter_files as $circle_parameters_file) {
  if (file_exists($circle_parameters_file)) {
    $parameter_contents = file_get_contents($circle_parameters_file);
    $one_set_of_parameters = json_decode($parameter_contents, 1);
    if ($one_set_of_parameters) {
      $parameters += $one_set_of_parameters;
    }
    else {
      echo "Could not parse parameter file $circle_parameters_file:\n$parameter_contents\n";
    }
  }
}

// Load our hidden credentials.
// See the README.md for instructions on storing secrets.
$secrets = json_decode(file_get_contents($_SERVER['HOME'] . '/files/private/secrets.json'), 1);
if ($secrets == FALSE) {
  die('No secrets file found. Aborting!');
}
if (!array_key_exists('circle_token', $secrets)) {
  die('Secrets file does not contain a circle_token. Aborting!');
}

// Let folks mix parameters into their secrets file, if they want.
// Parameters in secrets take precidence.
$parameters = $secrets + $parameters;

// Confirm that we have all of the parmeters that we need.
foreach ($required_parameters as $key) {
  if (!array_key_exists($key, $parameters)) {
    die("Required parameter '$key' not provided in parameters. Aborting!");
  }
}

// Copy secret and relevant parameters to variables.
$circle_token = $secrets['circle_token'];
$username = $parameters['circle_username'];
$project = $parameters['circle_project'];
$build = $parameters['circle_build'];

// URL to POST to Circle CI to retry a build
$circle_url = "https://circleci.com/api/v1/project/$username/$project/$build/retry?circle-token=$circle_token";

// Send the request
$post = array();
$payload = json_encode($post);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $circle_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
print("\n==== Posting to Circle CI ====\n");
$result = curl_exec($ch);
print("RESULT: $result");
print("\n===== Post Complete! =====\n");
curl_close($ch);
