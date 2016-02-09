<?php
require './url_checker_lib.php';
url_checker_init();

define('DRUPAL_ROOT', $_SERVER['DOCUMENT_ROOT']);
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_DATABASE);

$config = url_checker_get_config();
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

$output = url_checker_get_output($results, $failed);
print $output;

if ($failed > 0) {
  $body = "Below is a list of each tested url and its status:\n\n";
  $body .= $output;
  $message = array(
    'to' => $config['email'],
    'subject' => 'Failed status check (' . $failed . ')',
    'body' => $body
  );
  drupal_mail_send($message);
}
