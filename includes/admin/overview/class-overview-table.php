<?php
/**
 * Generates the table on the plugin overview page.
 *
 * @package    Infactform
 * @author     Infact
 * @since      1.0.0
*/
class WPForms_Overview_Table extends WP_List_Table {

	/**
	 * Number of forms to show per page.
	 *
	 * @since 1.0.0
	 */
	public $per_page;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Bring globals into scope for parent.
		global $status, $page;

		// Utilize the parent constructor to build the main class properties.
		parent::__construct(
			array(
				'singular' => 'form',
				'plural'   => 'forms',
				'ajax'     => false,
			)
		);

		// Default number of forms to show per page
		$this->per_page = apply_filters( 'wpforms_overview_per_page', 20 );
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 1.0.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {

		$columns = array(
			'cb'         => '<input type="checkbox" />',
			'form_name'  => __( 'Name', 'wpforms' ),
			'shortcode'  => __( 'Shortcode', 'wpforms' ),
			'created'    => __( 'Created', 'wpforms' ),
		);

		return apply_filters( 'wpforms_overview_table_columns', $columns );
	}

	/**
	 * Render the checkbox column.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $form
	 *
	 * @return string
	 */
	public function column_cb( $form ) {

		return '<input type="checkbox" name="form_id[]" value="' . absint( $form->ID ) . '" />';
	}

	/**
	 * Renders the columns.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $form
	 * @param string $column_name
	 *
	 * @return string
	 */
	public function column_default( $form, $column_name ) {

		  switch ( $column_name ) {
			  case 'id':
				  $value = $form->ID;
				  break;

			  case 'shortcode':
				  $value = '[wpforms id="' . $form->ID . '"]';
				  break;

			  case 'created':
				  $value = get_the_date( get_option( 'date_format' ), $form );
				  break;

			  case 'modified':
				  $value = get_post_modified_time( get_option( 'date_format' ), false, $form );
				  break;

			  case 'author':
				  $author = get_userdata( $form->post_author );
				  $value  = $author->display_name;
				  break;

			  case 'php':
				  $value = '<code style="display:block;font-size:11px;">if( function_exists( \'wpforms_get\' ) ){ wpforms_get( ' . $form->ID . ' ); }</code>';
				  break;

			  default:
				  $value = '';
			}

		return apply_filters( 'wpforms_overview_table_column_value', $value, $form, $column_name );
	}

	/**
	 * Render the form name column with action links.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $form
	 *
	 * @return string
	 */
	public function column_form_name( $form ) {

		// Prepare variables.
		$name = ! empty( $form->post_title ) ? $form->post_title : $form->post_name;
		$name = sprintf(
			'<a class="row-title" href="%s" title="%s"><strong>%s</strong></a>',
			add_query_arg(
				array(
					'view'    => 'fields',
					'form_id' => $form->ID,
				),
				admin_url( 'admin.php?page=wpforms-builder' )
			),
			__( 'Edit this form', 'wpforms' ),
			$name
		);

		// Build all of the row action links.
		$row_actions = array();

		// Edit
		$row_actions['edit'] = sprintf(
			'<a href="%s" title="%s">%s</a>',
			add_query_arg(
				array(
					'view'    => 'fields',
					'form_id' => $form->ID,
				),
				admin_url( 'admin.php?page=wpforms-builder' )
			),
			__( 'Edit this form', 'wpforms' ),
			__( 'Edit', 'wpforms' )
		);

		// Entries
		$row_actions['entries'] = sprintf(
			'<a href="%s" title="%s">%s</a>',
			add_query_arg(
				array(
					'view' => 'list',
					'form_id' => $form->ID,
				),
				admin_url( 'admin.php?page=wpforms-entries' )
			),
			__( 'View archives', 'wpforms' ),
			__( 'Archives', 'wpforms' )
		);



		// Duplicate
		$row_actions['duplicate'] = sprintf(
			'<a href="%s" title="%s">%s</a>',
			wp_nonce_url(
				add_query_arg(
					array(
						'action'  => 'duplicate',
						'form_id' => $form->ID,
					),
					admin_url( 'admin.php?page=wpforms-overview' )
				),
				'wpforms_duplicate_form_nonce'
			),
			__( 'Duplicate this form', 'wpforms' ),
			__( 'Duplicate', 'wpforms' )
		);

		// Delete
		$row_actions['delete'] = sprintf(
			'<a href="%s" title="%s">%s</a>',
			wp_nonce_url(
				add_query_arg(
					array(
						'action'  => 'delete',
						'form_id' => $form->ID,
					),
					admin_url( 'admin.php?page=wpforms-overview' )
				),
				'wpforms_delete_form_nonce'
			),
			__( 'Delete this form', 'wpforms' ),
			__( 'Delete', 'wpforms' )
		);

		// Build the row action links and return the value.
		$value = $name . $this->row_actions( $row_actions );

		return apply_filters( 'wpforms_overview_row_actions', $value, $form );
	}

