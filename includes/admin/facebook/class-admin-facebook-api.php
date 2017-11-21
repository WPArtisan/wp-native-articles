<?php
/**
 * Facebook api admin class.
 *
 * @since 1.0.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets up the Facebook Instant Article API integration.
 *
 * Registers a settings tab in the admin Facebook IA Page.
 * Registers widgets on the dashboard and post to display stats from the API.
 * Syncs articles between Facebook and WP using the API.
 *
 * @todo Split dashboard & post widgets into seperate classes
 *
 * == FB FLOW ==
 * @todo check FB allowed permissions
 * @todo check got access token
 *
 * @since  1.0.0
 */
class WPNA_Admin_Facebook_API extends WPNA_Admin_Base implements WPNA_Admin_Interface {

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
	 * The Facebook class SDK.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var object
	 */
	public $facebook = null;

	/**
	 * The ID of the authorised Facebook user.
	 *
	 * @since 1.1.6
	 * @access public
	 * @var int|null
	 */
	public $facebook_user_id = null;

	/**
	 * The user permissions to ask for from Facebook.
	 *
	 * @since 1.1.6
	 * @access public
	 * @var array Permissions to ask for.
	 */
	public $permissions_scope = array( 'pages_manage_instant_articles', 'pages_show_list', 'read_insights' );

	/**
	 * Hooks registered in this class.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_init',               array( $this, 'setup_settings' ), 10, 0 );
		add_action( 'wpna_admin_facebook_tabs', array( $this, 'setup_tabs' ), 10, 1 );
		add_action( 'save_post',                array( $this, 'save_post_meta' ), 10, 3 );
		add_action( 'load-native-articles_page_' . $this->page_slug,  array( $this, 'facebook_cb' ), 10, 0 );
		add_action( 'load-native-articles_page_' . $this->page_slug,  array( $this, 'facebook' ), 15, 0 );

		// Form sanitization filters.
		add_filter( 'wpna_sanitize_option_fbia_app_id',        'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_app_secret',    'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_page_id',       'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_sync_articles', 'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_environment',    array( $this, 'sanitize_fbia_enviroment' ), 10, 1 );

		// Post meta sanitization filters.
		add_filter( 'wpna_sanitize_post_meta_fbia_sync_articles', 'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_import_as_drafts', 'sanitize_text_field', 10, 1 );

		// Add tabs to post edit screen.
		add_filter( 'wpna_post_meta_box_content_tabs', array( $this, 'post_meta_box_facebook_status' ), 15, 1 );
	}

	/**
	 * Register the Facebook stats tab for use in the post meta box.
	 *
	 * Just a filter that enables modification of the $tabs array.
	 * Would be better switched to a function.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $tabs Existing tabs.
	 * @return array
	 */
	public function post_meta_box_facebook_status( $tabs ) {

		$tabs[] = array(
			'key'      => 'fbia_status',
			'title'    => esc_html__( 'Status', 'wp-native-articles' ),
			'callback' => array( $this, 'post_meta_box_facebook_status_callback' ),
		);

		return $tabs;
	}

	/**
	 * Output HTML for the Facebook status post meta box tab.
	 *
	 * Just a heading. Stats are loaded in via javascript.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  WP_Post $post The post currently being edited.
	 * @return void
	 */
	public function post_meta_box_facebook_status_callback( $post ) {
		wpna_premium_feature_notice();
		?>
		<h3><?php esc_html_e( 'Instant Article Status', 'wp-native-articles' ); ?></h3>

		<p class="description"><?php esc_html_e( 'Use these settings to override global values for this post only.', 'wp-native-articles' ); ?></p>

		<div class="wpna-form" style="padding-bottom: 15px;">

			<fieldset>
				<div class="wpna-form-control">
					<label for="fbia_sync_articles"><?php esc_html_e( 'Sync Post', 'wp-native-articles' ); ?></label>
					<select name="_wpna_fbia_sync_articles" id="fbia_sync_articles" disabled="disabled">
						<option></option>
						<option value="on"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_sync_articles', true ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
						<option value="off"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_sync_articles', true ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
					</select>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_sync_articles' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="wpna-form-control">
					<label for="fbia_import_as_drafts"><?php esc_html_e( 'Import status', 'wp-native-articles' ); ?></label>
					<select name="_wpna_fbia_import_as_drafts" id="fbia_import_as_drafts" disabled="disabled">
						<option></option>
						<option value="draft"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_import_as_drafts', true ), 'draft' ); ?>><?php esc_html_e( 'Draft', 'wp-native-articles' ); ?></option>
						<option value="publish"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_import_as_drafts', true ), 'publish' ); ?>><?php esc_html_e( 'Publish', 'wp-native-articles' ); ?></option>
					</select>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_import_as_drafts' );
					?>
				</div>
			</fieldset>

			<h4>
				<?php esc_html_e( 'Import Status:', 'wp-native-articles' ); ?>
				<span class="wpna-na">N/A</span>
			</h4>

			<?php
			/**
			 * Add extra fields using this action. Or deregister this method
			 * altogether and register your own.
			 *
			 * @since 1.2.3
			 */
			do_action( 'wpna_post_meta_box_facebook_status_footer' );
			?>

			<?php wp_nonce_field( 'wpna_save_post_meta-' . get_the_ID(), '_wpna_nonce' ); ?>
		</div>

		<?php
	}

