<?php

// Generating Developer Content for "Article" Content Type

// Only run this operation for development or multidev environments
if (isset($_POST['environment']) && !in_array($_POST['environment'], array('test', 'live'))) {

  // Only run this operation if Devel and Devel Generate modules are available. 
  // Enable the modules if they are not already enabled
  $modules = json_decode(shell_exec('drush pm-list --format=json'));
  if (isset($modules->devel) && isset($modules->devel_generate)) {

    if (isset($modules->devel) && $modules->devel->status !== 'Enabled') {
      passthru('drush pm-enable -y devel 2>&1');
    }
    if (isset($modules->devel_generate) && $modules->devel_generate->status !== 'Enabled') {
      passthru('drush pm-enable -y devel_generate 2>&1');
    }

    // Remove the existing production article content
    echo "Removing production article content...\n";
    passthru('drush genc --kill --types=article 0 0 2>&1');
    echo "Removal complete.\n";

    // Generate new development article content
    echo "Generating development article content...\n";
    passthru('drush generate-content 20 --types=article 2>&1');
    echo "Generation complete.\n";

    // Disable the Devel and Devel Generate modules as appropriate
    if (isset($modules->devel) && $modules->devel->status !== 'Enabled') {
      passthru('drush pm-disable -y devel 2>&1');
    }
    if (isset($modules->devel_generate) && $modules->devel_generate->status !== 'Enabled') {
      passthru('drush pm-disable -y devel_generate 2>&1');
    }
  }
  else {
    echo "The Devel and Devel Generate modules must be present for this operation to work";
  }
}
