<?php
/**
 * Unit suite bootstrap.
 *
 * Loads the base test case, which is not registered with Composer's autoloader.
 *
 * @package brianhenryie/bh-wp-cli-logger
 */

WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();
