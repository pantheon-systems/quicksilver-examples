<?php

require './url_checker_lib.php';
url_checker_init();

require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
global $wpdb;

$config = url_checker_get_config();
if (!$config) {
  die('Config not found.');
}

$failed = 0;
$results = array();

foreach ($config['check_paths'] as $path) {
  $response = wp_remote_get($config['base_url'] . $path);
  $status = $response['response']['code'];
  $results[] = array(
    'url' => $config['base_url'] . $path,
    'status' => $status
  );
  if ($status != 200) {
    $failed++;
  }
}

$output = url_checker_get_output($results, $failed);
print $output;

if ($failed > 0) {
  $subject = 'Failed status check (' . $failed . ')';
  $message = "Below is a list of each tested url and its status:\n\n";
  $message .= $output;
  wp_mail($config['email'], $subject, $message);
}
