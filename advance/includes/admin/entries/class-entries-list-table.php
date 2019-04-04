<?php

/**
 * Generates the table on the entries overview page.
 *
 * @package    Infactform
 * @author     Infact
 * @since      1.0.0
 */
class WPForms_Entries_Table extends WP_List_Table {

	/**
	 * Number of entries to show per page.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $per_page;

	/**
	 * Form data as an array.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $form_data;

	/**
	 * Form id.
	 *
	 * @since 1.0.0
	 *
	 * @var string|integer
	 */
	public $form_id;

	/**
	 * Number of different entry types.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $counts;

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
				'singular' => 'entry',
				'plural'   => 'entries',
				'ajax'     => false,
			)
		);

		// Default number of forms to show per page
		$this->per_page = apply_filters( 'wpforms_entries_per_page', 30 );
	}

	/**
	 * Get the entry counts for various types of entries.
	 *
	 * @since 1.0.0
	 */
	public function get_counts() {

		$this->counts = array();

		$this->counts['total'] = wpforms()->entry->get_entries(
			array(
				'form_id' => $this->form_id,
			),
			true
		);
		$this->counts['unread'] = wpforms()->entry->get_entries(
			array(
				'form_id' => $this->form_id,
				'viewed'  => '0',
			),
			true
		);
		$this->counts['starred'] = wpforms()->entry->get_entries(
			array(
				'form_id' => $this->form_id,
				'starred' => '1',
			),
			true
		);

		$this->counts = apply_filters( 'wpforms_entries_table_counts', $this->counts, $this->form_data );
	}

