<?php //phpcs:ignore
/**
 * If direct access than exit the file.
 *
 * @package WP_MARKDOWN
 */

defined( 'ABSPATH' ) || exit();
/**
 * Update class
 */
class WP_Markdown_Editor_Update {


	/**
	 * The upgrades
	 *
	 * @var array
	 */
	private static $upgrades = [];

	/**
	 * Get Installed version
	 *
	 * @return  string
	 */
	public function installed_version() {
		return get_option( 'dark_mode_version' );
	}

	/**
	 * Check if the plugin needs any update
	 *
	 * @return boolean
	 */
	public function needs_update() {

		// May be it's the first install.
		if ( ! $this->installed_version() ) {
			return false;
		}

		// if previous version is lower.
		if ( version_compare( $this->installed_version(), DARK_MODE_VERSION, '<' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Perform all the necessary upgrade routines
	 *
	 * @return void
	 */
	public function perform_updates() {
		foreach ( self::$upgrades as $version ) {
			if ( version_compare( $this->installed_version(), $version, '<' ) ) {
				include DARK_MODE_INCLUDES . '/updates/update-' . $version . '.php';

				update_option( 'dark_mode_version', $version );
			}
		}

		delete_option( 'dark_mode_version' );
		update_option( 'dark_mode_version', DARK_MODE_VERSION );
	}
}
