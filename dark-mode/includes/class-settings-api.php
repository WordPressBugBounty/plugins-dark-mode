<?php //phpcs:ignore
/**
 * If direct access than exit the file.
 *
 * @package WP_MARKDOWN
 */

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WPPOOL_Settings_API' ) ) {
	/**
	 * Settings_API Class
	 */
	class WPPOOL_Settings_API {
		/**
		 * Settings sections array
		 *
		 * @var array
		 */
		protected $settings_sections = array();
		/**
		 * Settings fields array
		 *
		 * @var array
		 */
		protected $settings_fields = array();

		/**
		 * Settings_API construct
		 *
		 * @return  void
		 */
		public function __construct() {
			add_action(
				'admin_enqueue_scripts',
				array( $this, 'admin_enqueue_scripts' )
			);
		}

		/**
		 * Enqueue scripts and styles
		 */
		public function admin_enqueue_scripts() {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_media();
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'jquery' );

			wp_enqueue_script( 'jquery-ui-slider' );
		}

		/**
		 * Set settings sections
		 *
		 * @param  array $sections  Get section information.
		 *
		 * @return  object
		 *
		 *  @since 1.0.0
		 */
		public function set_sections( $sections ) {
			$this->settings_sections = $sections;

			return $this;
		}

		/**
		 * Add a single section
		 *
		 * @param array $section add section information.
		 *
		 * @return $this
		 *
		 * @since 1.0.0
		 */
		public function add_section( $section ) {
			$this->settings_sections[] = $section;

			return $this;
		}

		/**
		 * Set settings fields
		 *
		 * @param array $fields Settings Field.
		 *
		 * @return $this
		 *
		 * @since 1.0.0
		 */
		public function set_fields( $fields ) {
			$this->settings_fields = $fields;

			return $this;
		}

		/**
		 * Set fields
		 *
		 * @param array $section Sections.
		 * @param array $field fields.
		 *
		 * @return $this
		 *
		 * @since 1.0.0
		 */
		public function add_field( $section, $field ) {
			$defaults                            = array(
				'name'  => '',
				'label' => '',
				'desc'  => '',
				'type'  => 'text',
			);
			$arg                                 = wp_parse_args( $field, $defaults );
			$this->settings_fields[ $section ][] = $arg;

			return $this;
		}

		/**
		 * Initialize and registers the settings sections and fileds to WordPress
		 *
		 * Usually this should be called at `admin_init` hook.
		 *
		 * This function gets the initiated settings sections and fields. Then
		 * registers them to WordPress and ready for use.
		 */
		public function admin_init() {
			// register settings sections.
			foreach ( $this->settings_sections as $section ) {
				if ( false === get_option( $section['id'] ) ) {
					add_option( $section['id'] );
				}
				if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {
					$section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
					$callback        = function () use ( $section ) {
						//phpcs:ignore
						echo str_replace( '"', '\"', $section['desc'] );
					};
				} elseif ( isset( $section['callback'] ) ) {
					$callback = $section['callback'];
				} else {
					$callback = null;
				}
				add_settings_section(
					$section['id'],
					$section['title'],
					$callback,
					$section['id']
				);
			}

			// register settings fields.
			foreach ( $this->settings_fields as $section => $field ) {
				foreach ( $field as $option ) {
					$name     = $option['name'];
					$type     = isset( $option['type'] ) ? $option['type'] : 'text';
					$label    = isset( $option['label'] ) ? $option['label'] : '';
					$callback = isset( $option['callback'] )
						? $option['callback']
						: array(
							$this,
							'callback_' . $type,
						);

					$args = array(
						'id'                => $name,
						'class'             => isset( $option['class'] ) ? $option['class'] : $name,
						'label_for'         => "{$section}[{$name}]",
						'desc'              => isset( $option['desc'] ) ? $option['desc'] : '',
						'name'              => $label,
						'section'           => $section,
						'size'              => isset( $option['size'] ) ? $option['size'] : null,
						'options'           => isset( $option['options'] ) ? $option['options'] : '',
						'std'               => isset( $option['default'] ) ? $option['default'] : '',
						'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
						'type'              => $type,
						'placeholder'       => isset( $option['placeholder'] ) ? $option['placeholder'] : '',
						'min'               => isset( $option['min'] ) ? $option['min'] : '',
						'max'               => isset( $option['max'] ) ? $option['max'] : '',
						'step'              => isset( $option['step'] ) ? $option['step'] : '',
						'upcoming'          => isset( $option['upcoming'] ) ? $option['upcoming'] : false,
					);

					add_settings_field(
						"{$section}[{$name}]",
						$label,
						$callback,
						$section,
						$section,
						$args
					);
				}
			}
			// creates our settings in the options table.
			foreach ( $this->settings_sections as $section ) {
				register_setting(
					$section['id'],
					$section['id'],
					array( $this, 'sanitize_options' )
				);
			}
		}

		/**
		 * Get field description for display.
		 *
		 * @param array $args args array.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_field_description( $args ) {
			if ( ! empty( $args['desc'] ) ) {
				$desc = sprintf(
					'<p class="description">%s</p>',
					$args['desc']
				);
			} else {
				$desc = '';
			}

			return $desc;
		}

		/**
		 * Displays a text field for a settings field
		 *
		 * @param   array $args  settings field args.
		 */
		public function callback_text( $args ) {
			$value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$type        = isset( $args['type'] ) ? $args['type'] : 'text';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			$html        = sprintf(
				'<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s/>',
				$type,
				$size,
				$args['section'],
				$args['id'],
				$value,
				$placeholder
			);
			$html       .= $this->get_field_description( $args );

			$allowed_tags = array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'input' => array(
						'type'        => true,
						'class'       => true,
						'id'          => true,
						'name'        => true,
						'value'       => true,
						'placeholder' => true,
					),
				)
			);

			echo wp_kses( $html, $allowed_tags );
		}

		/**
		 * Displays a url field for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_url( $args ) {
			$this->callback_text( $args );
		}

		/**
		 * Displays a number field for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_number( $args ) {
			$value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$type        = isset( $args['type'] ) ? $args['type'] : 'number';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			$min         = ( '' === $args['min'] ) ? '' : ' min="' . $args['min'] . '"';
			$max         = ( '' === $args['max'] ) ? '' : ' max="' . $args['max'] . '"';
			$step        = ( '' === $args['step'] ) ? '' : ' step="' . $args['step'] . '"';
			$html        = sprintf(
				'<input type="%1$s" class="%2$s-number" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s%8$s%9$s/>',
				$type,
				$size,
				$args['section'],
				$args['id'],
				$value,
				$placeholder,
				$min,
				$max,
				$step
			);
			$html       .= $this->get_field_description( $args );

			$allowed_tags = array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'input' => array(
						'type'        => true,
						'class'       => true,
						'id'          => true,
						'name'        => true,
						'value'       => true,
						'placeholder' => true,
					),
				)
			);

			echo wp_kses( $html, $allowed_tags );
		}

		/**
		 * Displays a heading field for a settings field
		 *
		 * @param array $args settings field args.
		 * @return void
		 */
		public function callback_heading( $args ) {
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$html  = sprintf(
				'<h2 class="wppool-settings-heading">%1$s</h2>',
				$value
			);
			$html .= $this->get_field_description( $args );
			echo wp_kses_post( $html );
		}
		/**
		 * Displays a checkbox for a settings field
		 *
		 * @param array $args settings field args.
		 * @return void
		 */
		public function callback_checkbox( $args ) {
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$html  = '<fieldset>';
			$html .= sprintf(
				'<label for="wppool-%1$s[%2$s]">',
				$args['section'],
				$args['id']
			);
			$html .= sprintf(
				'<input type="hidden" name="%1$s[%2$s]" value="off" />',
				$args['section'],
				$args['id']
			);
			$html .= sprintf(
				'<input type="checkbox" class="checkbox" id="wppool-%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s />',
				$args['section'],
				$args['id'],
				checked( $value, 'on', false )
			);
			$html .= sprintf( '%1$s</label>', $args['desc'] );
			$html .= '</fieldset>';

			$allowed_tags = array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'input' => array(
						'type'        => true,
						'class'       => true,
						'id'          => true,
						'name'        => true,
						'value'       => true,
						'placeholder' => true,
					),
				)
			);

			echo wp_kses( $html, $allowed_tags );
		}

		/**
		 * Displays a switcher for a settings field
		 *
		 * @param array $args settings field args.
		 *
		 * @return void
		 * @version 1.0.0
		 */
		public function callback_switcher( $args ) {
			$value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$is_upcoming = isset( $args['upcoming'] ) && $args['upcoming'];

			$html  = '<fieldset class="switcher" style="display: flex; align-items: center;">';
			$html .= sprintf(
				'<label for="wppool-%1$s[%2$s]" style="display: flex; align-items: center;">',
				$args['section'],
				$args['id']
			);
			$html .= sprintf(
				'<div class="wppool-switcher">
					<input type="hidden" name="%1$s[%2$s]" value="off" />
					<input type="checkbox" name="%1$s[%2$s]" id="wppool-%1$s[%2$s]" value="on" %3$s/>
					<div class="wp-dark-mode-ignore">
						<label for="wppool-%1$s[%2$s]"></label>
					</div>
				</div>',
				$args['section'],
				$args['id'],
				checked( $value, 'on', false )
			);

			if ( $is_upcoming ) {
				$html .= '<span class="upcoming-tag" style="margin-left: 12px; padding: 2px 10px; background: #f1c40f; color: #222; border-radius: 12px; font-size: 12px; font-weight: 600; letter-spacing: 1px;">Upcoming</span>';
			}

			$html .= '</label>';
			$html .= sprintf( '<p class="description"> %1$s</p>', $args['desc'] );
			$html .= '</fieldset>';

			$allowed_html = array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'fieldset' => array(
						'class' => true,
						'style' => true,
					),
					'label'    => array(
						'for'   => true,
						'style' => true,
					),
					'div'      => array(
						'class' => true,
					),
					'input'    => array(
						'type'    => true,
						'name'    => true,
						'id'      => true,
						'value'   => true,
						'checked' => true,
					),
					'span'     => array(
						'class' => true,
						'style' => true,
					),
					'p'        => array(
						'class' => true,
					),
				)
			);

			echo wp_kses( $html, $allowed_html );
		}


		/**
		 * Displays a multi-checkbox for a settings field
		 *
		 * @param array $args settings field args.
		 * @return void
		 */
		public function callback_multicheck( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$html  = '<fieldset>';
			$html .= sprintf(
				'<input type="hidden" name="%1$s[%2$s]" value="" />',
				$args['section'],
				$args['id']
			);
			foreach ( $args['options'] as $key => $label ) {
				$checked = isset( $value[ $key ] ) ? $value[ $key ] : '0';

				$html .= sprintf(
					'<label for="wppool-%1$s[%2$s][%3$s]">',
					$args['section'],
					$args['id'],
					$key
				);

				$html .= sprintf(
					'<input type="checkbox" class="checkbox" id="wppool-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />',
					$args['section'],
					$args['id'],
					$key,
					checked( $checked, $key, false )
				);

				$html .= sprintf( '%1$s</label><br>', $label );
			}

			$html .= $this->get_field_description( $args );
			$html .= '</fieldset>';

			$allowed_tags = array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'input' => array(
						'type'        => true,
						'class'       => true,
						'id'          => true,
						'name'        => true,
						'value'       => true,
						'placeholder' => true,
					),
					'label' => array(
						'for' => true,
					),
					'br'    => array(),
					'div'   => array(
						'class' => true,
					),
				)
			);

			echo wp_kses( $html, $allowed_tags );
		}

		/**
		 * Displays a radio button for a settings field
		 *
		 * @param array $args settings field args.
		 * @return void
		 */
		public function callback_radio( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$html  = '<fieldset>';
			foreach ( $args['options'] as $key => $label ) {
				$html .= sprintf(
					'<label for="wppool-%1$s[%2$s][%3$s]">',
					$args['section'],
					$args['id'],
					$key
				);
				$html .= sprintf(
					'<input type="radio" class="radio" id="wppool-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />',
					$args['section'],
					$args['id'],
					$key,
					checked( $value, $key, false )
				);
				$html .= sprintf( '%1$s</label><br>', $label );
			}
			$html .= $this->get_field_description( $args );
			$html .= '</fieldset>';

			$allowed_tags = array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'input' => array(
						'type'        => true,
						'class'       => true,
						'id'          => true,
						'name'        => true,
						'value'       => true,
						'placeholder' => true,
					),
				)
			);

			echo wp_kses( $html, $allowed_tags );
		}

		/**
		 * Displays a select box for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_select( $args ) {
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$html  = sprintf(
				'<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">',
				$size,
				$args['section'],
				$args['id']
			);
			foreach ( $args['options'] as $key => $label ) {
				$html .= sprintf(
					'<option value="%s"%s>%s</option>',
					$key,
					selected( $value, $key, false ),
					$label
				);
			}
			$html .= sprintf( '</select>' );
			$html .= $this->get_field_description( $args );

			$allowed_tags = array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'select' => array(
						'class' => true,
						'name'  => true,
						'id'    => true,
					),
					'option' => array(
						'value'    => true,
						'selected' => true,
					),
				)
			);

			echo wp_kses( $html, $allowed_tags );
		}

		/**
		 * Displays a textarea for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_textarea( $args ) {
			$value       = esc_textarea( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			$html        = sprintf(
				'<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%4$s>%5$s</textarea>',
				$size,
				$args['section'],
				$args['id'],
				$placeholder,
				$value
			);
			$html       .= $this->get_field_description( $args );

			$allowed_tags = array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'textarea' => array(
						'rows'        => true,
						'cols'        => true,
						'class'       => true,
						'id'          => true,
						'name'        => true,
						'placeholder' => true,
					),
				)
			);

			echo wp_kses( $html, $allowed_tags );
		}

		/**
		 * Run callback functions
		 *
		 * @param array $args Function with the context.
		 * @return void
		 */
		public function callback_cb_function( $args ) {
			call_user_func( $args['std'] );
		}

		/**
		 * Displays the html for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_html( $args ) {
			$allowed_tags = wp_kses_allowed_html( 'post' );
			$allowed_tags = array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'input'    => array(
						'type'        => true,
						'class'       => true,
						'id'          => true,
						'name'        => true,
						'value'       => true,
						'placeholder' => true,
					),
					'select'   => array(
						'class' => true,
						'name'  => true,
						'id'    => true,
					),
					'option'   => array(
						'value'    => true,
						'selected' => true,
					),
					'textarea' => array(
						'rows'        => true,
						'cols'        => true,
						'class'       => true,
						'id'          => true,
						'name'        => true,
						'placeholder' => true,
					),
					'p'        => array(
						'class' => true,
					),
					'div'      => array(
						'class' => true,
					),
				)
			);

			echo wp_kses( $this->get_field_description( $args ), $allowed_tags );
		}

		/**
		 * Displays a rich text textarea for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_wysiwyg( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : '500px';
			echo '<div style="max-width: ' . esc_attr( $size ) . ';">';
			$editor_settings = array(
				'teeny'         => true,
				'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
				'textarea_rows' => 10,
			);
			if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
				$editor_settings = array_merge( $editor_settings, $args['options'] );
			}
			wp_editor(
				$value,
				$args['section'] . '-' . $args['id'],
				$editor_settings
			);
			echo '</div>';

			$allowed_tags = array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'div' => array(
						'style' => true,
					),
				)
			);
			echo wp_kses( $this->get_field_description( $args ), $allowed_tags );
		}

		/**
		 * Displays a file upload field for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_file( $args ) {
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$id    = $args['section'] . '[' . $args['id'] . ']';
			$label = isset( $args['options']['button_label'] )
				? $args['options']['button_label'] : __( 'Choose File', 'dark-mode' );
			$html  = sprintf(
				'<input type="text" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>',
				$size,
				$args['section'],
				$args['id'],
				$value
			);
			$html .= '<input type="button" class="button wpsa-browse" value="' . $label . '" />';
			$html .= $this->get_field_description( $args );

			$allowed_tags = array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'input' => array(
						'type'        => true,
						'class'       => true,
						'id'          => true,
						'name'        => true,
						'value'       => true,
						'placeholder' => true,
					),
					'p'     => array(
						'class' => true,
					),
					'div'   => array(
						'class' => true,
					),
					'label' => array(
						'for' => true,
					),
				)
			);

			echo wp_kses( $html, $allowed_tags );
		}

		/**
		 * Displays a password field for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_password( $args ) {
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$html  = sprintf(
				'<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>',
				$size,
				$args['section'],
				$args['id'],
				$value
			);
			$html .= $this->get_field_description( $args );

			$allowed_tags = array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'input' => array(
						'type'        => true,
						'class'       => true,
						'id'          => true,
						'name'        => true,
						'value'       => true,
						'placeholder' => true,
					),
				)
			);

			echo wp_kses( $html, $allowed_tags );
		}

		/**
		 * Displays a color picker field for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_color( $args ) {
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$html  = sprintf(
				'<input type="text" class="%1$s-text wp-color-picker-field wppool-color-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />',
				$size,
				$args['section'],
				$args['id'],
				$value,
				$args['std']
			);
			$html .= $this->get_field_description( $args );

			$allowed_tags = array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'input' => array(
						'type'        => true,
						'class'       => true,
						'id'          => true,
						'name'        => true,
						'value'       => true,
						'placeholder' => true,
					),
					'div'   => array(
						'class' => true,
					),
					'label' => array(
						'for' => true,
					),
				)
			);

			echo wp_kses( $html, $allowed_tags );
		}

		/**
		 * Displays a select box for creating the pages select box
		 *
		 * @param array $args settings field args.
		 */
		public function callback_pages( $args ) {
			$dropdown_args = array(
				'selected' => esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) ),
				'name'     => esc_html( $args['section'] ) . esc_html( '[' . $args['id'] . ']' ),
				'id'       => esc_html( $args['section'] ) . esc_html( '[' . $args['id'] . ']' ),
				'echo'     => 1,
			);

			// phpcs:ignore
			wp_dropdown_pages( $dropdown_args );
		}

		/**
		 * Sanitize callback for Settings API
		 *
		 * @param  mixed $options The options to sanitize.
		 * @return mixed
		 */
		public function sanitize_options( $options ) {
			if ( ! $options ) {
				return $options;
			}
			foreach ( $options as $option_slug => $option_value ) {
				$sanitize_callback = $this->get_sanitize_callback( $option_slug );
				// If callback is set, call it.
				if ( $sanitize_callback ) {
					$options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
					continue;
				}
			}

			return $options;
		}

		/**
		 * Displays a slider field for a settings field
		 *
		 * @param array $args settings field args.
		 *
		 * @return void
		 * @version 1.0.0
		 */
		public function callback_slider( $args ) {
			$min   = ! empty( $args['min'] ) ? $args['min'] : 0;
			$max   = ! empty( $args['max'] ) ? $args['max'] : 100;
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );

			$html = sprintf(
				'
			<div class="wppool-slider" data-min="%4$s" data-max="%5$s" data-value="%6$s">
			<input type="hidden" id="%1$s[%2$s]" name="%1$s[%2$s]" value="%3$s" />
			<div class="wppool-slider-handle ui-slider-handle"></div>
			</div>
			',
				$args['section'],
				$args['id'],
				$args['std'],
				$min,
				$max,
				$value
			);

			$html .= $this->get_field_description( $args );

			$allowed_tags = array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'input' => array(
						'type'        => true,
						'class'       => true,
						'id'          => true,
						'name'        => true,
						'value'       => true,
						'placeholder' => true,
					),
					'div'   => array(
						'class'      => true,
						'data-min'   => true,
						'data-max'   => true,
						'data-value' => true,
					),
				)
			);

			echo wp_kses( $html, $allowed_tags );
		}

		/**
		 * Get sanitization callback for given option slug
		 *
		 * @param string $slug option slug.
		 * @return mixed string or bool false
		 */
		public function get_sanitize_callback( $slug = '' ) {
			if ( empty( $slug ) ) {
				return false;
			}
			// Iterate over registered fields and see if we can find proper callback.
			foreach ( $this->settings_fields as $section => $options ) {
				foreach ( $options as $option ) {
					if ( $option['name'] !== $slug ) {
						continue;
					}

					// Return the callback name.
					return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] )
						? $option['sanitize_callback'] : false;
				}
			}

			return false;
		}

		/**
		 * Get the value of a settings field
		 *
		 * @param string $option settings field name.
		 * @param string $section the section name this field belongs to.
		 * @param string $default default text if it's not found.
		 *
		 * @return string
		 */
		public function get_option( $option, $section, $default = '' ) {
			$options = get_option( $section );
			if ( isset( $options[ $option ] ) ) {
				return $options[ $option ];
			}

			return $default;
		}

		/**
		 * Show navigations as tab.
		 *
		 * Shows all the settings section labels as tab
		 *
		 * @return void
		 * @version 1.0.0
		 */
		public function show_navigation() {
			$count = count( $this->settings_sections );

			// Don't show the navigation if only one section exists.
			if ( 1 === $count ) {
				return;
			}

			$nav_items = array_map(
				function ( $tab ) {
					return sprintf(
						'<li><a href="#%1$s" id="%1$s-tab">%2$s</a></li>',
						esc_attr( $tab['id'] ),
						$tab['title'] // Don't escape HTML here since we want icons to render.
					);
				},
				$this->settings_sections
			);

			$escaped_nav_items = implode( '', $nav_items );

			printf(
				'<div class="wppool-settings-sidebar"><ul>%s</ul></div>',
				wp_kses_post( $escaped_nav_items )
			);
		}

		/**
		 * Show the section settings forms
		 *
		 * This function displays every sections in a different form
		 */
		public function show_forms() {
			$this->style_fix();
			?>
			<div class="wppool-settings-content">
				<?php
				foreach ( $this->settings_sections as $form ) {
					?>
					<div id="<?php echo esc_html( $form['id'] ); ?>" class="group" style="display: none;">

					<?php
					if ( 'wpmde_license' === $form['id'] ) {
						do_action( 'wsa_form_top_' . $form['id'], $form );

						settings_fields( $form['id'] );

						do_settings_sections( $form['id'] );

						do_action( 'wsa_form_bottom_' . $form['id'], $form );
					} else {
						?>

						<form method="post" action="options.php">
							<?php

							do_action( 'wsa_form_top_' . $form['id'], $form );

							settings_fields( $form['id'] );

							do_settings_sections( $form['id'] );

							do_action( 'wsa_form_bottom_' . $form['id'], $form );

							if ( isset( $this->settings_fields[ $form['id'] ] ) ) {
								?>
								<div style="padding-left: 10px">
									<?php
									submit_button(
										'Save Settings',
										'primary',
										'save_settings'
									);
									?>
								</div>
							<?php } ?>

						</form>

					<?php } ?>
					</div>
					<?php
				}

				do_action( 'wppool_after_settings' );

				?>
			</div>
			<?php
			$this->script();
		}

		/**
		 * Show dark mode settings
		 *
		 * @return void
		 * @version 1.0.0
		 */
		public function show_settings() {
			echo '<div class="wppool-settings d-flex">';
			$this->show_navigation();
			$this->show_forms();
			echo '</div>';
		}

		/**
		 * Tabbable JavaScript codes & Initiate Color Picker
		 *
		 * This code uses localstorage for displaying active tabs
		 */
		public function script() {
			?>
			<script>
				jQuery(document).ready(function ($) {
					$(".wppool-slider").each(function () {
						const $slider = $(this);
						const min = $slider.data('min');
						const max = $slider.data('max');
						const value = $slider.data('value');


						$slider.slider({
							range: 'min',
							step: 5,
							min,
							max,
							value,
							create: function () {
								const handle = $(".wppool-slider-handle", $slider);
								handle.text($(this).slider("value"));
							},

							slide: function (event, ui) {
								const handle = $(".wppool-slider-handle", $slider);
								$("input", $(this)).val(ui.value);
								handle.text(ui.value);
							}
						});
					});

					//Initiate Color Picker
					$('.wp-color-picker-field').wpColorPicker();

					$('.wp-picker-container').addClass('wp-dark-mode-ignore');

					// Switches option sections
					$('.group').hide();
					var activetab = '';
					if (typeof (localStorage) != 'undefined') {
						activetab = localStorage.getItem("activetab");
					}
					//if url has section id as hash then set it as active or override the current local storage value
					if (window.location.hash) {
						activetab = window.location.hash;
						if (typeof (localStorage) != 'undefined') {
							localStorage.setItem("activetab", activetab);
						}
					}
					if (activetab != '' && $(activetab).length) {
						$(activetab).fadeIn();
					} else {
						$('.group:first').fadeIn();
					}
					$('.group .collapsed').each(function () {
						$(this).find('input:checked').parent().parent().parent().nextAll().each(
							function () {
								if ($(this).hasClass('last')) {
									$(this).removeClass('hidden');
									return false;
								}
								$(this).filter('.hidden').removeClass('hidden');
							});
					});

					if (activetab != '' && $(activetab + '-tab').length) {
						$(activetab + '-tab').closest('li').addClass('active');
					} else {
						$('.wppool-settings-sidebar  li:first').addClass('active');
					}

					$('.wppool-settings-sidebar li a').click(function (evt) {
						$('.wppool-settings-sidebar li').removeClass('active');
						$(this).closest('li').addClass('active').blur();

						var clicked_group = $(this).attr('href');
						if (typeof (localStorage) != 'undefined') {
							localStorage.setItem("activetab", $(this).attr('href'));
						}
						$('.group').hide();
						$(clicked_group).fadeIn();
						evt.preventDefault();
					});

					$('.wpsa-browse').on('click', function (event) {
						event.preventDefault();
						var self = $(this);
						// Create the media frame.
						var file_frame = wp.media.frames.file_frame = wp.media({
							title: self.data('uploader_title'),
							button: {
								text: self.data('uploader_button_text'),
							},
							multiple: false
						});
						file_frame.on('select', function () {
							attachment = file_frame.state().get('selection').first().toJSON();
							self.prev('.wpsa-url').val(attachment.url).change();
						});
						// Finally, open the modal
						file_frame.open();
					});

					$(document).on('click', '.image-choose-opt:not(.disabled)', function () {
						$('.image-choose-opt').removeClass('active');
						$(this).addClass('active');
					});

					$(document).on('click', '.container-disabled label', function (event) {
						event.preventDefault();
					});

					$(document).on('click', '.container-disabled a', function (event) {
						event.stopPropagation();
						event.stopImmediatePropagation();
					});

				});
			</script>
			<?php
		}

		/**
		 * Fix style depend on version setting page css
		 *
		 * @return void
		 * @version 1.0.0
		 */
		public function style_fix() {
			global $wp_version;
			?>
			<style type="text/css">
				<?php if ( version_compare( $wp_version, '3.8', '<=' ) ) : ?>
				/** WordPress 3.8 Fix **/
				.form-table th {
					padding: 20px 10px;
				}

				<?php endif; ?>
				.wppool-settings *, .wppool-settings *::before, .wppool-settings *::after {
					box-sizing: border-box;
				}

				.wppool-settings {
					margin: 16px 0;
				}

				.wppool-settings.d-flex {
					display: -ms-flexbox !important;
					display: flex !important;
				}

				.wppool-settings-sidebar {
					position: relative;
					z-index: 1;
					min-width: 185px;
					background-color: #eaeaea;
					border-bottom: 1px solid #cccccc;
					border-left: 1px solid #cccccc;
				}

				.wppool-settings-sidebar > ul {
					margin: 0;
					/*overflow: hidden;*/
				}

				.wppool-settings-sidebar > ul > li {
					margin: 0;
					/*overflow: hidden;*/
				}

				.wppool-settings-sidebar > ul > li:first-child a {
					border-top-color: #cccccc;
				}

				.wppool-settings-sidebar > ul > li a {
					display: flex;
					align-items: center;
					justify-content: flex-start;
					padding: 0 10px;
					margin: 0 -1px 0 0;
					overflow: hidden;
					font-size: 13px;
					font-weight: 700;
					line-height: 3;
					color: #777;
					text-decoration: none;
					text-overflow: ellipsis;
					white-space: nowrap;
					border-top: 1px solid #f7f5f5;
					border-bottom: 1px solid #cccccc;
					width: 100%;
					border-right: 0;
					border-left: 0;
					box-shadow: none !important;
				}

				.wppool-settings-sidebar > ul > li a > i {
					margin-right: 8px;
					color: darkblue;
				}

				.wppool-settings-sidebar > ul > li.active a {
					color: #23282d;
					background-color: #fff;
					border-right: 1px solid #fff !important;
				}

				.wppool-settings-content {
					position: relative;
					width: 100%;
					padding: 10px 20px;
					background-color: #fff;
					border: 1px solid #cccccc;
					min-height: 750px;
				}

				.wppool-settings-content h2 {
					padding-bottom: 16px;
					margin: 8px 0 16px;
					font-size: 18px;
					font-weight: 300;
					border-bottom: 1px solid #cccccc;

				}

				.wppool-settings-heading {
					position: relative;
					left: -17%;
				}

				/*---- checkbox -----*/

				.wppool-switcher input[type="checkbox"] {
					display: none;
				}

				.wppool-switcher input[type="checkbox"]:checked ~ div {
					background: rgba(73, 168, 68, 1) !important;
					box-shadow: 0 0 2px rgba(73, 168, 68, 1) !important;
				}

				.wppool-switcher input[type="checkbox"]:checked ~ div label {
					transform: rotate(360deg);
					margin-left: auto !important;
				}

				.wppool-switcher input[type="checkbox"]:checked ~ div label::before {
					top: 4px;
					left: 10px;
					height: 12px;
					background: rgba(73, 168, 68, 1) !important;
				}

				.wppool-switcher input[type="checkbox"]:checked ~ div label::after {
					top: 10px;
					left: 3px;
					width: 6px;
					background: rgba(73, 168, 68, 1) !important;
				}

				.wppool-switcher input[type="checkbox"]:disabled ~ div {
					background: #666 !important;
				}

				.wppool-switcher input[type="checkbox"]:disabled ~ div label {
					background: #ddd !important;
				}

				.wppool-switcher input[type="checkbox"]:disabled ~ div label::before,
				.wppool-switcher input[type="checkbox"]:disabled ~ div label::after {
					background: #666;
					border-radius: 5px;
				}

				.wppool-switcher div,
				.wppool-switcher label {
					border-radius: 50px;
				}

				.wppool-switcher div {
					height: 25px;
					width: 50px;
					background: rgba(43, 43, 43, 1) !important;
					position: relative;
					box-shadow: 0 0 2px rgba(43, 43, 43, 1) !important;
					display: flex;
					align-items: center;
					padding: 0 5px;
					margin-top: 0;
					transition: all .2s ease;
				}

				.switcher label{
					display:flex;
				}

				.switcher .wppool-switcher label {
					height: 20px;
					width: 20px;
					background: rgba(255, 255, 255, 1) !important;
					cursor: pointer;
					display: flex !important;
					align-items: center;
					justify-content: center;
					margin: -3px !important;
				}

				.wppool-switcher label::before,
				.wppool-switcher label::after {
					background: rgba(43, 43, 43, 1) !important;
					border-radius: 5px;
				}

				.wppool-switcher label::before {
					content: '';
					height: 13px;
					width: 3px;
					position: absolute;
					transform: rotate(45deg);
				}

				.wppool-switcher label::after {
					content: '';
					height: 3px;
					width: 13px;
					position: absolute;
					transform: rotate(45deg);
				}

				/*------ color field fixing ------*/
				.wppool-settings .iris-square {
					margin-right: 20px;
				}

				.wppool-settings .iris-slider.iris-strip {
					height: 184px !important;
				}


				.wppool-settings .image-choose-opt img {
					max-width: 90px;
					border: 1px solid #ddd;
					border-radius: 10px;
					margin: 0 5px;
				}

				.wppool-settings .image-choose-opt input {
					display: none;
				}

				.wppool-settings .image-choose-opt.active img {
					border: 5px solid deeppink;

				}

				/**-- slider --**/
				.wppool-slider {
					max-width: 350px;
				}

				.wppool-slider input {
					display: none;
				}

				.wppool-slider-handle {
					width: 3em !important;
					height: 1.6em !important;
					margin-top: -3px;
					text-align: center;
					line-height: 1.6em;
					background: #555 !important;
					color: #fff !important;
				}

				.wppool-slider .ui-slider-range {
					background: #555 !important;
				}

				.wppool-settings .form-table td fieldset label {
					display: flex !important;
				}

			</style>
			<?php
		}
	}
}