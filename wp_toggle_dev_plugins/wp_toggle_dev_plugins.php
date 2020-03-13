<?php
// hello is our example plugin, swap out with the slug of your plugin of choice
echo "Toggle developer plugins: checking environment... \n";
if ( ! empty( $_ENV['PANTHEON_ENVIRONMENT'] ) ) {
  switch( $_ENV['PANTHEON_ENVIRONMENT'] ) {
    case 'live':
      echo "Toggle developer plugins: Live, deactivating plugins... \n";
      passthru('wp plugin deactivate hello ');
      break;
    case 'test':
      echo "Toggle developer plugins: Test, activating plugins... \n";
      passthru('wp plugin activate hello ');
      break;
    case 'dev':
      echo "Toggle developer plugins: Dev, activating plugins... \n";
      passthru('wp plugin activate hello ');
      break; 
  }
}
?>
