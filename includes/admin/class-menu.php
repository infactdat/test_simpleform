<?php
/**
 * Register menu elements and do other global tasks.
 *
 * @package    Infactform
 * @author     Infact
 * @since      1.0.0
 */
class WPForms_Admin_Menu {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Let's make some menus.
		add_action( 'admin_menu',         array( $this, 'register_menus' ), 9    );
		add_action( 'admin_print_styles', array( $this, 'menu_icon'      )       );

		// Plugins page settings link.
		add_filter( 'plugin_action_links_' . plugin_basename( WPFORMS_PLUGIN_DIR . 'wpforms.php' ), array( $this, 'settings_link' ) );
	}

	/**
	 * Register our menus.
	 *
	 * @since 1.0.0
	 */
	function register_menus() {

		$menu_cap = apply_filters( 'wpforms_manage_cap', 'manage_options' );

		// Default Forms top level menu item.
		add_menu_page(
			__( 'Simple form', 'wpforms' ),
			__( 'Simple form', 'wpforms' ),
			$menu_cap,
			'wpforms-overview',
			array( $this, 'admin_page' ),
			'dashicons-feedback',
			apply_filters( 'wpforms_menu_position', '57.7' )
		);

		// All Forms sub menu item.
		add_submenu_page(
			'wpforms-overview',
			__( 'Simple form', 'wpforms' ),
			__( 'All Forms', 'wpforms' ),
			$menu_cap,
			'wpforms-overview',
			array( $this, 'admin_page' )
		);

		// Add New sub menu item.
		add_submenu_page(
			'wpforms-overview',
			__( 'Form Builder', 'wpforms' ),
			__( 'Add New', 'wpforms' ),
			$menu_cap,
			'wpforms-builder',
			array( $this, 'admin_page' )
		);

		// Entries sub menu item.
		add_submenu_page(
			'wpforms-overview',
			__( 'Form Archives', 'wpforms' ),
			__( 'Archives', 'wpforms' ),
			$menu_cap,
			'wpforms-entries',
			array( $this, 'admin_page' )
		);

		do_action( 'wpform_admin_menu', $this );

		// Settings sub menu item.
		add_submenu_page(
			'wpforms-overview',
			__( 'WPForms Settings', 'wpforms' ),
			__( 'Settings', 'wpforms' ),
			$menu_cap,
			'wpforms-settings',
			array( $this, 'admin_page' )
		);

//        // Tools sub menu item.
//        add_submenu_page(
//            'wpforms-overview',
//            __( 'WPForms Tools', 'wpforms' ),
//            __( 'Tools', 'wpforms' ),
//            $menu_cap,
//            'wpforms-tools',
//            array( $this, 'admin_page' )
//        );

		// Hidden placeholder paged used for misc content.
		add_submenu_page(
			'wpforms-settings',
			__( 'WPForms', 'wpforms' ),
			__( 'Info', 'wpforms' ),
			$menu_cap,
			'wpforms-page',
			array( $this, 'admin_page' )
		);

	}

	/**
	 * Wrapper for the hook to render our custom settings pages.
	 *
	 * @since 1.0.0
	 */
	public function admin_page() {

		do_action( 'wpforms_admin_page' );
	}

	/**
	 * Load CSS for custom menu icon.
	 *
	 * @since 1.0.0
	 */
	public function menu_icon() {

		$menu_cap = apply_filters( 'wpforms_manage_cap', 'manage_options' );

		if ( ! current_user_can( $menu_cap ) ) {
			return;
		}
		?>
		
		<?php
	}

	/**
	 * Add settings link to the Plugins page.
	 *
	 * @since 1.3.9
	 * @param array $links
	 * @return array $links
	 */
	public function settings_link( $links ) {

		$admin_link = add_query_arg(
			array(
				'page' => 'wpforms-settings',
			),
			admin_url( 'admin.php' )
		);

		$setting_link = sprintf(
			'<a href="%s">%s</a>',
			$admin_link ,
			__( 'Settings', 'wpforms' )
		);

		array_unshift( $links, $setting_link );

		return $links;
	}

}
new WPForms_Admin_Menu;
