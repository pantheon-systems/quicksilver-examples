<?php

// Get the environment; we will post a new comment to Trello each time
// a commit appears on a new branch on Pantheon.
$env = $_ENV['PANTHEON_ENVIRONMENT'];

// Do not watch test or live, though.
if (($env == 'live') || ($env == 'test')) {
  exit(0);
}

// Look up the secrets from the secrets file.
$secrets = _get_secrets(array('trello_key', 'trello_token'), array());

// Get latest commit
$current_commithash = shell_exec('git rev-parse HEAD');
$last_commithash = FALSE;
// Retrieve the last commit processed by this script
$commit_file = $_SERVER['HOME'] . "/files/private/{$env}_trello_integration_commit.txt";
if (file_exists($commit_file)) {
  $last_processed_commithash = trim(file_get_contents($commit_file));
  // We should (almost) always find our last commit still in the repository;
  // if the user has force-pushed a branch, though, then our last commit
  // may be overwritten.  If this happens, only process the most recent commit.
  exec("git rev-parse $last_processed_commithash 2> /dev/null", $output, $status);
  if (!$status) {
    $last_commithash = $last_processed_commithash;
  }
}
// Update the last commit file with the latest commit
file_put_contents($commit_file, $current_commithash, LOCK_EX);

// Retrieve git log for commits after last processed, to current
$commits = _get_commits($current_commithash, $last_commithash, $env);

// Check each commit message for Trello card IDs
foreach ($commits['trello'] as $card_id => $commit_ids) {
  foreach ($commit_ids as $commit_id) {
    send_commit($secrets, $card_id, $commits['history'][$commit_id]);
  }
}

/**
 * Do git operations to find all commits between the specified commit hashes,
 * and return an associative array containing all applicable commits that
 * contain references to Trello cards.
 */
function _get_commits($current_commithash, $last_commithash, $env) {
  $commits = array(
    // Raw output of git log since the last processed
    'history_raw' => NULL,
    // Formatted array of commits being sent to Trello
    'history' => array(),
    // An array keyed by Trello card id, each holding an
    // array of commit ids.
    'trello' => array()
  );

  $cmd = 'git log'; // add -p to include diff
  if (!$last_commithash) {
    $cmd .= ' -n 1';
  }
  else {
    $cmd .= ' ' . $last_commithash . '...' . $current_commithash;
  }
  $commits['history_raw'] = shell_exec($cmd);
  // Parse raw history into an array of commits
  $history = preg_split('/^commit /m', $commits['history_raw'], -1, PREG_SPLIT_NO_EMPTY);
  foreach ($history as $str) {
    $commit = array(
      'full' => 'Commit: ' . $str
    );
    // Only interested in the lines before the diff now
    $lines = explode("\n", $str);
    $commit['id'] = $lines[0];
    $commit['message'] = trim(implode("\n", array_slice($lines, 4)));
    $commit['formatted'] = 'Commit: ' . substr($commit['id'], 0, 10) . ' [' . $env . ']
    ' . $commit['message'] . '
    ~' . $lines[1] . ' - ' . $lines[2];
    // Look for matches on a Trello card ID format
    // = [8 characters]
    preg_match('/\[[a-zA-Z0-9]{8}\]/', $commit['message'], $matches);
    if (count($matches) > 0) {
      // Build the $commits['trello'] array so there is
      // only 1 item per ticket id
      foreach ($matches as $card_id_enc) {
        $card_id = substr($card_id_enc, 1, -1);
        if (!isset($commits['trello'][$card_id])) {
          $commits['trello'][$card_id] = array();
        }
        // ... and only 1 item per commit id
        $commits['trello'][$card_id][$commit['id']] = $commit['id'];
      }
      // Add the commit to the history array since there was a match.
      $commits['history'][$commit['id']] = $commit;
    }
  }
  return $commits;
}

/**
 * Send commits to Trello
 */
function send_commit($secrets, $card_id, $commit) {
  $payload = array(
    'text' => $commit['formatted'],
    'key' => $secrets['trello_key'],
    'token' => $secrets['trello_token']
  );
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://api.trello.com/1/cards/' . $card_id . '/actions/comments');
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  print("\n==== Posting to Trello ====\n");
  $result = curl_exec($ch);
  print("RESULT: $result");
  print("\n===== Post Complete! =====\n");
  curl_close($ch);
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
    die('No secrets file ['.$secretsFile.'] found. Aborting!');
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
