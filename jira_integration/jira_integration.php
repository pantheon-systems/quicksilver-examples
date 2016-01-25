<?php
// TODO: this header should not be required...
header('Content-Type: text/plain; charset=UTF-8');

// Only watch the dev environment
if ($_ENV['PANTHEON_ENVIRONMENT'] != 'dev') {
  die();
}

// Retrieve jira destination and credentials
$jira = json_decode(file_get_contents($_SERVER['HOME'] . '/files/private/jira_integration.json'), 1);
if ($jira == FALSE) {
  die('No jira file found. Aborting!');
}

$commits = array(
  // The latest git hash
  'current' => NULL,
  // The hash at the last time we processed this script
  'last_processed' => NULL,
  // Raw output of git log since the last processed
  'history_raw' => NULL,
  // Formatted array of commits being sent to jira
  'history' => array(),
  // An array keyed by jira ticket id, each holding an
  // array of commit ids.
  'jira' => array()
);

// Get latest commit
$commits['current'] = shell_exec('git rev-parse HEAD');

// Retrieve the last commit processed by this script
$commit_file = $_SERVER['HOME'] . '/files/private/jira_integration.commit';
if (file_exists($commit_file)) {
  $commits['last_processed'] = trim(file_get_contents($commit_file));
}

// Update the last commit file with the latest commit
file_put_contents($commit_file, $commits['current'], LOCK_EX);

// Retrieve git log for commits after last processed, to current
$cmd = 'git log'; // add -p to include diff
if (!$commits['last_processed']) {
  $cmd .= ' -n 1';
}
else {
  $cmd .= ' ' . $commits['last_processed'] . '...' . $commits['current'];
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
  $commit['formatted'] = '{panel:title=Commit: ' . substr($commit['id'], 0, 10) . '|borderStyle=dashed|borderColor=#ccc|titleBGColor=#e5f2ff|bgColor=#f2f2f2}
  ' . $commit['message'] . '
  ~' . $lines[1] . ' - ' . $lines[2] . '~
  {panel}';

  // Look for matches on a Jira issue ID format
  // TODO: Fix - commas after issue id fail
  preg_match('/(?:\s|^)([A-Z]+-[0-9]+)(?=\s|$)/i', $commit['message'], $matches);
  if (count($matches) > 0) {
    // Build the $commits['jira'] array so there is
    // only 1 item per ticket id
    foreach ($matches as $ticket_id) {
      $ticket_id = strtoupper($ticket_id);
      if (!isset($commits['jira'][$ticket_id])) {
        $commits['jira'][$ticket_id] = array();
      }
      // ... and only 1 item per commit id
      $commits['jira'][$ticket_id][$commit['id']] = $commit['id'];
    }

    // Add the commit to the history array since there was a match.
    $commits['history'][$commit['id']] = $commit;
  }
}

// Check each commit message for Jira ticket numbers
foreach ($commits['jira'] as $ticket_id => $commit_ids) {
  foreach ($commit_ids as $commit_id) {
    send_commit($jira, $ticket_id, $commits['history'][$commit_id]);
  }
}

function send_commit($jira, $ticket_id, $commit) {
  $payload = json_encode(array('body' => $commit['formatted']));
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $jira['url'] . '/rest/api/2/issue/' . $ticket_id . '/comment');
  curl_setopt($ch, CURLOPT_USERPWD, $jira['user'] . ':' . $jira['pass']);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  print("\n==== Posting to Jira ====\n");
  $result = curl_exec($ch);
  print("RESULT: $result");
  print("\n===== Post Complete! =====\n");
  curl_close($ch);
}
