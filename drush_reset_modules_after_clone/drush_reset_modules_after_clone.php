
<?php
/**
 * This uninstalls the configuration read-only module when a database is cloned to a dev environment.
 * if you don't uninstall the module, your config will be read-only on a dev site after a clone from live or test
 *
 * This script should be instructed to run after the clone_database operation in pantheon.yml
 */
// The clone_database may be triggered on any environment, but we only want
// to automatically disable the configuration read-only module when this event happens in a dev
// or multidev environment.
if (isset($_POST['environment']) && !in_array($_POST['environment'], array('test', 'live'))) {
  // Retrieve a list of modules with drush pm-list.
  // shell_exec() will return the output of an executable as a string.
  // Pass the --format=json flag into the drush command so the output can be converted into an array with json_decode().
  $modules = json_decode(shell_exec('drush pm-list --format=json'));
  // uninstall config_readonly if it is installed and enabled.
  if (isset($modules->config_readonly) && $modules->config_readonly->status == 'Enabled') {
    // This time let's just passthru() to run the drush command so the command output prints to the workflow log.
    passthru('drush pm-uninstall -y config_readonly');
  }
}