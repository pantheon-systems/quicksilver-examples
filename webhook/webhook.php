<?php
// TODO: this header should not be required...
header('Content-Type: text/plain; charset=UTF-8');

// Retrieve webhook config data
$config = json_decode(file_get_contents($_SERVER['HOME'] . '/files/private/webhook.json'), 1);
if ($config == FALSE) {
  die('files/private/webhook.json found. Aborting!');
}

send_data($config);

function send_data($config) {
  $payload = json_encode(array('payload' => $_POST, 'env' => $_ENV));
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $config['url']);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'X-Auth-Key: ' . isset($config['api_key']) ? $config['api_key'] : '',
    'Content-Type: application/json'
  ));
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

  print("\n==== Posting to Webhook URL ====\n");
  $result = curl_exec($ch);
  print("RESULT: $result");
  print("\n===== Post Complete! =====\n");
  curl_close($ch);
}
