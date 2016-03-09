<?php
/*
  Plugin Name: Alter-wpcfm-config-path 
  Plugin URI: http://www.eyesopen.ca
  Description: Alters the wpcfm config path 
  Version: 0.1
  Author: Grant McInnes 
  Author URI: http://www.eyesopen.ca
*/

// Tell wp-cfm where our config files live
add_filter('wpcfm_config_dir', function($var) { return $_SERVER['DOCUMENT_ROOT'] . '/private/config'; });
add_filter('wpcfm_config_url', function($var) { return WP_HOME . '/private/config'; });
