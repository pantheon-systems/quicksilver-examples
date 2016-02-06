<?php
// Don't ever santize the database on the live environment. Doing so would
// destroy the canonical version of the data.
if (defined('PANTHEON_ENVIRONMENT') && (PANTHEON_ENVIRONMENT !== 'live')) {
  // Bootstrap Drupal using the same code as is in index.php.

  require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
  global $wpdb;
  // Adapted from rom http://crackingdrupal.com/blog/greggles/creating-sanitized-drupal-database-dump#comment-164
  $wpdb->query("UPDATE wp_users SET user_email = CONCAT(user_login, '@localhost'), user_pass = MD5(CONCAT('MILDSECRET', user_login)), user_activation_key = '';");
}
