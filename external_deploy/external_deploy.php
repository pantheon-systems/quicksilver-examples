<?php

include_once 'get_secrets.inc';

$env = 'test';
if (isset($_ENV['PANTHEON_ENVIRONMENT']) && $_ENV['PANTHEON_ENVIRONMENT'] == 'live') {
  $env = 'live';
}

if ($env != 'live') {
  print "only need to git deploy for live deploy\n";
  return;
}

// Load our hidden credentials.
// See the README.md for instructions on storing secrets.
$secrets = _get_secrets(array('username', 'password'));

$cwd = getcwd();
// If we're running from private/scripts, set current dir down to root.
if (strpos($cwd, 'private') !== FALSE && strpos($cwd, 'scripts') !== FALSE) {
  chdir('../../');
}

$spawned = getenv('DEPLOY_SPAWNED');
if (empty($spawned)) {
  putenv('DEPLOY_SPAWNED=1');
  // Re-run this in the background, providing more time to deploy and notify.
  exec_in_background('php private/scripts/external_deploy.php');
}
else {
  $command = '';
  if ($env == 'live') {
    $command = 'php phploy --debug -s live';
  }

  // Run the actual deploy if we have the commands set.
  if ($command) {
    putenv('PHPLOY_USER=' . trim($secrets['username']));
    putenv('PHPLOY_PASS=' . trim($secrets['password']));
    $output = array();
    exec($command, $output);
    // Send slack notification after git deploy completion.
    $action = 'finish';

    // Post the results to slack.
    if (!empty($secrets['slack_url']) && !empty($secrets['slack_channel'])) {
      // Add the returned values from phploy to the notification.
      $deploy_text = implode("\n", $output);

      include_once 'slack_notification.php';
    }
  }
}

/**
 * Execute a command in the background.
 *
 * @param string $cmd
 *   The command to execute.
 */
function exec_in_background($cmd) {
  if (substr(php_uname(), 0, 7) == "Windows") {
    pclose(popen("start /B ". $cmd, "r"));
  }
  else {
    exec($cmd . " > /dev/null &");
  }
}
