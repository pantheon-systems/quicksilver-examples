<?php
// **** BEGIN CONFIG ****

// Set the email address you would like non 200 status checks to be sent to
$email = 'admin@example.com';

// Base path (with the trailing slash)
$base_url = 'https://example.com/';

// Paths to check
$check_paths = array(
  '',         // good path
  'user',     // good path (for Drupal)
  'bad-path', // bad path
);

// **** END CONFIG ****


// Only run status check on live deployments
if ($_ENV['PANTHEON_ENVIRONMENT'] != 'live') {
  die();
}

require '../../vendor/autoload.php';

use GuzzleHttp\Client;

$client = new GuzzleHttp\Client(['base_uri' => $base_url]);
$failed = array();

print "\nStatus Checks\n--------\n";

foreach ($check_paths as $path) {
  $response = $client->get($path, ['exceptions' => false]);
  $status = $response->getStatusCode();

  print '  ' . $status . ' - ' . $base_url . $path . "\n";

  if ($status != 200) {
    $failed[] = array(
      'path' => $path,
      'status' => $status
    );
  }
}

print "--------\n" . count($failed) . " failed\n\n";

if (count($failed) > 0) {
  $subject = 'Failed status check (' . count($failed) . ')';
  $message = "The following urls failed to return a status 200:\n\n";

  foreach ($failed as $item) {
    $message .= ' ' . $item['status'] . ' - ' . $base_url . $item['path'] . "\n";
  }
  mail($email, $subject, $message);
}
