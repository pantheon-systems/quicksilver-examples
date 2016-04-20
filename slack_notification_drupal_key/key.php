<?php

/**
 * Get secrets from secrets file and Drupal Key if present
 *
 * @param array $requiredKeys  List of keys in secrets file that must exist.
 */
function key_get_secrets($requiredKeys, $defaults) {
	$secrets = array();
	$error_message = array();

	//Get our secrets.json file if it exists and pull in any values to the secrets array
  $secretsFile = $_SERVER['HOME'] . '/files/private/secrets.json';
  if (!file_exists($secretsFile)) {
    $error_message[] = 'No secrets file found. ';
  }
  else{
	  $secretsContents = file_get_contents($secretsFile);
	  $secrets = json_decode($secretsContents, 1);
	  if ($secrets == FALSE) {
	    $error_message[] = 'Could not parse json in secrets file. ';
	  }
  }

  $secrets += $defaults;

  $missing = array_diff($requiredKeys, array_keys($secrets));
  if (!empty($missing)) {
	  foreach($missing as $key) {
		  print("\n==== Missing the key $key in the secrets.json so looking for it in Drush ====\n");
		  $key_value = exec("drush key-get-key " . $key);
		  if($key_value != 'Sorry no key found by that name') {
			  $secrets[$key] = $key_value;
		  }
	  }
  }
  $missing = array_diff($requiredKeys, array_keys($secrets));
  if (!empty($missing)) {
    $error_message[] = 'Missing required keys in json secrets file: ' . implode(',', $missing) . '. ';
  }
  else{
	  //Clear out any errors as we have all the required keys
	  $error_message = array();
  }

  //Output any errors and die
  if(!empty($error_message)) {
	  print("\n==== Errors found in getting secrets ====\n");
	  foreach($error_message as $message) {
		  print($message);
	  }
	  die("Aborting!");
  }
  return $secrets;
}
