<?php

/**
 * Name text field.
 *
 * @package    Infactform
 * @author     Infact
 * @since      1.0.0
 */
class WPForms_Field_Name extends WPForms_Field {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Define field type information
		$this->name  = __( 'Name', 'wpforms' );
		$this->type  = 'name';
		$this->icon  = 'fa-user';
		$this->order = 15;

		// Define additional field properties.
		add_filter( 'wpforms_field_properties_name', array( $this, 'field_properties' ), 5, 3 );

		// Set field to default to required
		add_filter( 'wpforms_field_new_required', array( $this, 'default_required' ), 10, 2 );
	}

	/**
	 * Define additional field properties.
	 *
	 * @since 1.3.7
	 *
	 * @param array $properties
	 * @param array $field
	 * @param array $form_data
	 *
	 * @return array
	 */
	public function field_properties( $properties, $field, $form_data ) {

		$format = ! empty( $field['format'] ) ? esc_attr( $field['format'] ) : 'first-last';


		// Expanded formats.
		// Remove primary for expanded formats since we have first, middle, last.
		unset( $properties['inputs']['primary'] );

		$form_id  = absint( $form_data['id'] );
		$field_id = absint( $field['id'] );
		$dynamic  = apply_filters( 'wpforms_frontend_dynamic_values', false );

		$props      = array(
			'inputs' => array(
				'first' => array(
					'attr'     => array(
						'name'              => "wpforms[fields][{$field_id}][first]",
						'value'             => ! empty( $field['first_default'] ) ? apply_filters( 'wpforms_process_smart_tags', $field['first_default'], $form_data ) : '',
						'placeholder'       => ! empty( $field['first_placeholder'] ) ? $field['first_placeholder'] : '',
						'first_text_before' => ! empty( $field['first_text_before'] ) ? $field['first_text_before'] : '',
					),
					'block'    => array(
						'wpforms-field-row-block',
						'wpforms-large-input',
					),
					'class'    => array(
						'wpforms-field-name-first',
						'wpforms-field-name'
					),
					'data'     => array(),
					'id'       => "wpforms-{$form_id}-field_{$field_id}",
					'required' => ! empty( $field['required'] ) ? 'required' : '',
					'sublabel' => array(
						'hidden' => ! empty( $field['sublabel_hide'] ),
						'value'  => __( 'First', 'wpforms' ),
					),
				),

				'autokana_first' => array(
					'attr'     => array(
						'name'                   => "wpforms[fields][{$field_id}][autokana_first]",
						'value'                  => ! empty( $field['autokana_first_default'] ) ? apply_filters( 'wpforms_process_smart_tags', $field['autokana_first_default'], $form_data ) : '',
						'placeholder'            => ! empty( $field['autokana_first_placeholder'] ) ? $field['autokana_first_placeholder'] : '',
						'kana_first_text_before' => ! empty( $field['kana_first_text_before'] ) ? $field['kana_first_text_before'] : '',
					),
					'block'    => array(
						'wpforms-field-row-block',
						'autokana_input_wrap',
						'first'
					),
					'class'    => array(
						'wpforms-field-name-autokana_first',
					),
					'data'     => array(),
					'id'       => "wpforms-{$form_id}-field_{$field_id}-autokana_first",
					'required' => '',
					'sublabel' => array(
						'hidden' => ! empty( $field['sublabel_hide'] ),
						'value'  => __( 'Autokana First', 'wpforms' ),
					),
				),
				'autokana_last'  => array(
					'attr'     => array(
						'name'                  => "wpforms[fields][{$field_id}][autokana_last]",
						'value'                 => ! empty( $field['autokana_last_default'] ) ? apply_filters( 'wpforms_process_smart_tags', $field['autokana_last_default'], $form_data ) : '',
						'placeholder'           => ! empty( $field['autokana_last_placeholder'] ) ? $field['autokana_last_placeholder'] : '',
						'kana_last_text_before' => ! empty( $field['kana_last_text_before'] ) ? $field['kana_last_text_before'] : '',
					),
					'block'    => array(
						'wpforms-field-row-block',
						'autokana_input_wrap',
						'last'
					),
					'class'    => array(
						'wpforms-field-name-autokana_last',
					),
					'data'     => array(),
					'id'       => "wpforms-{$form_id}-field_{$field_id}-autokana_last",
					'required' => '',
					'sublabel' => array(
						'hidden' => ! empty( $field['sublabel_hide'] ),
						'value'  => __( 'Autokana Last', 'wpforms' ),
					),
				),
				'last'           => array(
					'attr'     => array(
						'name'             => "wpforms[fields][{$field_id}][last]",
						'value'            => ! empty( $field['last_default'] ) ? apply_filters( 'wpforms_process_smart_tags', $field['last_default'], $form_data ) : '',
						'placeholder'      => ! empty( $field['last_placeholder'] ) ? $field['last_placeholder'] : '',
						'last_text_before' => ! empty( $field['last_text_before'] ) ? $field['last_text_before'] : '',
					),
					'block'    => array(
						'wpforms-field-row-block',
						'wpforms-large-input'
					),
					'class'    => array(
						'wpforms-field-name-last',
						'wpforms-field-name'
					),
					'data'     => array(),
					'id'       => "wpforms-{$form_id}-field_{$field_id}-last",
					'required' => ! empty( $field['required'] ) ? 'required' : '',
					'sublabel' => array(
						'hidden' => ! empty( $field['sublabel_hide'] ),
						'value'  => __( 'Last', 'wpforms' ),
					),
				),
			),
		);
		$properties = array_merge_recursive( $properties, $props );

		// Input First: add error class if needed.
		if ( ! empty( $properties['error']['value']['first'] ) ) {
			$properties['inputs']['first']['class'][] = 'wpforms-error';
		}

		// Input First: add required class if needed.
		if ( ! empty( $field['required'] ) ) {
			$properties['inputs']['first']['class'][] = 'wpforms-field-required';
		}

		// Input First: add column class.
		$properties['inputs']['first']['block'][] = 'first-last' === $format ? 'wpforms-one-half' : 'wpforms-two-fifths';

		// Input First: dynamic value support.
		if ( $dynamic ) {
			if ( empty( $properties['inputs']['first']['attr']['value'] ) && ! empty( $_GET["f{$field_id}-first"] ) ) {
				$properties['inputs']['first']['attr']['value'] = sanitize_text_field( $_GET["f{$field_id}-first"] );
			}
		}

		// Input Last: add error class if needed.
		if ( ! empty( $properties['error']['value']['last'] ) ) {
			$properties['inputs']['last']['class'][] = 'wpforms-error';
		}

		// Input Last: add required class if needed.
		if ( ! empty( $field['required'] ) ) {
			$properties['inputs']['last']['class'][] = 'wpforms-field-required';
		}

		// Input Last: add column class.
		$properties['inputs']['last']['block'][] = 'first-last' === $format ? 'wpforms-one-half' : 'wpforms-two-fifths';

		// Input Last: dynamic value support.
		if ( $dynamic ) {
			if ( empty( $properties['inputs']['last']['attr']['value'] ) && ! empty( $_GET["f{$field_id}-last"] ) ) {
				$properties['inputs']['last']['attr']['value'] = sanitize_text_field( $_GET["f{$field_id}-last"] );
			}
		}

		return $properties;
	}

	/**
	 * Name fields should default to being required.
	 *
	 * @since 1.0.8
	 *
	 * @param bool $required
	 * @param array $field
	 *
	 * @return bool
	 */
	public function default_required( $required, $field ) {

		if ( 'name' === $field['type'] ) {
			return true;
		}

		return $required;
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field
	 */
	public function field_options( $field ) {

		// Define data.
		$format = ! empty( $field['format'] ) ? esc_attr( $field['format'] ) : 'first-last';

		// -------------------------------------------------------------------//
		// Basic field options.
		// -------------------------------------------------------------------//

		// Options open markup.
		$args = array(
			'markup' => 'open',
		);
		$this->field_option( 'basic-options', $field, $args );

		// Label.
		$this->field_option( 'label', $field );

//		$lbl = $this->field_element(
//			'label',
//			$field,
//			array(
//				'slug'    => 'format',
//				'value'   => __( 'Format', 'wpforms' ),
//				'tooltip' => __( 'Select format to use for the name form field', 'wpforms' ),
//			),
//			false
//		);
//
//		$fld = $this->field_element(
//			'select',
//			$field,
//			array(
//				'slug'    => 'format',
//				'value'   => $format,
//				'options' => array(
//					'simple'            => __( 'Simple', 'wpforms' ),
//					'first-last'        => __( 'First Last', 'wpforms' ),
//				),
//			),
//			false
//		);
//		$args = array(
//			'slug'    => 'format',
//			'content' => $lbl . $fld,
//		);
//		$this->field_element( 'row', $field, $args );


		$this->field_option( 'limit_character', $field );


		// Description.
		$this->field_option( 'description', $field );

		$show_values = $this->field_element(
			'checkbox',
			$field,
			array(
				'slug'    => 'enable_autokana',
				'value'   => isset( $field['enable_autokana'] ) ? $field['enable_autokana'] : '0',
				'desc'    => __( 'Enable autokana', 'wpforms' ),
				'tooltip' => __( 'Check this to generate katakana character automatically', 'wpforms' ),
			),
			false
		);
		$this->field_element( 'row', $field, array(
			'slug'    => 'enable_autokana',
			'content' => $show_values,
		) );


		// Custom expression
		$this->field_option( 'custom_expression', $field );

		// Required toggle.
		$this->field_option( 'required', $field );


		// Options close markup.
		$args = array(
			'markup' => 'close',
		);
		$this->field_option( 'basic-options', $field, $args );

		// -------------------------------------------------------------------//
		// Advanced field options.
		// -------------------------------------------------------------------//

		// Options open markup.
		$args = array(
			'markup' => 'open',
		);
		$this->field_option( 'advanced-options', $field, $args );


		echo '<div class="format-selected-' . $format . ' format-selected">';

		// First
		$first_placeholder = ! empty( $field['first_placeholder'] ) ? esc_attr( $field['first_placeholder'] ) : '';
		$first_default     = ! empty( $field['first_default'] ) ? esc_attr( $field['first_default'] ) : '';
		$first_text_before = ! empty( $field['first_text_before'] ) ? esc_attr( $field['first_text_before'] ) : '';

		printf( '<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-first" id="wpforms-field-option-row-%d-first" data-subfield="first-name" data-field-id="%d">', $field['id'], $field['id'] );
		$this->field_element( 'label', $field, array(
			'slug'    => 'first_placeholder',
			'value'   => __( 'First Name', 'wpforms' ),
			'tooltip' => __( 'First name field advanced options.', 'wpforms' )
		) );

		echo '<div class="placeholder">';
		printf( '<input type="text" class="placeholder" id="wpforms-field-option-%d-first_text_before" name="fields[%d][first_text_before]" value="%s">', $field['id'], $field['id'], $first_text_before );
		printf( '<label for="wpforms-field-option-%d-first_text_before" class="sub-label">%s</label>', $field['id'], __( 'Text before', 'wpforms' ) );
		echo '</div>';
		echo '<div class="placeholder">';
		printf( '<input type="text" class="placeholder" id="wpforms-field-option-%d-first_placeholder" name="fields[%d][first_placeholder]" value="%s">', $field['id'], $field['id'], $first_placeholder );
		printf( '<label for="wpforms-field-option-%d-first_placeholder" class="sub-label">%s</label>', $field['id'], __( 'Placeholder', 'wpforms' ) );
		echo '</div>';
		echo '<div class="default">';
		printf( '<input type="text" class="default" id="wpforms-field-option-%d-first_default" name="fields[%d][first_default]" value="%s">', $field['id'], $field['id'], $first_default );
		printf( '<label for="wpforms-field-option-%d-first_default" class="sub-label">%s</label>', $field['id'], __( 'Default Value', 'wpforms' ) );
		echo '</div>';
		echo '</div>';


		// Last
		$last_placeholder = ! empty( $field['last_placeholder'] ) ? esc_attr( $field['last_placeholder'] ) : '';
		$last_text_before = ! empty( $field['last_text_before'] ) ? esc_attr( $field['last_text_before'] ) : '';
		$last_default     = ! empty( $field['last_default'] ) ? esc_attr( $field['last_default'] ) : '';
		printf( '<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-last" id="wpforms-field-option-row-%d-last" data-subfield="last-name" data-field-id="%d">', $field['id'], $field['id'] );
		$this->field_element( 'label', $field, array(
			'slug'    => 'last_placeholder',
			'value'   => __( 'Last Name', 'wpforms' ),
			'tooltip' => __( 'Last name field advanced options.', 'wpforms' )
		) );

		echo '<div class="placeholder">';
		printf( '<input type="text" class="placeholder" id="wpforms-field-option-%d-last_text_before" name="fields[%d][last_text_before]" value="%s">', $field['id'], $field['id'], $last_text_before );
		printf( '<label for="wpforms-field-option-%d-last_text_before" class="sub-label">%s</label>', $field['id'], __( 'Text before', 'wpforms' ) );
		echo '</div>';

		echo '<div class="placeholder">';
		printf( '<input type="text" class="placeholder" id="wpforms-field-option-%d-last_placeholder" name="fields[%d][last_placeholder]" value="%s">', $field['id'], $field['id'], $last_placeholder );
		printf( '<label for="wpforms-field-option-%d-last_placeholder" class="sub-label">%s</label>', $field['id'], __( 'Placeholder', 'wpforms' ) );
		echo '</div>';
		echo '<div class="default">';
		printf( '<input type="text" class="default" id="wpforms-field-option-%d-last_default" name="fields[%d][last_default]" value="%s">', $field['id'], $field['id'], $last_default );
		printf( '<label for="wpforms-field-option-%d-last_default" class="sub-label">%s</label>', $field['id'], __( 'Default Value', 'wpforms' ) );
		echo '</div>';
		echo '</div>';


		// Autokana first
		$autokana_first_placeholder = ! empty( $field['autokana_first_placeholder'] ) ? esc_attr( $field['autokana_first_placeholder'] ) : '';
		$kana_first_text_before     = ! empty( $field['kana_first_text_before'] ) ? esc_attr( $field['kana_first_text_before'] ) : '';
		$autokana_first_default     = ! empty( $field['autokana_first_default'] ) ? esc_attr( $field['autokana_first_default'] ) : '';
		printf( '<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-autokana_first_placeholder" id="wpforms-field-option-row-%d-autokana_first_placeholder"  data-field-id="%d">', $field['id'], $field['id'] );
		$this->field_element( 'label', $field, array(
			'slug'  => 'autokana_first_placeholder',
			'value' => __( 'Autokana Field for First name', 'wpforms' ),
		) );
		echo '<div class="placeholder">';
		printf( '<input type="text" class="placeholder" id="wpforms-field-option-%d-kana_first_text_before" name="fields[%d][kana_first_text_before]" value="%s">', $field['id'], $field['id'], $kana_first_text_before );
		printf( '<label for="wpforms-field-option-%d-kana_first_text_before" class="sub-label">%s</label>', $field['id'], __( 'Text before', 'wpforms' ) );
		echo '</div>';
		echo '<div class="placeholder">';
		printf( '<input type="text" class="placeholder" id="wpforms-field-option-%d-autokana_first_placeholder" name="fields[%d][autokana_first_placeholder]" value="%s">', $field['id'], $field['id'], $autokana_first_placeholder );
		printf( '<label for="wpforms-field-option-%d-autokana_first_placeholder" class="sub-label">%s</label>', $field['id'], __( 'Place holder', 'wpforms' ) );
		echo '</div>';
		echo '<div class="default">';
		printf( '<input type="text" class="default" id="wpforms-field-option-%d-autokana_first_default" name="fields[%d][autokana_first_default]" value="%s">', $field['id'], $field['id'], $autokana_first_default );
		printf( '<label for="wpforms-field-option-%d-autokana_first_default" class="sub-label">%s</label>', $field['id'], __( 'Default Value', 'wpforms' ) );
		echo '</div>';
		echo '</div>';


		// Autokana last
		$autokana_last_placeholder = ! empty( $field['autokana_last_placeholder'] ) ? esc_attr( $field['autokana_last_placeholder'] ) : '';
		$kana_last_text_before     = ! empty( $field['kana_last_text_before'] ) ? esc_attr( $field['kana_last_text_before'] ) : '';
		$autokana_last_default     = ! empty( $field['autokana_last_default'] ) ? esc_attr( $field['autokana_last_default'] ) : '';
		printf( '<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-autokana_first_placeholder" id="wpforms-field-option-row-%d-autokana_first_placeholder" data-field-id="%d">', $field['id'], $field['id'] );
		$this->field_element( 'label', $field, array(
			'slug'  => 'autokana_first_placeholder',
			'value' => __( 'Autokana Field for Last name', 'wpforms' ),
		) );
		echo '<div class="placeholder">';
		printf( '<input type="text" class="placeholder" id="wpforms-field-option-%d-kana_last_text_before" name="fields[%d][kana_last_text_before]" value="%s">', $field['id'], $field['id'], $kana_last_text_before );
		printf( '<label for="wpforms-field-option-%d-kana_last_text_before" class="sub-label">%s</label>', $field['id'], __( 'Text before', 'wpforms' ) );
		echo '</div>';
		echo '<div class="placeholder">';
		printf( '<input type="text" class="placeholder" id="wpforms-field-option-%d-autokana_last_placeholder" name="fields[%d][autokana_last_placeholder]" value="%s">', $field['id'], $field['id'], $autokana_last_placeholder );
		printf( '<label for="wpforms-field-option-%d-autokana_last_placeholder" class="sub-label">%s</label>', $field['id'], __( 'Place holder', 'wpforms' ) );
		echo '</div>';
		echo '<div class="default">';
		printf( '<input type="text" class="default" id="wpforms-field-option-%d-autokana_last_default" name="fields[%d][autokana_last_default]" value="%s">', $field['id'], $field['id'], $autokana_last_default );
		printf( '<label for="wpforms-field-option-%d-autokana_last_default" class="sub-label">%s</label>', $field['id'], __( 'Default Value', 'wpforms' ) );
		echo '</div>';
		echo '</div>';
		echo '</div>';


		// Options close markup.
		$args = array(
			'markup' => 'close',
		);
		$this->field_option( 'advanced-options', $field, $args );
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field
	 */
	public function field_preview( $field ) {

		// Define data.
		$simple_placeholder = ! empty( $field['simple_placeholder'] ) ? esc_attr( $field['simple_placeholder'] ) : '';
		$first_placeholder  = ! empty( $field['first_placeholder'] ) ? esc_attr( $field['first_placeholder'] ) : '';
		$middle_placeholder = ! empty( $field['middle_placeholder'] ) ? esc_attr( $field['middle_placeholder'] ) : '';
		$last_placeholder   = ! empty( $field['last_placeholder'] ) ? esc_attr( $field['last_placeholder'] ) : '';
		$format             = ! empty( $field['format'] ) ? esc_attr( $field['format'] ) : 'first-last';

		// Label.
		$this->field_preview_option( 'label', $field );

		// Description.
		$this->field_preview_option( 'description', $field );
		?>

        <div class="format-selected-<?php echo $format; ?> format-selected">

            <div class="wpforms-simple">
                <input type="text" placeholder="<?php echo $simple_placeholder; ?>" class="primary-input" disabled>
            </div>

            <div class="wpforms-first-name">
                <input type="text" placeholder="<?php echo $first_placeholder; ?>" class="primary-input" disabled>
            </div>

            <div class="wpforms-middle-name">
                <input type="text" placeholder="<?php echo $middle_placeholder; ?>" class="primary-input" disabled>
            </div>

            <div class="wpforms-last-name">
                <input type="text" placeholder="<?php echo $last_placeholder; ?>" class="primary-input" disabled>
            </div>

        </div>

		<?php

	}


	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field
	 * @param array $deprecated
	 * @param array $form_data
	 */
	public function field_display( $field, $deprecated, $form_data ) {
		// Define data.
		$form_id                = absint( $form_data['id'] );
		$format                 = ! empty( $field['format'] ) ? esc_attr( $field['format'] ) : 'first-last';
		$primary                = ! empty( $field['properties']['inputs']['primary'] ) ? $field['properties']['inputs']['primary'] : '';
		$first                  = ! empty( $field['properties']['inputs']['first'] ) ? $field['properties']['inputs']['first'] : '';
		$middle                 = ! empty( $field['properties']['inputs']['middle'] ) ? $field['properties']['inputs']['middle'] : '';
		$autokana_first         = ! empty( $field['properties']['inputs']['autokana_first'] ) ? $field['properties']['inputs']['autokana_first'] : '';
		$autokana_last          = ! empty( $field['properties']['inputs']['autokana_last'] ) ? $field['properties']['inputs']['autokana_last'] : '';
		$last                   = ! empty( $field['properties']['inputs']['last'] ) ? $field['properties']['inputs']['last'] : '';
		$autoKata               = ! empty( $field['enable_autokana'] ) ? esc_attr( $field['enable_autokana'] ) : 0;
		$first_text_before      = ! empty( $field['first_text_before'] ) ? "<label class='text_before_input'>" . $field['first_text_before'] . "</label>" : '';
		$last_text_before       = ! empty( $field['last_text_before'] ) ? "<label class='text_before_input'>" . $field['last_text_before'] . "</label>" : '';
		$kana_last_text_before  = ! empty( $field['kana_last_text_before'] ) ? "<label class='text_before_input'>" . $field['kana_last_text_before'] . "</label>" : '';
		$kana_first_text_before = ! empty( $field['kana_first_text_before'] ) ? "<label class='text_before_input'>" . $field['kana_first_text_before'] . "</label>" : '';

		if ( $autoKata ) {
			wp_enqueue_script(
				'autokana-js',
				WPFORMS_PLUGIN_URL . '/advance/assets/js/jquery.autoKana.js',
				array( 'jquery' ),
				'1.0.0'
			);
		}
		// Simple format.
		if ( 'simple' === $format ) {

			// Primary field (Simple).
			echo '<div class="wpforms-field-row ' . ( $autoKata ? 'enable_autokana' : '' ) . '">';

			// First name.
			echo '<div ' . wpforms_html_attributes( false, $first['block'] ) . '>';

			printf( '<input type="text" %s %s>',
				wpforms_html_attributes( $first['id'], $first['class'], $first['data'], $first['attr'] ),
				$first['required']
			);
			$this->field_display_error( 'first', $field );
			echo '</div>';
			if ( $autoKata ) {
				echo '<div ' . wpforms_html_attributes( false, $autokana_first['block'] ) . '>';
				printf( '<input type="text" %s %s>',
					wpforms_html_attributes( $autokana_first['id'], $autokana_first['class'], $autokana_first['data'], $autokana_first['attr'] ),
					$autokana_first['required']
				);
				$this->field_display_error( 'autokana_first', $field );
				echo '</div>';
			}


			// Expanded formats.
		} else {

			// Row wrapper.
			echo '<div class="wpforms-field-row ' . ( $autoKata ? 'enable_autokana' : '' ) . '">';

			// First name.
			echo '<div ' . wpforms_html_attributes( false, $first['block'] ) . '>';
			echo $first_text_before;
			printf( '<input type="text" %s %s>',
				wpforms_html_attributes( $first['id'], $first['class'], $first['data'], $first['attr'] ),
				$first['required']
			);
			$this->field_display_error( 'first', $field );
			echo '</div>';

			// Last name.
			echo '<div ' . wpforms_html_attributes( false, $last['block'] ) . '>';
			echo $last_text_before;
			printf( '<input type="text" %s %s>',
				wpforms_html_attributes( $last['id'], $last['class'], $last['data'], $last['attr'] ),
				$last['required']
			);

			$this->field_display_error( 'last', $field );
			echo '</div>';

			if ( $autoKata ) {
				echo '<div ' . wpforms_html_attributes( false, $autokana_first['block'] ) . '>';
				echo $kana_first_text_before;
				printf( '<input type="text" %s %s>',
					wpforms_html_attributes( $autokana_first['id'], $autokana_first['class'], $autokana_first['data'], $autokana_first['attr'] ),
					$autokana_first['required']
				);
				$this->field_display_error( 'autokana_first', $field );
				echo '</div>';
			}

			if ( $autoKata ) {
				echo '<div ' . wpforms_html_attributes( false, $autokana_last['block'] ) . '>';
				echo $kana_last_text_before;
				printf( '<input type="text" %s %s>',
					wpforms_html_attributes( $autokana_last['id'], $autokana_last['class'], $autokana_last['data'], $autokana_last['attr'] ),
					$autokana_last['required']
				);

				$this->field_display_error( 'autokana_last', $field );
				echo '</div>';
			}


			echo '</div>';

		} // End if();


	}


	/**
	 * Validates field on form submit.
	 *
	 * @since 1.0.0
	 *
	 * @param int $field_id
	 * @param array $field_submit
	 * @param array $form_data
	 */
	public function validate( $field_id, $field_submit, $form_data ) {

		// Extended validation needed for the different name fields.
		if ( ! empty( $form_data['fields'][ $field_id ]['required'] ) ) {

			$form_id = $form_data['id'];
//			$format   = $form_data['fields'][ $field_id ]['format'];
			$required = apply_filters( 'wpforms_required_label', __( 'This field is required.', 'wpforms' ) );

//			if ( 'simple' === $format && empty( $field_submit ) ) {
//				wpforms()->process->errors[ $form_id ][ $field_id ] = $required;
//			}

			if ( empty( $field_submit['first'] ) ) {
				wpforms()->process->errors[ $form_id ][ $field_id ]['first'] = $required;
			}

			if ( empty( $field_submit['last'] ) ) {
				wpforms()->process->errors[ $form_id ][ $field_id ]['last'] = $required;
			}

		}
	}

	/**
	 * Formats field.
	 *
	 * @since 1.0.0
	 *
	 * @param int $field_id
	 * @param array $field_submit
	 * @param array $form_data
	 */
	public function format( $field_id, $field_submit, $form_data ) {

		// Define data.
		$name   = ! empty( $form_data['fields'][ $field_id ]['label'] ) ? $form_data['fields'][ $field_id ]['label'] : '';

		// Custom code for add a character before first name automatically
		$first  = ! empty( $field_submit['first'] ) ? $field_submit['first'] : '';




		$middle = ! empty( $field_submit['middle'] ) ? $field_submit['middle'] : '';

		$autokana_first = ! empty( $field_submit['autokana_first'] ) ? $field_submit['autokana_first'] : '';
		$autokana_last  = ! empty( $field_submit['autokana_last'] ) ? $field_submit['autokana_last'] : '';

		$last = ! empty( $field_submit['last'] ) ? $field_submit['last'] : '';

		if ( is_array( $field_submit ) ) {
			$value = implode( ' ', array_filter( array( $first, $autokana_first, $last, $autokana_last ) ) );
		} else {
			$value = $field_submit;
		}

		// Set final field details.
		wpforms()->process->fields[ $field_id ] = array(
			'name'           => sanitize_text_field( $name ),
			'value'          => sanitize_text_field( $value ),
			'id'             => absint( $field_id ),
			'type'           => $this->type,
			'first'          => sanitize_text_field( $first ),
			'middle'         => sanitize_text_field( $middle ),
			'autokana_first' => sanitize_text_field( $autokana_first ),
			'autokana_last'  => sanitize_text_field( $autokana_last ),

			'last' => sanitize_text_field( $last ),
		);
	}
}

new WPForms_Field_Name;
