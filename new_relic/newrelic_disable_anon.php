<?php
/*
 * Disable New Relic for anonymous users.
 *
 * This scripts disables New Relic data collection for anonymous users
 * except in the case that such users are submitting a form.
 *
 * See New Relic's PHP Agent API for more options:
 * https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api
 **/
if (function_exists('newrelic_ignore_transaction')) {
  $skip_new_relic = TRUE;
  // Capture all transactions for users with a PHP session.
  foreach (array_keys($_COOKIE) as $cookie) {
    if (substr($cookie, 0, 4) == 'SESS') {
      $skip_new_relic = FALSE;
    }
  }
  // Capture all POST requests, including anonymous form submissions.
    if (isset($_SERVER['REQUEST_METHOD']) &&
      $_SERVER['REQUEST_METHOD'] == 'POST') {
        $skip_new_relic = FALSE;
      }
  if ($skip_new_relic) {
    newrelic_ignore_transaction();
  }
}
