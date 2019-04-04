<?php

/**
 * Advance functions
 *
 * @since   1.2.1
 * @package Form
 */
class Advance_Functions {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.2.1
	 */
	public function __construct() {

		$this->includes();

		add_action( 'wpforms_loaded', array( $this, 'objects' ), 1 );
		add_action( 'wpforms_install', array( $this, 'install' ), 10 );
		add_action( 'wpforms_process_entry_save', array( $this, 'entry_save' ), 10, 4 );
		add_filter( 'wpforms_overview_table_columns', array( $this, 'form_table_columns' ), 10, 1 );
		add_filter( 'wpforms_overview_table_column_value', array( $this, 'form_table_columns_value' ), 10, 3 );
		add_action( 'wpforms_form_settings_notifications', array( $this, 'form_settings_notifications' ), 8, 1 );
		add_action( 'admin_notices', array( $this, 'conditional_logic_addon_notice' ) );
	}

	/**
	 * Include files.
	 *
	 * @since 1.0.0
	 */
	private function includes() {

		require_once WPFORMS_PLUGIN_DIR . 'advance/includes/class-db.php';
		require_once WPFORMS_PLUGIN_DIR . 'advance/includes/class-entry.php';
		require_once WPFORMS_PLUGIN_DIR . 'advance/includes/class-entry-fields.php';
		require_once WPFORMS_PLUGIN_DIR . 'advance/includes/class-entry-meta.php';
		require_once WPFORMS_PLUGIN_DIR . 'advance/includes/class-conditional-logic-fields.php';

		if ( is_admin() ) {
			require_once WPFORMS_PLUGIN_DIR . 'advance/includes/admin/ajax-actions.php';
			require_once WPFORMS_PLUGIN_DIR . 'advance/includes/admin/entries/class-entries-single.php';
			require_once WPFORMS_PLUGIN_DIR . 'advance/includes/admin/entries/class-entries-list.php';
			require_once WPFORMS_PLUGIN_DIR . 'advance/includes/admin/class-addons.php';
		}
	}

	/**
	 * Setup objects.
	 *
	 * @since 1.2.1
	 */
	public function objects() {

		// Global objects
		wpforms()->entry        = new WPForms_Entry_Handler;
		wpforms()->entry_fields = new WPForms_Entry_Fields_Handler;
		wpforms()->entry_meta   = new WPForms_Entry_Meta_Handler;


	}


	/**
	 * Handles plugin installation upon activation.
	 *
	 * @since 1.2.1
	 */
	public function install() {

		$wpforms_install               = new stdClass();
		$wpforms_install->entry        = new WPForms_Entry_Handler;
		$wpforms_install->entry_fields = new WPForms_Entry_Fields_Handler;
		$wpforms_install->entry_meta   = new WPForms_Entry_Meta_Handler;

		// Entry tables.
		$wpforms_install->entry->create_table();
		$wpforms_install->entry_fields->create_table();
		$wpforms_install->entry_meta->create_table();
	}

	/**
	 * Saves entry to database.
	 *
	 * @since 1.2.1
	 *
	 * @param array $fields
	 * @param array $entry
	 * @param int|string $form_id
	 * @param mixed $form_data
	 */
	public function entry_save( $fields, $entry, $form_id, $form_data = '' ) {

		// Check if form has entries disabled.
		if ( isset( $form_data['settings']['disable_entries'] ) ) {
			return;
		}

		// Provide the opportunity to override via a filter.
		if ( ! apply_filters( 'wpforms_entry_save', true, $fields, $entry, $form_data ) ) {
			return;
		}

		$fields     = apply_filters( 'wpforms_entry_save_data', $fields, $entry, $form_data );
		$user_id    = is_user_logged_in() ? get_current_user_id() : 0;
		$user_ip    = wpforms_get_ip();
		$user_agent = ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 256 ) : '';
		$user_uuid  = ! empty( $_COOKIE['_wpfuuid'] ) ? $_COOKIE['_wpfuuid'] : '';
		$date       = date( 'Y-m-d H:i:s' );
		$entry_id   = false;

		// Create entry.
		$entry_id = wpforms()->entry->add( array(
			'form_id'    => absint( $form_id ),
			'user_id'    => absint( $user_id ),
			'fields'     => wp_json_encode( $fields ),
			'ip_address' => sanitize_text_field( $user_ip ),
			'user_agent' => sanitize_text_field( $user_agent ),
			'date'       => $date,
			'user_uuid'  => sanitize_text_field( $user_uuid ),
		) );

