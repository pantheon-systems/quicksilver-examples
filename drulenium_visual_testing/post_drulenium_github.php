<?php

$test_definition = array (
  'staging_environment_domain' => 'http://test-drulenium-hosted.pantheonsite.io/',
  'dev_environment_domain_suffix' => '-drulenium-hosted.pantheonsite.io/',
  'notification_email_ids' => array(
    'drulenium@mailinator.com',
  ),
  'test_pages_sitemap' => array(
    'boston',
    'new-york',
    'node',
  ),
);

$github_username = 'Drulenium';
$github_repository = 'pantheon-travis';
$github_accesstoken = 'a06e6d536db8743056e1faae60aa803a0b17b13f';

$github_master_branch_sha = '0600c12ea73e185ac7f29a2d33deda1708672996';

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
$test_definition['dev_environment_domain'] = 'http://' . PANTHEON_ENVIRONMENT . $test_definition['dev_environment_domain_suffix'];
unset($test_definition['dev_environment_domain_suffix']);

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
// EOF 1
