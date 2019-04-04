<?php
/**
 * Global admin related items and functionality.
 *
 * @package    Infactform
 * @author     Infact
 * @since      1.0.0
 */

/**
 * Helper function to determine if viewing an WPForms related admin page.
 *
 * @since 1.3.9
 * @return boolean
 */
function wpforms_is_admin_page() {

	// Bail if we're not on a WPForms screen or page (also exclude form builder).
	if ( ! is_admin() || empty( $_REQUEST['page'] ) || strpos( $_REQUEST['page'], 'wpforms' ) === false || 'wpforms-builder' === $_REQUEST['page'] ) {
		return false;
	}

	return true;
}

/**
 * Load styles for all WPForms-related admin screens.
 *
 * @since 1.3.9
 */
function wpforms_admin_styles() {
	if ( ! wpforms_is_admin_page() ) {
		return;
	}

	// jQuery confirm.
	wp_enqueue_style(
		'jquery-confirm',
		WPFORMS_PLUGIN_URL . 'assets/css/jquery-confirm.min.css',
		array(),
		'3.3.2'
	);

	// Minicolors (color picker).
	wp_enqueue_style(
		'minicolors',
		WPFORMS_PLUGIN_URL . 'assets/css/jquery.minicolors.css',
		array(),
		'2.2.6'
	);

	// FontAwesome.
	wp_enqueue_style(
		'wpforms-font-awesome',
		WPFORMS_PLUGIN_URL . 'assets/css/font-awesome.min.css',
		null,
		'4.4.0'
	);

// FontAwesome. cdn
	wp_enqueue_style(
		'wpforms-font-awesome-cdn',
		'https://stackpath.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css',
		null,
		'4.4.0'
	);

	// Main admin styles.
	wp_enqueue_style(
		'wpforms-admin',
		WPFORMS_PLUGIN_URL . 'assets/css/admin.css',
		array(),
		WPFORMS_VERSION
	);

	// Final styles
	wp_enqueue_style(
		'infactform-final',
		WPFORMS_PLUGIN_URL . 'assets/css/final.css',
		array(),
		WPFORMS_VERSION
	);
}

add_action( 'admin_enqueue_scripts', 'wpforms_admin_styles' );

/**
 * Load scripts for all WPForms-related admin screens.
 *
 * @since 1.3.9
 */
