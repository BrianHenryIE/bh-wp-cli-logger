<?php
/**
 * @package brianhenryie/bh-wp-cli-logger
 */

namespace BrianHenryIE\WP_CLI_Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use WP_CLI;

class WP_CLI_Logger implements LoggerInterface {
	use LoggerTrait;

	public function log( $level, $message, array $context = array() ) {

		/**
		 * @see Runner::setup_bootstrap_hooks()
		 */
		if ( did_action( 'cli_init' ) ) {
			switch ( $level ) {
				case LogLevel::DEBUG:
					WP_CLI::line( WP_CLI::colorize( '%BDebug:%n   ' . $message ) );
					break;
				case LogLevel::INFO:
					WP_CLI::line( $message );
					break;
				case LogLevel::NOTICE:
					WP_CLI::line( WP_CLI::colorize( '%bNotice:%n  ' . $message ) );
					break;
				case LogLevel::WARNING:
					WP_CLI::line( WP_CLI::colorize( '%yWarning:%n ' . $message ) );
					break;
				case LogLevel::ERROR:
					WP_CLI::line( WP_CLI::colorize( '%rError:%n   ' . $message ) );
					break;
				case LogLevel::EMERGENCY:
				case LogLevel::ALERT:
				case LogLevel::CRITICAL:
					WP_CLI::error_multi_line( array( ucfirst( $level ) . ': ' . $message ) );
					break;
				default:
					WP_CLI::line( ucfirst( $level )  . ': ' . str_repeat( ' ', 7 - strlen( $level ) ) . $message );
			}
		}
	}
}