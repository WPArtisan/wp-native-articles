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
	public $page_slug = 'wpna_general';

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
		add_action( 'admin_init',            array( $this, 'welcome_redirect' ), 0, 0 );
		add_action( 'admin_menu',            array( $this, 'add_menu_items' ), 10, 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'styles' ), 10, 1 );
		add_action( 'save_post',             array( $this, 'save_post_meta' ), 10, 3 );
		// After the options are updated flush the permalink rules.
		add_action( 'update_option_wpna_options', 'flush_rewrite_rules', 10, 0 );

		add_filter( 'plugin_action_links_' . plugin_basename( WPNA_BASE_PATH . '/wp-native-articles.php' ), array( $this, 'add_plugin_action_links' ), 10, 1 );

		// These actions are only applied if Instant Articles is enabled.
		if ( wpna_switch_to_boolean( wpna_get_option( 'fbia_enable' ) ) ) {
			add_action( 'load-post.php',         array( $this, 'setup_post_meta_box' ), 10, 0 );
			add_action( 'load-post-new.php',     array( $this, 'setup_post_meta_box' ), 10, 0 );
		}
	}

	/**
	 * When the plugin is updated or installed, check for a redirect.
	 *
	 * @access public
	 * @return void
	 */
	public function welcome_redirect() {
		// Bail if no activation redirect.
		if ( ! get_transient( '_wpna_activation_redirect' ) ) {
			return;
		}

		// Delete the redirect transient.
		delete_transient( '_wpna_activation_redirect' );

		// Bail if activating from network, or bulk.
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) { // WPCS: CSRF ok.
			return;
		}

		$previous_version = get_site_option( 'wpna_previous_db_version' );

		if ( ! $previous_version ) {
			// Flush the rewrite rules to ensure the RSS feed is setup.
			flush_rewrite_rules();
			// First time install.
			wp_safe_redirect( sprintf( admin_url( 'admin.php?page=%s&tab=getting_started' ), $this->page_slug ) );
			exit;
		} elseif ( ! version_compare( $previous_version, WPNA_VERSION, '=' ) ) {
			// Update.
			wp_safe_redirect( sprintf( admin_url( 'admin.php?page=%s&tab=what_is_new' ), $this->page_slug ) );
			exit;
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

		$mylinks   = array();
		$mylinks[] = sprintf( '<a style="color:#d54e21;" href="%s" target="_blank">%s</a>', esc_url( 'https://wp-native-articles.com?utm_source=fplugin&utm_medium=plugin-settings' ), __( 'Upgrade to Premium', 'wp-native-articles' ) );

		$mylinks[] = sprintf( '<a href="%s">%s</a>', esc_url( $settings_page_url ), esc_html__( 'Settings', 'wp-native-articles' ) );

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
			if ( in_array( get_post_type(), wpna_allowed_post_types(), true ) ) {
				wp_enqueue_script( 'wpna-admin-post', plugins_url( '/assets/js/post-meta-box.js', WPNA_BASE_FILE ), array( 'jquery', 'jquery-ui-tabs' ), WPNA_VERSION, true );
			}
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
	 * @param  string $hook Current Page ID.
	 * @return void
	 */
	public function styles( $hook ) {
		// Edit post and New post pages.
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			if ( in_array( get_post_type(), wpna_allowed_post_types(), true ) ) {
				wp_enqueue_style( 'pure', plugins_url( '/assets/css/pure-min.css', WPNA_BASE_FILE ), null, '0.6.1' );
				wp_enqueue_style( 'wpna-admin-post', plugins_url( '/assets/css/post.css', WPNA_BASE_FILE ), null, WPNA_VERSION );
			}
		}

		// Main plugin options page CSS.
		if ( in_array( $hook, array( 'post.php', 'post-new.php', 'toplevel_page_wpna_general', 'toplevel_page_wpna_facebook', 'native-articles_page_wpna_facebook', 'native-articles_page_wpna_placements', 'native-articles_page_wpna_transformers' ), true ) ) {
			wp_enqueue_style( 'wpna-admin', plugins_url( '/assets/css/admin.css', WPNA_BASE_FILE ), null, WPNA_VERSION );
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

		$post_types = wpna_allowed_post_types();

		add_meta_box(
			'wp-native-articles',
			'WP Native Articles',
			array( $this, 'post_meta_box_callback' ),
			$post_types,
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

		<?php if ( ! empty( $tabs ) ) : ?>
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

				<?php wp_nonce_field( 'wpna_save_post_meta-' . get_the_ID(), '_wpna_nonce' ); ?>
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
	 * Save the new post meta field data.
	 *
	 * Creates a unique filter for each value that then uses hooks to provide
	 * sanitization. Values are then stored in the post meta.
	 *
	 * @since 1.3.5
	 *
	 * @access public
	 * @param  int  $post_id The post ID.
	 * @param  post $post    The post object.
	 * @param  bool $update  Whether this is an existing post being updated or not.
	 * @return void
	 */
	public function save_post_meta( $post_id, $post, $update = false ) {

		// Don't save if it's an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Don't save if it's a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Get the nonce.
		$nonce = filter_input( INPUT_POST, '_wpna_nonce', FILTER_SANITIZE_STRING );

		// Verify that the input is coming from the proper form.
		// Since an nonce will only include alpha-numeric characters, we use sanitize_key() to sanitize it.
		// Automatically strips out any quotes or slashes so unslash isn't needed.
		if ( ! $nonce || ! wp_verify_nonce( sanitize_key( $nonce ), 'wpna_save_post_meta-' . $post_id ) ) {
			return;
		}

		// Get the post type.
		$post_type = filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_STRING );

		// Get post types we want to add the box to.
		$allowed_post_types = wpna_allowed_post_types();

		// Check this is a valid post type.
		if ( ! in_array( $post_type, $allowed_post_types, true ) ) {
			return;
		}

		// Make sure the user has permissions to post.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$field_keys = array();

		if ( function_exists( 'apply_filters_deprecated' ) ) {

			// These filters are all deprecated. See 'wpna_post_meta_box_fields'.
			$field_keys = apply_filters_deprecated( 'wpna_post_meta_box_facebook_settings_field_keys', array( $field_keys, $post, $update ), '1.3.5', 'wpna_post_meta_box_fields' );
			$field_keys = apply_filters_deprecated( 'wpna_post_meta_box_facebook_styling_field_keys', array( $field_keys, $post, $update ), '1.3.5', 'wpna_post_meta_box_fields' );
			$field_keys = apply_filters_deprecated( 'wpna_post_meta_box_facebook_custom_content_field_keys', array( $field_keys, $post, $update ), '1.3.5', 'wpna_post_meta_box_fields' );
			$field_keys = apply_filters_deprecated( 'wpna_post_meta_box_facebook_status_field_keys', array( $field_keys, $post, $update ), '1.3.5', 'wpna_post_meta_box_fields' );

		}

		/**
		 * Use this filter to add any custom fields to the data.
		 *
		 * @since 1.3.5
		 *
		 * @var array  The keys to check.
		 * @var object $post       Whether this is an existing post being updated or not.
		 * @var bool   $update     Whether it's an update or new post.
		 */
		$field_keys = apply_filters( 'wpna_post_meta_box_fields', $field_keys, $post, $update );

		// All the options are inside the wpna_options array so let's get that.
		$post_data = filter_input( INPUT_POST, 'wpna_options', FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY );

		// Return all the values from $_POST that have keys in field_keys.
		$values = array_intersect_key( wp_unslash( $post_data ), array_flip( $field_keys ) );

		// Sanitize using the same hook / filter method as the global options.
		// Each key has a unique filter that can be hooked into to validate.
		$sanitized_values = array();

		foreach ( $values as $key => $value ) {

			// If the value is empty then remove any post meta for this key.
			// This means it will inherit the global defaults.
			if ( empty( $value ) ) {
				continue;
			}

			// Workout the correct filtername from the $key.
			$filter_name = 'wpna_sanitize_post_meta_' . $key;

			// Check if a filter exists.
			if ( has_filter( $filter_name ) ) {

				/**
				 * Use filters to allow sanitizing of individual options.
				 *
				 * All sanitization hooks should be registerd in the hooks() method.
				 * This is more for correct sanitization than setting default values.
				 * Use the hooks below for that.
				 *
				 * @since 1.0.0
				 *
				 * @param mixed  $value  The value to sanitize
				 * @param string $key    The options name
				 * @param array  $values All options
				 */
				$sanitized_values[ $key ] = apply_filters( $filter_name, $value, $key, $values );
			} else {
				// If no filter was found then throw an error.
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// @codingStandardsIgnoreLine
					trigger_error( esc_html( sprintf( 'Filter missing for `%s`', $filter_name ) ) );
				}
			}
		}

		// Only save the data that has actually been set.
		// Otherwise we create unnecessary meta rows.
		$sanitized_values = array_filter( $sanitized_values );

		if ( function_exists( 'apply_filters_deprecated' ) ) {

			// These filters are all deprecated. See 'wpna_post_meta_box_fields_pre_save'.
			$sanitized_values = apply_filters_deprecated( 'wpna_sanitize_post_meta_facebook', array( $sanitized_values, $field_keys, $post ), '1.3.5', 'wpna_post_meta_box_fields_pre_save' );
			$sanitized_values = apply_filters_deprecated( 'wpna_sanitize_post_meta_facebook_styling', array( $sanitized_values, $field_keys, $post ), '1.3.5', 'wpna_post_meta_box_fields_pre_save' );
			$sanitized_values = apply_filters_deprecated( 'wpna_sanitize_post_meta_facebook_custom_content', array( $sanitized_values, $field_keys, $post ), '1.3.5', 'wpna_post_meta_box_fields_pre_save' );
			$sanitized_values = apply_filters_deprecated( 'wpna_sanitize_post_meta_facebook', array( $sanitized_values, $field_keys, $post ), '1.3.5', 'wpna_post_meta_box_fields_pre_save' );

		}

		/**
		 * Filter the values before they're saved.
		 *
		 * @since 1.3.5
		 * @var array $sanitized_values Postmeta to save for this post.
		 * @var array $field_keys The fields keys to check for.
		 * @var array $post The post to save the meta to.
		 */
		$sanitized_values = apply_filters( 'wpna_post_meta_box_fields_pre_save', $sanitized_values, $field_keys, $post );

		// Work out which values haven't been set so they can be removed.
		$remove_fields = array_diff( $field_keys, array_keys( $sanitized_values ) );

		// Remove these fields. They will inherit the global values.
		foreach ( $remove_fields as $meta_key ) {
			// Prefix the meta key.
			$meta_key = '_wpna_' . $meta_key;
			// Update/insert the meta row.
			delete_post_meta( $post_id, $meta_key );
		}

		// Save the meta value.
		foreach ( $sanitized_values as $meta_key => $meta_value ) {
			// Prefix the meta key.
			$meta_key = '_wpna_' . $meta_key;
			// Update/insert the meta row.
			update_post_meta( $post_id, $meta_key, $meta_value );
		}
	}

}
