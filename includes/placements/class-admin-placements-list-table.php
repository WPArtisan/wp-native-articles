<?php
/**
 * Custom list table for displaying the placements table in the admin.
 *
 * @package     wp-native-articles
 * @subpackage  Includes/Placements
 * @copyright   Copyright (c) 2017, WPArtisan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.0
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Placemesnt List Table.
 */
class WPNA_Admin_Placements_List_Table extends WP_List_Table {

	/**
	 * Total items found. Used for pagination.
	 *
	 * @access public
	 * @var int
	 */
	public $total_items;

	/**
	 * Name of the placements table.
	 *
	 * @access public
	 * @var string
	 */
	public $table_name;

	/**
	 * Class constructor.
	 *
	 * Set the tablename & trigger the parent class constructor.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		global $wpdb;

		// Set the tablename.
		$this->table_name = "{$wpdb->base_prefix}wpna_placements";

		// Setup the parent list table class.
		parent::__construct( array(
			'singular' => esc_html__( 'Placement', 'wp-native-articles' ), // Singular name of the listed records.
			'plural'   => esc_html__( 'Placements', 'wp-native-articles' ), // Plural name of the listed records.
			'ajax'     => false, // No need for ajax.
		) );

	}

	/**
	 * Add extra markup in the toolbars before or after the list.
	 *
	 * @access public
	 * @param string $which Helps you decide if you add the markup after (bottom) or before (top) the list.
	 * @return void
	 */
	public function extra_tablenav( $which ) {
		// @codingStandardsIgnoreStart
		if ( 'top' === $which ) {
			// The code that goes before the table is here.
		} elseif ( 'bottom' === $which ) {
			// The code that goes after the table is here.
		}
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Set the columns for the table.
	 *
	 * @access public
	 * @return array The table columns.
	 */
	public function get_columns() {
		$columns = array(
			'cb'         => '<input type ="checkbox" />',
			'name'       => esc_html__( 'Name', 'wp-native-articles' ),
			'status'     => esc_html__( 'Status', 'wp-native-articles' ),
			'start_date' => esc_html__( 'Start Date', 'wp-native-articles' ),
			'end_date'   => esc_html__( 'End Date', 'wp-native-articles' ),
		);

		return $columns;
	}

	/**
	 * Format the row output for each column.
	 *
	 * @access public
	 * @param  array  $item        The current row item being dealt with.
	 * @param  string $column_name The current column being outputted.
	 * @return string The output for that row for that column.
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'status':
				// Style WP green if it's active.
				if ( 'active' === $item[ $column_name ] ) {
					return '<span style="color:#46b450;">' . $item[ $column_name ] . '</span>';
				} else {
					return $item[ $column_name ];
				}
			case 'start_date':
			case 'end_date':
				// If no date is set return a palceholder, otherwse format the date.
				if ( empty( $item[ $column_name ] ) || '0000-00-00 00:00:00' === $item[ $column_name ] ) {
					return '~';
				} else {
					return date( get_option( 'date_format' ), strtotime( $item[ $column_name ] ) );
				}
			default:
				return '';
		}
	}

	/**
	 * Format the output for the cb column.
	 *
	 * @access public
	 * @param  array $item Row item.
	 * @return string Output for this row and column.
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="placement[]" value="%d" />', absint( $item['id'] ) );
	}

	/**
	 * Foramt the output for the name column.
	 *
	 * @access public
	 * @param  array $item Row item.
	 * @return string Output for this row and column.
	 */
	public function column_name( $item ) {
		// Create a nonce.
		$delete_nonce = wp_create_nonce( 'wpna_delete_placement' );

		// @codingStandardsIgnoreLine
		$page_id = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) );

		// Create row actions for this item.
		$actions = array(
			'edit'   => sprintf(
				'<a href="?page=%s&wpna-action=%s&placement=%s">%s</a>',
				esc_attr( $page_id ),
				'edit_placement',
				absint( $item['id'] ),
				esc_html__( 'Edit', 'wp-native-articles' )
			),
			'delete' => sprintf(
				'<a href="?page=%s&action=%s&placement=%s&_wpnonce=%s" onclick="return confirm(\'%s\');">%s</a>',
				esc_attr( $page_id ),
				'delete',
				absint( $item['id'] ),
				$delete_nonce,
				esc_html__( 'Are you sure you want to delete this placement?', 'wp-native-articles' ),
				esc_html__( 'Delete', 'wp-native-articles' )
			),
		);

