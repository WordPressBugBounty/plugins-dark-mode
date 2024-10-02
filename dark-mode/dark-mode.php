<?php //phpcs:ignore
/**
 * Plugin Name: WP Markdown Editor (Formerly Dark Mode)
 * Plugin URI: https://wppool.dev/wp-markdown-editor
 * Description: Quickly edit content in WordPress by getting an immersive, peaceful and natural writing experience with the coolest editor..
 * Author: WPPOOL
 * Author URI: https://wppool.dev
 * Text Domain: dark-mode
 * Version: 4.1.7
 *
 * @package WP_Markdown
 */

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'Dark_Mode' ) ) {
	define( 'DARK_MODE_VERSION', '4.1.7' );
	define( 'DARK_MODE_FILE', __FILE__ );
	define( 'DARK_MODE_PATH', plugin_dir_path( DARK_MODE_FILE ) );
	define( 'DARK_MODE_INCLUDES', DARK_MODE_PATH . '/includes' );
	define( 'DARK_MODE_URL', plugin_dir_url( DARK_MODE_FILE ) );

	$mark_basename   = basename( __FILE__ );
	$plugin_basename = plugin_basename( __FILE__ );
	$plugin_dir      = str_replace( $mark_basename, '', $plugin_basename );
	$plugin_dir_name = preg_replace( '/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $plugin_dir );

	define( 'PLUGIN_DIR_NAME', $plugin_dir_name );

	register_activation_hook(
		__FILE__, function () {
			include DARK_MODE_PATH . '/includes/class-install.php';
		}
	);

	include DARK_MODE_PATH . '/includes/class-dark-mode.php';
}