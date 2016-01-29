<?php
// TODO: this header should not be required...
header('Content-Type: text/plain; charset=UTF-8');

// Retrieve webhook config data
$config_file = $_SERVER['HOME'] . '/files/private/webhook.json';
if (!file_exists($config_file)) {
  die('files/private/webhook.json found. Aborting!');
}
$config = json_decode(file_get_contents($config_file), 1);
if (!isset($config['url']) || !isset($config[''])) {
  die('Config file must contain a url and api_key. Aborting!');
}

send_data($config);

function send_data($config) {
  $payload = $_POST;
  $payload['site_name'] = $_ENV['PANTHEON_SITE_NAME'];
  $payload = http_build_query(array('payload' => $payload));
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, str_replace(':api_key', $config['api_key'], $config['url']));
  curl_setopt($ch,CURLOPT_POST, 1);
  curl_setopt($ch,CURLOPT_POSTFIELDS, $payload);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  print("\n==== Posting to Webhook URL ====\n");
  $result = curl_exec($ch);
  print("RESULT: $result");
  print("\n===== Post Complete! =====\n");
  curl_close($ch);
}
