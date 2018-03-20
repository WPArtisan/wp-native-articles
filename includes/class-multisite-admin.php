<?php
/**
 * Deals with multisite stuff.
 *
 * @since 1.0.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for handling all multisite settings.
 *
 * If it's a multisite install this sets up all
 * the multisite menus, settings, pages and dashboards etc.
 *
 * @since 1.0.0
 */
class WPNA_Multisite_Admin {

	/**
	 * The slug of the multisite general page.
	 *
	 * Used for registering menu items and tabs.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $page_slug = 'wpna_multisite';

	/**
	 * The slug of the general option group.
	 *
	 * Used for registering fields, creating nonces etc.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $option_group_general = 'wpna_multisite-general';

	/**
	 * The slug of the reset option group.
	 *
	 * Used for registering fields, creating nonces etc.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $option_group_reset = 'wpna_multisite-reset';

	/**
	 * Constructor.
	 *
	 * Triggers the hooks method straight away.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Hooks registered in this class.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_init',              array( $this, 'setup_settings' ), 10, 0 );
		add_action( 'network_admin_menu',      array( $this, 'add_menu_items' ), 10, 0 );
		add_action( 'wpmu_new_blog',           array( $this, 'new_blog_defaults' ), 10, 6 );
		add_action( 'network_admin_edit_' . $this->page_slug,         array( $this, 'save_options_callback' ), 10, 1 );
		add_action( 'network_admin_edit_' . $this->page_slug,         array( $this, 'reset_blog_callback' ), 10, 1 );
		add_filter( 'admin_menu',              array( $this, 'admin_page_capability' ), 999, 0 );

		add_filter( 'sanitize_option_wpna_multisite-reset', 'absint', 10, 1 );
		add_filter( 'sanitize_option_wpna_license_key', 'sanitize_text_field', 10, 1 );
	}

	/**
	 * Registers multisite options.
	 *
	 * Uses the settings API to register fields and manage options for the
	 * network dashboard. The settings API doesn't auto save fields for the
	 * multisite dashboard so this is handled manually below.
	 *
	 * @see save_options_callback()
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function setup_settings() {

		register_setting( $this->option_group_general, 'wpna_multisite_options', array( $this, 'validate_options_callback' ) );

		add_settings_section(
			$this->option_group_general, // Section ID.
			esc_html__( 'Multisite Options', 'wp-native-articles' ),
			array( $this, 'general_section_callback' ),
			$this->option_group_general // ID used to output fields.
		);

		add_settings_field(
			'access_level',
			sprintf( '<label for="access_level">%s</label>', esc_html__( 'Access Level', 'wp-native-articles' ) ),
			array( $this, 'access_level_field_callback' ),
			$this->option_group_general, // ID used to output fields.
			$this->option_group_general // Section ID.
		);

		add_settings_field(
			'inherit_id',
			sprintf( '<label for="inheit_id">%s</label>', esc_html__( 'Inherit ID', 'wp-native-articles' ) ),
			array( $this, 'inherit_id_field_callback' ),
			$this->option_group_general,
			$this->option_group_general
		);

		register_setting( $this->option_group_reset, 'wpna_multisite_reset', array( $this, 'validate_reset_callback' ) );

		add_settings_section(
			$this->option_group_reset,
			__( 'Reset Blog Settings', 'wp-native-articles' ),
			array( $this, 'blog_reset_section_callback' ),
			$this->option_group_reset
		);

		add_settings_field(
			'reset_blog_id',
			sprintf( '<label for="reset_blog_id">%s</label>', esc_html__( 'Blog ID', 'wp-native-articles' ) ),
			array( $this, 'reset_blog_id_field_callback' ),
			$this->option_group_reset,
			$this->option_group_reset
		);
	}

	/**
	 * Setups up menu items for the multisite dashboard.
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
		$settings_page = add_menu_page(
			esc_html__( 'Multisite Settings', 'wp-native-articles' ),
			esc_html__( 'Native Articles', 'wp-native-articles' ),
			'manage_network_options',
			$this->page_slug, // Page slug.
			array( $this, 'options_page_callback' ),
			'',
			89
		);
	}

	/**
	 * Outputs the content for the main network admin page.
	 *
	 * Uses the settings API to display the fields registered.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function options_page_callback() {
		// URL of the current page. For submitting forms to.
		$page_url = add_query_arg(
			array( 'action' => $this->page_slug ),
			network_admin_url( 'edit.php' )
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Multisite Settings', 'wp-native-articles' ); ?></h1>
			<form action="<?php echo esc_url_raw( $page_url ); ?>" method="post">
				<?php settings_fields( $this->option_group_general ); ?>
				<?php do_settings_sections( $this->option_group_general ); ?>
				<?php submit_button( esc_html__( 'Save Multisite Settings', 'wp-native-articles' ), 'primary', 'wpna_save_options' ); ?>
			</form>
			<form action="<?php echo esc_url_raw( $page_url ); ?>" method="post">
				<?php settings_fields( $this->option_group_reset ); ?>
				<?php do_settings_sections( $this->option_group_reset ); ?>
				<?php submit_button( esc_html__( 'Reset Site to Defaults', 'wp-native-articles' ), 'secondary', 'wpna_reset_blog' ); ?>
			</form>

			<?php
				/**
				 * Action to add anymore fields to the Multisite admin page.
				 *
				 * @since 1.0.0
				 */
				do_action( 'wpna_multisite_options_page_callback' );
			?>

