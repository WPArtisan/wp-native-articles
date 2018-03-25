<?php
/**
 * Transformer class.
 *
 * @package     wp-native-articles
 * @subpackage  Includes/Transformers
 * @copyright   Copyright (c) 2017, WPArtisan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets up transformers integration.
 *
 * Creates a sub menu page. Uses WP List table to output current transformers.
 * Deals with settings.
 *
 * @since 1.3.0
 */
class WPNA_Admin_Transformers extends WPNA_Admin_Base implements WPNA_Admin_Interface {

	/**
	 * The slug of the current page.
	 *
	 * Used for registering menu items and tabs.
	 *
	 * @since 1.2.3
	 * @access public
	 * @var string
	 */
	public $page_slug = 'wpna_transformers';

	/**
	 * Holds an instance of the List Table.
	 *
	 * @var WPNA_Admin_Transformers_List_Table
	 */
	public $transformers_list_table;

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
		add_action( 'wpna_add_transformer', array( $this, 'add_transformer_action' ), 10, 1 );
		add_action( 'wpna_edit_transformer', array( $this, 'edit_transformer_action' ), 10, 1 );
		add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );
	}

	/**
	 * Setup up menu items.
	 *
	 * This adds the sub level menu page for the Transformers page.
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
			esc_html__( 'Transformers', 'wp-native-articles' ),
			esc_html__( 'Transformers', 'wp-native-articles' ),
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
		do_action( 'wpna_admin_transformer_menu_items', $menu_id, $this->page_slug );
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
			add_action( current_filter(), 'wpna_transformers_contextual_help', 10, 0 );
		}

		add_action( current_filter(), array( $this, 'setup_admin_transformers_list_table' ), 10, 0 );
		add_action( current_filter(), array( $this, 'add_screen_options' ), 10, 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 1 );
	}

	/**
	 * Add styles and scripts needed for the Add/Update transformer page.
	 *
	 * @access public
	 * @param string $page_hook Name of the current page hook.
	 * @return void
	 */
	public function enqueue_scripts( $page_hook = '' ) {
		wp_enqueue_script( 'wpna-select2-js', plugins_url( '/assets/js/select2.min.js', WPNA_BASE_FILE ), array(), '4.0.4', true );
		wp_enqueue_script( 'wpna-transformers-js', plugins_url( '/assets/js/transformers.js', WPNA_BASE_FILE ), array( 'wpna-select2-js' ), WPNA_VERSION, true );

		wp_enqueue_style( 'wpna-select2-css', plugins_url( '/assets/css/select2.min.css', WPNA_BASE_FILE ), array(), '4.0.4', 'all' );
	}

	/**
	 * Setup the WPNA_Admin_Transformers_List_Table & process bulk actions.
	 *
	 * The same instance needs to be used in the screen options and to
	 * display the table.
	 *
	 * Process bulk action contains redirects so needs to run early on.
	 *
	 * @return void
	 */
	public function setup_admin_transformers_list_table() {
		// Setup the class instance.
		$this->transformers_list_table = new WPNA_Admin_Transformers_List_Table();
		// Check for bulk action and process.
		$this->transformers_list_table->process_bulk_action();

	}

	/**
	 * Setup screen options for the placements table.
	 *
	 * @access public
	 * @return void
	 */
	public function add_screen_options() {

		$args = array(
			'label'   => esc_html__( 'Transformers', 'wp-native-articles' ),
			'default' => 20,
			'option'  => 'transformers_per_page',
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

		if ( 'transformers_per_page' === $option ) {
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
		if ( isset( $_GET['wpna-action'] ) && 'add_transformer' === $_GET['wpna-action'] ) {
			// Load the Transformer template.
			require WPNA_BASE_PATH . '/includes/transformers/add-transformer.php';
		// @codingStandardsIgnoreLine
		} elseif ( isset( $_GET['wpna-action'] ) && 'edit_transformer' === $_GET['wpna-action'] ) {
			// Load the edit transformer template.
			require WPNA_BASE_PATH . '/includes/transformers/edit-transformer.php';
		} else {
			$this->list_table_output_callback();
		}
	}

	/**
	 * Outputs HTML for Transformers page.
	 *
	 * @since 1.4.2
	 *
	 * @access public
	 * @return void
	 */
	public function list_table_output_callback() {
		?>
		<div class="wrap">
			<h1>
				<?php esc_html_e( 'Transformers', 'wp-native-articles' ); ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'wpna-action' => 'add_transformer' ), admin_url( 'admin.php?page=wpna_transformers' ) ) ); ?>" class="add-new-h2"><?php esc_html_e( 'Add New', 'wp-native-articles' ); ?></a>
			</h1>
			<h4>
				<?php esc_html_e( 'Transformers are an easy way to correctly format troublesome content for Instant Articles.', 'wp-native-articles' ); ?>
			</h4>

			<p>
				<?php esc_html_e( 'Create special rules for any shortcodes or content filters that are not converting correctly.', 'wp-native-articles' ); ?>
				<br />
				<?php esc_html_e( 'Alternatively use the custom selector to target elements by CSS selectors or Xpath.', 'wp-native-articles' ); ?>
			</p>

			<?php do_action( 'wpna_transformers_page_top' ); ?>

			<form id="wpna-transformers-list-table" action="<?php echo esc_url( admin_url( 'admin.php?page=wpna_transformers' ) ); ?>" method="post">
				<input type="hidden" name="page" value="wpna_transformers" />
			<?php
				$this->transformers_list_table->prepare_items();
				$this->transformers_list_table->search_box( esc_html__( 'Search Transformers', 'wp-native-articles' ), 'search_id' );
				$this->transformers_list_table->display();
			?>
			</form>
			<?php do_action( 'wpna_transformers_page_bottom' ); ?>
		</div>
		<?php
	}

	/**
	 * Fired when the add transformer action is triggered.
	 *
	 * Validates and saves a transformer.
	 *
	 * @param array $data Raw, unfiltered POST data.
	 * @return void
	 */
	public function add_transformer_action( $data ) {

		// Only proceed if the form has been submitted.
		if ( ! isset( $data['submit'] ) ) {
			return;
		}

		if ( ! isset( $data['wpna-transformer-nonce'] ) || ! wp_verify_nonce( $data['wpna-transformer-nonce'], 'wpna_transformer_nonce' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to create transformers', 'wp-native-articles' ), esc_html__( 'Error', 'wp-native-articles' ), array( 'response' => 403 ) );
		}

		if ( empty( $data['type'] ) ) {
			wp_safe_redirect( add_query_arg( 'wpna-message', 'transformer_validation_fail' ) );
			die;
		}

		// Try and insert the transformer.
		if ( wpna_add_transformer( $data ) ) {
			wp_safe_redirect( add_query_arg( 'wpna-message', 'transformer_added_success' ) );
			die;
		} else {
			wp_safe_redirect( add_query_arg( 'wpna-message', 'transformer_added_error' ) );
			die;
		}

	}

	/**
	 * Fired when the edit transformer action is triggered.
	 *
	 * Validates and saves a transformer.
	 *
	 * @param array $data Raw, unfiltered POST data.
	 * @return void
	 */
	public function edit_transformer_action( $data ) {

		// Only proceed if the form has been submitted.
		if ( ! isset( $data['submit'] ) ) {
			return;
		}

		if ( ! isset( $data['wpna-transformer-nonce'] ) || ! wp_verify_nonce( $data['wpna-transformer-nonce'], 'wpna_transformer_nonce' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage transformers', 'wp-native-articles' ), esc_html__( 'Error', 'wp-native-articles' ), array( 'response' => 403 ) );
		}

		if ( empty( $data['transformer_id'] ) || empty( $data['type'] ) ) {
			wp_safe_redirect( add_query_arg( 'wpna-message', 'transformer_validation_fail' ) );
			die;
		}

		// Try and insert the transformer.
		if ( wpna_update_transformer( $data['transformer_id'], $data ) ) {
			wp_safe_redirect( add_query_arg( 'wpna-message', 'transformer_update_success' ) );
			die;
		} else {
			wp_safe_redirect( add_query_arg( 'wpna-message', 'transformer_update_error' ) );
			die;
		}

	}

}
