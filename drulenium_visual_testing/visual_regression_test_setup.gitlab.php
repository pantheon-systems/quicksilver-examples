<?php
/**
 * This example enables the drulenium module when a code commit is pushed to development environment(s).
 *
 * This script should be configured into the sync_code operation in pantheon.yml
 */
// The sync_code may be triggered on any environment, but we only want
// to automatically enable the drulenium module when this event happens on dev
// or multidev environment.

if (isset($_POST['environment']) && !in_array($_POST['environment'], array('test', 'live'))) {
  // First, let's retrieve a list of disabled modules with drush pm-list.
  // shell_exec() will return the output of an executable as a string.
  // Pass the --format=json flag into the drush command so the output can be converted into an array with json_decode().
  $modules = json_decode(shell_exec('drush pm-list --format=json'));
  // Now let's enable drulenium_gitlab if it is installed and not already enabled.
  if (isset($modules->drulenium_gitlab) && $modules->drulenium_gitlab->status !== 'Enabled') {
    // This time let's just passthru() to run the drush command so the command output prints to the workflow log.
    passthru('drush en -y drulenium_gitlab');
  }
  passthru("drush vset --yes drulenium_vr_config_server_opt 'drulenium_gitlab'");
  $secrets = _get_secrets(array('gitlab_token', 'gitlab_project', 'drulenium_org_api_secret', 'drulenium_org_project_uuid'), $defaults = array('gitlab_url' => 'https://gitlab.com/api/v3/'));
  $gitlab_token = $secrets['gitlab_token'];
  $gitlab_project = $secrets['gitlab_project'];
  $drulenium_org_api_secret = $secrets['drulenium_org_api_secret'];
  $drulenium_org_project_uuid = $secrets['drulenium_org_project_uuid'];
  passthru("drush vset --yes gitlab_token '{$gitlab_token}'");
  passthru("drush vset --yes gitlab_project '{$gitlab_project}'");
  passthru("drush vset --yes drulenium_vr_api_secret '{$drulenium_org_api_secret}'");
  passthru("drush vset --yes drulenium_vr_project_uuid '{$drulenium_org_project_uuid}'");
}

/**
 * Get secrets from secrets file.
 *
 * @param array $requiredKeys  List of keys in secrets file that must exist.
 */
function _get_secrets($requiredKeys, $defaults)
{
  $secretsFile = $_SERVER['HOME'] . '/files/private/secrets.json';
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
