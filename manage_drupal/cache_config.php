<?php
/**
 * Enable caching when deployed to the live environment.
 */
if ($_ENV['PANTHEON_ENVIRONMENT'] == 'live') {
  // Bootstrap Drupal.
  define('DRUPAL_ROOT', $_SERVER['DOCUMENT_ROOT']);
  require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
  drupal_bootstrap(DRUPAL_BOOTSTRAP_DATABASE);

  $enable = array(
    'page_cache_maximum_age' => 900,
    'cache_lifetime' => 0,
    'block_cache' => 1,
    'cache' => 1,
  );

  foreach ($enable as $var => $setting) {
    if (!is_numeric($var)) {
      db_merge('variable')->key(array('name' => $var))
                          ->fields(array('value' => serialize($setting)))
                          ->execute();
    }
  }
}