		</div>
		<?php
	}

	/**
	 * Displays output before the settings section.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function general_section_callback() {
		// Intentionally left blank.
	}

	/**
	 * Displays the output for the access_level field,
	 *
	 * A simple select box with only two options. Shows the field for deciding
	 * what user level is required to make plugin changes on the network sites.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $args Any additional arguments passed though from when
	 *                     the field was registered.
	 * @return void
	 */
	public function access_level_field_callback( $args ) {
		$options = get_site_option( 'wpna_options' );
		$value   = isset( $options['access_level'] ) ? $options['access_level'] : null;
		?>
		<select name="wpna_options[access_level]" id="access_level">
			<option value="administrator"<?php selected( $value, 'administrator' ); ?>><?php esc_html_e( 'Administrator', 'wp-native-articles' ); ?></option>
			<option value="network_administrator"<?php selected( $value, 'network_administrator' ); ?>><?php esc_html_e( 'Network Administrator', 'wp-native-articles' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'The minimum user role required to change native article settings.', 'wp-native-articles' ); ?></p>
		<?php
	}

	/**
	 * Displays the output for the inherit_id field,
	 *
	 * A simple text input for numbers with the minimum set to 0. Shows the input
	 * for setting the ID of the blog new blogs should inherit settings from.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $args Any additional arguments passed though from when
	 *                     the field was registered.
	 * @return void
	 */
	public function inherit_id_field_callback( $args ) {
		$options = get_site_option( 'wpna_options' );
		$value   = isset( $options['inherit_id'] ) ? $options['inherit_id'] : get_current_blog_id();
		?>
		<input type="number" id="inheit_id" name="wpna_options[inherit_id]" value="<?php echo absint( $value ); ?>" min="0" class="regular-text">
		<p class="description"><?php esc_html_e( 'When creating a new site inherit settings from this site.', 'wp-native-articles' ); ?></p>
		<?php
	}

	/**
	 * Validates and cleans data from the multisite settings form.
	 *
	 * This method is registered when we called 'register_setting()'. Validates
	 * the 'access_level' and 'inherit_id' fields.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $data The form data submitted for validation.
	 * @return array
	 */
	public function validate_options_callback( $data ) {

		$values = array();

		if ( ! empty( $data['access_level'] ) ) {
			$values['access_level'] = in_array( $data['access_level'], array( 'administrator', 'network_administrator' ), true ) ? $data['access_level'] : 'administrator';
		}

		if ( ! empty( $data['inherit_id'] ) ) {
			$values['inherit_id'] = absint( $data['inherit_id'] );
		}

		return $values;
	}

	/**
	 * Saves the multisite settings and redirects back.
	 *
	 * The settings API doesn't auto save fields on the multisite network screen
	 * so it has to be done manually. Checks permissions and nonces, setup the
	 * filters for validation, save the data, then redirect back with a URL
	 * param set to indicate which message should be shown.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function save_options_callback() {

		// Check if we want to save the options.
		$save_options = filter_input( INPUT_POST, 'wpna_save_options', FILTER_SANITIZE_STRING );

		if ( ! $save_options ) {
			return;
		}

		// Check user has the correct permissions.
		if ( ! is_super_admin() ) {
			return;
		}

		// Misleading name, validate nonce.
		check_admin_referer( $this->option_group_general . '-options' );

		// Grab the data from $_POST.
		$unfiltered_input = filter_input( INPUT_POST, 'wpna_options', FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY );

		/**
		 * This filter is used to validate & santize the data. It is called by
		 * the settings API and the validation function registered with the
		 * 'register_setting()' function is applied.
		 *
		 * @since 1.0.0
		 * @var array The form data to validate.
		 */
		$values = apply_filters( 'sanitize_option_' . $this->option_group_general, wp_unslash( $unfiltered_input ) );

		// Save options.
		$updated = update_site_option( 'wpna_options', $values );

		// Redirect back with a notice flag.
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'         => $this->page_slug,
					'wpna-message' => $updated ? 'multisite_options_update_success' : 'multisite_options_update_error',
				),
				network_admin_url( 'admin.php' )
			)
		);

		exit;
	}

	/**
	 * Displays output before the reset section.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function blog_reset_section_callback() {
		esc_html_e( 'Here you can reset ALL Native Article settings for a particular blog to the same as the Inherit ID blog above.', 'wp-native-articles' );
	}

	/**
	 * Displays the output for the reset_blog[id] field,
	 *
	 * A simple text input for numbers with the minimum set to 0. Shows the input
	 * for the reset blog ID field.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $args Any additional arguments passed though from when
	 *                     the field was registered.
	 * @return void
	 */
	public function reset_blog_id_field_callback( $args ) {
		?>
		<input type="number" name="reset_blog[id]" value="" min="0" class="regular-text" />
		<?php
	}

	/**
	 * Validates and cleans data from the multisite reset blog form.
	 *
	 * This method is registered when we called 'register_setting()'. Validates
	 * the 'id' field.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $data The form data submitted for validation.
	 * @return array
	 */
	public function validate_reset_callback( $data ) {

		if ( ! empty( $data['id'] ) ) {
			$data['id'] = absint( $data['id'] );
		}

		return $data;
	}

	/**
	 * Resets all plugin settings on a blog.
	 *
	 * Resets the chosen blog and redirects back with an appropriate messages.
	 * Checks permissions and nonces, setup the filters for validation, reset
	 * blog options, then redirect back with a URL param set to indicate which
	 * message should be shown.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function reset_blog_callback() {
		// Check if we want to reset the blog.
		$reset_blog = filter_input( INPUT_POST, 'wpna_reset_blog', FILTER_SANITIZE_STRING );

		if ( ! $reset_blog ) {
			return;
		}

		// Make sure they're a super admin.
		if ( ! is_super_admin() ) {
			return;
		}

		// Misleading name, validate nonce.
		check_admin_referer( $this->option_group_reset . '-options' );

		// Grab the data from $_POST and check it's an int.
		$unfiltered_input = filter_input( INPUT_POST, 'reset_blog', FILTER_VALIDATE_INT );

		/**
		 * This filter is used to validate & santize the data. It is called by
		 * the settings API and the validation function registered with the
		 * 'register_setting()' function is applied.
		 *
		 * @since 1.0.0
		 * @var array The form data to validate.
		 */
		$values = apply_filters( 'sanitize_option_' . $this->option_group_reset, $unfiltered_input );

		// If no ID was passed set an error message.
		if ( empty( $values['id'] ) ) {
			$notice = 'multisite_reset_error_missing_id';
		} else {
			// Get the ID of the blog to copy the options from.
			$options       = get_site_option( 'wpna_options' );
			$souce_blog_id = isset( $options['inherit_id'] ) ? $options['inherit_id'] : get_current_blog_id();

			// Reset the blog options.
			$this->set_blog_defaults( $souce_blog_id, $values['id'] );

			// Set the success notice.
			$notice = 'multisite_reset_success';
		}

		// Redirect back with a notice flag.
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'         => $this->page_slug,
					'wpna-message' => $notice,
				),
				network_admin_url( 'admin.php' )
			)
		);

		exit;
	}

	/**
	 * Sets default settings on any new blogs created.
	 *
	 * When a new blog is created copy all the plugin options from the 'source'
	 * blog set in the options. Defaults to the primary network site if the
	 * source blog isn't set.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param int    $blog_id The ID of the blog to set the options on.
	 * @param int    $user_id The current user ID.
	 * @param string $domain  The domain of the new blog.
	 * @param string $path    The path of the new blog.
	 * @param int    $site_id The new blog's parent site_id.
	 * @param array  $meta    Any other arguments passed through.
	 * @return void
	 */
	public function new_blog_defaults( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		$options       = get_site_option( 'wpna_options' );
		$souce_blog_id = isset( $options['inherit_id'] ) ? $options['inherit_id'] : get_current_blog_id();

		$this->set_blog_defaults( absint( $souce_blog_id ), absint( $blog_id ) );
	}

	/**
	 * Copies all `wpna` options from one blog to another.
	 *
	 * If you need to filter this us the WordPress update_blog_option filters.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  int $source_blog_id The ID of the blog to copy the options from.
	 * @param  int $target_blog_id The ID of the blog to copy the options to.
	 * @return void
	 */
	public function set_blog_defaults( $source_blog_id, $target_blog_id ) {
		$options = get_blog_option( $source_blog_id, 'wpna_options' );
		update_blog_option( $target_blog_id, 'wpna_options', $options );
	}

	/**
	 * Restricts menu items on blogs to the chosen user level.
	 *
	 * Checks to see who should be able to access the admin
	 * menu of the plugin and updates the capabilities accordingly. If they're
	 * unable to see it then the menu item is removed and all pages' capabilities
	 * adjusted.
	 *
	 * @since 1.0.0
	 * @global $submenu Submenus on the current blog.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_page_capability() {
		$options = get_site_option( 'wpna_options' );

		// Default to administrator.
		$access_level = 'manage_options';

		if ( ! empty( $options['access_level'] ) ) {
			if ( 'network_administrator' === $options['access_level'] ) {
				$access_level = 'manage_network_options';
			}
		}

		// Hide the menu if the current user can't access it.
		if ( ! current_user_can( $access_level ) ) {

			global $submenu;

			// Remove the menu item.
			remove_menu_page( 'wpna_general' );

			// Still need to update cap requirements even when hidden
			// Cycle through the menu and set the new capabilities.
			if ( ! empty( $submenu['wpna_general'] ) ) {
				foreach ( $submenu['wpna_general'] as $position => $data ) {
					// @codingStandardsIgnoreLine
					$submenu['wpna_general'][ $position ][1] = $access_level;
				}
			}
		}

	}

}
