<?php
/**
 * Move Patchwork's Composer "files" autoload entry to the front of the list so
 * Patchwork is loaded before any other autoloaded file. Otherwise a file loaded
 * earlier can pull in a class (e.g. WP_CLI) before Patchwork is able to
 * instrument it, causing:
 *
 * Patchwork\Exceptions\DefinedTooEarly : The file that defines WP_CLI::line() was included earlier than Patchwork. Please reverse this order to be able to redefine the function in question.
 *
 * Run after `composer dump-autoload` (e.g. from a `post-autoload-dump` script).
 *
 * @package brianhenryie/bh-wp-cli-logger
 */

$autoload_files = array(
	__DIR__ . '/../vendor/composer/autoload_files.php',
	__DIR__ . '/../vendor/composer/autoload_static.php',
);

foreach ( $autoload_files as $autoload_file ) {

	if ( ! is_file( $autoload_file ) ) {
		continue;
	}

	// Read the file into an array of lines, preserving line endings.
	$lines = file( $autoload_file );

	if ( false === $lines ) {
		continue;
	}

	// Find the line with the Patchwork entry.
	$patchwork_index = null;
	foreach ( $lines as $index => $line ) {
		if ( false !== strpos( $line, '/antecedent/patchwork/Patchwork.php' ) ) {
			$patchwork_index = $index;
			break;
		}
	}

	if ( null === $patchwork_index ) {
		continue;
	}

	$patchwork_line = $lines[ $patchwork_index ];
	unset( $lines[ $patchwork_index ] );
	$lines = array_values( $lines );

	// Move it to the beginning of the array it belongs to: insert it just after
	// the nearest preceding line that opens an array.
	$insert_index = 0;
	for ( $i = $patchwork_index - 1; $i >= 0; $i-- ) {
		if ( 1 === preg_match( '/array\s*\(/', $lines[ $i ] ) ) {
			$insert_index = $i + 1;
			break;
		}
	}

	array_splice( $lines, $insert_index, 0, $patchwork_line );

	file_put_contents( $autoload_file, implode( '', $lines ) );
}
