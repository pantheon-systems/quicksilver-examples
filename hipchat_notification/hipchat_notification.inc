<?php

/**
 * Get secrets from secrets file.
 *
 * @param array $required_keys  List of keys in secrets file that must exist.
 */
function hipchat_notification_get_secrets($required_keys, $defaults) {
  $secrets_file = $_SERVER['HOME'] . '/files/private/secrets.json';
  if (!file_exists($secrets_file)) {
    print $secrets_file . "\n";
    die('No secrets file found. Aborting!');
  }
  $secrets_contents = file_get_contents($secrets_file);
  $secrets = json_decode($secrets_contents, 1);
  if ($secrets == FALSE) {
    die('Could not parse json in secrets file. Aborting!');
  }
  $secrets += $defaults;
  $missing = array_diff($required_keys, array_keys($secrets));
  if (!empty($missing)) {
    die('Missing required keys in json secrets file: ' . implode(',', $missing) . '. Aborting!');
  }
  return $secrets;
}

/**
 * Send a notification to hipchat
 */
function hipchat_notification_send($room_id, $auth_token, $text) {
  $data = array('color' => 'yellow', 'message' => $text);
  $payload = json_encode($data);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://api.hipchat.com/v2/room/' . $room_id . '/notification');
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $auth_token
  ));
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

  // Uncomment this section for debug
  /*
  print("\n==== Begin Debug Data ====\n");
  print "Room ID: " . $room_id . "\n";
  print "Auth Token: " . $auth_token . "\n";
  print "Payload: \n";
  print_r($data);
  print("\n==== End Debug Data ====\n");
  */

  // Watch for messages with `terminus workflows watch --site=SITENAME`
  print("\n==== Posting to Hipchat ====\n");
  $result = curl_exec($ch);
  print("RESULT: $result");
  print("\n===== Post Complete! =====\n");
  curl_close($ch);
}
