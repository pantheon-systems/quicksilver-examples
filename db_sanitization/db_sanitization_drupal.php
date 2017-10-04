<?php
// Don't ever santize the database on the live environment. Doing so would
// destroy the canonical version of the data.
if (defined('PANTHEON_ENVIRONMENT') && (PANTHEON_ENVIRONMENT !== 'live')) {

	// Import all config changes.
	echo "Sanitizing the database...\n";
	passthru('drush sql-sanitize -y');
	echo "Database sanitization complete.\n";
	echo "Clearing the caches \n";
	passthru('drush cache-clear');
}
