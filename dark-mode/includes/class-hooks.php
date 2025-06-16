<?php //phpcs:ignore
/**
 * If direct access than exit the file.
 *
 * @package WP_MARKDOWN
 */

defined( 'ABSPATH' ) || exit();

/** Check if class `Dark_Mode_Hooks` not exists yet */
if ( ! class_exists( 'Dark_Mode_Hooks' ) ) {
	/**
	 * This class load hook.
	 */
	class Dark_Mode_Hooks {

		/**
		 * Instance
		 *
		 * @var null
		 */
		private static $instance = null;

		/**
		 * Dark_Mode_Hooks constructor.
		 */
		public function __construct() {
			add_action(
				'admin_bar_menu',
				array( $this, 'render_admin_switcher_menu' ),
				2000
			);
			add_action(
				'admin_head',
				array( $this, 'head_scripts' )
			);

			//phpcs:ignore
			// add_action( 'admin_footer', array( $this, 'footer_scripts' ) );

			add_action(
				'wp_ajax_wp_markdown_editor_update_notice',
				array( $this, 'handle_update_notice' )
			);
		}


		/**
		 * Load footer scripts
		 *
		 * @return  void
		 */
		public function footer_scripts() {
			?>
			<script>
				;(function () {
					var is_saved = localStorage.getItem('dark_mode_active');

					if (!is_saved) {
						is_saved = 1;
					}

					var is_gutenberg = document.querySelector('html').classList.contains('block-editor-page');

					if(is_gutenberg) return;


					if (is_saved && is_saved != 0) {
						document.querySelector('html').classList.add('dark-mode');
					}
				})();

			</script>
			<?php
		}

		/**
		 * Load update notice
		 *
		 * @return  void
		 */
		public function handle_update_notice() {
			update_option( 'wp_markdown_editor_update_notice_interval', 'off' );
			update_option(
				sanitize_key( 'wp_markdown_editor_notices' ),
				array()
			);
			die();
		}

		/**
		 * Load head scripts
		 *
		 * @return  void
		 */
		public function head_scripts() {

			if ( ! wpmde_darkmode_enabled() ) {
				return;
			}

			?>
			<script>

				//Check Darkmode
				;(function () {
					var is_saved = localStorage.getItem('dark_mode_active');

					if (!is_saved) {
						is_saved = 1;
					}

					var is_gutenberg = document.querySelector('html').classList.contains('block-editor-page');

					if(is_gutenberg) return;


					if (is_saved && is_saved != 0) {
						document.querySelector('html').classList.add('dark-mode');
					}
				})();

				//Check OS aware mode
				;(function () {

					var is_saved = localStorage.getItem('dark_mode_active');

					if (is_saved == 0) {
						return;
					}

					//check os aware mode
					var darkMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

					try {
						// Chrome & Firefox
						darkMediaQuery.addEventListener('change', function (e) {
							var newColorScheme = e.matches ? 'dark' : 'light';

							if ('dark' === newColorScheme) {
								document.querySelector('html').classList.add('dark-mode');
							} else {
								document.querySelector('html').classList.remove('dark-mode');
							}

							window.dispatchEvent(new Event('dark_mode_init'));

						});
					} catch (e1) {
						try {
							// Safari
							darkMediaQuery.addListener(function (e) {
								var newColorScheme = e.matches ? 'dark' : 'light';

								if ('dark' === newColorScheme) {
									document.querySelector('html').classList.add('dark-mode');
								} else {
									document.querySelector('html').classList.remove('dark-mode');
								}

								window.dispatchEvent(new Event('dark_mode_init'));

							});
						} catch (e2) {
							console.error(e2);
						}
					}

					/** check init dark theme */
					if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
						document.querySelector('html').classList.add('dark-mode');
						window.dispatchEvent(new Event('dark_mode_init'));
					}

				})();

			</script>
			<?php
		}

		/**
		 * Display dark mode switcher button on the admin bar menu.
		 */
		public function render_admin_switcher_menu() {

			if ( ! wpmde_darkmode_enabled() ) {
				return;
			}

			$light_text = __( 'Light', 'dark-mode' );
			$dark_text  = __( 'Dark', 'dark-mode' );

			global $wp_admin_bar;
			$wp_admin_bar->add_menu(
				array(
					'id'    => 'dark-mode-switch',
					'title' => sprintf(
						'<div class="dark-mode-switch dark-mode-ignore">
							<div class="toggle dark-mode-ignore"></div>
							<div class="modes dark-mode-ignore">
								<p class="light dark-mode-ignore">%s</p>
								<p class="dark dark-mode-ignore">%s</p>
							</div>
						</div>',
						$light_text,
						$dark_text
					),
					'href'  => '#',
				)
			);
		}

		/**
		 * Singletone instance.
		 *
		 * @return Dark_Mode_Hooks|null
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}

Dark_Mode_Hooks::instance();