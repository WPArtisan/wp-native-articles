<?php
/**
 * Admin class
 *
 * @since 1.0.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	public $page_slug;

	/**
	 * Hooks registered in this class.
	 *
	 * This method is auto called from WPNA_Admin_Base.
	 *
	 * @since 1.0.0
	 * @todo Change meta box hook
	 *
	 * @access public
	 * @return void
	 */
	public function hooks() {
		// Done like this so the source parser can compile the seperate versions.
		$this->page_slug = 'wpna_facebook';

		add_action( 'admin_menu',            array( $this, 'add_menu_items' ), 10, 0 );

		add_filter( 'plugin_action_links_' . plugin_basename( WPNA_BASE_PATH . '/wp-native-articles.php' ), array( $this, 'add_plugin_action_links' ), 10, 1 );

		// These actions are only applied if Instant Articles is enabled.
		if ( wpna_switch_to_boolean( wpna_get_option( 'fbia_enable' ) ) ) {
			add_action( 'admin_init',            array( $this, 'rating_notice' ), 10, 0 );
			add_action( 'wp_ajax_wpna-dismiss-notice', array( $this, 'ajax_dismiss_notice' ), 10, 0 );
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ), 10, 1 );
			add_action( 'admin_enqueue_scripts', array( $this, 'styles' ), 10, 1 );
			add_action( 'load-post.php',         array( $this, 'setup_post_meta_box' ), 10, 0 );
			add_action( 'load-post-new.php',     array( $this, 'setup_post_meta_box' ), 10, 0 );
		}
	}

	/**
	 * Add extra links to the plugin action links.
	 *
	 * Adds a quick link to the settings page + a premium upgrade link
	 * on the free version.
	 *
	 * @access public
	 * @param array $links The current links for the plugin.
	 * @return array
	 */
	public function add_plugin_action_links( $links ) {
		if ( ! is_array( $links ) ) {
			$links = array( $links );
		}

		// Construct the settings page link.
		$settings_page_url = add_query_arg( array(
			'page' => 'wpna_facebook',
		), admin_url( 'admin.php' ) );

		$mylinks = array();
		$mylinks[] = sprintf( '<a style="color:#d54e21;" href="%s" target="_blank">%s</a>', esc_url( 'https://wp-native-articles.com?utm_source=fplugin&utm_medium=plugin-settings' ), __( 'Upgrade to Premium', 'wp-native-articles' ) );

		$mylinks[] = sprintf( '<a href="%s">%s</a>', esc_url( $settings_page_url ), __( 'Settings', 'wp-native-articles' ) );

		// Merge the arrays together and return.
		return array_merge( $mylinks, $links );
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
	 * @return void
	 */
	public function add_menu_items() {

		// Add the top level page.
		$page_id = add_menu_page(
			esc_html__( 'Native Articles', 'wp-native-articles' ),
			esc_html__( 'Native Articles', 'wp-native-articles' ),
			'manage_options',
			$this->page_slug,
			null,
			'dashicons-palmtree',
			91
		);

		/**
		 * Use this action to add any more menu items to the admin menu
		 *
		 * @since 1.0.0
		 * @param string $page_id  The unique ID for the menu page.
		 * @param string $page_slug The unique slug for the menu page.
		 */
		do_action( 'wpna_admin_menu_items', $page_id, $this->page_slug );
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
	 * @param  string $hook The current page hook.
	 * @return void
	 */
	public function scripts( $hook ) {
		// Edit post and New post pages.
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			wp_enqueue_script( 'wpna-admin-post', plugins_url( '/assets/js/post-meta-box.js', WPNA_BASE_FILE ), array( 'jquery', 'jquery-ui-tabs' ), WPNA_VERSION, true );
		}

		// Dismissible JS. Create Nonce.
		wp_enqueue_script( 'wpna-notices', plugins_url( '/assets/js/notices.js', WPNA_BASE_FILE ), array( 'jquery', 'wp-util' ), WPNA_VERSION, true );
		wp_localize_script( 'wpna-notices', 'wpnaNotices', array(
			'nonce' => wp_create_nonce( 'wpna_notices_ajax_nonce' ),
		));
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
	 * @param  string $hook Current Page ID.
	 * @return void
	 */
	public function styles( $hook ) {
		// Edit post and New post pages.
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			wp_enqueue_style( 'pure', plugins_url( '/assets/css/pure-min.css', WPNA_BASE_FILE ), '0.6.1', true );
			wp_enqueue_style( 'wpna-admin-post', plugins_url( '/assets/css/post.css', WPNA_BASE_FILE ), WPNA_VERSION );
		}

		// Main plugin options page CSS.
		if ( in_array( $hook, array( 'post.php', 'post-new.php', 'native-articles_page_wpna_facebook', 'toplevel_page_wpna_facebook' ), true ) ) {
			wp_enqueue_style( 'wpna-admin', plugins_url( '/assets/css/admin.css', WPNA_BASE_FILE ), WPNA_VERSION );
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
	 * @return void
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
	 * @return void
	 */
	public function add_post_meta_box() {
		add_meta_box(
			'wp-native-articles',
			'WP Native Articles',
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
	 * @param  WP_Post $post The current post object being edited.
	 * @return void
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

	/**
	 * Whether to display the rating notice prompts.
	 *
	 * Compares the activation time to the prompt intervals aon whether to
	 * show a rating prompt notice or not.
	 *
	 * @since 1.0.3
	 *
	 * @access public
	 * @return void
	 */
	public function rating_notice() {

		// We only want admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$activation_time = get_site_option( 'wpna_activation_time' );
		$prompts = (array) get_site_option( 'wpna_rating_prompts' );

		// Sort the prompts to ensure they're in order.
		sort( $prompts, SORT_NUMERIC );

		// If any of the prompts are within the activation time then show the admin message.
		foreach ( $prompts as $prompt ) {
			if ( strtotime( $activation_time ) < strtotime( "-{$prompt} days" ) ) {
				add_action( 'admin_notices', array( $this, 'rating_notice_callback' ), 10, 0 );
				break;
			}
		}
	}

	/**
	 * Outputs the HTML for the rating notice admin prompt.
	 *
	 * We bug admins at certain intervals to rate the plugin.
	 *
	 * @since 1.0.3
	 *
	 * @access public
	 * @return void
	 */
	public function rating_notice_callback() {
		?>
			<div class="wpna-notice notice notice-info is-dismissible">
				<p><?php esc_html_e( "Hey, we noticed you've been using WP Native Articles for a little while now – that’s brilliant! Could you please do me a BIG favor and give it a 5-star rating on WordPress? It really helps us spread the word and boosts our motivation.", 'wp-native-articles' ); ?></p>
				<p>- Edward</p>
				<p><a href="https://wordpress.org/support/plugin/wp-native-articles/reviews/" target="_blank"><?php esc_html_e( 'Sure, you deserve it', 'wp-native-articles' ); ?></a></p>
				<p><a href="#" class="wpna-dismiss" data-notice="rating-permanent"><?php esc_html_e( 'I already have', 'wp-native-articles' ); ?></a></p>
				<p><a href="#" class="wpna-dismiss" data-notice="rating-temporary"><?php esc_html_e( 'Nope, not right now', 'wp-native-articles' ); ?></a></p>
			</div>

		<?php
	}

	/**
	 * Dismisses admin notices.
	 *
	 * Ajax end point for dealing with notice dismissal. Ensure the notice has a
	 * custom name then deal with it in the switch statment.
	 *
	 * @since 1.0.3
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_dismiss_notice() {

		// Check it's an AJAX request.
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			wp_die();
		}

		// Check the nonce is valid.
		check_ajax_referer( 'wpna_notices_ajax_nonce' );

		// We only want admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$notice = filter_input( INPUT_POST, 'notice', FILTER_SANITIZE_STRING );

		// If the notice isn't set then do nothing.
		if ( ! $notice ) {
			wp_die();
		}

		switch ( $notice ) {

			// They've already rated the app, kill all rating prompts.
			case 'rating-permanent':
				delete_site_option( 'wpna_rating_prompts' );
			break;

			// They don't want to be bugged anymore at the moment.
			// Remove the current interval prompt.
			case 'rating-temporary':
				$prompts = (array) get_site_option( 'wpna_rating_prompts' );

				// 1 or fewer intervals and just remove the whole option.
				if ( count( $prompts ) <= 1 ) {
					delete_site_option( 'wpna_rating_prompts' );

				} else {
					// Sort the array and remove the lowest interval.
					sort( $prompts, SORT_NUMERIC );
					array_shift( $prompts );
					update_site_option( 'wpna_rating_prompts', $prompts );
				}

			break;

			default:
				// Notice not found, do nothing.
			break;
		}

		// Kill the response properly.
		wp_die();

	}

}
