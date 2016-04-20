<?php

// An example of usign Pantheon's Quicksilver technology to do 
// automatic visual regression testing using Drulenium

// Modify default Test definition accordingly.
/* Example
$test_definition = array (
  'notification_email_ids' => array(
    'drulenium@mailinator.com',
  ),
  'test_pages_sitemap' => array(
    'boston',
    'new-york',
    'node',
  ),
);
*/
/*----------START of REQUIRED Simple Configuration----------*/
$test_definition = array (
  'notification_email_ids' => array(
    'add-my-email-here',
  ),
  'test_pages_sitemap' => array(
    'add-my-first-page-here',
    'add-my-second-page-here',
    'add-my-third-page-here',
  ),
);
/*----------END of REQUIRED Simple Configuration----------*/

$secrets = _get_secrets(array('github_username', 'github_repository', 'github_accesstoken', 'github_master_branch_sha'), $defaults = array());

$github_username = $secrets['github_username'];
$github_repository = $secrets['github_repository'];
$github_accesstoken = $secrets['github_accesstoken'];

$github_master_branch_sha = $secrets['github_master_branch_sha'];

/*
 * Create a git branch. branch name will be like tests/test-XXXX
 */
$unique_id = uniqid('test-');
$git_branch_name = 'tests/'.$unique_id;
$url = 'https://api.github.com/repos/'.rawurlencode($github_username).'/'.rawurlencode($github_repository).'/git/refs';
$parameters = array(
    'ref' => 'refs/heads/'.$git_branch_name,
    'sha' => $github_master_branch_sha,
);
$data = github_post_content($url, $github_accesstoken, "POST", $parameters);
print($data); // For debugging/watching in Terminus
print('\r\n');

/*
 * Structure the test.json file
 */
$test_definition['staging_environment_domain'] = 'http://test-'. $_ENV['PANTHEON_SITE_NAME'] . '.pantheonsite.io/';
$test_definition['dev_environment_domain'] = 'http://' . PANTHEON_ENVIRONMENT . '-'. $_ENV['PANTHEON_SITE_NAME'] . '.pantheonsite.io/';

/*
 * Upload the test.json file to github repository
 */
$new_file_path = 'drulenium/tests/'.$unique_id.'.json';
$url = 'https://api.github.com/repos/'.rawurlencode($github_username).'/'.rawurlencode($github_repository).'/contents/'.rawurlencode($new_file_path);
$new_file_content = json_encode($test_definition, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
$commit_message = $unique_id;
$parameters = array(
    'content' => base64_encode($new_file_content)."\n", // New line at the end of the file
    'message' => $commit_message,
    'branch' => $git_branch_name,
);
$data = github_post_content($url, $github_accesstoken, "PUT", $parameters);
print($data); // For debugging/watching in Terminus
print('\r\n');

/*
 * Create the Pull Request
 */
$url = 'https://api.github.com/repos/'.rawurlencode($github_username).'/'.rawurlencode($github_repository).'/pulls';
$parameters = array(
    'title' => $git_branch_name,
    'head' => "Drulenium:$git_branch_name",
    'base' => 'master',
    'body' => $new_file_content,
);
$data = github_post_content($url, $github_accesstoken, "POST", $parameters);
print_r(json_decode($data)); // For debugging/watching in Terminus

/**
 * Function to POST or PUT request to github
 * @param string $url
 * @param string $accesstoken
 * @param string $method POST / PUT
 * @param Array $parameters
 * @return JSON encoded data from github
 */
function github_post_content($url, $accesstoken, $method = "POST", $parameters) {
  $headr = array ();
  $headr [] = 'Authorization: token ' . $accesstoken;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_USERAGENT, 'Drulenium-Test-Creation');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);

  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));

  $content = curl_exec($ch);
  curl_close($ch);
  return $content;
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
// EOF
