<?php //phpcs:ignore
/**
 * If direct access than exit the file.
 *
 * @package WP_MARKDOWN
 */

defined( 'ABSPATH' ) || exit();


if ( ! class_exists( 'Dark_Mode_Admin' ) ) {
	/**
	 * Admin Class
	 */
	class Dark_Mode_Admin {
		/**
		 * Instance
		 *
		 * @var null
		 */
		private static $instance = null;

		/**
		 * Dark_Mode_Admin constructor.
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'init_update' ) );
		}

		/**
		 * Triggered before any other hook
		 *
		 * @return void
		 */
		public function init_update() {

			if ( ! class_exists( 'WP_Markdown_Editor_Update' ) ) {
				require_once DARK_MODE_INCLUDES . '/class-update.php';
			}

			$updater = new WP_Markdown_Editor_Update();

			if ( $updater->needs_update() ) {
				$updater->perform_updates();
			}
		}

		/**
		 * Instance
		 *
		 * @return Dark_Mode_Admin|null
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}

}

Dark_Mode_Admin::instance();