		return sprintf( '<strong>%1$s</strong>%2$s', $item['name'], $this->row_actions( $actions ) );
	}

	/**
	 * Define which columns are sortable.
	 *
	 * @access public
	 * @return array Columns that should be sortable.
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'name'       => array( 'name', false ),
			'status'     => array( 'status', false ),
			'start_date' => array( 'start_date', false ),
			'end_date'   => array( 'end_date', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Lists the bulk actions that can take place.
	 *
	 * @access public
	 * @return array Bulk actions to return.
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-activate'   => esc_html__( 'Activate', 'wp-native-articles' ),
			'bulk-deactivate' => esc_html__( 'Deactivate', 'wp-native-articles' ),
			'bulk-delete'     => esc_html__( 'Delete', 'wp-native-articles' ),
		);

		return $actions;
	}

	/**
	 * The text to display if there are no rows to show.
	 *
	 * @access public
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'No placements available', 'wp-native-articles' );
	}

	/**
	 * Setup the columns, items and pagination.
	 *
	 * @access public
	 * @return void
	 */
	public function prepare_items() {
		// Setup the colum headers.
		$this->_column_headers = $this->get_column_info();

		// Get the items.
		$this->items = $this->get_items();

		// Pagination.
		$per_page    = $this->get_items_per_page( 'placements_per_page', 20 );
		$total_items = $this->total_items;

		// REQUIRED. We also have to register our pagination options & calculations.
		$this->set_pagination_args( array(
			'total_items' => absint( $total_items ), // WE have to calculate the total number of items.
			'per_page'    => absint( $per_page ),    // WE have to determine how many items to show on a page.
			'total_pages' => ceil( $total_items / $per_page ), // WE have to calculate the total number of pages.
		) );
	}

	/**
	 * Queries the DB for the actual items.
	 *
	 * @access public
	 * @return array
	 */
	public function get_items() {
		global $wpdb;

		// Only show for premium users.
		if ( ! defined( 'WPNA_PREMIUM' ) || ! WPNA_PREMIUM ) {
			return;
		}

		// Ordering parameters.
		$order = 'ASC';
		// @codingStandardsIgnoreLine
		if ( ! empty( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
			$order = 'DESC';
		}

		// Order by columns.
		$valid_orderby_options = $this->get_columns();
		$orderby               = 'status, id';
		// @codingStandardsIgnoreStart
		if ( ! empty( $_GET['orderby'] ) && isset( $valid_orderby_options[ $_GET['orderby'] ] ) ) {
			$orderby = wp_unslash( $_GET['orderby'] );
		}
		// @codingStandardsIgnoreEnd

		$where = $wpdb->prepare( 'WHERE blog_id = %d', get_current_blog_id() );

		// If search is set. Searches don't need nonces.
		// @codingStandardsIgnoreStart
		if ( ! empty( $_POST['s'] ) ) {
			$where .= $wpdb->prepare(
				" AND ( id = %d OR name LIKE '%%%s%%' OR content LIKE '%%%s%%' )",
				absint( wp_unslash( $_POST['s'] ) ),
				sanitize_text_field( wp_unslash( $_POST['s'] ) ),
				sanitize_text_field( wp_unslash( $_POST['s'] ) )
			);
		}
		// @codingStandardsIgnoreEnd

		// Construct the default query.
		$query = "FROM {$this->table_name} {$where} ORDER BY {$orderby} $order";

		// Total items.
		// @codingStandardsIgnoreStart
		$this->total_items = $wpdb->get_var( 'SELECT COUNT(*) ' . $query );

		// Pagination options.
		$per_page = $this->get_items_per_page( 'placements_per_page', 5 );

		// Grab the current page.
		$current_page = $this->get_pagenum();

		// Record set to show is -1 from the current page.
		$page_to_show = absint( $current_page - 1 );

		// Add the pagination stuff into the query.
		$query .= $wpdb->prepare( ' LIMIT %d, %d', ( $page_to_show * $per_page ), $per_page );

		// Construct the select query.
		$select_query = 'SELECT * ' . $query;

		// @codingStandardsIgnoreLine
		return $wpdb->get_results( $select_query, ARRAY_A );
	}

	/**
	 * Handles any bulk actions that can be performed on the table.
	 *
	 * Also handles in row deletes.
	 *
	 * @access public
	 * @return void
	 */
	public function process_bulk_action() {

		// No action specified then return.
		if ( ! $this->current_action() ) {
			return;
		}

		// Check the user has the correct privileges.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check there's a nonce and that it is valid.
		// This checks both the bulk-action nonce and the inline action URL nonce.
		// @codingStandardsIgnoreLine
		if ( empty( $_REQUEST['_wpnonce'] ) || ( ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'wpna_delete_placement' ) && ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'bulk-' . $this->_args['plural'] ) ) ) {
			wp_die( 'Invalid nonce - WPNA Placements Action' );
		}

		global $wpdb;

		// Ensure it's always an array of ids.
		// @codingStandardsIgnoreLine
		$placements = (array) $_REQUEST['placement'];
		// remive any WP added slashes.
		$placements = wp_unslash( $placements );
		// Validate they're all positive intergers.
		$placements = array_map( 'absint', $placements );

		// Record the rows affected.
		$rows_affected = array();

		switch ( $this->current_action() ) {

			// Delete rows.
			case 'delete':
			case 'bulk-delete':

				// Loop over the array of record IDs and delete them.
				foreach ( $placements as $id ) {
					$rows_affected[] = wpna_delete_placement( $id );
				}

				wp_safe_redirect( esc_url_raw( add_query_arg( array( 'page' => 'wpna_placements', 'wpna-message' => 'placement_delete_success' ), admin_url( '/admin.php' ) ) ) );
				exit;

				break;

			// Change row state.
			case 'bulk-activate':
			case 'bulk-deactivate':

				// Work out the new status.
				$status = 'active';
				if ( 'bulk-deactivate' === $this->current_action() ) {
					$status = 'inactive';
				}

				// Loop over the array of record IDs and update them.
				foreach ( $placements as $id ) {
					// todo: change to fucntion call.
					$rows_affected[] = $wpdb->update( $this->table_name, array( 'status' => $status ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );

					$placement = wpna_get_placement( $id );
					$placement->clear_cache();
				}

				break;

			// No matching action found.
			default:
				wp_die( esc_html__( 'No matching action found - WPNA Placements', 'wp-native-articles' ) );
				break;
		}

		// Redirect back with message.
		wp_safe_redirect( esc_url_raw( add_query_arg( array( 'page' => 'wpna_placements', 'wpna-message' => 'placement_update_success' ), admin_url( '/admin.php' ) ) ) );
		exit;

	}

}
