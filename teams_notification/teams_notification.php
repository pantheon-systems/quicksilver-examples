<?php

// Default values for parameters if needed
$defaults = array();

// Load our hidden credentials.
// See the README.md for instructions on storing secrets.
$secrets = _get_secrets(array('teams_url'), $defaults);

$params = [
  'USERMAIL' => $_POST['user_email'],
  'USERMAIL_HASH' =>  md5(strtolower(trim($_POST['user_email']))),
  'USERNAME' =>  $_POST['user_fullname'],
  'ENV' => $_ENV['PANTHEON_ENVIRONMENT'],
  'PROJECT_NAME' => $_ENV['PANTHEON_SITE_NAME']
];

switch($_POST['wf_type']) {
  case 'deploy':

    // Find out what tag we are on and last commit date
    $deploy_tag= `git describe --tags`;

    // [TODO] needs to be more accurate with exact date of the creation of tag and not last commit date
    $deploy_date = `git log -1 --format=%ai `;

    // Set additional parameters for deploy case
    $params += [
      'DEPLOY_NOTE' => $_POST['deploy_message'],
      'DEPLOY_TAG' => $deploy_tag,
      'DEPLOY_LOG_URL' => 'https://dashboard.pantheon.io/sites/' . $_ENV['PANTHEON_SITE'] . '#'. strtolower($_ENV['PANTHEON_ENVIRONMENT']) .'/deploys',
      'ENV_URL' => 'https://' . $_ENV['PANTHEON_ENVIRONMENT'] . '-' . $_ENV['PANTHEON_SITE_NAME'] . '.pantheonsite.io'
    ];

    // Get the "deploy" message template
    $message = file_get_contents("samples/deploy_msg.json");

    break;

  case 'sync_code':
    $committer = `git log -1 --pretty=%cn`;
    $email = `git log -1 --pretty=%ce`;
    $message = `git log -1 --pretty=%B`;
    $hash = `git log -1 --pretty=%h`;

    $text = 'Most recent commit: '. rtrim($hash) . ' by ' . rtrim($committer) . ' (' . $email . '): ' . $message;

    $params += [
      'MESSAGE' => $text
    ];

    // Get the "sync code" message template
    $message = file_get_contents("samples/sync_code_msg.json");

    break;

  // [TODO] not working for now
  case 'clear_cache':
  // Get the "clear cache" message template
    $message = file_get_contents("samples/clear_cache_msg.json");

    break;

  default:
    //$text = "Workflow $workflow_description<br />" . $_POST['qs_description'];
    break;

}

$message = preg_replace_callback('/{{((?:[^}]|}[^}])+)}}/', function($match) use ($params) { return ($params[$match[1]]); }, $message);

_teams_notification($secrets['teams_url'],$message);

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
  if ($secrets == false) {
    die('Could not parse json in secrets file. Aborting!');
  }
  $secrets += $defaults;
  $missing = array_diff($requiredKeys, array_keys($secrets));
  if (!empty($missing)) {
    die('Missing required keys in json secrets file: ' . implode(',', $missing) . '. Aborting!');
  }
  return $secrets;
}


/**
* Send notifications to Microsoft Teams
*/
function _teams_notification($teams_url,$message){

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $teams_url);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($ch, CURLOPT_POSTFIELDS, $message);

  // Watch for messages with `terminus workflows watch --site=SITENAME`
  print("\n==== Posting to Teams ====\n");
  $result = curl_exec($ch);
  print("RESULT: $result");
  print("MESSAGE SENT: $message");
  print("\n===== Post Complete! =====\n");
  curl_close($ch);
}
