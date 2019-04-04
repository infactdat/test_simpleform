<?php

/**
 * Scribe to Email list form template.
 *
 * @package    Infactform
 * @author     Infact
 * @since      1.0.0
 */
class WPForms_Template_Subscribe extends WPForms_Template {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->name        = __( 'Basic template', 'wpforms' );
		$this->slug        = 'basic_template';
		$this->description = __( 'First template of simple form, include some basic fields.', 'wpforms' );
		$this->includes    = '';
		$this->icon        = '';
		$this->core        = true;
		$this->data        = array(
			'field_id' => '2',
			'fields'   => array(
				'0' => array(
					'id'       => '101',
					'type'     => 'pagebreak',
					'position' => 'top'
				),
				'1' => array(
					'id'       => '102',
					'type'     => 'checkbox',
					'label'    => __( 'お問合せ内容', 'wpforms' ),
					'choices'  => array(
						'1' => array(
							'label' => __( 'お困り事について', 'wpforms' ),
							'value' => __( 'お困り事について', 'wpforms' ),
						),
						'2' => array(
							'label' => __( 'サービスについて', 'wpforms' ),
							'value' => __( 'サービスについて', 'wpforms' ),
						),
						'3' => array(
							'label' => __( '資料請求', 'wpforms' ),
							'value' => __( '資料請求', 'wpforms' ),
						),
						'4' => array(
							'label' => __( 'その他お問合せ', 'wpforms' ),
							'value' => __( 'その他お問合せ', 'wpforms' ),
						),
					),
					'required' => '0',
				),
				'2' => array(
					'id'                         => '104',
					'type'                       => 'name',
					'label'                      => __( 'お名前', 'wpforms' ),
					'required'                   => '1',
					'enable_autokana'            => '1',
					'first_text_before'          => '姓',
					'first_placeholder'          => '山田',
					'last_text_before'           => '名',
					'last_placeholder'           => '太郎',
					'kana_first_text_before'     => 'セイ',
					'autokana_first_placeholder' => 'ヤマダ',
					'kana_last_text_before'      => 'メイ',
					'autokana_last_placeholder'  => 'タロウ'
				),
				'3' => array(
					'id'       => '105',
					'type'     => 'text',
					'label'    => __( '会社名', 'wpforms' ),
					'required' => '0',
				),
				'4' => array(
					'id'           => '103',
					'type'         => 'email',
					'label'        => __( 'メールアドレス', 'wpforms' ),
					'required'     => '1',
					'confirmation' => '1',
					'placeholder'  => 'abc@xyz.jp'
				),
				'5' => array(
					'id'          => '106',
					'type'        => 'textarea',
					'label'       => __( 'お問合せ内容', 'wpforms' ),
					'required'    => '1',
					'placeholder' => 'お問合せ内容をご記入ください'
				),

				'6' => array(
					'id'   => '107',
					'type' => 'pagebreak',
				),
				'7' => array(
					'id'          => '108',
					'type'        => 'pagebreak',
					'position'    => 'bottom',
					'prev_toggle' => true,
					'prev'        => __( 'Previous', 'wpforms' )
				)
			),
			'settings' => array(
				'honeypot'                    => '1',
				'confirmation_message_scroll' => '1',
				'submit_text_processing'      => __( 'Sending...', 'wpforms' ),
                'recaptcha'=>'1'
			),
			'meta'     => array(
				'template' => $this->slug,
			),
		);
	}

	/**
	 * Conditional to determine if the template informational modal screens
	 * should display.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data
	 *
	 * @return boolean
	 */
	function template_modal_conditional( $form_data ) {

		// If we do not have provider data, then we can assume a provider
		// method has not yet been configured, so we display the modal to
		// remind the user they need to set it up for the form to work
		// correctly.
		if ( empty( $form_data['providers'] ) ) {
			return true;
		} else {
			return false;
		}
	}
}

new WPForms_Template_Subscribe;
