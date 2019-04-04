<?php

/**
 * Paragraph text field.
 *
 * @package    Infactform
 * @author     Infact
 * @since      1.0.0
 */
class WPForms_Field_Textarea extends WPForms_Field {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Define field type information
		$this->name  = __( 'Textarea', 'wpforms' );
		$this->type  = 'textarea';
		$this->icon  = 'fa-paragraph';
		$this->order = 5;
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field
	 */
	public function field_options( $field ) {

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

		$this->field_option( 'limit_character', $field );

		// Description
		$this->field_option( 'description', $field );

		// Custom expression
		$this->field_option( 'custom_expression', $field );

		// Required toggle.
		$this->field_option( 'required', $field );

		// Options close markup.
		$args = array(
			'markup' => 'close',
		);
		$this->field_option( 'basic-options', $field, $args );
		$this->field_option( 'placeholder', $field );
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
		$placeholder      = ! empty( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '';
		$custom_note_text = ! empty( $field['custom_textarea_infor'] ) ? esc_attr( $field['custom_textarea_infor'] ) : '';

		// Label.
		$this->field_preview_option( 'label', $field );

		// Description.
		$this->field_preview_option( 'description', $field );

		// Primary input.
		echo '<textarea placeholder="' . $placeholder . '" class="primary-input" disabled></textarea>';

		?>

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
		$primary = $field['properties']['inputs']['primary'];
		$value   = '';
		if ( ! empty( $primary['attr']['value'] ) ) {
			$value = $primary['attr']['value'];
			unset( $primary['attr']['value'] );

			$value = wpforms_sanitize_textarea_field( $value );
		}

		// Primary field.
		printf(
			'<textarea %s %s>%s</textarea>',
			wpforms_html_attributes( $primary['id'], $primary['class'], $primary['data'], $primary['attr'] ),
			$primary['required'],
			$value
		);
	}
}

new WPForms_Field_Textarea;
