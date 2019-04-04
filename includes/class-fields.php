<?php
/**
 * Load the field types.
 *
 * @package    Infactform
 * @author     Infact
 * @since      1.0.0
*/
class WPForms_Fields {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->init();
	}

	/**
	 * Load and init the base field class.
	 *
	 * @since 1.2.8
	 */
	public function init() {

		// Parent class template
		require_once WPFORMS_PLUGIN_DIR . 'includes/fields/class-base.php';

		// Load default fields on WP init
		add_action( 'init', array( $this, 'load' ) );
	}

	/**
	 * Load default field types.
	 *
	 * @since 1.0.0
	 */
	public function load() {

		$fields = apply_filters( 'wpforms_load_fields', array(
			'text',
			'textarea',
			'select',
			'radio',
			'checkbox',
			'divider',
			'email',
			'url',
			'hidden',
			'html',
			'name',
			'password',
			'address',
			'phone',
			'date-time',
			'number',
			'page-break',
			'file-upload',
			'payment-single',
			'payment-multiple',
			'payment-dropdown',
			'payment-credit-card',
			'payment-total',
            'store'
		) );

		foreach ( $fields as $field ) {

			if ( file_exists( WPFORMS_PLUGIN_DIR . 'includes/fields/class-' . $field . '.php' ) ) {
				require_once WPFORMS_PLUGIN_DIR . 'includes/fields/class-' . $field . '.php';
			} elseif ( file_exists( WPFORMS_PLUGIN_DIR . 'advance/includes/fields/class-' . $field . '.php' ) ) {
				require_once WPFORMS_PLUGIN_DIR . 'advance/includes/fields/class-' . $field . '.php';
			}
		}
	}
}
new WPForms_Fields;