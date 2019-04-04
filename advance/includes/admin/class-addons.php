<?php
/**
 * Addons class.
 *
 * @package    Infactform
 * @author     Infact
 * @since      1.0.0
*/
class WPForms_Addons {

	/**
	 * WPForms addons
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $addons;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Maybe load addons page.
		add_action( 'admin_init', array( $this, 'init' ) );
	}

	/**
	 * Determing if the user is viewing the settings page, if so, party on.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Check what page we are on.
		$page = isset( $_GET['page'] ) ? $_GET['page'] : '';

		// Only load if we are actually on the settings page
		if ( 'wpforms-addons' === $page ) {

			add_action( 'admin_enqueue_scripts',  array( $this, 'enqueues' ) );
			add_action( 'wpforms_admin_page',     array( $this, 'output'   ) );
		}
	}

	/**
	 * Enqueue assets for the addons page.
	 *
	 * @since 1.0.0
	 */
	public function enqueues() {

		// JS
		wp_enqueue_script(
			'jquery-matchheight',
			WPFORMS_PLUGIN_URL . 'assets/js/jquery.matchHeight-min.js',
			array( 'jquery'  ),
			'0.7.0',
			false
		);
	}

	/**
	 * Build the output for the plugin addons page.
	 *
	 * @since 1.0.0
	 */
	public function output() {

		$refresh      = isset( $_GET['wpforms_refresh_addons'] );
		$errors       = wpforms()->license->get_errors();
		$type         = wpforms()->license->type();
		$this->addons = wpforms()->license->addons( $refresh );

	}

	/**
	 * Renders grid of addons.
	 *
	 * @since 1.0.0
	 * @param object $addons
	 * @param string $type_current
	 * @param array $type_show
	 * @param bool $unlock
	 */
	function addon_grid( $addons, $type_current, $type_show , $unlock = false ) {

		$count   = 0;
		$plugins = get_plugins();


		// Ultimate is the same level pro
		if ( $type_current === 'ultimate' ) {
			$type_current = 'pro';
		}

		foreach ( $addons as $id => $addon ) {

			$addon           = (array) $addon;
			$found           = false;
			$plugin_basename = $this->get_plugin_basename_from_slug( $addon['slug'], $plugins );
			$status_label    = '';
			$action_class    = 'action-button';

			foreach( $addon['types'] as $type ) {
				if ( in_array( $type, $type_show ) ) {
					$found = true;
				}
			}

			if ( ! $found ) {
				continue;
			}

			if ( ! in_array( $type_current, $addon['types'], true ) ) {
				$status = 'upgrade';
			} elseif ( is_plugin_active( $plugin_basename ) ) {
				$status       = 'active';
				$status_label = __( 'Active', 'wpforms' );
			} elseif( ! isset( $plugins[ $plugin_basename ] ) ) {
				$status       = 'download';
				$status_label = __( 'Not Installed', 'wpforms' );
			} elseif( is_plugin_inactive( $plugin_basename ) ) {
				$status       = 'inactive';
				$status_label = __( 'Inactive', 'wpforms' );
			} else {
				$status = 'upgrade';
			}

			$image = ! empty( $addon['image'] ) ? $addon['image'] :  WPFORMS_PLUGIN_URL . 'assets/images/sullie.png';



			$count++;

			if ( ! empty( $this->addons[ $id ] ) ) {
				unset( $this->addons[ $id ] );
			}
		}

		echo '<div style="clear:both;"></div>';
	}

	/**
	 * Retrieve the plugin basename from the plugin slug.
	 *
	 * @since 1.0.0
	 * @param string $slug The plugin slug.
	 * @return string      The plugin basename if found, else the plugin slug.
	 */
	public function get_plugin_basename_from_slug( $slug, $plugins ) {

		$keys = array_keys( $plugins );

		foreach ( $keys as $key ) {
			if ( preg_match( '|^' . $slug . '|', $key ) ) {
				return $key;
			}
		}
		return $slug;
	}
}
new WPForms_Addons;
