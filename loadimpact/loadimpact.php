<?php

// An example of usign Pantheon's Quicksilver technology to do 
// a performance test using Load Impact

// Provide the API Key provided by Load Impact
// For extra security, you can store this information in
// the private area of the files directory as documented
// at https://github.com/pantheon-systems/quicksilver-examples.
$api_key = 'add-api-key-here';

// Provide the Test ID for the performance test on Loadimpact.com
$test_id = 'add-test-id-here';

// If we are deploying to test, run a performance test on that environment
// The specifics of the test will be defined on Loadimpact.com
  echo 'Starting a performance test on the test environment...' . "\n";
  $curl = curl_init();
  $curl_options = array(
    CURLOPT_URL => 'https://api.loadimpact.com/v2/test-configs/' . $test_id . '/start',
    CURLOPT_USERPWD => $api_key . ':',
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_POST => 1,
  );
  curl_setopt_array($curl, $curl_options);
  $curl_response = json_decode(curl_exec($curl));
  curl_close($curl);
 
  if (isset($curl_response->id)) {
    echo 'You have kicked off test #' . $curl_response->id . "\n";
    $curl_two = curl_init();
    $curl_two_options = array(
      CURLOPT_URL => 'https://api.loadimpact.com/v2/tests/' . $test_id,
      CURLOPT_USERPWD => $api_key . ':',
      CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
      CURLOPT_RETURNTRANSFER => 1,
    ); 
    curl_setopt_array($curl_two, $curl_two_options);
    $curl_two_response = json_decode(curl_exec($curl_two));
    curl_close($curl_two);
    echo "Check out the result here: " . $curl_two_response->public_url . "\n";
  }
  else {
    echo 'There has been an error: ' . ucwords($curl_response->message) . "\n";
  }