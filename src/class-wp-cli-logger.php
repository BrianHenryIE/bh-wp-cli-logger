<?php
/**
 * A PSR log implementation which prints the log to the terminal, via WP_CLI, colorized.
 *
 * @package brianhenryie/bh-wp-cli-logger
 */

namespace BrianHenryIE\WP_CLI_Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use WP_CLI;

/**
 * Provides one filter, `bh_wp_cli_logger_log`, to customise the color and message.
 */
class WP_CLI_Logger implements LoggerInterface {
	use LoggerTrait;

	/**
	 * Map of log levels to the ANSI color to highlight the level with.
	 *
	 * @see WP_CLI::colorize()
	 * @see https://en.wikipedia.org/wiki/ANSI_escape_code
	 * @see https://ansi.org/
	 *
	 * @var array<string, string> The color code indexed by the log level.
	 */
	protected static array $ansi_colors = array(
		LogLevel::DEBUG     => '%B',
		LogLevel::INFO      => '',
		LogLevel::NOTICE    => '%b',
		LogLevel::WARNING   => '%y',
		LogLevel::ERROR     => '%r',
		LogLevel::EMERGENCY => '',
		LogLevel::ALERT     => '',
		LogLevel::CRITICAL  => '',
	);

	/**
	 * The log output implementation.
	 *
	 * This function is called by each of the LoggerTrait methods, e.g. `::notice()`, `::warning()`.
	 *
	 * @see LoggerInterface::log()
	 * @see LogLevel
	 *
	 * @param string $level One of LogLevel levels (but really could be arbitrary).
	 * @param string $message The sentence to print.
	 * @param array  $context Array of associated data.
	 *
	 * @return void
	 */
	public function log( $level, $message, array $context = array() ) {

		/**
		 * Only run when WP CLI is active.
		 *
		 * @see Runner::setup_bootstrap_hooks()
		 */
		if ( ! did_action( 'cli_init' ) ) {
			return;
		}

		$log = array(
			'level'      => $level,
			'message'    => $message,
			'context'    => $context,
			'prepend'    => ucfirst( $level ) . ':',
			'ansi_color' => self::$ansi_colors[ $level ] ?? '',
		);

		// Don't prepend or colorize info messages.
		if ( LogLevel::INFO === $level ) {
			$log['prepend'] = '';
		}

		/**
		 * Filter logs to control output style.
		 *
		 * NB: try to use the `context` to only apply this filter to logs output by your own plugin.
		 * Return null, an empty array, or an empty message to prevent output.
		 *
		 * @var ?array{level:string, message:string, context:array<mixed>, prepend:string, ansi_color:string} $log
		 */
		$log = apply_filters( 'bh_wp_cli_logger_log', $log );

		// Allow filtering logs to prevent output.
		if ( empty( $log ) || empty( $log['message'] ) ) {
			return;
		}

		if ( ! empty( $log['ansi_color'] ) && ! empty( $log['prepend'] ) ) {
			WP_CLI::line(
				WP_CLI::colorize(
					$log['ansi_color'] . $log['prepend'] . '%n' . str_repeat( ' ', 9 - strlen( $log['prepend'] ) ) . $message
				)
			);
		} elseif (
			in_array( $log['level'], array( LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL ), true )
			&& ! empty( $log['prepend'] )
		) {
			WP_CLI::error_multi_line( array( $log['prepend'] . ': ' . $message ) );
		} elseif (
			empty( $log['ansi_color'] ) && ! empty( $log['prepend'] ) ) {
			WP_CLI::line(
				$log['prepend'] . str_repeat( ' ', 9 - strlen( $log['prepend'] ) ) . $message
			);
		} else {
			WP_CLI::line( $message );
		}
	}
}