function wpforms_admin_scripts() {
	if ( ! wpforms_is_admin_page() ) {
		return;
	}

	wp_enqueue_media();

	// jQuery confirm
	wp_enqueue_script(
		'jquery-confirm',
		WPFORMS_PLUGIN_URL . 'assets/js/jquery.jquery-confirm.min.js',
		array( 'jquery' ),
		'3.3.2',
		false
	);

	// Minicolors (color picker).
	wp_enqueue_script(
		'minicolors',
		WPFORMS_PLUGIN_URL . 'assets/js/jquery.minicolors.min.js',
		array( 'jquery' ),
		'2.2.6',
		false
	);

	// Choices.js.
	wp_enqueue_script(
		'choicesjs',
		WPFORMS_PLUGIN_URL . 'assets/js/choices.min.js',
		array(),
		'2.8.10',
		false
	);

	// TODO: we should use wpforms_get_min_suffix() here.
	$dir    = wpforms_debug() ? '/src' : '';
	$suffix = wpforms_debug() ? '' : '.min';

	// Main admin script.
	wp_enqueue_script(
		'wpforms-admin',
		WPFORMS_PLUGIN_URL . "assets/js{$dir}/admin{$suffix}.js",
		array( 'jquery' ),
		WPFORMS_VERSION,
		false
	);


	$strings = array(
		'addon_activate'                  => __( 'Activate', 'wpforms' ),
		'addon_active'                    => __( 'Active', 'wpforms' ),
		'addon_deactivate'                => __( 'Deactivate', 'wpforms' ),
		'addon_inactive'                  => __( 'Inactive', 'wpforms' ),
		'addon_install'                   => __( 'Install Addon', 'wpforms' ),
		'ajax_url'                        => admin_url( 'admin-ajax.php' ),
		'cancel'                          => __( 'Cancel', 'wpforms' ),
		'close'                           => __( 'Close', 'wpforms' ),
		'entry_delete_confirm'            => __( 'Are you sure you want to delete this archive?', 'wpforms' ),
		'entry_delete_all_confirm'        => __( 'Are you sure you want to delete ALL archives?', 'wpforms' ),
		'entry_empty_fields_hide'         => __( 'Hide Empty Fields', 'wpforms' ),
		'entry_empty_fields_show'         => __( 'Show Empty Fields', 'wpforms' ),
		'entry_field_columns'             => __( 'Archives Field Columns', 'wpforms' ),
		'entry_note_delete_confirm'       => __( 'Are you sure you want to delete this note?', 'wpforms' ),
		'entry_unstar'                    => __( 'Unstar entry', 'wpforms' ),
		'entry_star'                      => __( 'Star entry', 'wpforms' ),
		'entry_read'                      => __( 'Mark entry read', 'wpforms' ),
		'entry_unread'                    => __( 'Mark entry unread', 'wpforms' ),
		'fields_select'                   => __( 'Select fields', 'wpforms' ),
		'form_delete_confirm'             => __( 'Are you sure you want to delete this form?', 'wpforms' ),
		'form_duplicate_confirm'          => __( 'Are you sure you want to duplicate this form?', 'wpforms' ),
		'heads_up'                        => __( 'Heads up!', 'wpforms' ),
		'importer_forms_required'         => __( 'Please select at least one form to import.', 'wpforms' ),
		'isPro'                           => wpforms()->pro,
		'nonce'                           => wp_create_nonce( 'wpforms-admin' ),
		'ok'                              => __( 'OK', 'wpforms' ),
		'plugin_install_activate_btn'     => __( 'Install and Activate', 'wpforms' ),
		'plugin_install_activate_confirm' => __( 'needs to be installed and activated to import its forms. Would you like us to install and activate it for you?', 'wpforms' ),
		'plugin_activate_btn'             => __( 'Activate', 'wpforms' ),
		'plugin_activate_confirm'         => __( 'needs to be activated to import its forms. Would you like us to activate it for you?.', 'wpforms' ),
		'provider_delete_confirm'         => __( 'Are you sure you want to disconnect this account?', 'wpforms' ),
		'provider_auth_error'             => __( 'Could not authenticate with the provider.', 'wpforms' ),
		'save_refresh'                    => __( 'Save and Refresh', 'wpforms' ),
		'upgrade_completed'               => __( 'Upgrade was successfully completed!', 'wpforms' ),
		'upload_image_title'              => __( 'Upload or Choose Your Image', 'wpforms' ),
		'upload_image_button'             => __( 'Use Image', 'wpforms' ),
		/* translators: %1$s - opening link tag; %2$s - closing link tag; %3$s - opening link tag; %4$s - closing link tag. */
		'upgrade_modal'                   => '',
	);
	$strings = apply_filters( 'wpforms_admin_strings', $strings );

	wp_localize_script(
		'wpforms-admin',
		'wpforms_admin',
		$strings
	);
}


add_action( 'admin_enqueue_scripts', 'wpforms_admin_scripts' );

/**
 * Add body class to WPForms admin pages for easy reference.
 *
 * @since 1.3.9
 *
 * @param string $classes
 *
 * @return string
 */
function wpforms_admin_body_class( $classes ) {
	if ( ! wpforms_is_admin_page() ) {
		return $classes;
	}

	return "$classes wpforms-admin-page";
}

add_filter( 'admin_body_class', 'wpforms_admin_body_class', 10, 1 );


/**
 * Outputs the WPForms admin header.
 *
 * @since 1.3.9
 */
function wpforms_admin_header() {

	// Bail if we're not on a WPForms screen or page (also exclude form builder).
	if ( ! wpforms_is_admin_page() ) {
		return;
	}

	// Omit header from Welcome activation screen.
	if ( 'wpforms-getting-started' === $_REQUEST['page'] ) {
		return;
	}
}

add_action( 'in_admin_header', 'wpforms_admin_header', 100 );

/**
 * Remove non-WPForms notices from WPForms pages.
 *
 * @since 1.3.9
 */
