<?php //phpcs:ignore
/**
 * If direct access than exit the file.
 *
 * @package WP_MARKDOWN
 */

defined( 'ABSPATH' ) || exit();

/**
 * Class WP_Markdown_Editor_Update_4_0_7
 *
 * Handles update routine for version 4.0.7 of WP Markdown Editor.
 */
class WP_Markdown_Editor_Update_4_0_7 {

	/**
	 * Singleton instance of the class.
	 *
	 * @var WP_Markdown_Editor_Update_4_0_7|null
	 */
	private static $instance = null;

	/**
	 * WP_Markdown_Editor_Update_4_0_7 constructor.
	 *
	 * Initializes the update routine.
	 */
	public function __construct() {
		$this->update_settings();
	}

	/**
	 * Updates plugin settings for the free version when upgrading to 4.0.7.
	 *
	 * This function sets specific default values for general settings
	 * unless the PRO version is defined.
	 *
	 * @return void
	 */
	private function update_settings() {

		if ( defined( 'WPMDE_PRO_VERSION' ) ) {
			return;
		}

		$settings = (array) get_option( 'wpmde_general' );

		$settings['only_darkmode']      = 'on';
		$settings['markdown_editor']    = 'off';
		$settings['admin_darkmode']     = 'on';
		$settings['productivity_sound'] = 'off';
		$settings['new_fonts']          = 'off';

		update_option( 'wpmde_general', $settings );
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return WP_Markdown_Editor_Update_4_0_7
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

WP_Markdown_Editor_Update_4_0_7::instance();
