<?php
/**
 * Basic tests, part-written by Claude.
 *
 * @package brianhenryie/bh-wp-cli-logger
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WP_CLI_Logger_Test_Plugin;

use BrianHenryIE\WP_CLI_Logger\Unit_Testcase;
use BrianHenryIE\WP_CLI_Logger\WP_CLI_Logger;
use Patchwork;
use Psr\Log\LogLevel;
use WP_Mock;

/**
 * Uses Patchwork to log the log calls!
 *
 * @coversDefaultClass \BrianHenryIE\WP_CLI_Logger\WP_CLI_Logger
 */
class WP_CLI_Logger_Unit_Test extends Unit_Testcase {

	/**
	 * Strings the logger passed to `WP_CLI::line()`.
	 *
	 * @var string[]
	 */
	protected array $lines;

	/**
	 * Message arrays the logger passed to `WP_CLI::error_multi_line()`.
	 *
	 * @var array<string[]>
	 */
	protected array $error_multi_lines;

	/**
	 * Capture WP-CLI output by redefining its static methods, rather than
	 * loading and running WP-CLI itself.
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->lines             = array();
		$this->error_multi_lines = array();

		Patchwork\redefine(
			'WP_CLI::line',
			function ( $message = '' ): void {
				$this->lines[] = $message;
			}
		);
		Patchwork\redefine(
			'WP_CLI::colorize',
			fn( $string_param ) => $string_param
		);
		Patchwork\redefine(
			'WP_CLI::error_multi_line',
			function ( $message_lines ): void {
				$this->error_multi_lines[] = $message_lines;
			}
		);
	}

	/**
	 * Outside of a WP CLI request `cli_init` has not fired, so nothing is printed.
	 *
	 * @covers ::log
	 */
	public function test_no_output_when_not_in_cli_context(): void {

		WP_Mock::userFunction( 'did_action' )->with( 'cli_init' )->andReturnFalse();

		( new WP_CLI_Logger() )->info( 'Should not print.' );

		$this->assertEmpty( $this->lines );
		$this->assertEmpty( $this->error_multi_lines );
	}

	/**
	 * `info` messages are printed plainly: no prefix, no color.
	 *
	 * @covers ::log
	 */
	public function test_info_message_is_printed_plain(): void {

		WP_Mock::userFunction( 'did_action' )->with( 'cli_init' )->andReturnTrue();

		( new WP_CLI_Logger() )->info( 'Just so you know.' );

		$this->assertEquals( array( 'Just so you know.' ), $this->lines );
	}

	/**
	 * `notice` is colorized and prefixed, padded to nine characters.
	 *
	 * @covers ::log
	 */
	public function test_notice_is_colorized_and_prefixed(): void {

		WP_Mock::userFunction( 'did_action' )->with( 'cli_init' )->andReturnTrue();

		( new WP_CLI_Logger() )->notice( 'Heads up.' );

		$this->assertEquals( array( '%bNotice:%n  Heads up.' ), $this->lines );
	}

	/**
	 * `error` is colorized red and prefixed.
	 *
	 * @covers ::log
	 */
	public function test_error_is_colorized_red(): void {

		WP_Mock::userFunction( 'did_action' )->with( 'cli_init' )->andReturnTrue();

		( new WP_CLI_Logger() )->error( 'Something broke.' );

		$this->assertEquals( array( '%rError:%n   Something broke.' ), $this->lines );
	}

	/**
	 * `emergency`, `alert` and `critical` have no color, so they are routed
	 * through `WP_CLI::error_multi_line()`.
	 *
	 * @covers ::log
	 */
	public function test_emergency_uses_error_multi_line(): void {

		WP_Mock::userFunction( 'did_action', array( 'return' => 1 ) );
		WP_Mock::userFunction( 'apply_filters', array( 'return_arg' => 1 ) );

		( new WP_CLI_Logger() )->emergency( 'The house is on fire.' );

		$this->assertEquals(
			array( array( 'Emergency: The house is on fire.' ) ),
			$this->error_multi_lines
		);
		$this->assertEmpty( $this->lines );
	}

	/**
	 * The filter receives the expected log array under the documented hook name.
	 *
	 * @covers ::log
	 */
	public function test_filter_is_applied_with_expected_log_array(): void {

		WP_Mock::userFunction( 'did_action' )->with( 'cli_init' )->andReturnTrue();

		$expected_log = array(
			'level'      => LogLevel::WARNING,
			'message'    => 'Careful now.',
			'context'    => array( 'key' => 'value' ),
			'prepend'    => 'Warning:',
			'ansi_color' => '%y',
		);

		WP_Mock::expectFilter( 'bh_wp_cli_logger_log', $expected_log );

		( new WP_CLI_Logger() )->warning( 'Careful now.', array( 'key' => 'value' ) );

		$this->assertEquals( array( '%yWarning:%n Careful now.' ), $this->lines );
	}

	/**
	 * Returning an empty message from the filter suppresses output.
	 *
	 * TODO: NB: It seems like this test has to run last or others fail.
	 *
	 * @covers ::log
	 */
	public function test_filter_can_suppress_output(): void {

		WP_Mock::userFunction( 'did_action' )->with( 'cli_init' )->andReturnTrue();

		WP_Mock::onFilter( 'bh_wp_cli_logger_log' )
			->withAnyArgs()
			->reply( array( 'message' => '' ) );

		( new WP_CLI_Logger() )->warning( 'This will be suppressed.' );

		$this->assertEmpty( $this->lines );
		$this->assertEmpty( $this->error_multi_lines );
	}
}