function wpforms_admin_hide_unrelated_notices() {

	// Bail if we're not on a WPForms screen or page.
	if ( empty( $_REQUEST['page'] ) || strpos( $_REQUEST['page'], 'wpforms' ) === false ) {
		return;
	}

	global $wp_filter;

	if ( ! empty( $wp_filter['user_admin_notices']->callbacks ) && is_array( $wp_filter['user_admin_notices']->callbacks ) ) {
		foreach ( $wp_filter['user_admin_notices']->callbacks as $priority => $hooks ) {
			foreach ( $hooks as $name => $arr ) {
				if ( is_object( $arr['function'] ) && $arr['function'] instanceof Closure ) {
					unset( $wp_filter['user_admin_notices']->callbacks[ $priority ][ $name ] );
					continue;
				}
				if ( ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) && strpos( strtolower( get_class( $arr['function'][0] ) ), 'wpforms' ) !== false ) {
					continue;
				}
				if ( ! empty( $name ) && strpos( $name, 'wpforms' ) === false ) {
					unset( $wp_filter['user_admin_notices']->callbacks[ $priority ][ $name ] );
				}
			}
		}
	}

	if ( ! empty( $wp_filter['admin_notices']->callbacks ) && is_array( $wp_filter['admin_notices']->callbacks ) ) {
		foreach ( $wp_filter['admin_notices']->callbacks as $priority => $hooks ) {
			foreach ( $hooks as $name => $arr ) {
				if ( is_object( $arr['function'] ) && $arr['function'] instanceof Closure ) {
					unset( $wp_filter['admin_notices']->callbacks[ $priority ][ $name ] );
					continue;
				}
				if ( ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) && strpos( strtolower( get_class( $arr['function'][0] ) ), 'wpforms' ) !== false ) {
					continue;
				}
				if ( ! empty( $name ) && strpos( $name, 'wpforms' ) === false ) {
					unset( $wp_filter['admin_notices']->callbacks[ $priority ][ $name ] );
				}
			}
		}
	}

	if ( ! empty( $wp_filter['all_admin_notices']->callbacks ) && is_array( $wp_filter['all_admin_notices']->callbacks ) ) {
		foreach ( $wp_filter['all_admin_notices']->callbacks as $priority => $hooks ) {
			foreach ( $hooks as $name => $arr ) {
				if ( is_object( $arr['function'] ) && $arr['function'] instanceof Closure ) {
					unset( $wp_filter['all_admin_notices']->callbacks[ $priority ][ $name ] );
					continue;
				}
				if ( ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) && strpos( strtolower( get_class( $arr['function'][0] ) ), 'wpforms' ) !== false ) {
					continue;
				}
				if ( ! empty( $name ) && strpos( $name, 'wpforms' ) === false ) {
					unset( $wp_filter['all_admin_notices']->callbacks[ $priority ][ $name ] );
				}
			}
		}
	}
}

add_action( 'admin_print_scripts', 'wpforms_admin_hide_unrelated_notices' );

/**
 * Upgrade link used within the various admin pages.
 *
 * Previously was only included as a method in wpforms-lite.php, but made
 * available globally in 1.3.9.
 *
 * @since 1.3.9
 */
function wpforms_admin_upgrade_link() {
	return '';
}

/**
 * Check the current PHP version and display a notice if on unsupported PHP.
 *
 * @since 1.4.0.1
 */
function wpforms_check_php_version() {

	// Display for PHP below 5.3.
	if ( version_compare( PHP_VERSION, '5.3.0', '>=' ) ) {
		return;
	}

	// Display for admins only.
	if ( ! is_super_admin() ) {
		return;
	}

	// Display on Dashboard page only.
	if ( isset( $GLOBALS['pagenow'] ) && 'index.php' !== $GLOBALS['pagenow'] ) {
		return;
	}

	// Display the notice, finally.
	WPForms_Admin_Notice::error(
		'<p>Your PHP version is too low, please update to 5.3.0 or higher</p>'
	);
}

add_action( 'admin_init', 'wpforms_check_php_version' );


if ( ! function_exists( "script_frontend" ) ) {
	# code...
	function script_frontend( $hook ) {

		// create my own version codes
		$my_js_ver = date( "ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/addtional-validate.js' ) );

		wp_enqueue_script( 'addtional-validate', plugins_url( 'assets/js/addtional-validate.js', __FILE__ ), array(), $my_js_ver );
	}

	add_action( 'wp_enqueue_scripts', 'script_frontend' );
}


/**
 * Load styles for admin dashboard
 *
 * @since 1.3.9
 */
function global_admin_style() {
	// Final styles
	wp_enqueue_style(
		'infactform-final',
		WPFORMS_PLUGIN_URL . 'assets/css/final.css',
		array(),
		WPFORMS_VERSION
	);

	wp_enqueue_script(
		'infact_admin',
		WPFORMS_PLUGIN_URL . "assets/js/admin-validate.js",
		array( 'jquery' ),
		WPFORMS_VERSION,
		false
	);
}

add_action( 'admin_enqueue_scripts', 'global_admin_style', 99999, 1 );