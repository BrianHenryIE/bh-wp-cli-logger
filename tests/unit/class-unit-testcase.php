<?php
/**
 * Superclass for tests, to allow adding common functions and common setup and teardown code.
 *
 * @package brianhenryie/bh-wp-cli-logger
 */

namespace BrianHenryIE\WP_CLI_Logger;

use BrianHenryIE\ColorLogger\ColorLogger;
use Codeception\Test\Unit;
use Psr\Log\Test\TestLogger;
use WP_Mock;

/**
 * Common TestCase.
 */
class Unit_Testcase extends Unit {

	/**
	 * TestLogger for `::hasWarningThatContains()` etc.
	 *
	 * @var TestLogger|ColorLogger
	 */
	protected TestLogger $logger;

	/**
	 * Setup WP_Mock and TestLogger:ColorLogger.
	 */
	protected function setUp(): void {
		parent::setUp();
		WP_Mock::setUp();

		$this->logger = new ColorLogger();
	}

	/**
	 * Reset WP_Mock and Patchwork.
	 */
	protected function tearDown(): void {
		parent::tearDown();
		WP_Mock::tearDown();
		\Patchwork\restoreAll();
	}
}
