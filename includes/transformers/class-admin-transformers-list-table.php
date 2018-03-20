<?php
/**
 * Custom list table for displaying the transformer table in the admin.
 *
 * @package     wp-native-articles
 * @subpackage  Includes/Transformers
 * @copyright   Copyright (c) 2017, WPArtisan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5.0
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Transformers List Table.
 */
class WPNA_Admin_Transformers_List_Table extends WP_List_Table {

	/**
	 * Total active transformers found. Used for pagination.
	 *
	 * @access public
	 * @var int
	 */
	public $active_count = 0;

	/**
	 * Total inactive transformers found. Used for pagination.
	 *
	 * @access public
	 * @var int
	 */
	public $inactive_count = 0;
	/**
	 * Total transformers found. Used for pagination.
	 *
	 * @access public
	 * @var int
	 */
	public $total_count = 0;

	/**
	 * Class constructor.
	 *
	 * Set the tablename & trigger the parent class constructor.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		// Setup the parent list table class.
		parent::__construct( array(
			'singular' => esc_html__( 'Transformer', 'wp-native-articles' ), // Singular name of the listed records.
			'plural'   => esc_html__( 'Transformers', 'wp-native-articles' ), // Plural name of the listed records.
			'ajax'     => false, // No need for ajax.
		) );

		$this->get_transformers_counts();
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
			'cb'       => '<input type ="checkbox" />',
			'name'     => esc_html__( 'Name', 'wp-native-articles' ),
			'status'   => esc_html__( 'Status', 'wp-native-articles' ),
			'type'     => esc_html__( 'Type', 'wp-native-articles' ),
			'selector' => esc_html__( 'Selector', 'wp-native-articles' ),
			'rule'     => esc_html__( 'Rule', 'wp-native-articles' ),
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
		return $item->$column_name;
	}

	/**
	 * Format the output for the cb column.
	 *
	 * @access public
	 * @param  array $item Row item.
	 * @return string Output for this row and column.
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="transformer[]" value="%d" />', absint( $item->ID ) );
	}

	/**
	 * Format the output for the name column.
	 *
	 * @access public
	 * @param  array $item Row item.
	 * @return string Output for this row and column.
	 */
	public function column_name( $item ) {
		// Create a nonce.
		$delete_nonce = wp_create_nonce( 'wpna_delete_transformer' );

		// @codingStandardsIgnoreLine
		$page_id = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) );

		// Create row actions for this item.
		$actions = array(
			'edit'   => sprintf(
				'<a href="?page=%s&wpna-action=%s&transformer=%s">%s</a>',
				esc_attr( $page_id ),
				'edit_transformer',
				absint( $item->ID ),
				esc_html__( 'Edit', 'wp-native-articles' )
			),
			'delete' => sprintf(
				'<a href="?page=%s&action=%s&transformer=%s&_wpnonce=%s" onclick="return confirm(\'%s\');">%s</a>',
				esc_attr( $page_id ),
				'delete',
				absint( $item->ID ),
				$delete_nonce,
				esc_html__( 'Are you sure you want to delete this transformer?', 'wp-native-articles' ),
				esc_html__( 'Delete', 'wp-native-articles' )
			),
		);

		// If it hasn't got a name, set it as 'untitled'.
		$name = empty( $item->name ) ? esc_html__( '(untitled)', 'wp-native-articles' ) : $item->name;

		return sprintf( '<strong>%1$s</strong>%2$s', $name, $this->row_actions( $actions ) );
	}

	/**
	 * Format the output for the status column.
	 *
	 * @access public
	 * @param  array $item Row item.
	 * @return string Output for this row and column.
	 */
	public function column_status( $item ) {
		// Style WP green if it's active.
		if ( 'active' === $item->status ) {
			return '<span style="color:#46b450;">' . $item->status . '</span>';
		}
		return $item->status;
	}

	/**
	 * Format the output for the status column.
	 *
	 * @access public
	 * @param  array $item Row item.
	 * @return string Output for this row and column.
	 */
	public function column_selector( $item ) {
		// If it's a shortcode wrap it in shortcode brackets.
		if ( 'shortcode' === $item->type ) {
			return sprintf( '[%s]', $item->selector );
		}
		return $item->selector;
	}

	/**
	 * Define which columns are sortable.
	 *
	 * @access public
	 * @return array Columns that should be sortable.
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'name'   => array( 'name', false ),
			'status' => array( 'status', false ),
			'type'   => array( 'type', false ),
			'rule'   => array( 'rule', false ),
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
		esc_html_e( 'No transformers found.', 'wp-native-articles' );
	}

	/**
	 * Retrieve the transformers counts
	 *
	 * @access public
	 * @return void
	 */
	public function get_transformers_counts() {
		$transformers_count   = wp_count_posts( 'wpna_transformer' );
		$this->active_count   = $transformers_count->active;
		$this->inactive_count = $transformers_count->inactive;
		$this->total_count    = $transformers_count->active + $transformers_count->inactive;
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
		$per_page    = $this->get_items_per_page( 'transformers_per_page', 20 );
		$total_items = $this->total_count;

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

		$args = array(
			'posts_per_page' => $this->get_items_per_page( 'transformers_per_page', 20 ),
			'paged'          => isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1, // WPCS: CSRF ok.
			// @codingStandardsIgnoreLine
			'post_status'    => isset( $_GET['status'] ) && in_array( $_GET['status'], array( 'active', 'inactive' ), true ) ? wp_unslash( $_GET['status'] ) : array( 'active', 'inactive' ), // WPCS: CSRF ok, VIP ok.
			'order'          => 'DESC',
			'orderby'        => 'ID',
		);

		// Ordering parameters.
		// @codingStandardsIgnoreLine
		if ( ! empty( $_GET['order'] ) && 'asc' === $_GET['order'] ) {
			$args['order'] = 'asc';
		}

		// Orderby parameters.
		// // @codingStandardsIgnoreStart
		if ( ! empty( $_GET['orderby'] ) && array_key_exists( $_GET['orderby'], $this->get_sortable_columns() ) ) {

			if ( in_array( $_GET['orderby'], array( 'name', 'ID' ), true ) ) {
				$args['orderby'] = $_GET['orderby'];
			} else {
				$args['orderby']  = 'meta_value';
				$args['meta_key'] = '_wpna_transformer_' . $_GET['orderby'];
			}

		}
		// // @codingStandardsIgnoreEnd

		// Search parameters.
		if ( ! empty( $_POST['s'] ) ) {
			$args['s'] = sanitize_text_field( wp_unslash( $_POST['s'] ) );// WPCS: CSRF ok.
		}

		// Get the transformers.
		$transformers = wpna_get_transformers( $args );

		return $transformers;
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
		if ( empty( $_REQUEST['_wpnonce'] ) || ( ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'wpna_delete_transformer' ) && ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'bulk-' . $this->_args['plural'] ) ) ) {
			wp_die( 'Invalid nonce - WPNA Transformer Action' );
		}

		global $wpdb;

		// Ensure it's always an array of ids.
		// @codingStandardsIgnoreLine
		$transformers = (array) $_REQUEST['transformer'];
		// remive any WP added slashes.
		$transformers = wp_unslash( $transformers );
		// Validate they're all positive intergers.
		$transformers = array_map( 'absint', $transformers );

		// Record the rows affected.
		$rows_affected = array();

		switch ( $this->current_action() ) {

			// Delete rows.
			case 'delete':
			case 'bulk-delete':
				// Loop over the array of record IDs and delete them.
				foreach ( $transformers as $id ) {
					$rows_affected[] = wpna_delete_transformer( $id );
				}

				// Clear the cache.
				wpna_clear_transformer_cache();

				wp_safe_redirect(
					esc_url_raw(
						add_query_arg(
							array(
								'page'         => 'wpna_transformers',
								'wpna-message' => 'transformer_delete_success',
							),
							admin_url( '/admin.php' )
						)
					)
				);
				exit;
				// @codingStandardsIgnoreLine
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
				foreach ( $transformers as $id ) {
					$id = wp_update_post(
						array(
							'ID'          => $id,
							'post_status' => $status,
						)
					);
				}

				// Clear the cache.
				wpna_clear_transformer_cache();

				break;

			// No matching action found.
			default:
				wp_die( esc_html__( 'No matching action found - WPNA Transformer', 'wp-native-articles' ) );
				break;
		}

		// Redirect back with message.
		wp_safe_redirect(
			esc_url_raw(
				add_query_arg(
					array(
						'page'         => 'wpna_transformers',
						'wpna-message' => 'transformer_update_success',
					),
					admin_url( '/admin.php' )
				)
			)
		);
		exit;

	}

}
