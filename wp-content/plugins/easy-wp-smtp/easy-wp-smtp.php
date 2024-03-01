<?php
/**
 * Plugin Name: Easy WP SMTP
 * Version: 2.0.1
 * Requires at least: 5.2
 * Requires PHP: 5.6.20
 * Plugin URI: https://easywpsmtp.com/
 * Author: Easy WP SMTP
 * Author URI: https://easywpsmtp.com/
 * Description: Fix your WordPress email delivery by sending them via a transactional email provider or an SMTP server.
 * Text Domain: easy-wp-smtp
 * Domain Path: /assets/languages
 */

if ( ! defined( 'EasyWPSMTP_PLUGIN_VERSION' ) ) {
	define( 'EasyWPSMTP_PLUGIN_VERSION', '2.0.1' );
}
if ( ! defined( 'EasyWPSMTP_PHP_VERSION' ) ) {
	define( 'EasyWPSMTP_PHP_VERSION', '5.6.20' );
}
if ( ! defined( 'EasyWPSMTP_WP_VERSION' ) ) {
	define( 'EasyWPSMTP_WP_VERSION', '5.2' );
}
if ( ! defined( 'EasyWPSMTP_PLUGIN_FILE' ) ) {
	define( 'EasyWPSMTP_PLUGIN_FILE', __FILE__ );
}

/**
 * Autoloader. We need it being separate and not using Composer autoloader because of the vendor libs,
 * which are huge and not needed for most users.
 * Inspired by PSR-4 examples: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 *
 * @since 2.0.0
 *
 * @param string $class The fully-qualified class name.
 */
spl_autoload_register( function ( $class ) {

	list( $plugin_space ) = explode( '\\', $class );
	if ( $plugin_space !== 'EasyWPSMTP' ) {
		return;
	}

	$plugin_dir = basename( __DIR__ );

	// Default directory for all code is plugin's /src/.
	$base_dir = plugin_dir_path( __DIR__ ) . '/' . $plugin_dir . '/src/';

	// Get the relative class name.
	$relative_class = substr( $class, strlen( $plugin_space ) + 1 );

	// Prepare a path to a file.
	$file = wp_normalize_path( $base_dir . $relative_class . '.php' );

	// If the file exists, require it.
	if ( is_readable( $file ) ) {
		/** @noinspection PhpIncludeInspection */
		require_once $file;
	}
} );

/**
 * Global function-holder. Works similar to a singleton's instance().
 *
 * @since 2.0.0
 *
 * @return EasyWPSMTP\Core
 */
function easy_wp_smtp() {
	/**
	 * @var \EasyWPSMTP\Core
	 */
	static $core;

	if ( ! isset( $core ) ) {
		$core = new \EasyWPSMTP\Core();
	}

	return $core;
}

easy_wp_smtp();
