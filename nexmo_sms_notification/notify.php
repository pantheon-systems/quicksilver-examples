<?php

require_once __DIR__ . '/vendor/autoload.php';

// See the README.md for instructions on storing secrets.
$defaults = array();
$secrets = _get_secrets(array('primary_mobile_number', 'nexmo_api_key', 'nexmo_api_secret'), $defaults);
$mobile_number = $secrets['primary_mobile_number'];
$workflow_description = ucfirst($_POST['stage']) . ' ' . str_replace('_', ' ', $_POST['wf_type']);

$basic  = new \Nexmo\Client\Credentials\Basic($secrets['nexmo_api_key'], $secrets['nexmo_api_secret']);
$client = new \Nexmo\Client($basic);


switch($_POST['wf_type']) {
    case 'deploy':
      // Find out what tag we are on and get the annotation.
      $deploy_tag = `git describe --tags`;
      $deploy_message = $_POST['deploy_message'];
      // Prepare the message
      $text =  $_POST['user_fullname'] . ' deployed ' . $_ENV['PANTHEON_SITE_NAME'] . '
      On branch "' . PANTHEON_ENVIRONMENT . '"Workflow: ' . $workflow_description . '
      Deploy Message: ' . htmlentities($deploy_message);
      break;
    case 'sync_code':
      // Get the committer, hash, and message for the most recent commit.
      $committer = `git log -1 --pretty=%cn`;
      $email = `git log -1 --pretty=%ce`;
      $message = `git log -1 --pretty=%B`;
      $hash = `git log -1 --pretty=%h`;
      // Prepare the message
      $text = $_POST['user_fullname'] . ' committed to ' . $_ENV['PANTHEON_SITE_NAME'] . '
      On branch "' . PANTHEON_ENVIRONMENT . '" Workflow: ' . $workflow_description . '
      - ' . htmlentities($message);
      break;
    default:
      $text = "Workflow $workflow_description " . $_POST['qs_description'];
      break;
}

  
try {
    $message = $client->message()->send([
        'to' => $mobile_number,
        'from' => 'Acme Inc',
        'text' => $text
    ]);
    $response = $message->getResponseData();
    if($response['messages'][0]['status'] == 0) {
        echo "The message was sent successfully\n";
    } else {
        echo "The message failed with status: " . $response['messages'][0]['status'] . "\n";
    }
} catch (Exception $e) {
    echo "The message was not sent. Error: " . $e->getMessage() . "\n";
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
  if ($secrets == false) {
    die('Could not parse json in secrets file. Aborting!');
  }

  $secrets += $defaults;

  return $secrets;
}