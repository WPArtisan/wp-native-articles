<?php
/**
 * Admin class
 *
 * @since 1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

 /**
  * Main admin class for the plugin.
  *
  * Sets up all menus, settings, pages and dashboards.
  *
  * @since  1.0.0
  */
class WPNA_Admin extends WPNA_Admin_Base {

	/**
	 * The slug of the current page.
	 *
	 * Used for registering menu items and tabs.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $page_slug = 'wpna_facebook';

	/**
	 * Hooks registered in this class.
	 *
	 * This method is auto called from WPNA_Admin_Base.
	 *
	 * @since 1.0.0
	 * @todo Change meta box hook
	 *
	 * @access public
	 * @return null
	 */
	public function hooks() {
		add_action( 'admin_menu',            array( $this, 'add_menu_items' ), 10, 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'styles' ), 10, 1 );
		add_action( 'load-post.php',         array( $this, 'setup_post_meta_box' ), 10, 0 );
		add_action( 'load-post-new.php',     array( $this, 'setup_post_meta_box' ), 10, 0 );
	}

	/**
	 * Setup menu items.
	 *
	 * This adds the top level menu page for the plugin.
	 * All plugin sub pages are added using the action provided.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function add_menu_items() {

		// Add the top level page
		$page_slug = add_menu_page(
			esc_html__( 'Native Articles', 'wp-native-articles' ),
			esc_html__( 'Native Articles', 'wp-native-articles' ),
			'manage_options',
			$this->page_slug,
			null,
			'',
			91
		);

		/**
		 * Use this action to add any more menu items to the admin menu
		 *
		 * @since 1.0.0
		 * @param string $page_slug The unique slug for the menu page.
		 */
		do_action( 'wpna_admin_menu_items', $page_slug );
	}

	/**
	 * Load admin JS files.
	 *
	 * Targets the new and edit posts screens and loads in the javascript
	 * required for setting up the meta boxes.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $hook The current page hook
	 * @return null
	 */
	public function scripts( $hook ) {
		// Edit post and New post pages
		if ( in_array( $hook, array( 'post.php', 'post-new.php') ) ) {
			wp_enqueue_script( 'wpna-admin-post', plugins_url( '/assets/js/post-meta-box.js', dirname( __FILE__ ) ), array( 'jquery-ui-tabs' ), WPNA_VERSION, true );
		}
	}

	/**
	 * Load admin CSS files.
	 *
	 * Targets the new and edit posts screens and loads in the CSS
	 * required for setting up the meta boxes. Uses the Pure CSS framework.
	 *
	 * @since 1.0.0
	 * @todo Check for SCRIPT_DEBUG
	 *
	 * @access public
	 * @param  string $hook
	 * @return null
	 */
	public function styles( $hook ) {
		// Edit post and New post pages
		if ( in_array( $hook, array( 'post.php', 'post-new.php') ) ) {
			wp_enqueue_style( 'pure', plugins_url( '/assets/css/pure-min.css', dirname( __FILE__ ) ), '0.6.0', true );
			wp_enqueue_style( 'wpna-admin-post', plugins_url( '/assets/css/post.css', dirname( __FILE__ ) ), '0.0.1' );
		}

	}

	/**
	 * Registers the action to add_meta_boxes.
	 *
	 * This method is called from the load-post.php & load-post-new.php
	 * actions so the meta boxes are only registered on those screens.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function setup_post_meta_box() {
		add_action( 'add_meta_boxes', array( $this, 'add_post_meta_box' ) );
	}

	/**
	 * Adds the post meta box.
	 *
	 * Reigsters the post meta box for the plugin. To add more or alter this
	 * hook into the 'add_meta_boxes' native WP hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function add_post_meta_box() {
		add_meta_box(
			'wp-native-articles',
			esc_html__( 'WP Native Articles', 'wp-native-articles' ),
			array( $this, 'post_meta_box_callback' ),
			'post',
			'advanced',
			'default'
		);
	}

	/**
	 * Outputs the content for the post meta box.
	 *
	 * Although there are header and footer actions the post meta box is largely
	 * a tabbed layout. Any new content should be added in using the post meta box
	 * tabs filter 'wpna_post_meta_box_content_tabs'.
	 *
	 * @since 1.0.0
	 * @todo Change to function?
	 *
	 * @access public
	 * @param  $post  WP_Post The current post object being edited
	 * @return null
	 */
	public function post_meta_box_callback( $post ) {

		/**
		 * Outputs content in the post meta box header.
		 *
		 * @since 1.0.0
		 * @param $post  WP_Post The current post object being edited
		 */
		do_action( 'wpna_post_meta_box_content_header', $post );

		/**
		 * Adds or removes post meta box tabs.
		 *
		 * The post meta box is tabbed in nature. This is achieved simply via
		 * an array of params and a foreach loop. Any tabs and their output
		 * should be registered using this filter and extending the array.
		 * e.g.
		 *    $tabs[] = array(
		 *         'key'      => 'fbia_settings',
		 *         'title'    => esc_html__( 'Settings', 'wp-native-articles' ),
		 *         'callback' => array( $this, 'fbia_settings_post_meta_box_callback' ),
		 *        );
		 *
		 * @since 1.0.0
		 * @param array Empty array of tabs.
		 */
		$tabs = apply_filters( 'wpna_post_meta_box_content_tabs', array() );

		?>

		<?php if ( ! empty( $tabs ) ) :?>
			<div id="wpna-tabs" class="wpna-tabs">

				<ul>
					<?php foreach ( $tabs as $tab ) : ?>
						<li><a href="#<?php echo esc_attr( $tab['key'] ); ?>"><?php echo esc_html( $tab['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>

				<?php foreach ( $tabs as $tab ) : ?>
					<div id="<?php echo esc_attr( $tab['key'] ); ?>">
						<?php call_user_func( $tab['callback'], $post ); ?>
					</div>
				<?php endforeach; ?>

			</div>
		<?php endif; ?>

		<?php
		/**
		 * Outputs content in the post meta box footer.
		 *
		 * @since 1.0.0
		 * @param $post  WP_Post The current post object being edited
		 */
		do_action( 'wpna_post_meta_box_content_footer', $post );
	}

}
