<?php

//Revert all features
echo "Reverting all features...\n";
passthru('drush fra -y 2>&1');
echo "Reverting complete.\n";

//Clear all cache
echo "Clearing cache.\n";
passthru('drush cc all 2>&1');
echo "Clearing cache complete.\n";
