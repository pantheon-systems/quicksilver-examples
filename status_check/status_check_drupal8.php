<?php

require './status_check_lib.php';
status_check_init();

require '../../../vendor/autoload.php';
use GuzzleHttp\Client;

$config = status_check_get_config();
if (!$config) {
  die('Config not found.');
}

$client = new GuzzleHttp\Client(['base_uri' => $config['base_url']]);
$failed = 0;
$results = array();

foreach ($config['check_paths'] as $path) {
  $response = $client->get($path, ['exceptions' => false]);
  $status = $response->getStatusCode();
  $results[] = array(
    'url' => $config['base_url'] . $path,
    'status' => $status
  );
  if ($status != 200) {
    $failed++;
  }
}

$output = status_check_get_output($results, $failed);
print $output;

if ($failed > 0) {
  $subject = 'Failed status check (' . $failed . ')';
  $message = "Below is a list of each tested url and its status:\n\n";
  $message .= $output;
  mail($config['email'], $subject, $message);
}
