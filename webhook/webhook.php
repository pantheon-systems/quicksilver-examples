<?php
// Define the url the data should be sent to.
// {wf_type} will be replaced with the workflow operation: clone_database, clear_cache, deploy, or sync_code.
$url = 'http://example.com/quicksilver/{wf_type}';

$payload = $_POST;
// Add the site name to the payload here in case the receiving app is handling more than 1 site.
$payload['site_name'] = $_ENV['PANTHEON_SITE_NAME'];
$payload = http_build_query($payload);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, str_replace('{event}', $_POST['wf_type'], $url));
curl_setopt($ch,CURLOPT_POST, 1);
curl_setopt($ch,CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
print("\n==== Posting to Webhook URL ====\n");
$result = curl_exec($ch);
print("RESULT: $result");
print("\n===== Post Complete! =====\n");
curl_close($ch);
