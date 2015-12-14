<?php
# We want to return the type `text/plain; charset=UTF-8` so that we can store logs correctly
header('Content-Type: text/plain; charset=UTF-8');

// Don't ever santize the database on the live environment. Doing so would
// destroy the canonical version of the data.
if (defined('PANTHEON_ENVIRONMENT') && (PANTHEON_ENVIRONMENT !== 'live')) {
  // @todo add a check to ensure that this script is actually initiated through
  // quicksilver and not over the public web.
  // Bootstrap Drupal using the same code as is in index.php.
  define('DRUPAL_ROOT', getcwd());
  require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
  drupal_bootstrap(DRUPAL_BOOTSTRAP_DATABASE);
  // From http://crackingdrupal.com/blog/greggles/creating-sanitized-drupal-database-dump#comment-164
  db_query("UPDATE users SET mail = CONCAT(name, '@localhost'), init = CONCAT(name, '@localhost'), pass = MD5(CONCAT('MILDSECRET', name));");
}
