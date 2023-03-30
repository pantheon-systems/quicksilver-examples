<?php
// Don't ever sanitize the database on the live environment. Doing so would
// destroy the canonical version of the data.
if (defined('PANTHEON_ENVIRONMENT') && (PANTHEON_ENVIRONMENT !== 'live')) {

	// Run the Drush command to sanitize the database.
	echo "Sanitizing the database...\n";
	passthru('drush sql-sanitize -y 2>&1');
	echo "Database sanitization complete.\n";
}
