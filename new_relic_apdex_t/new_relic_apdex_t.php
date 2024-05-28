<?php

/**
 * @file
 * Sets New Relic Apdex T values for newly created multidev environments.
 */

define("API_KEY_SECRET_NAME", "new_relic_api_key");

// get New Relic info from the dev environment
// Change to test or live as you wish
$app_info = get_app_info( 'dev' );

$settings = $app_info['settings'];

// The "t" value (number of seconds) for your server-side apdex.
// https://docs.newrelic.com/docs/apm/new-relic-apm/apdex/apdex-measuring-user-satisfaction
$app_apdex_threshold = $app_info['settings']['app_apdex_threshold'];
// Do you want New Relic to add JavaScript to pages to analyze rendering time?
// https://newrelic.com/browser-monitoring
$enable_real_user_monitoring = $app_info['settings']['enable_real_user_monitoring'];
// The "t" value (number of seconds) for browser apdex. (The "real user
// monitoring turned off or on with $enable_real_user_monitoring")
$end_user_apdex_threshold = $app_info['settings']['end_user_apdex_threshold'];

set_thresholds( $app_apdex_threshold, $end_user_apdex_threshold, $enable_real_user_monitoring );

/**
 * Gets the New Relic API Key so that further requests can be made.
 *
 * Also gets New Relic's name for the given environment.
 */
function get_nr_connection_info( $env = 'dev' ) {
  $output = array();
  $req    = pantheon_curl( 'https://api.live.getpantheon.com/sites/self/name', null, 8443 );
  $site_name = trim($req['body']);
  $site_name = trim($site_name, '"');
  $app_name = sprintf( "%s (%s)", $site_name, $env );
  $output['app_name'] = $app_name;

  // Now get secrets for this site.
  $req = pantheon_curl( 'https://customer-secrets.svc.pantheon.io/site/secrets' );

  $secrets_json   = json_decode( $req['body'], true );
  // Use API_KEY_SECRET_NAME to get the API key.
  if ( empty( $secrets_json['Secrets'][API_KEY_SECRET_NAME] ) ) {
    echo "Failed to get secrets\n";

    return;
  }

  $secret = $secrets_json['Secrets'][API_KEY_SECRET_NAME];
  $output['api_key'] = $secret['Value'];

  return $output;
}

/**
 * Get the id of the current multidev environment.
 */
function get_app_id( $api_key, $app_name ) {
  $return = '';
  $s      = curl_init();
  curl_setopt( $s, CURLOPT_URL, 'https://api.newrelic.com/v2/applications.json' );
  curl_setopt( $s, CURLOPT_HTTPHEADER, array( 'X-API-KEY:' . $api_key ) );
  curl_setopt( $s, CURLOPT_RETURNTRANSFER, 1 );
  $result = curl_exec( $s );
  curl_close( $s );

  $result = json_decode( $result, true );

  foreach ( $result['applications'] as $application ) {
    if ( $application['name'] === $app_name ) {
      $return = $application['id'];
      break;
    }
  }

  return $return;
}

/**
 * Get New Relic information about a given environment.
 *
 * Used to retrive T values for a pre-existing environment.
 */
function get_app_info( $env = 'dev' ) {
  $nr_connection_info = get_nr_connection_info();
  if ( empty( $nr_connection_info ) ) {
    echo "Unable to get New Relic connection info\n";

    return;
  }

  $api_key  = $nr_connection_info['api_key'];
  $app_name = $nr_connection_info['app_name'];

  $app_id = get_app_id( $api_key, $app_name );

  $url = "https://api.newrelic.com/v2/applications/$app_id.json";

  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_URL, $url );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
  $headers = [
      'X-API-KEY:' . $api_key
  ];
  curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

  $response = curl_exec( $ch );

  if ( curl_errno( $ch ) ) {
    echo 'Error:' . curl_error( $ch );
  }

  curl_close( $ch );

  $output = json_decode( $response, true );

  return $output['application'];
}

/**
 * Sets the apdex thresholds.
 */
function set_thresholds( $app_apdex_threshold, $end_user_apdex_threshold, $enable_real_user_monitoring ) {

  $nr_connection_info = get_nr_connection_info( PANTHEON_ENVIRONMENT );
  if ( empty( $nr_connection_info ) ) {
    echo "Unable to get New Relic connection info\n";

    return;
  }

  $api_key  = $nr_connection_info['api_key'];
  $app_name = $nr_connection_info['app_name'];

  $app_id = get_app_id( $api_key, $app_name );

  echo "===== Setting New Relic Values for the App '$app_name' =====\n";
  echo "Application Apdex Threshold: $app_apdex_threshold\n";
  echo "End User Apdex Threshold: $end_user_apdex_threshold\n";
  echo "Enable Real User Monitoring: $enable_real_user_monitoring\n";

  $url = 'https://api.newrelic.com/v2/applications/' . $app_id . '.json';

  $settings = [
      'application' => [
          'name'     => $app_name,
          'settings' => [
              'app_apdex_threshold'         => $app_apdex_threshold,
              'end_user_apdex_threshold'    => $end_user_apdex_threshold,
              'enable_real_user_monitoring' => $enable_real_user_monitoring,
          ],
      ],
  ];

  $data_json = json_encode( $settings );

  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_URL, $url );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
  curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_json );
  curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );
  $headers = [
      'X-API-KEY:' . $api_key,
      'Content-Type: application/json'
  ];
  curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
  $result = curl_exec( $ch );
  if ( curl_errno( $ch ) ) {
    echo 'Error:' . curl_error( $ch );
  }
  curl_close( $ch );

  echo "===== Finished Setting New Relic Values =====\n";
}
