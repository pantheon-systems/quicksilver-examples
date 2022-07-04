<?php

echo ("\n==== Drupal Environment importing database ====\n");
// Get paths for imports
$path  = $_SERVER['DOCUMENT_ROOT'] . '/private/data';

// Import database
echo ('Importing Database from ...');

// Please don't store your database in the repository. This is just for demo purposes.
echo "${path}/microsite-database.sql && drush cr";
$cmd = "drush sql:cli < ${path}/microsite-database.sql && drush cr";
passthru($cmd);

// Import media and files
echo ('Unzipping image files...');
$files = $_SERVER['HOME'] . '/files';
$cmd = "unzip ${path}/files.zip -d ${files}";
passthru($cmd);

echo ("\n==== Drupal Environment Initialization Complete ====\n");
