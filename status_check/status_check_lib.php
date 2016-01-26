<?php

/**
 * Initialization
 */
function status_check_init() {
  // Only run status check on live deployments
  if ($_ENV['PANTHEON_ENVIRONMENT'] != 'live') {
    die();
  }
}

/**
 * Returns decoded config defined in config.json
 */
function status_check_get_config() {
  return json_decode(file_get_contents('./config.json'), 1);
}

/**
 * Prints workflow output
 */
function status_check_get_output($results, $failed) {
  $output = "\nStatus Checks\n--------\n";
  foreach ($results as $item) {
    $output .= '  ' . $item['status'] . ' - ' . $item['path'] . "\n";
  }
  $output .= "--------\n" . count($failed) . " failed\n\n";
  return $output;
}