	/**
	 * Define bulk actions available for our table listing.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {

		$actions = array(
			'delete' => __( 'Delete', 'wpforms' ),
		);
		return $actions;
	}

	/**
	 * Process the bulk actions.
	 *
	 * @since 1.0.0
	 */
	public function process_bulk_actions() {

		$ids = isset( $_GET['form_id'] ) ? $_GET['form_id'] : array();

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		$ids    = array_map( 'absint', $ids );
		$action = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : false;

		if ( empty( $ids ) || empty( $action ) ) {
			return;
		}

		// Delete one or multiple forms - both delete links and bulk actions
		if ( 'delete' === $this->current_action() ) {

			if (
				wp_verify_nonce( $_GET['_wpnonce'], 'bulk-forms' ) ||
				wp_verify_nonce( $_GET['_wpnonce'], 'wpforms_delete_form_nonce' )
			) {
				foreach ( $ids as $id ) {
					wpforms()->form->delete( $id );
				}
				?>
				<div class="notice updated">
					<p>
						<?php
						if ( count( $ids ) === 1 ) {
							_e( 'Form was successfully deleted.', 'wpforms' );
						} else {
							_e( 'Forms were successfully deleted.', 'wpforms' );
						}
						?>
					</p>
				</div>
				<?php
			} else {
				?>
				<div class="notice updated">
					<p>
						<?php _e( 'Security check failed. Please try again.', 'wpforms' ); ?>
					</p>
				</div>
				<?php
			}
		}

		// Duplicate form - currently just delete links (no bulk action at the moment)
		if ( 'duplicate' === $this->current_action() ) {

			if ( wp_verify_nonce( $_GET['_wpnonce'], 'wpforms_duplicate_form_nonce' ) ) {
				foreach ( $ids as $id ) {
					wpforms()->form->duplicate( $id );
				}
				?>
				<div class="notice updated">
					<p>
						<?php
						if ( count( $ids ) === 1 ) {
							_e( 'Form was successfully duplicated.', 'wpforms' );
						} else {
							_e( 'Forms were successfully duplicated.', 'wpforms' );
						}
						?>
					</p>
				</div>
				<?php
			} else {
				?>
				<div class="notice updated">
					<p>
						<?php _e( 'Security check failed. Please try again.', 'wpforms' ); ?>
					</p>
				</div>
				<?php
			}
		}
	}

	/**
	 * Message to be displayed when there are no forms.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {

		printf( __( 'Dont have created a form yet. Create new form <a href="%s">here </a>', 'wpforms' ), admin_url( 'admin.php?page=wpforms-builder' ) );
	}

	/**
	 * Fetch and setup the final data for the table.
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {

		// Process bulk actions if found
		$this->process_bulk_actions();

		// Setup the columns
		$columns  = $this->get_columns();

		// Hidden columns (none)
		$hidden = array();

		// Define which columns can be sorted - form name, date
		$sortable = array(
			'form_name' => array( 'title', false ),
			'created'   => array( 'date', false ),
		);

		// Set column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Get forms
		$total    = wp_count_posts( 'wpforms' )->publish;
		$page     = $this->get_pagenum();
		$order    = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
		$orderby  = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'ID';
		$per_page = $this->get_items_per_page( 'wpforms_forms_per_page', $this->per_page );
		$data     = wpforms()->form->get( '', array(
			'orderby'        => $orderby,
			'order'          => $order,
			'nopaging'       => false,
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'no_found_rows'  => false,
		) );

		// Giddy up
		$this->items = $data;

		// Finalize pagination
		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total / $per_page ),
			)
		);
	}
}
