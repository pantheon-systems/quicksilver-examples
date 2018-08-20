<?php
echo "Replacing previous environment urls with new environment urls... \n";

if ( ! empty( $_ENV['PANTHEON_ENVIRONMENT'] ) ) {
  switch( $_ENV['PANTHEON_ENVIRONMENT'] ) {
    case 'live':
      passthru('wp search-replace "://test-example.pantheonsite.io" "://example.com" --all-tables ');
      break;
    case 'test':
      passthru('wp search-replace "://example1.pantheonsite.io" "://test-examplesite.pantheonsite.io" --all-tables ');
      passthru('wp search-replace "://example2.pantheonsite.io" "://test-examplesite.pantheonsite.io" --all-tables ');
      passthru('wp search-replace "://example3.pantheonsite.io" "://test-examplesite.pantheonsite.io" --all-tables ');
      break;
  }
}
?>
