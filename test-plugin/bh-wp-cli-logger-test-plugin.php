<?php
/**
 * Plugin Name: BH WP CLI Logger Test Plugin
 */

namespace BrianHenryIE\WP_CLI_Logger_Test_Plugin;

use Psr\Log\LogLevel;
use WP_CLI;

spl_autoload_register(
	function ( $classname ) {
		if( 'BrianHenryIE\WP_CLI_Logger\WP_CLI_Logger' === $classname )
			require_once __DIR__ . '/class-bh-wp-cli-logger.php';
	}
);

require_once __DIR__ . '/vendor/autoload.php';

add_action( 'cli_init', function() {
	WP_CLI::add_command( 'cli-logger', '\BrianHenryIE\WP_CLI_Logger_Test_Plugin\test_command' );
});

/**
 * Test the WP_CLI PSR logger.
 *
 * [<levels>...]
 * : Optional list of log levels to show.
 *
 * [--message=<message>]
 * : A message to use in the output. Replaces `{level}` in the template if present.
 * ---
 * default: "This is a {level} log message."
 * ---
 *
 * ## EXAMPLES
 *
 *      # Print out a log message for notice
 *      $ wp test-plugin notice
 *      Notice:  This is a notice log message.
 *
 *      # Print out a custom log message for warning
 *      $ wp test-plugin warning --message="Uh, oh... something looks amiss."
 *      Warning: Uh, oh... something looks amiss.
 *
 *      # Print out a log messages for two levels
 *      $ wp test-plugin notice debug
 *      Notice:  This is a notice log message.
 *      Debug:   This is a debug log message.
 *
 * @param string[] $args
 * @param array<string,string> $assoc_args
 */
function test_command( array $args, array $assoc_args ) {

	$logger = new \BrianHenryIE\WP_CLI_Logger\WP_CLI_Logger();

	$levels = !empty( $args ) ? $args : array(
		LogLevel::EMERGENCY,
		LogLevel::ALERT,
		LogLevel::CRITICAL,
		LogLevel::ERROR,
		LogLevel::WARNING,
		LogLevel::NOTICE,
		LogLevel::INFO,
		LogLevel::DEBUG,
	);

	foreach( $levels as $level ) {
		$logger->log(
			$level,
			str_replace( '{level}', $level, $assoc_args['message'] )
		);
	}
}