	/**
	 * Register Facebook api settings.
	 *
	 * Uses the settings API to create and register all the settings fields in
	 * the API tab of the Facebook admin. Uses the global wpna_sanitize_options()
	 * function to provide validation hooks based on each field name.
	 *
	 * The settings API replaces the entire global settings object with the new
	 * values. wpna_sanitize_options() takes any other fields found in the global
	 * settings array that aren't registered here and merges them in to ensure
	 * they're not lost.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function setup_settings() {
		// Group name. Used for nonces etc.
		$option_group = 'wpna_facebook-api';

		register_setting( $option_group, 'wpna_options', 'wpna_sanitize_options' );

		add_settings_section(
			'wpna_facebook-api_section_1',
			esc_html__( 'Facebook Auth', 'wp-native-articles' ),
			array( $this, 'section_1_callback' ),
			$option_group // This needs to be unique to this tab + Match the one called in do_settings_sections.
		);

		add_settings_field(
			'fbia_app_id',
			'<label for="fbia_app_id">' . esc_html__( 'App ID', 'wp-native-articles' ) . '</label>',
			array( $this, 'app_id_callback' ),
			$option_group,
			'wpna_facebook-api_section_1'
		);

		add_settings_field(
			'fbia_app_secret',
			'<label for="fbia_app_secret">' . esc_html__( 'App Secret', 'wp-native-articles' ) . '</label>',
			array( $this, 'app_secret_callback' ),
			$option_group,
			'wpna_facebook-api_section_1'
		);

		add_settings_field(
			'fbia_fb_user',
			esc_html__( 'Facebook User', 'wp-native-articles' ),
			array( $this, 'fb_user_callback' ),
			$option_group,
			'wpna_facebook-api_section_1'
		);

		add_settings_field(
			'fbia_page_id',
			'<label for="fbia_page_id">' . esc_html__( 'Page ID', 'wp-native-articles' ) . '</label>',
			array( $this, 'page_id_callback' ),
			$option_group,
			'wpna_facebook-api_section_1'
		);

		// DOESN'T SAVE ANYTHING
		// Just using the hook.
		add_settings_field(
			'fbia_status',
			esc_html__( 'Facebook Status', 'wp-native-articles' ),
			array( $this, 'status_callback' ),
			$option_group,
			'wpna_facebook-api_section_1'
		);

		add_settings_field(
			'fbia_sync_articles',
			'<label for="fbia_sync_articles">' . esc_html__( 'Sync Articles', 'wp-native-articles' ) . '</label>',
			array( $this, 'sync_articles_callback' ),
			$option_group,
			'wpna_facebook-api_section_1'
		);

		add_settings_field(
			'fbia_sync_cron',
			'<label for="fbia_sync_cron">' . esc_html__( 'CRON Sync', 'wp-native-articles' ) . '</label>',
			array( $this, 'sync_cron_callback' ),
			$option_group,
			'wpna_facebook-api_section_1'
		);

		add_settings_field(
			'fbia_enviroment',
			'<label for="fbia_enviroment">' . esc_html__( 'Enviroment', 'wp-native-articles' ) . '</label>',
			array( $this, 'enviroment_callback' ),
			$option_group,
			'wpna_facebook-api_section_1'
		);

		add_settings_field(
			'fbia_import_as_drafts',
			'<label for="fbia_import_as_drafts">' . esc_html__( 'Import As Draft', 'wp-native-articles' ) . '</label>',
			array( $this, 'fbia_import_as_drafts_callback' ),
			$option_group,
			'wpna_facebook-api_section_1'
		);

	}

	/**
	 * Registers a tab in the Facebook admin.
	 *
	 * Uses the tabs helper class.
	 *
	 * @access public
	 * @param object $tabs Tabs helper class.
	 * @return void
	 */
	public function setup_tabs( $tabs ) {
		$tabs->register_tab(
			'api',
			esc_html__( 'API', 'wp-native-articles' ),
			$this->page_url(),
			array( $this, 'api_tab_callback' )
		);
	}

