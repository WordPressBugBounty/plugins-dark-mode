<?php //phpcs:ignore
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * If direct access than exit the file.
 *
 * @package WP_MARKDOWN
 */

/** If class `WPMDE_Settings` not exists yet */
if ( ! class_exists( 'WPMDE_Settings' ) ) {
	/**
	 * This class is for settings
	 */
	class WPMDE_Settings {
		/**
		 * Instance
		 *
		 * @var null
		 */
		private static $instance = null;

		/**
		 * Settings API
		 *
		 * @var null
		 */
		private static $settings_api = null;

		/**
		 * WPMDE_Settings constructor.
		 */
		public function __construct() {
			add_action(
				'admin_init',
				array( $this, 'settings_fields' )
			);
			add_action(
				'admin_menu',
				array( $this, 'settings_menu' )
			);
		}


		/**
		 * Registers settings section and fields
		 */
		public function settings_fields() {

			$sections = array(
				array(
					'id'    => 'wpmde_general',
					// translators: %s is replaced with dashicon HTML for General Settings tab.
					'title' => sprintf(
						'%s <span>General Settings</span>',
						'<i class="dashicons dashicons-admin-generic"></i>'
					),
				),
			);

			$fields = array(

				'wpmde_general' => array(

					'only_darkmode'      => array(
						'name'     => 'only_darkmode',
						'default'  => 'off',  // Changed from 'on' to 'off'.
						'label'    => __( 'Only Dark Mode', 'dark-mode' ),
						'desc'     => __( 'Turn ON to disable all the features of this plugin except Dark Mode.', 'dark-mode' ),
						'type'     => 'switcher',
						'upcoming' => true,
					),

					'markdown_editor'    => array(
						'name'     => 'markdown_editor',
						'default'  => 'off',
						'label'    => __( 'Enable Markdown Editor', 'dark-mode' ),
						'desc'     => __( 'Enable/disable The Markdown Editor.', 'dark-mode' ),
						'type'     => 'switcher',
						'upcoming' => true,
					),

					'admin_darkmode'     => array(
						'name'    => 'admin_darkmode',
						'default' => 'on',
						'label'   => __( 'Enable Admin Darkmode', 'dark-mode' ),
						'desc'    => __( 'Enable/disable Darkmode in the admin dashboard.', 'dark-mode' ),
						'type'    => 'switcher',
					),

					'productivity_sound' => array(
						'name'     => 'productivity_sound',
						'default'  => 'off',  // Changed from 'on' to 'off'.
						'label'    => __( 'Enable Productivity Sounds', 'dark-mode' ),
						'desc'     => __( 'Enable/disable productivity sounds in the admin dashboard.', 'dark-mode' ),
						'type'     => 'switcher',
						'upcoming' => true,
					),

					// Commented out fields from your original code.
					//phpcs:ignore
					// 'gutenberg_darkmode'      => array(),
					//
					// 'classic_editor_darkmode' => array(),

					// Commented out new_fonts field from your original code.
					//phpcs:ignore
					// 'new_fonts' => array(
					// 'name'      => 'new_fonts',
					// 'default'   => 'on',
					// 'label'     => __( 'Enable New Fonts', 'dark-mode' ),
					// 'desc'      => __( 'Enable/disable new fonts for Gutenberg and Markdown editor.', 'dark-mode' ),
					// 'type'      => 'switcher',
					// ),

				),

			);

			//phpcs:ignore
			// if ( wpmde_is_classic_editor_plugin_active() ) {
			//
			//
			// unset($fields['wpmde_general']['gutenberg_darkmode']);
			//
			//
			// $fields['wpmde_general']['classic_editor_darkmode'] = array(
			// 'name'    => 'classic_editor_darkmode',
			// 'default' => 'on',
			// 'label'   => __( 'Enable Darkmode in Classic Editor', 'dark-mode' ),
			// 'desc'    => __( 'Enable/disable Darkmode in the classic editor.', 'dark-mode' ),
			// 'type'    => 'switcher',
			// );
			// }else{
			// unset($fields['wpmde_general']['classic_editor_darkmode']);
			//
			// $fields['wpmde_general']['gutenberg_darkmode'] = array(
			// 'name'    => 'gutenberg_darkmode',
			// 'default' => 'off',
			// 'label'   => __( 'Enable Darkmode in Gutenberg', 'dark-mode' ),
			// 'desc'    => __( 'Enable/disable Darkmode in the Gutenberg editor.', 'dark-mode' ),
			// 'type'    => 'switcher',
			// );
			// }.

			self::$settings_api = new WPPOOL_Settings_API();

			// set sections and fields.
			self::$settings_api->set_sections( $sections );
			self::$settings_api->set_fields( $fields );

			// initialize them.
			self::$settings_api->admin_init();
		}

		/**
		 * License output
		 *
		 * @return  void
		 */
		public function license_output() {
			global $wp_markdown_editor_license;
			if ( $wp_markdown_editor_license ) {
				$wp_markdown_editor_license->menu_output();
			}
		}


		/**
		 * Register the plugin page
		 */
		public function settings_menu() {
			add_options_page(
				'WP Markdown Editor Settings',
				'WP Markdown Editor',
				'manage_options',
				'wp-markdown-settings',
				array( $this, 'settings_page' )
			);
		}

		/**
		 * Display the plugin settings options page
		 */
		public function settings_page() {

			update_option( 'wp_markdown_editor_update_notice_interval', 'off' );

			?>
				<div class="wrap wp-markodwn-editor-settings-page">
					<h2><?php esc_html_e( 'WP Markdown Editor Settings', 'dark-mode' ); ?></h2>
					<?php self::$settings_api->show_settings(); ?>
				</div>
			<?php
		}

		/**
		 * Instance.
		 *
		 * @return WPMDE_Settings|null
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}

WPMDE_Settings::instance();