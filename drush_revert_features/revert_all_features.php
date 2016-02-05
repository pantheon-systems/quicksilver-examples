<?php

//Revert all features
echo "Reverting all features...";
passthru('drush fra -y');
echo "Reverting complete...";

//Clear all cache
echo "Clearing cache...";
passthru('drush cc all');
echo "Clearing cache complete...";