		// Create fields.
		if ( $entry_id ) {
			foreach ( $fields as $field ) {

				$field = apply_filters( 'wpforms_entry_save_fields', $field, $form_data, $entry_id );

				if ( isset( $field['value'] ) && '' !== $field['value'] ) {
					wpforms()->entry_fields->add( array(
						'entry_id' => $entry_id,
						'form_id'  => absint( $form_id ),
						'field_id' => absint( $field['id'] ),
						'value'    => $field['value'],
						'date'     => $date,
					) );
				}
			}
		}

		wpforms()->process->entry_id = $entry_id;
	}


	/**
	 * Add entry counts column to form table.
	 *
	 * @since 1.2.1
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function form_table_columns( $columns ) {

		$columns['entries'] = __( 'Archives', 'wpforms' );

		return $columns;
	}

	/**
	 * Add entry counts value to entry count column.
	 *
	 * @since 1.2.1
	 *
	 * @param string $value
	 * @param object $form
	 * @param string $column_name
	 *
	 * @return string
	 */
	public function form_table_columns_value( $value, $form, $column_name ) {

		if ( 'entries' === $column_name ) {
			$count = wpforms()->entry->get_entries(
				array(
					'form_id' => $form->ID,
				),
				true
			);

			$value = sprintf(
				'<a href="%s">%d</a>',
				add_query_arg(
					array(
						'view'    => 'list',
						'form_id' => $form->ID,
					),
					admin_url( 'admin.php?page=wpforms-entries' )
				),
				$count
			);
		}

		return $value;
	}

	/**
	 * Form notification settings, supports multiple notifications.
	 *
	 * @since 1.2.3
	 *
	 * @param object $settings
	 */
	public function form_settings_notifications( $settings ) {

		$cc = wpforms_setting( 'email-carbon-copy', true );

		$bcc = wpforms_setting( 'email-blind-carbon-copy', true );
		// Fetch next ID and handle backwards compatibility
		if ( ! empty( $settings->form_data['settings']['notifications'] ) ) {
			$next_id = max( array_keys( $settings->form_data['settings']['notifications'] ) ) + 1;
		} elseif ( $next_id = 2 ) {
			$message_user = 'この度はお問い合せ頂き誠にありがとうございました。
改めて担当者よりご連絡をさせていただきます。

─ご送信内容の確認─────────────────

{all_fields}

──────────────────────────

このメールに心当たりの無い場合は、お手数ですが
下記連絡先までお問い合わせください。

この度はお問い合わせ重ねてお礼申し上げます。

━━━━━━━━━━━━━━━━━━━━━━━━━━
会社名
http://www.abc@xyz.com';

			$message_admin = 'フォームより以下の内容が送られました。
──────────────────────────

{all_fields}';

			$settings->form_data['settings']['notifications'][1]['notification_name'] = ! empty( $settings->form_data['settings']['notification_name'] ) ? $settings->form_data['settings']['notification_name'] : __( '管理者宛メール設定', 'wpforms' );
			$settings->form_data['settings']['notifications'][1]['email']             = ! empty( $settings->form_data['settings']['notification_email'] ) ? $settings->form_data['settings']['notification_email'] : '{admin_email}';
			$settings->form_data['settings']['notifications'][1]['subject']           = ! empty( $settings->form_data['settings']['notification_subject'] ) ? $settings->form_data['settings']['notification_subject'] : __( 'フォームよりお問合せがありました', 'wpforms' );
			$settings->form_data['settings']['notifications'][1]['sender_name']       = ! empty( $settings->form_data['settings']['notification_fromname'] ) ? $settings->form_data['settings']['notification_fromname'] : '';
			$settings->form_data['settings']['notifications'][1]['sender_address']    = ! empty( $settings->form_data['settings']['notification_fromaddress'] ) ? $settings->form_data['settings']['notification_fromaddress'] : '{admin_email}';
			$settings->form_data['settings']['notifications'][1]['replyto']           = ! empty( $settings->form_data['settings']['notification_replyto'] ) ? $settings->form_data['settings']['notification_replyto'] : '';
			$settings->form_data['settings']['notifications'][1]['message']           = ! empty( $settings->form_data['settings']['message'] ) ? $settings->form_data['settings']['message'] : $message_admin;

			$settings->form_data['settings']['notifications'][2]['notification_name'] = ! empty( $settings->form_data['settings']['notification_name'] ) ? $settings->form_data['settings']['notification_name'] : __( '自動返信メール設定', 'wpforms' );
			$settings->form_data['settings']['notifications'][2]['email']          = ! empty( $settings->form_data['settings']['notification_email'] ) ? $settings->form_data['settings']['notification_email'] : '{field_id="103"}';
			$settings->form_data['settings']['notifications'][2]['subject']        = ! empty( $settings->form_data['settings']['notification_subject'] ) ? $settings->form_data['settings']['notification_subject'] : 'お問合せいただきありがとうございます';
			$settings->form_data['settings']['notifications'][2]['sender_name']    = ! empty( $settings->form_data['settings']['notification_fromname'] ) ? $settings->form_data['settings']['notification_fromname'] : '';
			$settings->form_data['settings']['notifications'][2]['sender_address'] = ! empty( $settings->form_data['settings']['notification_fromaddress'] ) ? $settings->form_data['settings']['notification_fromaddress'] : '{admin_email}';
			$settings->form_data['settings']['notifications'][2]['replyto']        = ! empty( $settings->form_data['settings']['notification_replyto'] ) ? $settings->form_data['settings']['notification_replyto'] : '';
			$settings->form_data['settings']['notifications'][2]['message']        = ! empty( $settings->form_data['settings']['message'] ) ? $settings->form_data['settings']['message'] : $message_user ;

		}

		echo '<div class="wpforms-panel-content-section-title">';
		_e( 'Notifications', 'wpforms' );
		echo '</div>';

		wpforms_panel_field(
			'select',
			'settings',
			'notification_enable',
			$settings->form_data,
			__( 'Notifications', 'wpforms' ),
			array(
				'default' => '1',
				'options' => array(
					'1' => __( 'On', 'wpforms' ),
					'0' => __( 'Off', 'wpforms' ),
				),
			)
		);


		wpforms_panel_field(
			'select',
			'settings',
			'previous_link_enable',
			$settings->form_data,
			__( 'Previous url ', 'wpforms' ),
			array(
				'default' => '1',
				'options' => array(
					'1' => __( 'On', 'wpforms' ),
					'0' => __( 'Off', 'wpforms' ),
				),
				'tooltip' => __( 'This options to allow Previous url can be installed in email to admin automatically. ', 'wpforms' ),
			)
		);
		wpforms_panel_field(
			'select',
			'settings',
			'enable_attachment',
			$settings->form_data,
			__( 'Enable attachment fields to admin mail ', 'wpforms' ),
			array(
				'default' => '1',
				'options' => array(
					'1' => __( 'On', 'wpforms' ),
					'0' => __( 'Off', 'wpforms' ),
				),
				'tooltip' => __( 'This options to allow All Attachments can be installed in email. ', 'wpforms' ),
			)
		);

		wpforms_panel_field(
			'select',
			'settings',
			'enable_auto_character_firstname',
			$settings->form_data,
			__( 'Enable adding 様 character after First name.', 'wpforms' ),
			array(
				'default' => '1',
				'options' => array(
					'1' => __( 'On', 'wpforms' ),
					'0' => __( 'Off', 'wpforms' ),
				),
			)
		);

		foreach ( $settings->form_data['settings']['notifications'] as $id => $notification ) {

			$name         = ! empty( $notification['notification_name'] ) ? $notification['notification_name'] : __( 'Default Notification', 'wpforms' );
			$closed_state = '';
			$toggle_state = '<i class="fa fa-chevron-up"></i>';

			if ( ! empty( $settings->form_data['id'] ) && wpforms_builder_notification_get_state( $settings->form_data['id'], $id ) === 'closed' ) {
				$closed_state = 'style="display:none"';
				$toggle_state = '<i class="fa fa-chevron-down"></i>';
			}
			?>

            <div class="wpforms-notification">

                <div class="wpforms-notification-header">
                    <span class="wpforms-notification-name"><?php echo $name; ?></span>

                    <div class="wpforms-notification-name-edit">
                        <input type="text" name="settings[notifications][<?php echo $id; ?>][notification_name]"
                               value="<?php echo esc_attr( $name ); ?>">
                    </div>

                    <div class="wpforms-notification-actions">
						<?php do_action( 'wpforms_form_settings_notifications_single_action', $id, $notification, $settings ); ?>
                        <button class="wpforms-notification-edit"><i class="fa fa-pencil"></i></button>
                        <button class="wpforms-notification-toggle"><?php echo $toggle_state; ?></button>
                        <button class="wpforms-notification-delete"><i class="fa fa-times-circle"></i></button>
                    </div>

                </div>

                <div class="wpforms-notification-content" <?php echo $closed_state; ?>>

					<?php
					wpforms_panel_field(
						'text',
						'notifications',
						'email',
						$settings->form_data,
						__( 'Send To Email Address', 'wpforms' ),
						array(
							'default'    => '{admin_email}',
							'tooltip'    => __( 'Enter the email address to receive form entry notifications. For multiple notifications, separate email addresses with a comma.', 'wpforms' ),
							'smarttags'  => array(
								'type'   => 'fields',
								'fields' => 'email',
							),
							'parent'     => 'settings',
							'subsection' => $id,
							'class'      => 'email-recipient',
						)
					);
					if ( $cc ) :
						wpforms_panel_field(
							'text',
							'notifications',
							'carboncopy',
							$settings->form_data,
							__( 'CC', 'wpforms' ),
							array(

								'parent'     => 'settings',
								'subsection' => $id,
							)
						);
					endif;

					if ( $bcc ) :
						wpforms_panel_field(
							'text',
							'notifications',
							'blindcarboncopy',
							$settings->form_data,
							__( 'BCC', 'wpforms' ),
							array(
								'parent'     => 'settings',
								'subsection' => $id,
							)
						);
					endif;
					wpforms_panel_field(
						'text',
						'notifications',
						'subject',
						$settings->form_data,
						__( 'Email Subject', 'wpforms' ),
						array(
							'default'    => sprintf( _x( 'New Entry: %s', 'Form name', 'wpforms' ), $settings->form->post_title ),
							'parent'     => 'settings',
							'subsection' => $id,
						)
					);
					wpforms_panel_field(
						'text',
						'notifications',
						'sender_name',
						$settings->form_data,
						__( 'From Name', 'wpforms' ),
						array(
							'default' => sanitize_text_field( get_option( 'blogname' ) ),

							'parent'     => 'settings',
							'subsection' => $id,
						)
					);
					wpforms_panel_field(
						'text',
						'notifications',
						'sender_address',
						$settings->form_data,
						__( 'From Email', 'wpforms' ),
						array(
							'default' => '{admin_email}',
							'parent'     => 'settings',
							'subsection' => $id,
						)
					);
					wpforms_panel_field(
						'text',
						'notifications',
						'replyto',
						$settings->form_data,
						__( 'Reply-To', 'wpforms' ),
						array(

							'parent'     => 'settings',
							'subsection' => $id,
						)
					);

					wpforms_panel_field(
						'textarea',
						'notifications',
						'message',
						$settings->form_data,
						__( 'Message', 'wpforms' ),
						array(
							'rows'       => 6,
							'default'    => '{all_fields}',
							'parent'     => 'settings',
							'subsection' => $id,
							'class'      => 'email-msg',
							'after'      => '<p class="note">' . __( 'To display all form fields, use the <code>{all_fields}</code> Smart Tag. ', 'wpforms' ) . '</p>',
						)
					);

					// Conditional Logic, if addon is activated
					if ( function_exists( 'wpforms_conditional_logic' ) ) {
						wpforms_conditional_logic()->conditionals_block( array(
							'form'        => $settings->form_data,
							'type'        => 'panel',
							'panel'       => 'notifications',
							'parent'      => 'settings',
							'subsection'  => $id,
							'actions'     => array(
								'go'   => __( 'Send', 'wpforms' ),
								'stop' => __( 'Don\'t send', 'wpforms' ),
							),
							'action_desc' => __( 'this notification if', 'wpforms' ),
							'reference'   => __( 'Email notifications', 'wpforms' ),
						) );
					} else {

					}

					// Hook for addons
					do_action( 'wpforms_form_settings_notifications_single_after', $settings, $id );
					?>

                </div><!-- /.wpforms-notification-content -->

            </div><!-- /.wpforms-notification -->

			<?php
		}
	}

	/**
	 * Check to see if the Conditional Logic addon is installed, if so notify
	 * the user that it can be removed.
	 *
	 * @since 1.3.8
	 */
	public function conditional_logic_addon_notice() {
		echo '';
	}
}

new Advance_Functions;
