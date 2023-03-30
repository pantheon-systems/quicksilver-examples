<?php
// Import all config changes.
echo "Importing configuration from yml files...\n";
passthru('drush config-import -y 2>&1');
echo "Import of configuration complete.\n";
//Clear all cache
echo "Rebuilding cache.\n";
passthru('drush cr 2>&1');
echo "Rebuilding cache complete.\n";
