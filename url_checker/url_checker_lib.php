<?php

/**
 * Initialization
 */
function url_checker_init() {
  // Only run status check on live deployments
  if ($_ENV['PANTHEON_ENVIRONMENT'] != 'live') {
    die();
  }
}

/**
 * Returns decoded config defined in config.json
 */
function url_checker_get_config() {
  return json_decode(file_get_contents('./config.json'), 1);
}

/**
 * Prints workflow output
 */
function url_checker_get_output($results, $failed) {
  $output = "\nURL Checks\n--------\n";
  foreach ($results as $item) {
    $output .= '  ' . $item['status'] . ' - ' . $item['url'] . "\n";
  }
  $output .= "--------\n" . count($failed) . " failed\n\n";
  return $output;
}

/**
 * Generic status checker
 */
function url_checker_get_status_code($url) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_exec($ch);
  $info = curl_getinfo($ch);
  curl_close($ch);
  return $info['http_code'];
}
