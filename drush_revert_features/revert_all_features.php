<?php

//Revert all features
echo "Reverting all features...\n";
passthru('drush fra -y');
echo "Reverting complete.\n";

//Clear all cache
echo "Clearing cache.\n";
passthru('drush cc all');
echo "Clearing cache complete.\n";
