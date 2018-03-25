<?php
/**
 * Facebook Admin class.
 *
 * @since  1.0.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	 * Name used for the FBIA status input.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var string
	 */
	public $post_status_input_name = 'fbia_status';

	/**
	 * Hooks registered in this class.
	 *
	 * This method is auto called from WPNA_Admin_Base.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_init',            array( $this, 'setup_settings' ), 10, 0 );
		add_action( 'wpna_admin_menu_items', array( $this, 'add_menu_items' ), 10, 2 );
		add_action( 'save_post',             array( $this, 'flush_content_cache' ), 10, 1 );

		// These actions are only applied if Instant Articles is enabled.
		if ( wpna_switch_to_boolean( wpna_get_option( 'fbia_enable' ) ) ) {
			// Register post meta fields.
			add_action( 'init', array( $this, 'register_post_meta' ), 10, 0 );
			// Output the Instant Articles authorisation ID.
			add_action( 'wp_head', array( $this, 'output_authorisation_id' ), 10, 0 );
		}

		add_filter( 'wpna_post_meta_box_content_tabs', array( $this, 'post_meta_box_facebook_settings' ), 10, 1 );
		add_filter( 'wpna_post_meta_box_fields', array( $this, 'post_meta_box_fields' ), 10, 1 );

		// Form sanitization filters.
		// No express sanitization for fbia_analytics or fbia_ad_code.
		add_filter( 'wpna_sanitize_option_fbia_enable',               'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_content_parser',       'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_authorise_id',         'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_style',                'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_rtl',                  'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_sponsored',            'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_image_likes',          'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_image_comments',       'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_credits',              'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_copyright',            'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_enable_ads',           'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_auto_ad_placement',    'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_ad_density',           'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_ad_code_type',         'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_ad_code_placement_id', 'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_option_fbia_recirculation_ad',     'sanitize_text_field', 10, 1 );

		// Post meta sanitization filters.
		// No express sanitization for fbia_analytics or fbia_ad_code.
		// Post meta sanitization filters.
		add_filter( 'wpna_sanitize_post_meta_fbia_status',               'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_style',                'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_rtl',                  'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_sponsored',            'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_image_likes',          'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_image_comments',       'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_credits',              'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_copyright',            'sanitize_text_field', 10, 1 );
		// Stops missing validation function warning.
		add_filter( 'wpna_sanitize_post_meta_fbia_analytics',            'trim', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_enable_ads',           'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_auto_ad_placement',    'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_ad_density',           'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_ad_code_type',         'sanitize_text_field', 10, 1 );
		// Stops missing validation function warning.
		add_filter( 'wpna_sanitize_post_meta_fbia_ad_code',              'trim', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_ad_code_placement_id', 'sanitize_text_field', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_recirculation_ad',     'sanitize_text_field', 10, 1 );
	}

	/**
	 * These actions only fire on this page.
	 *
	 * @since 1.2.6
	 * @access public
	 * @return void
	 */
	public function page_hooks() {
		add_action( current_filter(), array( $this, 'setup_tabs' ), 11, 0 );
		add_action( current_filter(), array( $this, 'setup_meta_boxes' ), 11, 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 0 );
	}

	/**
	 * Enqueue scritps.
	 *
	 * Only loaded on this admin page.
	 *
	 * @access public
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'wpna-admin-fbia', plugins_url( '/assets/js/admin-fbia.js', WPNA_BASE_FILE ), array( 'jquery' ), WPNA_VERSION, true );
	}

	/**
	 * Setup up menu items.
	 *
	 * This adds the sub level menu page for the Facebook settings page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $parent_page_id   The unique id of the parent page.
	 * @param string $parent_page_slug The unique slug of the parent page.
	 * @return void
	 */
	public function add_menu_items( $parent_page_id, $parent_page_slug ) {
		$page_hook = add_submenu_page(
			$parent_page_slug,  // Parent page slug.
			esc_html__( 'Facebook Instant Articles', 'wp-native-articles' ),
			esc_html__( 'Facebook Instant Articles', 'wp-native-articles' ),
			'manage_options',
			$this->page_slug,
			array( $this, 'output_callback' )
		);

		// Load actions that should only fire on this page.
		add_action( 'load-' . $page_hook, array( $this, 'page_hooks' ) );

		/**
		 * Custom action for adding more menu items.
		 *
		 * @since 1.0.0
		 * @param string $page_hook The unique ID for the menu page.
		 * @param string $page_slug The unique slug for the menu page.
		 */
		do_action( 'wpna_admin_facebook_menu_items', $page_hook, $this->page_slug );
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
	 * @return void
	 */
	public function output_callback() {
		?>
		<div class="wrap">
			<div id="icon-tools" class="icon32"></div>
			<h1><?php esc_html_e( 'Facebook Instant Articles', 'wp-native-articles' ); ?></h1>
			<div class="wrap wpna">
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
	 * @return void
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
		 * to register new tabs.
		 */
		do_action( 'wpna_admin_facebook_tabs', $this->tabs );
	}

	/**
	 * Register the meta fields we're using on the posts page.
	 *
	 * @todo Add all the other meta fields in.
	 *
	 * @access public
	 * @return void.
	 */
	public function register_post_meta() {
	}

	/**
	 * Setup the screen columns.
	 *
	 * Do actions for registering meta boxes for this screen.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function setup_meta_boxes() {
		$screen = get_current_screen();

		/**
		 * Trigger the add_meta_boxes_{$screen_id} hook to allow meta boxes
		 * to be added to this screen.
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
	 * @return void
	 */
	public function setup_settings() {

		// Unique ID for this section.
		$section_general = 'wpna_facebook-general';

		register_setting( $section_general, 'wpna_options', 'wpna_sanitize_options' );

		add_settings_section(
			$section_general,
			esc_html__( 'General', 'wp-native-articles' ),
			array( $this, 'section_general_callback' ),
			$section_general
		);

		add_settings_field(
			'fbia_enable',
			'<label for="fbia_enable">' . esc_html__( 'Enable', 'wp-native-articles' ) . '</label>',
			array( $this, 'enable_callback' ),
			$section_general,
			$section_general
		);

		add_settings_field(
			'fbia_content_parser',
			'<label for="fbia_content_parser">' . esc_html__( 'Content Parser', 'wp-native-articles' ) . '</label>',
			array( $this, 'content_parser_callback' ),
			$section_general,
			$section_general
		);

		add_settings_field(
			'fbia_authorise_id',
			'<label for="fbia_authorise_id">' . esc_html__( 'Authorization ID', 'wp-native-articles' ) . '</label>',
			array( $this, 'authorise_id_callback' ),
			$section_general,
			$section_general
		);

		add_settings_field(
			'fbia_style',
			'<label for="fbia_style">' . esc_html__( 'Article Style', 'wp-native-articles' ) . '</label>',
			array( $this, 'style_callback' ),
			$section_general,
			$section_general
		);

		add_settings_field(
			'fbia_rtl',
			'<label for="fbia_rtl">' . esc_html__( 'RTL', 'wp-native-articles' ) . '</label>',
			array( $this, 'rtl_callback' ),
			$section_general,
			$section_general
		);

		add_settings_field(
			'fbia_sponsored',
			'<label for="fbia_sponsored">' . esc_html__( 'Default Article Sponsored', 'wp-native-articles' ) . '</label>',
			array( $this, 'default_sponsored_callback' ),
			$section_general,
			$section_general
		);

		add_settings_field(
			'fbia_image_likes',
			'<label for="fbia_image_likes">' . esc_html__( 'Image Likes', 'wp-native-articles' ) . '</label>',
			array( $this, 'image_likes_callback' ),
			$section_general,
			$section_general
		);

		add_settings_field(
			'fbia_image_comments',
			'<label for="fbia_image_comments">' . esc_html__( 'Image Comments', 'wp-native-articles' ) . '</label>',
			array( $this, 'image_comments_callback' ),
			$section_general,
			$section_general
		);

		add_settings_field(
			'fbia_credits',
			'<label for="fbia_credits">' . esc_html__( 'Default Article Credits', 'wp-native-articles' ) . '</label>',
			array( $this, 'default_credits_callback' ),
			$section_general,
			$section_general
		);

		add_settings_field(
			'fbia_copyright',
			'<label for="fbia_copyright">' . esc_html__( 'Default Article Copyright', 'wp-native-articles' ) . '</label>',
			array( $this, 'default_copyright_callback' ),
			$section_general,
			$section_general
		);

		// Unique ID for this section.
		$section_general_ads = 'wpna_facebook-general-ads';

		register_setting( $section_general_ads, 'wpna_options', 'wpna_sanitize_options' );

		add_settings_section(
			$section_general_ads,
			esc_html__( 'Ads', 'wp-native-articles' ),
			array( $this, 'section_general_ads_callback' ),
			$section_general_ads
		);

		add_settings_field(
			'fbia_enable_ads',
			'<label for="fbia_enable_ads">' . esc_html__( 'Enable Ads', 'wp-native-articles' ) . '</label>',
			array( $this, 'enable_ads_callback' ),
			$section_general_ads,
			$section_general_ads
		);

		add_settings_field(
			'fbia_auto_ad_placement',
			'<label for="fbia_auto_ad_placement">' . esc_html__( 'Auto Place Ads', 'wp-native-articles' ) . '</label>',
			array( $this, 'auto_ad_placement_callback' ),
			$section_general_ads,
			$section_general_ads
		);

		add_settings_field(
			'fbia_ad_density',
			'<label for="fbia_ad_density">' . esc_html__( 'Ad Density', 'wp-native-articles' ) . '</label>',
			array( $this, 'ad_density_callback' ),
			$section_general_ads,
			$section_general_ads
		);

		add_settings_field(
			'fbia_ad_code_type',
			'<label for="fbia_ad_code_type">' . esc_html__( 'Ad Type', 'wp-native-articles' ) . '</label>',
			array( $this, 'ad_code_type_callback' ),
			$section_general_ads,
			$section_general_ads
		);

		add_settings_field(
			'fbia_ad_code_placement_id',
			'<label for="fbia_ad_code_placement_id">' . esc_html__( 'Placement ID', 'wp-native-articles' ) . '</label>',
			array( $this, 'fbia_ad_code_placement_id_callback' ),
			$section_general_ads,
			$section_general_ads
		);

		add_settings_field(
			'fbia_ad_code',
			'<label for="fbia_ad_code">' . esc_html__( 'Ad Code', 'wp-native-articles' ) . '</label>',
			array( $this, 'ad_code_callback' ),
			$section_general_ads,
			$section_general_ads
		);

		add_settings_field(
			'fbia_recirculation_ad',
			'<label for="fbia_recirculation_ad">' . esc_html__( 'Recirculation Ad', 'wp-native-articles' ) . '</label>',
			array( $this, 'recirculation_ad_callback' ),
			$section_general_ads,
			$section_general_ads
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
	 * @return void
	 */
	public function general_tab_callback() {
		// Temporary. Being moved to helper functions in the future.
		$sections = array( 'analytics', 'ads' );

		// Default to gneral.
		$section        = 'general';
		$section_fields = 'wpna_facebook-general';

		// @codingStandardsIgnoreLine
		if ( isset( $_GET['section'] ) && in_array( wp_unslash( $_GET['section'] ), $sections, true ) ) {
			// @codingStandardsIgnoreLine
			$section         = $_GET['section'];
			$section_fields .= '-' . $section;
		}

		?>
		<?php // @codingStandardsIgnoreStart ?>
		<div class="wpna-subsections-wrapper">
			<ul>
				<li class="<?php if ( 'general' === $section ) echo ' active' ;?>">
					<a href="<?php echo esc_url( add_query_arg( 'section', 'general' ) ); ?>"><?php esc_html_e( 'General', 'wp-native-articles' ); ?></a>
					&nbsp;|&nbsp;
				</li>
				<li class="<?php if ( 'analytics' === $section ) echo ' active' ;?>">
					<a href="<?php echo esc_url( add_query_arg( 'section', 'analytics' ) ); ?>"><?php esc_html_e( 'Analytics', 'wp-native-articles' ); ?></a>
					&nbsp;|&nbsp;
				</li>
				<li class="<?php if ( 'ads' === $section ) echo ' active' ;?>">
					<a href="<?php echo esc_url( add_query_arg( 'section', 'ads' ) ); ?>"><?php esc_html_e( 'Ads', 'wp-native-articles' ); ?></a>
				</li>
			</ul>
		</div>
		<?php // @codingStandardsIgnoreEnd ?>

		<form action="options.php" method="post">
			<?php settings_fields( $section_fields ); ?>
			<?php do_settings_sections( $section_fields ); ?>
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
	 * @return void
	 */
	public function section_general_callback() {
		?>
		<p>
			<?php esc_html_e( 'Use this section to set generic Instant Article settings.', 'wp-native-articles' ); ?>
			<?php esc_html_e( 'They can all be overridden on a per article basis with the exception of the Authorization ID field.', 'wp-native-articles' ); ?>
		</p>

		<?php
		// Get all the default template paths for the plugin.
		$default_templates = glob( WPNA_BASE_PATH . '/templates/*.php' );

		$overriden_templates = array();

		foreach ( $default_templates as $default_template ) {
			// Get just the template name.
			$template_name = basename( $default_template );
			// Check if they've been overriden or not.
			if ( wpna_locate_template( $template_name ) !== $default_template ) {
				$overriden_templates[ $template_name ] = wpna_locate_template( $template_name );
			}
		}

		// If any are being overriden show a warning message.
		if ( ! empty( $overriden_templates ) ) : ?>
			<hr />
			<p>
				<span class="wpna-label wpna-label-warning"><?php esc_html_e( 'Warning', 'wp-native-articles' ); ?></span>
				<i><b><?php esc_html_e( 'Templates being overridden', 'wp-native-articles' ); ?></b></i>
			</p>

			<p><?php esc_html_e( 'The following templates are being overridden. Well this is normally fine it could mean that some of the settings below are not being outputted or that the output is modified in some way.', 'wp-native-articles' ); ?></p>

			<?php foreach ( $overriden_templates as $template_name => $new_location ) : ?>
				<p><strong><?php echo esc_html( $template_name ); ?></strong> - <code><?php echo esc_html( strstr( $new_location, 'wp-content' ) ); ?></code></p>
			<?php endforeach; ?>
			<hr />
		<?php endif;

	}

	/**
	 * Outputs the HTML for the 'fbia_enable' settings field.
	 *
	 * Whether the Facebook Instant Articles feed is enabled or not.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function enable_callback() {
		?>
		<label for="fbia_enable">
			<select name="wpna_options[fbia_enable]" id="fbia-enable">
				<option value="off"<?php selected( wpna_get_option( 'fbia_enable' ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
				<option value="on"<?php selected( wpna_get_option( 'fbia_enable' ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
			</select>
			<?php esc_html_e( 'Enable Facebook Instant Articles', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_enable' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_content_parser' settings field.
	 *
	 * Whether the Facebook Instant Articles feed is enabled or not.
	 *
	 * @since 1.3.2
	 *
	 * @access public
	 * @return void
	 */
	public function content_parser_callback() {
		?>
		<label for="fbia_content_parser">
			<select name="wpna_options[fbia_content_parser]" id="fbia-content-parser">
				<option value="v1"<?php selected( wpna_get_option( 'fbia_content_parser' ), 'v1' ); ?>><?php esc_html_e( 'Version One', 'wp-native-articles' ); ?></option>
				<option value="v2"<?php selected( wpna_get_option( 'fbia_content_parser' ), 'v2' ); ?>><?php esc_html_e( 'Version Two', 'wp-native-articles' ); ?></option>
			</select>
			<p class="description">
				<?php esc_html__( 'Version Two of the content parser is up to 10x faster, uses fewer resources, and is better at transforming content.', 'wp-native-articles' ); ?>
			</p>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_content_parser' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_authorise_id' settings field.
	 *
	 * The authorization ID from claiming your URL. Outputted in the header.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function authorise_id_callback() {
		?>
		<input type="text" name="wpna_options[fbia_authorise_id]" id="fbia_authorise_id" class="regular-text" value="<?php echo esc_attr( wpna_get_option( 'fbia_authorise_id' ) ); ?>">
		<p class="description"><?php esc_html_e( 'The authorization ID for Claim Your URL', 'wp-native-articles' ); ?></p>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_authorise_id' );
		?>

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
	 * @return void
	 */
	public function style_callback() {
		?>
		<input type="text" name="wpna_options[fbia_style]" id="fbia_style" class="regular-text" value="<?php echo esc_attr( wpna_get_option( 'fbia_style' ) ); ?>">
		<p class="description"><?php esc_html_e( 'Default styling template to use', 'wp-native-articles' ); ?></p>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_style' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_rtl' settings field.
	 *
	 * Sets whether to enable RTL support for articles. Can be overriden
	 * on a per article basis.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 * @return void
	 */
	public function rtl_callback() {
		?>
		<label for="fbia_rtl">
			<select name="wpna_options[fbia_rtl]" id="fbia_rtl">
				<option value="auto"<?php selected( wpna_get_option( 'fbia_rtl' ), 'auto' ); ?>><?php esc_html_e( 'Auto Detect', 'wp-native-articles' ); ?></option>
				<option value="off"<?php selected( wpna_get_option( 'fbia_rtl' ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
				<option value="on"<?php selected( wpna_get_option( 'fbia_rtl' ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
			</select>
			<p class="description">
				<?php esc_html_e(
					'Set RTL support for articles. Auto Detect uses the WordPress admin setting.',
					'wp-native-articles'
				); ?>
			</p>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_rtl' );
		?>

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
	 * @return void
	 */
	public function default_sponsored_callback() {
		?>
		<label for="fbia_sponsored">
			<select name="wpna_options[fbia_sponsored]" id="fbia_sponsored">
				<option value="off"<?php selected( wpna_get_option( 'fbia_sponsored' ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
				<option value="on"<?php selected( wpna_get_option( 'fbia_sponsored' ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
			</select>
			<p class="description">
				<?php esc_html_e(
					'Make all articles on this site Sponsored articles by default.
					Pulls the Facebook profile link from the author page.
					The Facebook profile link is added to the WordPress user profile page by numerous plugins but most noticeably Yoast SEO.',
					'wp-native-articles'
				); ?>
			</p>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_sponsored' );
		?>

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
	 * @return void
	 */
	public function image_likes_callback() {
		?>
		<label for="fbia_image_likes">
			<select name="wpna_options[fbia_image_likes]" id="fbia-image-likes">
				<option value="off"<?php selected( wpna_get_option( 'fbia_image_likes' ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
				<option value="on"<?php selected( wpna_get_option( 'fbia_image_likes' ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
			</select>
			<?php esc_html_e( 'Add Like overlay for every image', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_image_likes' );
		?>

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
	 * @return void
	 */
	public function image_comments_callback() {
		?>
		<label for="fbia_image_comments">
			<select name="wpna_options[fbia_image_comments]" id="fbia-image-comments">
				<option value="off"<?php selected( wpna_get_option( 'fbia_image_comments' ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
				<option value="on"<?php selected( wpna_get_option( 'fbia_image_comments' ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
			</select>
			<?php esc_html_e( 'Add Comments overlay for every image', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_image_comments' );
		?>

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
	 * @return void
	 */
	public function default_credits_callback() {
		?>
		<input type="text" name="wpna_options[fbia_credits]" id="fbia_credits" class="regular-text" value="<?php echo esc_attr( wpna_get_option( 'fbia_credits' ) ); ?>">
		<p class="description"><?php esc_html_e( 'Default credits applied to the bottom of every article', 'wp-native-articles' ); ?></p>
		<p class="description">
			<?php echo wp_kses(
				__( 'Date placeholders prefixed by a <strong>%</strong> percent symbol can be used.', 'wp-native-articles' ),
				array( 'strong' => array() )
			);?>
		</p>
		<p class="description">
			<?php echo sprintf(
				wp_kses(
					// translators: Placeholder is the URL to the document page.
					__( 'See the <a target="_blank" href="%s">date documentation</a> for more information.', 'wp-native-articles' ),
					array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
						),
					)
				),
				esc_url( 'http://docs.wp-native-articles.com/article/43-date-variables' )
			);?>
		</p>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_credits' );
		?>

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
	 * @return void
	 */
	public function default_copyright_callback() {
		?>
		<input type="text" name="wpna_options[fbia_copyright]" id="fbia_copyright" class="regular-text" value="<?php echo esc_attr( wpna_get_option( 'fbia_copyright' ) ); ?>">
		<p class="description"><?php esc_html_e( 'Default copyright applied to the bottom of every article', 'wp-native-articles' ); ?></p>
		<p class="description">
			<?php echo wp_kses(
				__( 'Date placeholders prefixed by a <strong>%</strong> percent symbol can be used.', 'wp-native-articles' ),
				array( 'strong' => array() )
			);?>
		</p>
		<p class="description">
			<?php echo sprintf(
				wp_kses(
					// translators: Placeholder is the URL to the document page.
					__( 'See the <a target="_blank" href="%s">date documentation</a> for more information.', 'wp-native-articles' ),
					array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
						),
					)
				),
				esc_url( 'http://docs.wp-native-articles.com/article/43-date-variables' )
			);?>
		</p>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_copyright' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML displayed at the top of the settings section.
	 *
	 * @since 1.3.3
	 *
	 * @access public
	 * @return void
	 */
	public function section_general_ads_callback() {
		?>
		<p></p>
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
	 * @return void
	 */
	public function enable_ads_callback() {
		?>
		<label for="fbia_enable_ads">
			<select name="wpna_options[fbia_enable_ads]" id="fbia_enable_ads">
				<option value="off"<?php selected( wpna_get_option( 'fbia_enable_ads' ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
				<option value="on"<?php selected( wpna_get_option( 'fbia_enable_ads' ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
			</select>
			<?php esc_html_e( 'Enable ads in Instant Articles', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_enable_ads' );
		?>

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
	 * @return void
	 */
	public function auto_ad_placement_callback() {
		?>
		<label for="fbia_auto_ad_placement">
			<select name="wpna_options[fbia_auto_ad_placement]" id="fbia_auto_ad_placement">
				<option value="off"<?php selected( wpna_get_option( 'fbia_auto_ad_placement' ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
				<option value="on"<?php selected( wpna_get_option( 'fbia_auto_ad_placement' ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
			</select>
			<?php esc_html_e( 'Allow Facebook to auto position your ads in articles', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_auto_ad_placement' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_ad_density' settings field.
	 *
	 * Faceook now allows you to specify ad density.
	 * Options:
	 * - default (250 word gap which offers high ad density)
	 * - medium (350 word gap which offers medium ad density)
	 * - low (500 word gap which offers low ad density)
	 *
	 * @since 1.2.2
	 *
	 * @access public
	 * @return void
	 */
	public function ad_density_callback() {
		?>
		<label for="fbia_ad_density">
			<select name="wpna_options[fbia_ad_density]" id="fbia_ad_density">
				<option value="default"<?php selected( wpna_get_option( 'fbia_ad_density' ), 'default' ); ?>><?php esc_html_e( 'Default (250 word gap)', 'wp-native-articles' ); ?></option>
				<option value="medium"<?php selected( wpna_get_option( 'fbia_ad_density' ), 'medium' ); ?>><?php esc_html_e( 'Medium (350 word gap)', 'wp-native-articles' ); ?></option>
				<option value="low"<?php selected( wpna_get_option( 'fbia_ad_density' ), 'low' ); ?>><?php esc_html_e( 'Low (500 word gap)', 'wp-native-articles' ); ?></option>
			</select>
			<?php esc_html_e( 'How frequently you would like ads to appear within your article.', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_ad_density' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_ad_code_type' settings field.
	 *
	 * Most people just use audience network so they only really need to
	 * enter the ID. This makes it a little easier.
	 *
	 * Default to 'custom' if no value is set. This is for backward compatibility.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 * @return void
	 */
	public function ad_code_type_callback() {
		?>
		<label for="fbia_ad_code_type">
			<select name="wpna_options[fbia_ad_code_type]" id="fbia_ad_code_type">
				<option value="audience_network"<?php selected( wpna_get_option( 'fbia_ad_code_type' ), 'audience_network' ); ?>><?php esc_html_e( 'Audience Network', 'wp-native-articles' ); ?></option>
				<option value="custom"<?php selected( wpna_get_option( 'fbia_ad_code_type', 'custom' ), 'custom' ); ?>><?php esc_html_e( 'Custom', 'wp-native-articles' ); ?></option>
			</select>
			<?php esc_html_e( 'The type of ad to place.', 'wp-native-articles' ); ?>
		</label>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_ad_code_type' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_ad_code_placement_id' settings field.
	 *
	 * If they just enter the ID for their audience network ad we can then
	 * correctly format it on our end.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 * @return void
	 */
	public function fbia_ad_code_placement_id_callback() {
		?>
		<input type="text" name="wpna_options[fbia_ad_code_placement_id]" id="fbia_ad_code_placement_id" class="regular-text" value="<?php echo esc_attr( wpna_get_option( 'fbia_ad_code_placement_id' ) ); ?>">
		<p class="description"><?php esc_html_e( 'Your Audience Network Placement ID', 'wp-native-articles' ); ?></p>
		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_ad_code_placement_id' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_ad_code' settings field.
	 *
	 * Sets the default ad code to use for each article. Can be overridden on a
	 * per article basis.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function ad_code_callback() {
		?>
		<textarea name="wpna_options[fbia_ad_code]" rows="10" cols="50" id="fbia_ad_code" class="large-text code"><?php echo esc_textarea( wpna_get_option( 'fbia_ad_code' ) ); ?></textarea>
		<p class="description">
			<?php echo sprintf(
				// translators: Placeholder is the example code.
				esc_html__( 'Ad code for displaying your ads. Ensure it is wrapped in %s.', 'wp-native-articles' ),
				'<code>&lt;figure class="op-ad"&gt;&lt;/figure&gt;</code>'
			);
			?>
		</p>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_ad_code' );
		?>

		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_recirculation_ad_callback' settings field.
	 *
	 * Facebook is experimenting with ads in Recirculation units.
	 *
	 * @since 1.2.2
	 *
	 * @access public
	 * @return void
	 */
	public function recirculation_ad_callback() {
		?>
		<input type="text" name="wpna_options[fbia_recirculation_ad]" id="fbia_recirculation_ad" class="regular-text" value="<?php echo esc_attr( wpna_get_option( 'fbia_recirculation_ad' ) ); ?>">

		<p class="recirculation-ad-warning" style="display:none;">
			<span class="wpna-label wpna-label-warning"><?php esc_html_e( 'Warning', 'wp-native-articles' ); ?></span>
			<i><b><?php esc_html_e( 'Recirculation Ad ID must be different to your Audience Network ID', 'wp-native-articles' ); ?></b></i>
		</p>
		<p class="description"><?php esc_html_e( 'The ad placement ID (different to your standard Audience Network Placement ID)', 'wp-native-articles' ); ?></p>
		<p class="description"><?php esc_html_e( 'Facebook is experimenting with placing native ads within the recirculation unit at the bottom of articles. Adding a placement ID in here will enable it.', 'wp-native-articles' ); ?></p>
		<p class="description">
			<?php echo sprintf(
				wp_kses(
					// translators: Placeholder is the URL to the document page.
					__( 'See the <a target="_blank" href="%s">Placing Ads in Recirculation Units Documentation</a> for more information and setup.', 'wp-native-articles' ),
					array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
						),
					)
				),
				esc_url( 'https://developers.facebook.com/docs/instant-articles/monetization/ad-placement#recirculation-units' )
			);?>
		</p>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_recirculation_ad' );
		?>

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
	 * @param  array $tabs Existing tabs.
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
	 * Reigster fields that should be saved in the post meta.
	 *
	 * @since 1.3.5
	 * @access public
	 * @param  array $fields Fields that should be saved.
	 * @return array $fields
	 */
	public function post_meta_box_fields( $fields ) {
		$fields[] = 'fbia_style';
		$fields[] = 'fbia_rtl';
		$fields[] = 'fbia_sponsored';
		$fields[] = 'fbia_image_likes';
		$fields[] = 'fbia_image_comments';
		$fields[] = 'fbia_credits';
		$fields[] = 'fbia_copyright';
		$fields[] = 'fbia_analytics';
		$fields[] = 'fbia_enable_ads';
		$fields[] = 'fbia_auto_ad_placement';
		$fields[] = 'fbia_ad_density';
		$fields[] = 'fbia_ad_code_type';
		$fields[] = 'fbia_ad_code_placement_id';
		$fields[] = 'fbia_ad_code';
		$fields[] = 'fbia_recirculation_ad';
		$fields[] = $this->post_status_input_name;

		return $fields;
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
	 * @param  WP_Post $post Global post object.
	 * @return void
	 */
	public function post_meta_box_facebook_settings_callback( $post ) {
		?>
		<h3><?php esc_html_e( 'Override Default Values', 'wp-native-articles' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Use these settings to override global values for this post only', 'wp-native-articles' ); ?></p>

		<div class="pure-form pure-form-aligned">

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-style"><?php esc_html_e( 'Override Style Template', 'wp-native-articles' ); ?></label>
					<input type="text" name="wpna_options[fbia_style]" id="fbia-style" placeholder="<?php echo esc_attr( wpna_get_option( 'fbia_style' ) ); ?>" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wpna_fbia_style', true ) ); ?>">
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_style' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-rtl"><?php esc_html_e( 'RTL', 'wp-native-articles' ); ?></label>
					<select name="wpna_options[fbia_rtl]" id="fbia-rtl">
						<option></option>
						<option value="off"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_rtl', true ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
						<option value="on"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_rtl', true ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
					</select>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_rtl' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-sponsored"><?php esc_html_e( 'Sponsored Article', 'wp-native-articles' ); ?></label>
					<select name="wpna_options[fbia_sponsored]" id="fbia-sponsored">
						<option></option>
						<option value="off"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_sponsored', true ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
						<option value="on"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_sponsored', true ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
					</select>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_sponsored' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-image-likes"><?php esc_html_e( 'Image Likes', 'wp-native-articles' ); ?></label>
					<select name="wpna_options[fbia_image_likes]" id="fbia-image-likes">
						<option></option>
						<option value="off"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_image_likes', true ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
						<option value="on"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_image_likes', true ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
					</select>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_image_likes' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-image-comments"><?php esc_html_e( 'Image Comments', 'wp-native-articles' ); ?></label>
					<select name="wpna_options[fbia_image_comments]" id="fbia-image-comments">
						<option></option>
						<option value="off"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_image_comments', true ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
						<option value="on"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_image_comments', true ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
					</select>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_image_comments' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-credits"><?php esc_html_e( 'Credits', 'wp-native-articles' ); ?></label>
					<input type="text" name="wpna_options[fbia_credits]" id="fbia-credits" placeholder="<?php echo esc_attr( wpna_get_option( 'fbia_credits' ) ); ?>" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wpna_fbia_credits', true ) ); ?>">
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_credits' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-copyright"><?php esc_html_e( 'Copyright', 'wp-native-articles' ); ?></label>
					<input type="text" name="wpna_options[fbia_copyright]" id="fbia-copyright" placeholder="<?php echo esc_attr( wpna_get_option( 'fbia_copyright' ) ); ?>" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wpna_fbia_copyright', true ) ); ?>">
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_copyright' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-analytics"><?php esc_html_e( 'Analytics', 'wp-native-articles' ); ?></label>
					<textarea name="wpna_options[fbia_analytics]" id="fbia-analytics" rows="6" cols="50" class="code" placeholder="<?php echo esc_attr( wpna_get_option( 'fbia_analytics' ) ); ?>"><?php echo esc_textarea( get_post_meta( get_the_ID(), '_wpna_fbia_analytics', true ) ); ?></textarea>
					<p style="margin-left:11em;font-style:italic;"><?php esc_html_e( 'Appended to the global analytics. Does not override.', 'wp-native-articles' ); ?></p>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_analytics' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-enable-ads"><?php esc_html_e( 'Enable Ads', 'wp-native-articles' ); ?></label>
					<select name="wpna_options[fbia_enable_ads]" id="fbia-enable-ads">
						<option></option>
						<option value="off"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_enable_ads', true ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
						<option value="on"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_enable_ads', true ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
					</select>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_enable_ads' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-auto-ad-placement"><?php esc_html_e( 'Auto Place Ads', 'wp-native-articles' ); ?></label>
					<select name="wpna_options[fbia_auto_ad_placement]" id="fbia-auto-ad-placement">
						<option></option>
						<option value="off"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_auto_ad_placement', true ), 'off' ); ?>><?php esc_html_e( 'Disabled', 'wp-native-articles' ); ?></option>
						<option value="on"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_auto_ad_placement', true ), 'on' ); ?>><?php esc_html_e( 'Enabled', 'wp-native-articles' ); ?></option>
					</select>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_auto_ad_placement' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-ad-density"><?php esc_html_e( 'Ad Density', 'wp-native-articles' ); ?></label>
					<select name="wpna_options[fbia_ad_density]" id="fbia-ad-density">
						<option></option>
						<option value="default"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_ad_density', true ), 'default' ); ?>><?php esc_html_e( 'Default (250 word gap)', 'wp-native-articles' ); ?></option>
						<option value="medium"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_ad_density', true ), 'medium' ); ?>><?php esc_html_e( 'Medium (350 word gap)', 'wp-native-articles' ); ?></option>
						<option value="low"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_ad_density', true ), 'low' ); ?>><?php esc_html_e( 'Low (500 word gap)', 'wp-native-articles' ); ?></option>
					</select>

					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_ad_density' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-ad-code-type"><?php esc_html_e( 'Ad Type', 'wp-native-articles' ); ?></label>
					<select name="wpna_options[fbia_ad_code_type]" id="fbia-ad-code-type">
						<option></option>
						<option value="audience_network"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_ad_code_type', true ), 'audience_network' ); ?>><?php esc_html_e( 'Audience Network', 'wp-native-articles' ); ?></option>
						<option value="custom"<?php selected( get_post_meta( get_the_ID(), '_wpna_fbia_ad_code_type', true ), 'custom' ); ?>><?php esc_html_e( 'Custom', 'wp-native-articles' ); ?></option>
					</select>

					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_ad_code_type' );
					?>
				</div>
			</fieldset>

			<fieldset id="wpna-ad-code-template-audience_network" class="wpna-ad-code-template">
				<div class="pure-control-group">
					<label for="fbia-ad-code-placement-id"><?php esc_html_e( 'Placement ID', 'wp-native-articles' ); ?></label>
					<input type="text" name="wpna_options[fbia_ad_code_placement_id]" id="fbia-ad-code-placement-id" placeholder="<?php echo esc_attr( wpna_get_option( 'fbia_ad_code_placement_id' ) ); ?>" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wpna_fbia_ad_code_placement_id', true ) ); ?>">
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_ad_code_placement_id' );
					?>
				</div>
			</fieldset>

			<fieldset id="wpna-ad-code-template-custom" class="wpna-ad-code-template">
				<div class="pure-control-group">
					<label for="fbia-ad-code"><?php esc_html_e( 'Ad Code', 'wp-native-articles' ); ?></label>
					<textarea name="wpna_options[fbia_ad_code]" id="fbia-ad-code" rows="10" cols="50" class="code" placeholder="<?php echo esc_attr( wpna_get_option( 'fbia_ad_code' ) ); ?>"><?php echo esc_textarea( get_post_meta( get_the_ID(), '_wpna_fbia_ad_code', true ) ); ?></textarea>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_ad_code' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia-recirculation-ad"><?php esc_html_e( 'Recirculation Ad', 'wp-native-articles' ); ?></label>
					<input type="text" name="wpna_options[fbia_recirculation_ad]" id="fbia-recirculation-ad" placeholder="<?php echo esc_attr( wpna_get_option( 'fbia_recirculation_ad' ) ); ?>" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wpna_fbia_recirculation_ad', true ) ); ?>" />
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_recirculation_ad' );
					?>
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

		</div>

		<?php
	}

	/**
	 * Flush the content cache.
	 *
	 * Whenever a post is updated we need to flush the posts content flash.
	 *
	 * @access public
	 * @param  int $post_id Id of the post to delete the cache for.
	 * @return void
	 */
	public function flush_content_cache( $post_id ) {
		delete_transient( 'wpna_facebook_post_content_' . $post_id );
	}

	/**
	 * Outputs the FB IA authorisation ID meta tag in the header.
	 *
	 * @access public
	 * @return void
	 */
	public function output_authorisation_id() {
		if ( $value = wpna_get_option( 'fbia_authorise_id' ) ) {
			printf( '<meta property="fb:pages" content="%s" />', esc_attr( $value ) );
		}
	}

}