	/**
	 * Retrieve the view types.
	 *
	 * @since 1.1.6
	 */
	public function get_views() {

		$base = add_query_arg(
			array(
				'page'    => 'wpforms-entries',
				'view'    => 'list',
				'form_id' => $this->form_id,
			),
			admin_url( 'admin.php' )
		);

		$current = isset( $_GET['type'] ) ? $_GET['type'] : '';
		$total   = '&nbsp;<span class="count">(<span class="total-num">' . $this->counts['total'] . '</span>)</span>';
		$unread  = '&nbsp;<span class="count">(<span class="unread-num">' . $this->counts['unread'] . '</span>)</span>';
		$starred = '&nbsp;<span class="count">(<span class="starred-num">' . $this->counts['starred'] . '</span>)</span>';
		$all     = ( empty( $_GET['status'] ) && ( 'all' === $current || empty( $current ) ) ) ? 'class="current"' : '';

		$views = array(
			'all'     => sprintf( '<a href="%s"%s>%s</a>', esc_url( remove_query_arg( 'type', $base ) ), $all, __( 'All', 'wpforms' ) . $total ),
		);

		return apply_filters( 'wpforms_entries_table_views', $views, $this->form_data, $this->counts );
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 1.0.0
	 *
	 * @return array $columns Array of all the list table columns.
	 */
	public function get_columns() {

		$field_columns = 3;

		$columns               = array();
		$columns['cb']         = '<input type="checkbox" />';
		$columns['indicators'] = '';
		$columns               = $this->get_columns_form_fields( $columns, $field_columns );

		$columns['date'] = __( 'Date', 'wpforms' );

		$actions            = __( 'Actions', 'wpforms' );
		$actions            .= ' <a href="#" title="' . esc_attr__( 'Change columns to display', 'wpforms' ) . '" id="wpforms-entries-table-edit-columns"><i class="fa fa-cog" aria-hidden="true"></i></a>';
		$columns['actions'] = $actions;

		return apply_filters( 'wpforms_entries_table_columns', $columns, $this->form_data );
	}

	/**
	 * Retrieve the table's sortable columns.
	 *
	 * @since 1.2.6
	 *
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {

		$sortable = array(
			'id'            => array( 'title', false ),
			'date'          => array( 'date', false ),
			'status'        => array( 'status', false ),
			'payment_total' => array( 'payment_total', false ),
		);

		return apply_filters( 'wpforms_entries_table_sortable', $sortable, $this->form_data );
	}

	/**
	 * Logic to determine which fields are displayed in the table columns.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns
	 * @param int $display
	 *
	 * @return array
	 */
	public function get_columns_form_fields( $columns = array(), $display = 3 ) {

		$entry_columns = wpforms()->form->get_meta( $this->form_id, 'entry_columns' );

		if ( ! $entry_columns && ! empty( $this->form_data['fields'] ) ) {
			$x = 0;
			foreach ( $this->form_data['fields'] as $id => $field ) {
				$disallow = apply_filters( 'wpforms_entries_table_fields_disallow', array( 'divider', 'html', 'pagebreak', 'captcha' ) );
				if ( ! in_array( $field['type'], $disallow, true ) && $x < $display ) {
					$name                              = ! empty( $field['label'] ) ? wp_strip_all_tags( $field['label'] ) : __( 'Field', 'wpforms' );
					$columns[ 'wpforms_field_' . $id ] = $name;
					$x ++;
				}
			}
		} else {
			foreach ( $entry_columns as $id ) {
				// Check to make sure the field as not been removed.
				if ( empty( $this->form_data['fields'][ $id ] ) ) {
					continue;
				}
				$name                              = ! empty( $this->form_data['fields'][ $id ]['label'] ) ? wp_strip_all_tags( $this->form_data['fields'][ $id ]['label'] ) : __( 'Field', 'wpforms' );
				$columns[ 'wpforms_field_' . $id ] = $name;
			}
		}

		return $columns;
	}

	/**
	 * Render the checkbox column.
	 *
	 * @since 1.0.0
	 *
	 * @param object $entry
	 *
	 * @return string
	 */
	public function column_cb( $entry ) {

		return '<input type="checkbox" name="entry_id[]" value="' . absint( $entry->entry_id ) . '" />';
	}

	/**
	 * Show specific form fields.
	 *
	 * @since 1.0.0
	 *
	 * @param object $entry
	 * @param string $column_name
	 *
	 * @return string
	 */
	public function column_form_field( $entry, $column_name ) {

		$field_id     = str_replace( 'wpforms_field_', '', $column_name );
		$entry_fields = wpforms_decode( $entry->fields );

		if (
			! empty( $entry_fields[ $field_id ] ) &&
			! empty( $entry_fields[ $field_id ]['value'] )
		) {

			$value = $entry_fields[ $field_id ]['value'];

			// Limit to 5 lines
			$lines = explode( "\n", $value );
			$value = array_slice( $lines, 0, 4 );
			$value = implode( "\n", $value );

			if ( count( $lines ) > 5 ) {
				$value .= '&hellip;';
			} elseif ( strlen( $value ) > 75 ) {
				$value = substr( $value, 0, 75 ) . '&hellip;';
			}

			$value = nl2br( wp_strip_all_tags( trim( $value ) ) );

			return apply_filters( 'wpforms_html_field_value', $value, $entry_fields[ $field_id ], $this->form_data, 'entry-table' );

		} else {
			return '-';
		}
	}

	/**
	 * Renders the columns.
	 *
	 * @since 1.0.0
	 *
	 * @param object $entry
	 * @param string $column_name
	 *
	 * @return string
	 */
	public function column_default( $entry, $column_name ) {

		$entry_meta = json_decode( $entry->meta, true );

		switch ( $column_name ) {

			case 'id':
				$value = absint( $entry->entry_id );
				break;

			case 'date':
				$value = date_i18n( get_option( 'date_format' ), strtotime( $entry->date ) + ( get_option( 'gmt_offset' ) * 3600 ) );
				break;

			case 'status':
				if ( 'payment' === $entry->type ) {
					// For payments, display dollar icon to easily indicate this
					// status is related to a payment
					if ( ! empty( $entry->status ) ) {
						$value = ucwords( sanitize_text_field( $entry->status ) );
					} else {
						$value = __( 'Unknown', 'wpforms' );
					}
					$value .= ' <img src="' . WPFORMS_PLUGIN_URL . '/assets/images/icon-dollar.png" alt="Payment"> ';
				} else {
					if ( ! empty( $entry->status ) ) {
						$value = ucwords( sanitize_text_field( $entry->status ) );
					} else {
						$value = __( 'Completed', 'wpforms' );
					}
				}
				break;

			case 'payment_total':
				if ( 'payment' === $entry->type && isset( $entry_meta['payment_total'] ) ) {
					$amount = wpforms_sanitize_amount( $entry_meta['payment_total'], $entry_meta['payment_currency'] );
					$total  = wpforms_format_amount( $amount, true, $entry_meta['payment_currency'] );
					$value  = $total;
				} else {
					$value = '-';
				}
				break;

			default:
				if ( false !== strpos( $column_name, 'wpforms_field_' ) ) {
					$value = $this->column_form_field( $entry, $column_name );
				} else {
					$value = '';
				}
		}

		return apply_filters( 'wpforms_entry_table_column_value', $value, $entry, $column_name );
	}

	/**
	 * Render the indicators column.
	 *
	 * @since 1.1.6
	 *
	 * @param object $entry
	 *
	 * @return string
	 */
	public function column_indicators( $entry ) {
		return '';
	}

	/**
	 * Render the actions column.
	 *
	 * @since 1.0.0
	 *
	 * @param object $entry
	 *
	 * @return string
	 */
	public function column_actions( $entry ) {

		$actions = array();

		// View
		$actions[] = sprintf(
			'<a href="%s" title="%s" class="view">%s</a>',
			add_query_arg(
				array(
					'view'     => 'details',
					'entry_id' => $entry->entry_id,
				),
				admin_url( 'admin.php?page=wpforms-entries'
				)
			),
			__( 'View Form Entry', 'wpforms' ),
			__( 'View', 'wpforms' )
		);

		// Delete
		$actions[] = sprintf(
			'<a href="%s" title="%s" class="delete">%s</a>',
			wp_nonce_url(
				add_query_arg(
					array(
						'view'     => 'list',
						'action'   => 'delete',
						'form_id'  => $this->form_id,
						'entry_id' => $entry->entry_id,
					)
				),
				'bulk-entries'
			),
			__( 'Delete Form Entry', 'wpforms' ),
			__( 'Delete', 'wpforms' )
		);

		return implode( ' <span class="sep">|</span> ', apply_filters( 'wpforms_entry_table_actions', $actions, $entry ) );
	}

	/**
	 * Define bulk actions available for our table listing
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
	 * Process the bulk actions
	 *
	 * @since 1.0.0
	 */
	public function process_bulk_actions() {

		if ( empty( $_REQUEST['_wpnonce'] ) ) {
			return;
		}

		if ( ! current_user_can( apply_filters( 'wpforms_manage_cap', 'manage_options' ) ) ) {
			return;
		}

		if (
			! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-entries' ) &&
			! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-entries-nonce' )
		) {
			return;
		}

		$ids = isset( $_GET['entry_id'] ) ? $_GET['entry_id'] : false;

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		$ids    = array_map( 'absint', $ids );
		$action = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : false;

		if ( empty( $ids ) || empty( $action ) ) {
			return;
		}

		// Mark as read
		if ( 'read' === $this->current_action() ) {

			foreach ( $ids as $id ) {
				wpforms()->entry->update(
					$id,
					array(
						'viewed' => '1',
					)
				);
			}
			?>
			<div class="updated notice">
				<p>
					<?php
					if ( count( $ids ) === 1 ) {
						_e( 'Entry was successfully marked as read.', 'wpforms' );
					} else {
						_e( 'Entries were successfully marked as read.', 'wpforms' );
					}
					?>
				</p>
			</div>
			<?php
		}

		// Mark as unread
		if ( 'unread' === $this->current_action() ) {

			foreach ( $ids as $id ) {
				wpforms()->entry->update(
					$id,
					array(
						'viewed' => '0',
					)
				);
			}
			?>
			<div class="updated notice">
				<p>
					<?php
					if ( count( $ids ) === 1 ) {
						_e( 'Entry was successfully marked as unread.', 'wpforms' );
					} else {
						_e( 'Entries were successfully marked as unread.', 'wpforms' );
					}
					?>
				</p>
			</div>
			<?php
		}

		// Star entry
		if ( 'star' === $this->current_action() ) {

			foreach ( $ids as $id ) {
				wpforms()->entry->update(
					$id,
					array(
						'starred' => '1',
					)
				);
			}
			?>
			<div class="updated notice">
				<p>
					<?php
					if ( count( $ids ) === 1 ) {
						_e( 'Entry was successfully starred.', 'wpforms' );
					} else {
						_e( 'Entries were successfully starred.', 'wpforms' );
					}
					?>
				</p>
			</div>
			<?php
		}

		// Star entry
		if ( 'unstar' === $this->current_action() ) {

			foreach ( $ids as $id ) {
				wpforms()->entry->update(
					$id,
					array(
						'starred' => '0',
					)
				);
			}
			?>
			<div class="updated notice">
				<p>
					<?php
					if ( count( $ids ) === 1 ) {
						_e( 'Entry was successfully unstarred.', 'wpforms' );
					} else {
						_e( 'Entries were successfully unstarred.', 'wpforms' );
					}
					?>
				</p>
			</div>
			<?php
		}

		// Delete entries
		if ( 'delete' === $this->current_action() ) {

			foreach ( $ids as $id ) {
				wpforms()->entry->delete( $id );
			}
			?>
			<div class="updated notice">
				<p>
					<?php
					if ( count( $ids ) === 1 ) {
						_e( 'Archive was successfully deleted.', 'wpforms' );
					} else {
						_e( 'Archives were successfully deleted.', 'wpforms' );
					}
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Message to be displayed when there are no entries.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {

		_e( 'Whoops, it appears you do not have any form archives yet.', 'wpforms' );
	}

	/**
	 * Fetch and setup the final data for the table
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {

		// Process bulk actions if found
		$this->process_bulk_actions();

		// Retrieve count
		$this->get_counts();

		// Setup the columns
		$columns = $this->get_columns();

		// Hidden columns (none)
		$hidden = array();

		// Define which columns can be sorted
		$sortable = $this->get_sortable_columns();

		// Set column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Get entries
		$total_items = $this->counts['total'];
		$page        = $this->get_pagenum();
		$order       = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
		$orderby     = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'entry_id';
		$per_page    = $this->get_items_per_page( 'wpforms_entries_per_page', $this->per_page );
		$data_args   = array(
			'form_id' => $this->form_id,
			'number'  => $per_page,
			'offset'  => $per_page * ( $page - 1 ),
			'order'   => $order,
			'orderby' => $orderby,
		);

		if ( ! empty( $_GET['type'] ) && 'starred' === $_GET['type'] ) {
			$data_args['starred'] = '1';
			$total_items          = $this->counts['starred'];
		}
		if ( ! empty( $_GET['type'] ) && 'unread' === $_GET['type'] ) {
			$data_args['viewed'] = '0';
			$total_items         = $this->counts['unread'];
		}
		if ( ! empty( $_GET['status'] ) ) {
			$data_args['status'] = sanitize_text_field( $_GET['status'] );
			$total_items         = $this->counts['abandoned'];
		}

		$data_args = apply_filters( 'wpforms_entry_table_args', $data_args );
		$data      = wpforms()->entry->get_entries( $data_args );

		// Maybe sort by payment total
		if ( 'payment_total' === $orderby ) {
			usort( $data, array( $this, 'payment_total_sort' ) );
			if ( 'DESC' === strtoupper( $order ) ) {
				$data = array_reverse( $data );
			}
		}

		// Giddy up
		$this->items = $data;

		// Finalize pagination
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	/**
	 * Sort by payment total.
	 *
	 * @since 1.2.6
	 *
	 * @param object $a
	 * @param object $b
	 *
	 * @return int
	 */
	public function payment_total_sort( $a, $b ) {

		$a_meta  = json_decode( $a->meta, true );
		$a_total = ! empty( $a_meta['payment_total'] ) ? wpforms_sanitize_amount( $a_meta['payment_total'] ) : 0;
		$b_meta  = json_decode( $b->meta, true );
		$b_total = ! empty( $b_meta['payment_total'] ) ? wpforms_sanitize_amount( $b_meta['payment_total'] ) : 0;

		if ( $a_total == $b_total ) {
			return 0;
		}

		return ( $a_total < $b_total ) ? - 1 : 1;
	}
}
