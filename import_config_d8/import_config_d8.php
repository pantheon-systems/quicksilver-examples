<?php

// Automagically import config into your D8 site upon code deployment

echo "Importing configuration...\n";
passthru('drush config-import -y');
echo "Importing configuration complete.\n";
