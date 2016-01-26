<?php

require './status_check_lib.php';
status_check_init();

define('DRUPAL_ROOT', $_SERVER['DOCUMENT_ROOT']);
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_DATABASE);

$config = status_check_get_config();
if (!$config) {
  die('Config not found.');
}

$failed = 0;
$results = array();

foreach ($config['check_paths'] as $path) {
  $response = drupal_http_request($config['base_url'] . $path);
  $status = $response['code'];
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
