<?php
/**
 * This example runs the drulenium tests when a code commit is pushed to development environment(s).
 *
 * This script should be configured into the sync_code operation in pantheon.yml
 */
// The sync_code may be triggered on any environment, but we only want
// to automatically enable the drulenium module when this event happens on dev
// or multidev environment.

if (isset($_POST['environment']) && !in_array($_POST['environment'], array('test', 'live'))) {
  passthru('drush vr1 ' . PANTHEON_ENVIRONMENT . ' "' . $_POST['wf_description'] . '"');
}