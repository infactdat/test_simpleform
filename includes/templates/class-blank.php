<?php

/**
 * Blank form template.
 *
 * @package    Infactform
 * @author     Infact
 * @since      1.0.0
 */
class WPForms_Template_Blank extends WPForms_Template {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->name        = __( 'Blank template', 'wpforms' );
		$this->slug        = 'blank';
		$this->description = '';
		$this->includes    = '';
		$this->icon        = '';
		$this->modal       = '';
		$this->core        = true;
		$this->data        = array(
			'field_id' => '1',
			'fields'   => array(),
			'settings' => array(
				'honeypot'                    => '1',
				'confirmation_message_scroll' => '1',
				'submit_text_processing'      => __( 'Sending...', 'wpforms' ),
			),
			'meta'     => array(
				'template' => $this->slug,
			),
		);
	}
}

new WPForms_Template_Blank;
