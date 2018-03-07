<?php

/**
 * Get secrets from secrets file.
 *
 * @param array $requiredKeys
 *   List of keys in secrets file that must exist.
 * @param array $defaults
 *   List of default values.
 */
function _get_secrets($requiredKeys, $defaults = array()) {
  if (empty($_ENV['PANTHEON_ENVIRONMENT'])) {
    $secretsFile = 'private/secrets.json';
  }
  else {
    $secretsFile = $_SERVER['HOME'] . '/files/private/secrets.json';
  }
  if (!file_exists($secretsFile)) {
    die('No secrets file found. Aborting!');
  }
  $secretsContents = file_get_contents($secretsFile);
  $secrets = json_decode($secretsContents, 1);
  if ($secrets == FALSE) {
    die('Could not parse json in secrets file. Aborting!');
  }
  $secrets += $defaults;
  $missing = array_diff($requiredKeys, array_keys($secrets));
  if (!empty($missing)) {
    die('Missing required keys in json secrets file: ' . implode(',', $missing) . '. Aborting!');
  }
  return $secrets;
}
