<?php
/**
 * Facebook Admin class.
 *
 * @since  1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends the Admin Base and adds all generic Facebook pages & settings that
 * aren't directly related to the RSS feed or API.
 *
 * @since 1.0.0
 */
class WPNA_Admin_Facebook extends WPNA_Admin_Base implements WPNA_Admin_Interface {

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
	 * An instance of the Helper_Tabs class.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var WPNA_Helper_Tabs
	 */
	public $tabs;

	/**
	 * Hooks registered in this class.
	 *
	 * This method is auto called from WPNA_Admin_Base.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function hooks() {
		add_action( 'admin_init',                 array( $this, 'setup_settings' ), 10, 0 );
		add_action( 'wpna_admin_menu_items',      array( $this, 'add_menu_items' ), 10, 0 );
		add_action( 'save_post',                  array( $this, 'flush_content_cache' ), 10, 1 );
		add_action( 'save_post',                  array( $this, 'save_post_meta' ), 10, 3 );

		// After the Facebook options are updated flush the permalink rules.
		add_action( 'update_option_wpna_options', 'flush_rewrite_rules', 10, 0 );

		// These actions are only applied if Instant Articles is enabled
		if ( wpna_switch_to_boolean( wpna_get_option('fbia_enable') ) ) {
			add_action( 'wp_head', array( $this, 'output_authorisation_id' ), 10, 0 );
		}

		add_filter( 'wpna_post_meta_box_content_tabs', array( $this, 'post_meta_box_facebook_settings' ), 10, 1 );

		// Form sanitization filters
		// No express sanitization for fbia_analytics or fbia_ad_code
		add_filter( 'wpna_sanitize_option-fbia_enable',            'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option-fbia_authorise_id',      'absint', 10, 1 );
		add_filter( 'wpna_sanitize_option-fbia_style',             'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_option-fbia_sponsored',         'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option-fbia_image_likes',       'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option-fbia_image_comments',    'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option-fbia_credits',           'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_option-fbia_copyright',         'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_option-fbia_enable_ads',        'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option-fbia_auto_ad_placement', 'wpna_switchval', 10, 1 );

		// Post meta sanitization filters
		// No express sanitization for fbia_analytics or fbia_ad_code
		add_filter( 'wpna_sanitize_post_meta-fbia_style',             'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta-fbia_sponsored',         'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta-fbia_image_likes',       'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta-fbia_image_comments',    'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta-fbia_credits',           'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta-fbia_copyright',         'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta-fbia_enable_ads',        'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta-fbia_auto_ad_placement', 'wpna_switchval', 10, 1 );

	}

	/**
	 * Setup up menu items.
	 *
	 * This adds the sub level menu page for the Facebook settings page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function add_menu_items() {
		$page_hook = add_submenu_page(
			'wpna_facebook',  // Parent page slug
			esc_html__( 'Facebook Instant Articles', 'wp-native-articles' ),
			esc_html__( 'Facebook Instant Articles', 'wp-native-articles' ),
			'manage_options',
			$this->page_slug,
			array( $this, 'output_callback' )
		);

		add_action( 'load-' . $page_hook, array( $this, 'setup_tabs' ) );
		add_action( 'load-' . $page_hook, array( $this, 'setup_meta_boxes' ) );

		/**
		 * Custom action for adding more menu items.
		 *
		 * @since 1.0.0
		 * @param string $page_hook The Unique hook of the newly registered page
		 */
		do_action( 'wpna_admin_facebook_menu_items', $page_hook );
	}

	/**
	 * Outputs HTML for Facebook admin settings page.
	 *
	 * The Facebook settings page is a tabbed interface. It uses
	 * the WPNA_Helper_Tabs class to setup and register the tabbed interface.
	 * The WPNA_Helper_Tabs class is initiated in the setup_tabs method.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return nul
	 */
	public function output_callback() {
		?>
		<div class="wrap">
			<?php settings_errors(); ?>
			<div id="icon-tools" class="icon32"></div>
			<h1><?php esc_html_e( 'Facebook Instant Articles', 'wp-native-articles' ); ?></h1>
			<div class="wrap">
				<?php $this->tabs->tabs_nav(); ?>
				<?php $this->tabs->tabs_content(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Sets up the tab helper for the Admin Facebook page.
	 *
	 * Creates a new instance of the WPNA_Helper_Tabs class and registers the
	 * first tab, 'General'. Other tabs are added using the
	 * 'wpna_admin_facebook_tabs' action.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function setup_tabs() {
		$this->tabs = new WPNA_Helper_Tabs();

		$this->tabs->register_tab(
			'general',
			esc_html__( 'General', 'wp-native-articles' ),
			$this->page_url(),
			array( $this, 'general_tab_callback' ),
			true
		);

		/**
		 * Called after the first tab has been setup for this page.
		 * Passes the tabs in so it can be modified, other tabs added etc.
		 *
		 * @since 1.0.0
		 * @param WPNA_Helper_Tabs $this->tabs Instance of the tabs helper. Used
		 *                                     to register new tabs.
		 */
		do_action( 'wpna_admin_facebook_tabs', $this->tabs );
	}

	/**
	 * Setup the screen columns.
	 *
	 * Do actions for registering meta boxes for this screen.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function setup_meta_boxes() {
		$screen = get_current_screen();

		/**
		 * Trigger the add_meta_boxes_{$screen_id} hook to allow meta boxes
		 * to be added to this screen.
		 *
		 * @since 1.0.0
		 */
		do_action( 'add_meta_boxes_' . $screen->id );

		/**
		* Trigger the add_meta_boxes hook to allow meta boxes to be added.
		 *
		 * @since 1.0.0
		 * @param string $screen->id The ID of the screen for the admin page.
		 */
		do_action( 'add_meta_boxes', $screen->id );

		// Add screen option: user can choose between 1 or 2 columns (default 2)
		add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );
	}

	/**
	 * Register general Facebook settings.
	 *
	 * Uses the settings API to create and register all the settings fields in
	 * the General tab of the Facebook admin. Uses the global wpna_sanitize_options()
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
	 * @return null
	 */
	public function setup_settings() {

		// Group name. Used for nonces etc
		$option_group = 'wpna_facebook-general';

		register_setting( $option_group, 'wpna_options', 'wpna_sanitize_options' );

		add_settings_section(
			$option_group,
			esc_html__( 'General', 'wp-native-articles' ),
			array( $this, 'facebook_general_callback' ),
			$this->page_slug
		);

		add_settings_field(
			'fbia_enable',
			'<label for="fbia_enable">' . esc_html__( 'Enable', 'wp-native-articles' ) . '</label>',
			array( $this, 'enable_callback' ),
			$this->page_slug,
			$option_group
		);

		add_settings_field(
			'fbia_authorise_id',
			'<label for="fbia_authorise_id">' . esc_html__( 'Authorisation ID', 'wp-native-articles' ) . '</label>',
			array( $this, 'authorise_id_callback' ),
			$this->page_slug,
			$option_group
		);

		add_settings_field(
			'fbia_style',
			'<label for="fbia_style">' . esc_html__( 'Article Style', 'wp-native-articles' ) . '</label>',
			array( $this, 'style_callback' ),
			$this->page_slug,
			$option_group
		);

		add_settings_field(
			'fbia_sponsored',
			'<label for="fbia_sponsored">' . esc_html__( 'Default Article Sponsored', 'wp-native-articles' ) . '</label>',
			array( $this, 'default_sponsored_callback' ),
			$this->page_slug,
			$option_group
		);

		add_settings_field(
			'fbia_image_likes',
			'<label for="fbia_image_likes">' . esc_html__( 'Image Likes', 'wp-native-articles' ) . '</label>',
			array( $this, 'image_likes_callback' ),
			$this->page_slug,
			$option_group
		);

		add_settings_field(
			'fbia_image_comments',
			'<label for="fbia_image_comments">' . esc_html__( 'Image Comments', 'wp-native-articles' ) . '</label>',
			array( $this, 'image_comments_callback' ),
			$this->page_slug,
			$option_group
		);

		add_settings_field(
			'fbia_credits',
			'<label for="fbia_credits">' . esc_html__( 'Default Article Credits', 'wp-native-articles' ) . '</label>',
			array( $this, 'default_credits_callback' ),
			$this->page_slug,
			$option_group
		);

		add_settings_field(
			'fbia_copyright',
			'<label for="fbia_copyright">' . esc_html__( 'Default Article Copyright', 'wp-native-articles' ) . '</label>',
			array( $this, 'default_copyright_callback' ),
			$this->page_slug,
			$option_group
		);

		add_settings_field(
			'fbia_analytics',
			'<label for="fbia_analytics">' . esc_html__( 'Analytics Code', 'wp-native-articles' ) . '</label>',
			array( $this, 'analytics_callback' ),
			$this->page_slug,
			$option_group
		);

		add_settings_field(
			'fbia_enable_ads',
			'<label for="fbia_enable_ads">' . esc_html__( 'Enable Ads', 'wp-native-articles' ) . '</label>',
			array( $this, 'enable_ads_callback' ),
			$this->page_slug,
			$option_group
		);

		add_settings_field(
			'fbia_auto_ad_placement',
			'<label for="fbia_auto_ad_placement">' . esc_html__( 'Auto Place Ads', 'wp-native-articles' ) . '</label>',
			array( $this, 'auto_ad_placement_callback' ),
			$this->page_slug,
			$option_group
		);

		add_settings_field(
			'fbia_ad_code',
			'<label for="fbia_ad_code">' . esc_html__( 'Ad Code', 'wp-native-articles' ) . '</label>',
			array( $this, 'ad_code_callback' ),
			$this->page_slug,
			$option_group
		);

	}

	/**
	 * Output the HTML for the General tab.
	 *
	 * Uses the settings API and outputs the fields registered.
	 * settings_fields() requries the name of the group of settings to ouput.
	 * do_settings_sections() requires the unique page slug for this settings form.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function general_tab_callback() {
		?>
		<form action="options.php" method="post">
			<?php settings_fields( 'wpna_facebook-general' ); ?>
			<?php do_settings_sections( $this->page_slug ); ?>
			<?php submit_button(); ?>
		</form>
		<?php
	}

	/**
	 * Outputs the HTML displayed at the top of the settings section.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function facebook_general_callback() {
		?>
		<p>
			<?php esc_html_e( 'Use this section to set generic Instant Article settings.', 'wp-native-articles' ); ?>
			<?php esc_html_e( 'They can all be overridden on a per article basis with the exception of the `Authorisation ID` field.', 'wp-native-articles' ); ?>
		</p>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_enable' settings field.
	 *
	 * Whether the Facebook Instant Articles feed is enabled or not.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function enable_callback() {
		?>
		<label for="fbia_enable">
			<select name="wpna_options[fbia_enable]" id="fbia-enable">
				<option value="off"<?php selected( wpna_get_option('fbia_enable'), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
				<option value="on"<?php selected( wpna_get_option('fbia_enable'), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
			</select>
			<?php esc_html_e( 'Enable Facebook Instant Articles', 'wp-native-articles' ); ?>
		</label>
		<?php
	}

	 /**
	 * Outputs the HTML for the 'fbia_authorise_id' settings field.
	 *
	 * The authorisation ID from claiming your URL. Outputted in the header.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function authorise_id_callback() {
		?>
		<input type="text" name="wpna_options[fbia_authorise_id]" id="fbia_authorise_id" class="regular-text" value="<?php echo sanitize_text_field( wpna_get_option('fbia_authorise_id') ); ?>">
		<p class="description"><?php esc_html_e( 'The authorisation ID for `Claim Your URL`', 'wp-native-articles' ); ?></p>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_style' settings field.
	 *
	 * Sets the default styling template to use for articles. Can be overridden on a
	 * per article basis.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function style_callback() {
		?>
		<input type="text" name="wpna_options[fbia_style]" id="fbia_style" class="regular-text" value="<?php echo sanitize_text_field( wpna_get_option('fbia_style') ); ?>">
		<p class="description"><?php esc_html_e( 'Default styling template to use', 'wp-native-articles' ); ?></p>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_sponsored' settings field.
	 *
	 * Sets the default copyright to use for each article. Can be overridden on a
	 * per article basis.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function default_sponsored_callback() {
		?>
		<label for="fbia_sponsored">
			<select name="wpna_options[fbia_sponsored]" id="fbia_sponsored">
				<option value="off"<?php selected( wpna_get_option('fbia_sponsored'), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
				<option value="on"<?php selected( wpna_get_option('fbia_sponsored'), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
			</select>
			<p class="description">
				<?php esc_html_e(
					'Make all articles on this site `Sponsored` articles by default.
					Pulls the Facebook profile link from the author page.
					The Facebook profile link is added to the WordPress user profile page by numerous plugins but most noticeably Yoast SEO.',
					'wp-native-articles'
				); ?>
			</p>
		</label>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_image_likes' settings field.
	 *
	 * Auto adds Like option to images. Can be overridden on a
	 * per article basis.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function image_likes_callback() {
		?>
		<label for="fbia_image_likes">
			<select name="wpna_options[fbia_image_likes]" id="fbia-image-likes">
				<option value="off"<?php selected( wpna_get_option('fbia_image_likes'), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
				<option value="on"<?php selected( wpna_get_option('fbia_image_likes'), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
			</select>
			<?php esc_html_e( 'Add Like overlay for every image', 'wp-native-articles' ); ?>
		</label>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_image_comments' settings field.
	 *
	 * Auto adds Comments option to images. Can be overridden on a
	 * per article basis.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function image_comments_callback() {
		?>
		<label for="fbia_image_comments">
			<select name="wpna_options[fbia_image_comments]" id="fbia-image-comments">
				<option value="off"<?php selected( wpna_get_option('fbia_image_comments'), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
				<option value="on"<?php selected( wpna_get_option('fbia_image_comments'), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
			</select>
			<?php esc_html_e( 'Add Comments overlay for every image', 'wp-native-articles' ); ?>
		</label>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_credits' settings field.
	 *
	 * Sets the default credits to use for each article. Can be overridden on a
	 * per article basis.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function default_credits_callback() {
		?>
		<input type="text" name="wpna_options[fbia_credits]" id="fbia_credits" class="regular-text" value="<?php echo sanitize_text_field( wpna_get_option('fbia_credits') ); ?>">
		<p class="description"><?php esc_html_e( 'Default credits applied to the bottom of every article', 'wp-native-articles' ); ?></p>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_copyright' settings field.
	 *
	 * Sets the default copyright to use for each article. Can be overridden on a
	 * per article basis.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function default_copyright_callback() {
		?>
		<input type="text" name="wpna_options[fbia_copyright]" id="fbia_copyright" class="regular-text" value="<?php echo sanitize_text_field( wpna_get_option('fbia_copyright') ); ?>">
		<p class="description"><?php esc_html_e( 'Default copyright applied to the bottom of every article', 'wp-native-articles' ); ?></p>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_analytics' settings field.
	 *
	 * Sets the analytics code to use in each article. Can be overridden on a
	 * per article basis.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function analytics_callback() {
		?>
		<textarea name="wpna_options[fbia_analytics]" rows="10" cols="50"  id="fbia_analytics" class="large-text code"><?php echo esc_textarea( wpna_get_option('fbia_analytics') ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Analytics code to be used in every article. Auto wrapped in an iFrame', 'wp-native-articles' ); ?></p>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_enable_ads' settings field.
	 *
	 * Enables Ads in Facebook instant articles. Can be overridden on a
	 * per article basis.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function enable_ads_callback() {
		?>
		<label for="fbia_enable_ads">
			<select name="wpna_options[fbia_enable_ads]" id="fbia_enable_ads">
				<option value="off"<?php selected( wpna_get_option('fbia_enable_ads'), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
				<option value="on"<?php selected( wpna_get_option('fbia_enable_ads'), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
			</select>
			<?php esc_html_e( 'Enable ads in Instant Articles', 'wp-native-articles' ); ?>
		</label>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_auto_ad_placement' settings field.
	 *
	 * Enables the auto ad placement feature in Facebook. Can be overridden on a
	 * per article basis.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function auto_ad_placement_callback() {
		?>
		<label for="fbia_auto_ad_placement">
			<select name="wpna_options[fbia_auto_ad_placement]" id="fbia_auto_ad_placement">
				<option value="off"<?php selected( wpna_get_option('fbia_auto_ad_placement'), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
				<option value="on"<?php selected( wpna_get_option('fbia_auto_ad_placement'), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
			</select>
			<?php esc_html_e( 'Allow Facebook to auto position your ads in articles', 'wp-native-articles' ); ?>
		</label>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_auto_ad_placement' settings field.
	 *
	 * Sets the default ad code to use for each article. Can be overridden on a
	 * per article basis.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return null
	 */
	public function ad_code_callback() {
		?>
		<textarea name="wpna_options[fbia_ad_code]" rows="10" cols="50" id="fbia_ad_code" class="large-text code"><?php echo esc_textarea( wpna_get_option('fbia_ad_code') ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Ad code for displaying your ads', 'wp-native-articles' ); ?></p>
		<?php
	}

	/**
	 * Register the Facebook settings tab for use in the post meta box.
	 *
	 * Just a filter that enables modification of the $tabs array.
	 * Would be better switched to a function.
	 *
	 * @since 1.0.0
	 * @todo Refactor. Tabs class?
	 *
	 * @access public
	 * @param  array $tabs Existing tabs
	 * @return array
	 */
	public function post_meta_box_facebook_settings( $tabs ) {

		$tabs[] = array(
			'key'      => 'fbia_settings',
			'title'    => esc_html__( 'Settings', 'wp-native-articles' ),
			'callback' => array( $this, 'post_meta_box_facebook_settings_callback' ),
		);

		return $tabs;
	}

	/**
	 * Output HTML for the Facebook settings post meta box tab.
	 *
	 * These values are set per article and override global defaults.
	 * Fields are currently hardcoded. The settings API won't work here.
	 * Fields have the same names as their global variables. This allows for
	 * checking if the global variables has been overridden at an article level
	 * or not.
	 *
	 * @since 1.0.0
	 * @todo Publish button
	 * @todo Swtich to hooks for fields
	 *
	 * @access public
	 * @param  WP_Post $post Global post object
	 * @return null
	 */
	public function post_meta_box_facebook_settings_callback( $post ) {
		?>
		<h3><?php esc_html_e( 'Override Default Values', 'wp-native-articles' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Use these settings to override global values for this post only', 'wp-native-articles' ); ?></p>

		<div class="pure-form pure-form-aligned">

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-style"><?php esc_html_e( 'Override Style Template', 'wp-native-articles' ); ?></label>
					<input type="text" name="fbia_style" id="fbia-style" placeholder="<?php echo esc_attr( wpna_get_option('fbia_style') ); ?>" value="<?php echo sanitize_text_field( get_post_meta( get_the_ID(), 'fbia_style', true ) ); ?>">
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_sponsored"><?php esc_html_e( 'Sponsored Article', 'wp-native-articles' ); ?></label>
					<select name="fbia_sponsored" id="fbia_sponsored">
						<option></option>
						<option value="off"<?php selected( get_post_meta( get_the_ID(), 'fbia_sponsored', true ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
						<option value="on"<?php selected( get_post_meta( get_the_ID(), 'fbia_sponsored', true ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
					</select>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-image-likes"><?php esc_html_e( 'Image Likes', 'wp-native-articles' ); ?></label>
					<select name="fbia_image_likes" id="fbia-image-likes">
						<option></option>
						<option value="off"<?php selected( get_post_meta( get_the_ID(), 'fbia_image_likes', true ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
						<option value="on"<?php selected( get_post_meta( get_the_ID(), 'fbia_image_likes', true ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
					</select>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-image-comments"><?php esc_html_e( 'Image Comments', 'wp-native-articles' ); ?></label>
					<select name="fbia_image_comments" id="fbia-image-comments">
						<option></option>
						<option value="off"<?php selected( get_post_meta( get_the_ID(), 'fbia_image_comments', true ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
						<option value="on"<?php selected( get_post_meta( get_the_ID(), 'fbia_image_comments', true ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
					</select>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-credits"><?php esc_html_e( 'Credits', 'wp-native-articles' ); ?></label>
					<input type="text" name="fbia_credits" id="fbia-credits" placeholder="<?php echo esc_attr( wpna_get_option('fbia_credits') ); ?>" value="<?php echo sanitize_text_field( get_post_meta( get_the_ID(), 'fbia_credits', true ) ); ?>">
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-copyright"><?php esc_html_e( 'Copyright', 'wp-native-articles' ); ?></label>
					<input type="text" name="fbia_copyright" id="fbia-copyright" placeholder="<?php echo esc_attr( wpna_get_option('fbia_copyright') ); ?>" value="<?php echo sanitize_text_field( get_post_meta( get_the_ID(), 'fbia_copyright', true ) ); ?>">
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-analytics"><?php esc_html_e( 'Analytics', 'wp-native-articles' ); ?></label>
					<textarea name="fbia_analytics" rows="6" cols="50" class="code" placeholder="<?php echo esc_attr( wpna_get_option('fbia_analytics') ); ?>"><?php echo esc_textarea( get_post_meta( get_the_ID(), 'fbia_analytics', true ) ); ?></textarea>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-enable-ads-override"><?php esc_html_e( 'Enable Ads', 'wp-native-articles' ); ?></label>
					<select name="fbia_enable_ads" id="fbia-enable-ads">
						<option></option>
						<option value="off"<?php selected( get_post_meta( get_the_ID(), 'fbia_enable_ads', true ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
						<option value="on"<?php selected( get_post_meta( get_the_ID(), 'fbia_enable_ads', true ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
					</select>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-auto-ad-placement"><?php esc_html_e( 'Auto Place Ads', 'wp-native-articles' ); ?></label>
					<select name="fbia_auto_ad_placement" id="fbia-auto-ad-placement">
						<option></option>
						<option value="off"<?php selected( get_post_meta( get_the_ID(), 'fbia_auto_ad_placement', true ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
						<option value="on"<?php selected( get_post_meta( get_the_ID(), 'fbia_auto_ad_placement', true ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
					</select>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-ad-code"><?php esc_html_e( 'Ad Code', 'wp-native-articles' ); ?></label>
					<textarea name="fbia_ad_code" rows="10" cols="50" class="code" placeholder="<?php echo esc_attr( wpna_get_option('fbia_ad_code') ); ?>"><?php echo esc_textarea( get_post_meta( get_the_ID(), 'fbia_ad_code', true ) ); ?></textarea>
				</div>
			</fieldset>

			<?php
			/**
			 * Add extra fields using this action. Or deregister this method
			 * altogether and register your own.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wpna_post_meta_box_facebook_settings_footer' );
			?>

			<?php wp_nonce_field( 'wpna_save_post_meta-' . get_the_ID(), '_wpna_nonce' ); ?>
		</div>

		<?php
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
	 * @param  int    $post_id The post ID.
	 * @param  post   $post    The post object.
	 * @param  bool   $update  Whether this is an existing post being updated or not.
	 * @return null
	 */
	public function save_post_meta( $post_id, $post, $update ) {

		// Don't save if it's an autosave
		if ( wp_is_post_autosave( $post_id ) )
			return;

		// Don't save if it's a revision
		if ( wp_is_post_revision( $post_id ) )
			return;

		// Verify that the input is coming from the proper form
		if ( ! isset( $_POST['_wpna_nonce'] ) || ! wp_verify_nonce( $_POST['_wpna_nonce'], 'wpna_save_post_meta-' . $post_id ) ) {
			return;
		}

		// Make sure the user has permissions to post
		if ( 'post' == $_POST['post_type'] && ! current_user_can( 'edit_post', $post_id ) )
			return;

		// Nothing fancy, let's just buid our own data array
		$field_keys = array(
			'fbia_style',
			'fbia_sponsored',
			'fbia_image_likes',
			'fbia_image_comments',
			'fbia_credits',
			'fbia_copyright',
			'fbia_analytics',
			'fbia_enable_ads',
			'fbia_auto_ad_placement',
			'fbia_ad_code',
		);

		/**
		 * Use this filter to add any custom fields to the data.
		 *
		 * @since 1.0.0
		 *
		 * @var array  $field_keys The keys to check.
		 * @var object $post       Whether this is an existing post being updated or not.
		 * @var bool   $update     Whether it's an update or new post.
		 */
		$field_keys = apply_filters( 'wpna_post_meta_box_facebook_settings_field_keys', $field_keys, $post, $update );

		// Return all the values from $_POST that have keys in field_keys
		$values = array_intersect_key( $_POST, array_flip( $field_keys ) );

		// Sanitize using the same hook / filter method as the global options
		// Each key has a unique filter that can be hooked into to validate
		$sanitized_values = array();

		foreach ( $values as $key => $value ) {

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
			$sanitized_values[ $key ] = apply_filters( 'wpna_sanitize_post_meta-' . $key, $value, $key, $values );

		}

		// Delete any existing rows from the post
		foreach ( $sanitized_values as $key => $value ) {
			delete_post_meta( $post->ID, $key );
		}

		// Only save the data that has actually been set.
		// Otherwise we create unnecessary meta rows
		$sanitized_values = array_filter( $sanitized_values );

		// Save the new meta
		foreach ( $sanitized_values as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

	}

	/**
	 * Flush the content cache.
	 *
	 * Whenever a post is updated we need to flush the posts content flash.
	 *
	 * @access public
	 * @param  int  $post_id
	 * @return null
	 */
	public function flush_content_cache( $post_id ) {
		delete_transient( 'wpna_facebook_post_content_' . $post_id );
	}

	/**
	 * Outputs the FB IA authorisation ID meta tag in the header.
	 *
	 * @access public
	 * @return null
	 */
	public function output_authorisation_id() {
		if ( $value = wpna_get_option('fbia_authorise_id') )
			printf( '<meta property="fb:pages" content="%s" />', esc_attr( $value ) );
	}

}