	/**
	 * Output the HTML for the API tab.
	 *
	 * Uses the settings API and outputs the fields registered.
	 * settings_fields() requries the name of the group of settings to ouput.
	 * do_settings_sections() requires the unique page slug for this settings form.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function api_tab_callback() {
		?>
		<form action="options.php" method="post">
			<?php settings_fields( 'wpna_facebook-api' ); ?>
			<?php do_settings_sections( 'wpna_facebook-api' ); ?>
			<?php submit_button( null, 'primary', null, null, array( 'disabled' => 'true' ) ); ?>
		</form>
		<?php
	}

	/**
	 * Outputs the HTML displayed at the top of the settings section.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function section_1_callback() {
		wpna_premium_feature_notice();
		?>
		<p>
			<?php esc_html_e( 'These settings apply to the Facebook API.', 'wp-native-articles' ); ?>
			<br />
			<?php esc_html_e( 'Unlike the RSS feed the API is in real time, articles will be updated on Facebook the same time they are updated on WordPress.', 'wp-native-articles' ); ?>
		</p>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_app_id' settings field.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function app_id_callback() {
		?>
		<input type="text" name="wpna_options[fbia_app_id]" id="fbia_app_id" class="regular-text" value="<?php echo esc_attr( wpna_get_option( 'fbia_app_id' ) ); ?>" disabled="disabled" />
		<p class="description"><?php esc_html_e( 'Your Facebook App ID', 'wp-native-articles' ); ?></p>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_app_id' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_app_secret' settings field.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function app_secret_callback() {
		?>
		<input type="text" autocomplete="off" name="wpna_options[fbia_app_secret]" id="fbia_app_secret" class="regular-text" value="<?php echo esc_attr( wpna_get_option( 'fbia_app_secret' ) ); ?>" disabled="disabled"/>
		<p class="description"><?php esc_html_e( 'Your Facebook App Secret', 'wp-native-articles' ); ?></p>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_app_secret' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for connecting to Facebook.
	 *
	 * - If no app ID or App secret is set show and error.
	 * - If a Facebook user has been successfully auth'd shows a logout button and
	 * their profile picture.
	 * - If a Facebook user hasn't been auth'd shows a Login button.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function fb_user_callback() {
			?>
			<a href="#" class="button button-primary" disabled="disabled">
				<?php esc_html_e( 'Login', 'wp-native-articles' ); ?>
			</a>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_page_id' settings field.
	 *
	 * If Facebook has been auth'd shows a dropdown select of all Facebook
	 * pages the authorised user has permissions on. If not shows and error
	 * message.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function page_id_callback() {
		?>
		<p class="description"><?php echo esc_html__( 'No pages found. Please check your permissions.', 'wp-native-articles' ); ?></p>
		<?php
	}

	/**
	 * Output the Facebook connection status.
	 *
	 * Doesn't actually save anything, just outputs the connection status with
	 * a total article count for the selected page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function status_callback() {
		?>
		<p>
			<span class="wpna-label wpna-label-danger">
				<?php esc_html_e( 'Disconnected', 'wp-native-articles' ); ?>
			</span>
		</p>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_sync_articles' settings field.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function sync_articles_callback() {
		?>
		<label for="fbia_sync_articles">
			<select name="wpna_options[fbia_sync_articles]" id="fbia-sync-articles" disabled="disabled">
				<option value="off"<?php selected( wpna_get_option( 'fbia_sync_articles' ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
				<option value="on"<?php selected( wpna_get_option( 'fbia_sync_articles' ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
			</select>
			<?php esc_html_e( 'Auto publish, update & delete Instant Articles in sync with WordPress posts', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_sync_articles' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_sync_cron' settings field.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 * @return void
	 */
	public function sync_cron_callback() {
		?>
		<label for="fbia_sync_cron">
			<input type="hidden" name="wpna_options[fbia_sync_cron]" value="0">
			<input type="checkbox" name="wpna_options[fbia_sync_cron]" id="fbia_sync_cron" class="" value="true"<?php checked( (bool) wpna_get_option( 'fbia_sync_cron' ) ); ?>  disabled="disabled" />
			<?php esc_html_e( 'Use background CRON to sync posts. Can speed up post saving time.', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_sync_cron' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_enviroment' settings field.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function enviroment_callback() {
		?>
		<label for="fbia_enviroment">
			<select name="wpna_options[fbia_enviroment]" id="fbia_enviroment" disabled="disabled">
				<option value="development"<?php selected( wpna_get_option( 'fbia_enviroment' ), 'development' ); ?>><?php esc_html_e( 'Development', 'wp-native-articles' ); ?></option>
				<option value="production"<?php selected( wpna_get_option( 'fbia_enviroment' ), 'production' ); ?>><?php esc_html_e( 'Production', 'wp-native-articles' ); ?></option>
			</select>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_enviroment' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_import_as_drafts' settings field.
	 *
	 * @since 1.2.3
	 *
	 * @access public
	 * @return void
	 */
	public function fbia_import_as_drafts_callback() {
		?>
		<label for="fbia_import_as_drafts">
			<input type="hidden" name="wpna_options[fbia_import_as_drafts]" value="0">
			<input type="checkbox" name="wpna_options[fbia_import_as_drafts]" id="fbia_import_as_drafts" class="" value="true"<?php checked( (bool) wpna_get_option( 'fbia_import_as_drafts' ) ); ?> disabled="disabled" />
			<?php esc_html_e( 'Import all articles as drafts. Articles can then published individually.', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_import_as_drafts' );
		?>

		<?php
	}

	/**
	 * Sanitizes the environment variable.
	 *
	 * A custom validation method for the environment field.
	 * Ensures it matches either 'production' or 'development'.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $input The input string to sanitize.
	 * @return string
	 */
	public function sanitize_fbia_environment( $input ) {
		return 'production' === $input ? 'production' : 'development';
	}

	/**
	 * Save the new post meta field data.
	 *
	 * Creates a unique filter for each value that then uses hooks to provide
	 * sanitization. Values are then stored in the post meta.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  int  $post_id The post ID.
	 * @param  post $post    The post object.
	 * @param  bool $update  Whether this is an existing post being updated or not.
	 * @return void
	 */
	public function save_post_meta( $post_id, $post, $update ) {

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

		// Nothing fancy, let's just build our own data array.
		$field_keys = array(
			'_wpna_fbia_sync_articles',
			'_wpna_fbia_import_as_drafts',
		);

		/**
		 * Use this filter to add any custom fields to the data.
		 *
		 * @since 1.2.3
		 *
		 * @var array  $field_keys The keys to check.
		 * @var object $post       Whether this is an existing post being updated or not.
		 * @var bool   $update     Whether it's an update or new post.
		 */
		$field_keys = apply_filters( 'wpna_post_meta_box_facebook_status_field_keys', $field_keys, $post, $update );

		// Return all the values from $_POST that have keys in field_keys.
		$values = array_intersect_key( wp_unslash( $_POST ), array_flip( $field_keys ) ); // Input var okay.

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
			$filter_name = str_replace( '_wpna_', 'wpna_sanitize_post_meta_', $key );

			// Check if a filter exists.
			if ( has_filter( $filter_name ) ) {

				/**
				 * Use filters to allow sanitizing of individual options.
				 *
				 * All sanitization hooks should be registerd in the hooks() method.
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

		/**
		 * Filter the values before they're saved.
		 *
		 * @since 1.1.4
		 * @var array Postmeta to save for this post.
		 */
		$sanitized_values = apply_filters( 'wpna_sanitize_post_meta_facebook', $sanitized_values, $field_keys, $post );

		// Work out which valeus haven't been set so they can be removed.
		$remove_fields = array_diff( $field_keys, array_keys( $sanitized_values ) );

		// Remove these fields. They will inherit the global values.
		foreach ( $remove_fields as $meta_key ) {
			delete_post_meta( $post_id, $meta_key );
		}

		// Save the new meta.
		foreach ( $sanitized_values as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

	}

}
