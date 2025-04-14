<?php
echo "Replacing previous environment urls with new environment urls... \n";

if (!empty($_ENV['PANTHEON_ENVIRONMENT'])) {
  switch ($_ENV['PANTHEON_ENVIRONMENT']) {
    // For multisite setup, appending --network option might be necessary
    case 'live':
      $command = 'wp search-replace "://test-example.pantheonsite.io" "://example.com" --all-tables ';
      if ($_ENV['FRAMEWORK'] == "wordpress_network") {
        $command .= '--network';
      }
      passthru($command);

      break;
    case 'test':
      $command = 'wp search-replace "://example1.pantheonsite.io" "://test-examplesite.pantheonsite.io" --all-tables ';
      if ($_ENV['FRAMEWORK'] == "wordpress_network") {
        $command .= '--network';
      }
      passthru($command);

      $command = 'wp search-replace "://example2.pantheonsite.io" "://test-examplesite.pantheonsite.io" --all-tables ';
      if ($_ENV['FRAMEWORK'] == "wordpress_network") {
        $command .= '--network';
      }
      passthru($command);

      break;
  }
}
?>