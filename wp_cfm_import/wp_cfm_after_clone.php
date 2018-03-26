<?php
print( "\n==== WP-CFM Config Import Starting ====\n" );
// Activate the wp-cfm plugin
exec( 'wp plugin activate wp-cfm 2>&1' );

// Automagically import config into WP-CFM site upon code deployment
$path  = $_SERVER['DOCUMENT_ROOT'] . '/private/config';
$files = scandir( $path );
$files = array_diff( scandir( $path ), array( '.', '..' ) );

// Import all config .json files in private/config
foreach( $files as $file ){

	$file_parts = pathinfo($file);

	if( $file_parts['extension'] != 'json' ){
		continue;
	}

	exec( 'wp config pull ' . $file_parts['filename'] . ' 2>&1', $output );

	if ( count( $output ) > 0 ) {
		$output = preg_replace( '/\s+/', ' ', array_slice( $output, 1, - 1 ) );
		$output = str_replace( ' update', ' [update]', $output );
		$output = str_replace( ' create', ' [create]', $output );
		$output = str_replace( ' delete', ' [delete]', $output );
		$output = implode( $output, "\n" );
		$output = rtrim( $output );
	}
}

// Flush the cache
exec( 'wp cache flush' );

print( "\n==== WP-CFM Config Import Complete ====\n" );
