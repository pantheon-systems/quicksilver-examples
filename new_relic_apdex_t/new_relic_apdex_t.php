<?php

/**
 * @file
 * Sets New Relic Apdex T values for newly created multidev environments.
 */

/**
 * CHANGE THESE VARIABLES FOR YOUR OWN SITE.
 */
// The "t" value (number of seconds) for your server-side apdex.
// https://docs.newrelic.com/docs/apm/new-relic-apm/apdex/apdex-measuring-user-satisfaction
$app_apdex_threshold = 0.4;
// Do you want New Relic to add JavaScript to pages to analyze rendering time?
// https://newrelic.com/browser-monitoring
$enable_real_user_monitoring = TRUE;
// The "t" value (number of seconds) for browser apdex. (The "real user
// monitoring turned off or on with $enable_real_user_monitoring")
$end_user_apdex_threshold = 6;


// Do not change these variables.
$site_name = $_SERVER['PANTHEON_SITE_NAME'];
$env = $_SERVER['PANTHEON_ENVIRONMENT'];
$app_name = $site_name . " ($env)";

set_thresholds($app_apdex_threshold, $end_user_apdex_threshold, $enable_real_user_monitoring, $app_name);


/**
 * Gets the New Relic API Key so that further requests can be made.
 */
function get_api_key() {
  $return = '';
  $req = pantheon_curl('https://api.live.getpantheon.com/sites/self/bindings?type=newrelic', NULL, 8443);
  $meta = json_decode($req['body'], true);

  foreach($meta as $data) {
    if ($data['environment'] === PANTHEON_ENVIRONMENT && !empty($data['api_key'])) {
      $return = $data['api_key'];
      break;
    }
  }
  return $return;
};

/**
 * Get the id of the current multidev environment.
 */
function get_app_id($api_key, $app_name) {
  $return = '';
  $s = curl_init();
  curl_setopt($s,CURLOPT_URL,'https://api.newrelic.com/v2/applications.json');
  curl_setopt($s,CURLOPT_HTTPHEADER,array('X-API-KEY:' . $api_key));
  curl_setopt($s,CURLOPT_RETURNTRANSFER,1);
  $result = curl_exec($s);
  curl_close($s);

  $result = json_decode($result, true);

  foreach ($result['applications'] as $application) {
    if ($application['name'] === $app_name ) {
      $return = $application['id'];
      break;
    }
  }
  return $return;
}

/**
 * Sets the apdex thresholds.
 */
function set_thresholds($app_apdex_threshold, $end_user_apdex_threshold, $enable_real_user_monitoring, $app_name) {

  $api_key = get_api_key();
  if (empty($api_key)) {
    echo "Failed to get API Key";
    return;
  }

  $app_id = get_app_id($api_key, $app_name);
  if (empty($app_id)) {
    echo "Failed to get app id";
    return;
  }

  $url = 'https://api.newrelic.com/v2/applications/' . $app_id . '.json';

  $settings = [
    'application' => [
      'name' => $app_name,
      'settings' => [
        'app_apdex_threshold' => $app_apdex_threshold,
        'end_user_apdex_threshold' => $end_user_apdex_threshold,
        'enable_real_user_monitoring' => $enable_real_user_monitoring,
      ],
    ],
  ];

  $data_json = json_encode($settings);

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
  $headers = [
    'X-API-KEY:' . $api_key,
    'Content-Type: application/json'
  ];
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
  }
  curl_close ($ch);
}

?>
