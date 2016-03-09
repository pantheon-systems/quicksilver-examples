<?php
function my_wp_cfm_runcmd($cmd, $msg) {
  print("\n$msg\n");
  print("Running command: $cmd\n");
  exec($cmd, $output, $status);
  print("Status: $status\n");
  print("Output: ". implode("\n",$output));
}

// Don't ever run this on the live environment! 
if (defined('PANTHEON_ENVIRONMENT') && (PANTHEON_ENVIRONMENT !== 'live')) {

  # For each Pantheon environment, define a mapping from the Pantheon
  # environment name to a file stored in the wp-cfm configuration directory.
  #
  # The "default" environment will be used for any environment that isn't
  # defined, so it should always be set.
  #
  # N.B.the actual filename in the wp-cfm configuration directory will
  # have a .json extension
  #
  # N.B. the default location for the wp-cfm configuration directory is /config
  # in the document root.  That doesn't make sense in the Pantheon context,
  # because it will be browsable.  Better to store it in /private/config. One
  # way to do that is to add a mu-plugin that uses the `wpcfm_content_dir`
  # directory filter to alter the path.  See the README.md for more details. 
  $config_map = [ 'test' => 'pantheon_test',
		  'dev'  => 'pantheon_dev',
		  'default' => 'pantheon_dev' ];
  $config_name = array_key_exists(PANTHEON_ENVIRONMENT, $config_map) ? $config_map[PANTHEON_ENVIRONMENT] : $config_map['default'];
 
  my_wp_cfm_runcmd("wp plugin activate wp-cfm 2>&1",
         "Making sure that wp-cfm plugin is activated");
  
  my_wp_cfm_runcmd("wp config pull $config_name 2>&1",
         "pulling config for $config_name");
}

