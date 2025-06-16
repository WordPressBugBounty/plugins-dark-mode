<?php //phpcs:ignore
/**
 * The class loads all admin scripts and notice
 *
 * @package WP_MARKDOWN
 */

defined( 'ABSPATH' ) || exit();

/**
 * Dark Mode Class
 */
class Dark_Mode {
	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	/**
	 * Make WordPress Dark.
	 *
	 * @return void
	 * @since 1.1 Changed admin_enqueue_scripts hook to 99 to override admin colour scheme styles.
	 * @since 1.3 Added hook for the Feedback link in the toolbar.
	 * @since 1.8 Added filter for the plugin table links and removed admin toolbar hook.
	 * @since 3.1 Added the admin body class filter.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->includes();

		add_action(
			'admin_enqueue_scripts',
			array( $this, 'admin_scripts' ),
			99,
			0
		);

		add_filter(
			'plugin_action_links_' . plugin_basename( DARK_MODE_FILE ),
			array( $this, 'plugin_action_links' )
		);

		add_action(
			'admin_notices',
			array( $this, 'print_notices' ),
			15
		);
		// phpcs:ignore
		// add_filter( 'admin_body_class', array( $this, 'add_body_class' ), 10, 1 );
	}

	/**
	 * Add body class
	 *
	 * @param  string $classes  Add class in body tag.
	 *
	 * @return  string
	 */
	public static function add_body_class( $classes ) {
		if ( wpmde_darkmode_enabled() ) {
			$classes .= ' dark-mode ';
		}

		return $classes;
	}

	/**
	 * Admin Notice
	 *
	 * @return void
	 */
	public function print_notices() {
		$notices = get_option(
			sanitize_key( 'wp_markdown_editor_notices' ),
			array()
		);
		foreach ( $notices as $notice ) { ?>
			<div class="notice notice-<?php echo esc_attr( $notice['class'] ); ?>">
				<?php echo wp_kses_post( $notice['message'] ); ?>
			</div>
			<?php
			update_option(
				sanitize_key( 'wp_markdown_editor_notices' ),
				array()
			);
		}
	}

	/**
	 * Includes require files
	 */
	public function includes() {
		include DARK_MODE_PATH . '/includes/functions.php';
		include DARK_MODE_PATH . '/includes/class-settings-api.php';
		include DARK_MODE_PATH . '/includes/class-settings.php';
		include DARK_MODE_PATH . '/includes/class-hooks.php';

		if ( is_admin() ) {
			include DARK_MODE_PATH . '/includes/class-admin.php';
		}

		// Require WPPOOL SDK files.
		if ( file_exists( DARK_MODE_PATH . '/includes/wppool/class-plugin.php' ) ) {
			require_once DARK_MODE_PATH . '/includes/wppool/class-plugin.php';
		}
	}

	/**
	 * Add the scripts to the dashboard if enabled.
	 *
	 * @return void
	 * @since 2.1 Removed the register stylesheet function.
	 *
	 * @since 1.0
	 */
	public function admin_scripts() {
		wp_enqueue_style(
			'wp-markdown-editor-admin',
			DARK_MODE_URL . 'assets/css/admin.css',
			false,
			DARK_MODE_VERSION
		);

		wp_enqueue_script(
			'jquery.syotimer',
			DARK_MODE_URL . 'assets/js/jquery.syotimer.min.js',
			array( 'jquery' ),
			'2.1.2',
			true
		);
		wp_enqueue_script(
			'wp-markdown-editor-admin',
			DARK_MODE_URL . 'assets/js/admin.min.js',
			array( 'jquery', 'wp-util' ),
			DARK_MODE_VERSION,
			true
		);

		wp_localize_script(
			'wp-markdown-editor-admin',
			'markdown',
			array(
				'plugin_url' => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'wp_markdown_admin_nonce' ),
			)
		);
	}

	/**
	 * Check pro plugin activate or not
	 *
	 * @return  mixed
	 */
	public function is_pro_active() {
		//phpcs:ignore
		return apply_filters( 'wp_markdown_editor/is_pro_active', false );
	}

	/**
	 * Plugin action links
	 *
	 * @param   array $links plugin active link.
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {

		$links[] = sprintf(
			'<a href="%1$s" >%2$s</a>',
			admin_url( 'options-general.php?page=wp-markdown-settings' ),
			__( 'Settings', 'dark-mode' )
		);

		return $links;
	}

	/**
	 * Singleton instance
	 *
	 * @return Dark_Mode|null
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

Dark_Mode::instance();