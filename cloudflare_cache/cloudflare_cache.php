<?php
// TODO: this header should not be required...
header('Content-Type: text/plain; charset=UTF-8');

// Only purge Cloudflare cache when the live environment's cache is cleared.
if ($_ENV['PANTHEON_ENVIRONMENT'] != 'live') {
  die();
}

// Retrieve Cloudflare config data
$config_file = $_SERVER['HOME'] . '/files/private/cloudflare_cache.json';
$config = json_decode(file_get_contents($_SERVER['HOME'] . '/files/private/cloudflare_cache.json'), 1);
if ($config == FALSE) {
  die('files/private/cloudflare_cache.json found. Aborting!');
}

purge_cache($config);

function purge_cache($config) {
  $payload = json_encode(array('purge_everything' => TRUE));
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://api.cloudflare.com/client/v4/zones/' . $config['zone_id'] . '/purge_cache');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'X-Auth-Email: ' . $config['email'],
    'X-Auth-Key: ' . $config['api_key'],
    'Content-Type: application/json'
  ));
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  print("\n==== Sending Request to Cloudflare ====\n");
  $result = curl_exec($ch);
  print("RESULT: $result");
  print("\n===== Request Complete! =====\n");
  curl_close($ch);
}

