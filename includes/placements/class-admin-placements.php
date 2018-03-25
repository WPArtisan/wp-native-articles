<?php
/**
 * Placements class.
 *
 * @package     wp-native-articles
 * @subpackage  Includes/Placements
 * @copyright   Copyright (c) 2017, WPArtisan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets up placements integration.
 *
 * Creates a sub menu page. Uses WP List table to output current placements.
 * Deals with settings.
 *
 * @since 1.3.0
 */
class WPNA_Admin_Placements extends WPNA_Admin_Base implements WPNA_Admin_Interface {

	/**
	 * The slug of the current page.
	 *
	 * Used for registering menu items and tabs.
	 *
	 * @since 1.2.3
	 * @access public
	 * @var string
	 */
	public $page_slug = 'wpna_placements';

	/**
	 * Holds an instance of the List Table.
	 *
	 * @var WPNA_Admin_Placements_List_Table
	 */
	public $placements_list_table;

	/**
	 * Hooks registered in this class.
	 *
	 * This method is auto called from WPNA_Admin_Base.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 * @return void
	 */
	public function hooks() {
		add_action( 'wpna_admin_menu_items', array( $this, 'add_menu_items' ), 14, 2 );
		add_action( 'wpna_add_placement', array( $this, 'add_placement_action' ), 10, 1 );
		add_action( 'wpna_edit_placement', array( $this, 'edit_placement_action' ), 10, 1 );
		add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );
	}

	/**
	 * Setup up menu items.
	 *
	 * This adds the sub level menu page for the Placements page.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 * @param string $parent_page_id   The unique id of the parent page.
	 * @param string $parent_page_slug The unique slug of the parent page.
	 * @return void
	 */
	public function add_menu_items( $parent_page_id, $parent_page_slug ) {
		$menu_id = add_submenu_page(
			$parent_page_slug, // Parent page slug.
			esc_html__( 'Placements', 'wp-native-articles' ),
			esc_html__( 'Placements', 'wp-native-articles' ),
			'manage_options',
			$this->page_slug,
			array( $this, 'route_output' )
		);

		add_action( "load-{$menu_id}", array( $this, 'page_hooks' ), 1, 0 );

		/**
		 * Custom action for adding more menu items.
		 *
		 * @since 1.3.0
		 * @param string $menu_id The unique ID for the menu page.
		 * @param string $page_slug The unique slug for the menu page.
		 */
		do_action( 'wpna_admin_placements_menu_items', $menu_id, $this->page_slug );
	}

	/**
	 * Add page specific hooks based of the page screen id.
	 *
	 * The advantage of this is we never have to hard code the $page_hook anywhere.
	 *
	 * @access public
	 * @return void
	 */
	public function page_hooks() {
		// @todo Add better help.
		if ( 1 === 2 ) {
			add_action( "load-{$menu_id}", 'wpna_placements_contextual_help', 10, 0 );
		}

		// Only run these on the main page.
		// @codingStandardsIgnoreLine
		if ( empty( $_GET['wpna-action'] ) ) {
			add_action( current_filter(), array( $this, 'setup_admin_placements_list_table' ), 10, 0 );
			add_action( current_filter(), array( $this, 'setup_meta_boxes' ), 10, 0 );
			add_action( current_filter(), array( $this, 'add_screen_options' ), 10, 0 );
		} else {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 0 );
		}
	}

	/**
	 * Add styles and scripts needed for the Add/Update placement page.
	 *
	 * @access public
	 * @param string $page_hook Name of the current page hook.
	 * @return void
	 */
	public function enqueue_scripts( $page_hook = '' ) {
		wp_enqueue_script( 'wpna-select2-js', plugins_url( '/assets/js/select2.min.js', WPNA_BASE_FILE ), array(), '4.0.4', true );
		wp_enqueue_script( 'wpna-placements-js', plugins_url( '/assets/js/placements.js', WPNA_BASE_FILE ), array( 'wpna-select2-js' ), WPNA_VERSION, true );

		wp_enqueue_style( 'wpna-select2-css', plugins_url( '/assets/css/select2.min.css', WPNA_BASE_FILE ), array(), '4.0.4', 'all' );
	}

	/**
	 * Setup the WPNA_Admin_Placements_List_Table & process bulk actions.
	 *
	 * The same instance needs to be used in the screen options and to
	 * display the table.
	 *
	 * Process bulk action contains redirects so needs to run early on.
	 *
	 * @return void
	 */
	public function setup_admin_placements_list_table() {
		// Setup the class instance.
		$this->placements_list_table = new WPNA_Admin_Placements_List_Table();
		// Check for bulk action and process.
		$this->placements_list_table->process_bulk_action();
	}

	/**
	 * Setup screen options for the placements table.
	 *
	 * @access public
	 * @return void
	 */
	public function add_screen_options() {

		$args = array(
			'label'   => esc_html__( 'Placements', 'wp-native-articles' ),
			'default' => 20,
			'option'  => 'placements_per_page',
		);

		add_screen_option( 'per_page', $args );
	}

	/**
	 * Sets the value for the screen option when retrieved.
	 *
	 * @param string $status Screen option.
	 * @param string $option Name of the screen option to get the value for.
	 * @param int    $value Stored value for the screen option.
	 * @return int The value to set.
	 */
	public function set_screen_option( $status, $option, $value ) {

		if ( 'placements_per_page' === $option ) {
			return $value;
		}

		return $status;
	}

	/**
	 * Workout what template to load.
	 *
	 * @access public
	 * @return void
	 */
	public function route_output() {

		// @codingStandardsIgnoreLine
		if ( isset( $_GET['wpna-action'] ) && 'add_placement' === $_GET['wpna-action'] ) {
			// Load the add placement template.
			require WPNA_BASE_PATH . '/includes/placements/add-placement.php';

		// @codingStandardsIgnoreLine
		} elseif ( isset( $_GET['wpna-action'] ) && 'edit_placement' === $_GET['wpna-action'] ) {
			// Load the edit placement template.
			require WPNA_BASE_PATH . '/includes/placements/edit-placement__isPro.php';
		} else {
			$this->list_table_output_callback();
		}
	}

	/**
	 * Outputs HTML for Placements page.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 * @return void
	 */
	public function list_table_output_callback() {
		?>
		<div class="wrap">
			<h1>
				<?php esc_html_e( 'Placements', 'wp-native-articles' ); ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'wpna-action' => 'add_placement' ), admin_url( 'admin.php?page=wpna_placements' ) ) ); ?>" class="add-new-h2"><?php esc_html_e( 'Add New', 'wp-native-articles' ); ?></a>
			</h1>
			<?php

				wpna_premium_feature_notice();

			?>
			<h4>
				<?php esc_html_e( 'Placements are an easy way to add extra code to your Instant Articles.', 'wp-native-articles' ); ?>
			</h4>

			<p>
				<?php esc_html_e( 'Add analytics, ad code, inline related articles, videos, or any custom content you wish to your Instant Articles using Placements.', 'wp-native-articles' ); ?>
				<br />
				<?php esc_html_e( 'They can be inserted into to all your Instant Articles or specific ones by category, tag, author or using a custom query.', 'wp-native-articles' ); ?>
			</p>

			<?php do_action( 'wpna_placements_page_top' ); ?>

			<form id="wpna-placements-list-table" action="<?php echo esc_url( admin_url( 'admin.php?page=wpna_placements' ) ); ?>" method="post">
				<input type="hidden" name="page" value="wpna_placements" />
			<?php
				$this->placements_list_table->prepare_items();
				$this->placements_list_table->search_box( esc_html__( 'Search Placements', 'wp-native-articles' ), 'search_id' );
				$this->placements_list_table->display();
			?>
			</form>
			<?php do_action( 'wpna_placements_page_bottom' ); ?>
		</div>
		<?php
	}

	/**
	 * Setup the screen columns.
	 *
	 * Do actions for registering meta boxes for this screen.
	 *
	 * @access public
	 * @return void
	 */
	public function setup_meta_boxes() {
		$screen = get_current_screen();

		/**
		 * Trigger the add_meta_boxes hook to allow meta boxes to be added.
		 *
		 * @since 1.0.0
		 * @param string $screen->id The ID of the screen for the admin page.
		 * @param null For compatibility.
		 */
		do_action( 'add_meta_boxes_' . $screen->id, null );

		/**
		* Trigger the add_meta_boxes hook to allow meta boxes to be added.
		 *
		 * @since 1.0.0
		 * @param string $screen->id The ID of the screen for the admin page.
		 * @param null For compatibility.
		 */
		do_action( 'add_meta_boxes', $screen->id, null );

		// Add screen option: user can choose between 1 or 2 columns (default 2).
		add_screen_option( 'layout_columns',
			array(
				'max'     => 2,
				'default' => 2,
			)
		);
	}

	/**
	 * Fired when the add placement action is triggered.
	 *
	 * Validates and saves a placement.
	 *
	 * @param array $data Raw, unfiltered POST data.
	 * @return void
	 */
	public function add_placement_action( $data ) {

		// Only proceed if the form has been submitted.
		if ( ! isset( $data['submit'] ) ) {
			return;
		}

		if ( ! isset( $data['wpna-placement-nonce'] ) || ! wp_verify_nonce( $data['wpna-placement-nonce'], 'wpna_placement_nonce' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to create placements', 'wp-native-articles' ), esc_html__( 'Error', 'wp-native-articles' ), array( 'response' => 403 ) );
		}

		if ( empty( $data['name'] ) ) {
			wp_safe_redirect( add_query_arg( 'wpna-message', 'placement_validation_fail' ) );
			die;
		}

		// Try and insert the placement.
		if ( wpna_add_placement( $data ) ) {
			wp_safe_redirect( add_query_arg( 'wpna-message', 'placement_added_success' ) );
			die;
		} else {
			wp_safe_redirect( add_query_arg( 'wpna-message', 'placement_added_error' ) );
			die;
		}

	}

	/**
	 * Fired when the edit placement action is triggered.
	 *
	 * Validates and saves a placement.
	 *
	 * @param array $data Raw, unfiltered POST data.
	 * @return void
	 */
	public function edit_placement_action( $data ) {

		// Only proceed if the form has been submitted.
		if ( ! isset( $data['submit'] ) ) {
			return;
		}

		if ( ! isset( $data['wpna-placement-nonce'] ) || ! wp_verify_nonce( $data['wpna-placement-nonce'], 'wpna_placement_nonce' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage placements', 'wp-native-articles' ), esc_html__( 'Error', 'wp-native-articles' ), array( 'response' => 403 ) );
		}

		if ( empty( $data['placement_id'] ) || empty( $data['name'] ) ) {
			wp_safe_redirect( add_query_arg( 'wpna-message', 'placement_validation_fail' ) );
			die;
		}

		// Try and insert the placement.
		if ( wpna_update_placement( $data['placement_id'], $data ) ) {
			wp_safe_redirect( add_query_arg( 'wpna-message', 'placement_update_success' ) );
			die;
		} else {
			wp_safe_redirect( add_query_arg( 'wpna-message', 'placement_update_error' ) );
			die;
		}

	}

}
