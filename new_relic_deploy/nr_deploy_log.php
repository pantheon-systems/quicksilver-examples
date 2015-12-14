<?php
header('Content-Type: text/plain; charset=UTF-8');
// Fetch all the metadata about this environment from Pantheon
$req = pantheon_curl('https://api.live.getpantheon.com/sites/self/bindings', NULL, 8443);
$meta = json_decode($req['body'], true);
$nr = FALSE;
// Filter to just the New Relic data
foreach($meta as $data) {
  if ($data['type'] == 'newrelic') {
    $nr = $data;
    break;
  }
}

if ($nr) {
  // This example uses New Relic's own example code.
  // A more sophisticated example could use curl() directly.
  // @TODO: populate the revision and changelog data.
  $curl = 'curl -H "x-api-key:'. $data['api_key'] .'"';
  $curl .= ' -d "deployment[application_id]=' . $data['app_name'] .'"';
  $curl .= ' -d "deployment[description]=Deployment automatically logged via Quicksilver"';
  // $curl .= ' -d "deployment[revision]=2"'; 
  // $curl .= ' -d "deployment[changelog]=many hands make light work"';
  $curl .= ' -d "deployment[user]='. $_POST['user_email'] .'"';
  $curl .= ' https://api.newrelic.com/deployments.xml';
  passthru($curl);
}
else {
  echo "\n\nALERT! No New Relic metadata could be found.\n\n";
}