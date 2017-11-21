<?php
/**
 * Plugin Name: WP Native Articles
 * Description: Advanced Facebook Instant Articles integration for Wordpress
 * Author: OzTheGreat (WPArtisan)
 * Author URI: https://wpartisan.me
 * Version: 1.3.4
 * Plugin URI: https://wp-native-articles.com
 *
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define the current version.
if ( ! defined( 'WPNA_VERSION' ) ) {
	define( 'WPNA_VERSION', '1.3.4' );
}

// Define the plugin base file.
if ( ! defined( 'WPNA_BASE_FILE' ) ) {
	define( 'WPNA_BASE_FILE', __FILE__ );
}

// Define the plugin base path.
if ( ! defined( 'WPNA_BASE_PATH' ) ) {
	define( 'WPNA_BASE_PATH', dirname( __FILE__ ) );
}

if ( ! function_exists( 'wpna_initialise' ) ) :

	/**
	 * This function kicks everything off.
	 *
	 * It includes all the necessary files and initalises all classes that need
	 * to be initalised.
	 *
	 * @since 1.0.0
	 *
	 * @return stdClass The initiated classes.
	 */
	function wpna_initialise() {

		// Holds all the classes initialized.
		$classes = new stdClass();

		// Preparation for when we re-factor the pro plugin.
		$GLOBALS['wpna_pro'] = new stdClass();

		// Require the class if it doesn't exist.
		if ( ! class_exists( 'WPNA_Activator' ) ) {
			require WPNA_BASE_PATH . '/class-activator.php';
		}

		// Register the plugin activation method.
		register_activation_hook( __FILE__, array( 'WPNA_Activator', 'run' ) );

		// Require the class if it doesn't exist.
		if ( ! class_exists( 'WPNA_Deactivator' ) ) {
			require WPNA_BASE_PATH . '/class-deactivator.php';
		}

		// Register the plugin deactivation method.
		register_deactivation_hook( __FILE__, array( 'WPNA_Deactivator', 'run' ) );

		/**
		 * Global functions and helper files
		 */

		// Load the helper class.
		require WPNA_BASE_PATH . '/includes/functions-helper.php';

		/**
		 * Setup the global options array.
		 * Although they are stored in here they are never accessed
		 * directly, only through the helper functions.
		 */
		$GLOBALS['wpna_options'] = wpna_get_options();

		// Load the sanitization function.
		require WPNA_BASE_PATH . '/includes/functions-sanitization.php';

		// Load the variable functions.
		require WPNA_BASE_PATH . '/includes/functions-variables.php';

		/**
		 * Action files.
		 */

		// Load the upgrade file.
		require WPNA_BASE_PATH . '/upgrade.php';

		// Load the global actions file.
		require WPNA_BASE_PATH . '/includes/admin/admin-actions.php';

		// Load the global notices file.
		require WPNA_BASE_PATH . '/includes/admin/admin-notices.php';

		/**
		 * Contextual Help.
		 */

		 // Load the placements contextual help.
		 require WPNA_BASE_PATH . '/includes/placements/contextual-help.php';

		/**
		 * Classes that register hooks and do stuff.
		 */

		// Load the admin tabs helper class.
		if ( ! class_exists( 'WPNA_Helper_Tabs' ) ) {
			require WPNA_BASE_PATH . '/includes/class-helper-tabs.php';
		}

		// Load the base admin class & interface.
		if ( ! class_exists( 'WPNA_Admin_Base' ) ) {
			require WPNA_BASE_PATH . '/includes/admin/class-admin-base.php';
		}

		if ( ! interface_exists( 'WPNA_Admin_Interface' ) ) {
			require WPNA_BASE_PATH . '/includes/admin/interface-admin-base.php';
		}

		// Facebook post object. Used in the templates.
		if ( ! class_exists( 'WPNA_Facebook_Post' ) ) {
			require WPNA_BASE_PATH . '/includes/class-facebook-post.php';
		}

		// Load the list table class for placements.
		if ( ! class_exists( 'WPNA_Admin_Placements_List_Table' ) ) {
			require WPNA_BASE_PATH . '/includes/placements/class-admin-placements-list-table.php';
		}

		// Load the multisite functionality.
		if ( is_multisite() ) {
			if ( ! class_exists( 'WPNA_Multisite_Admin' ) ) {
				require WPNA_BASE_PATH . '/includes/class-multisite-admin.php';
			}
			$classes->wpna_multisite_admin = new WPNA_Multisite_Admin();
		}

		// Load the main admin section.
		if ( ! class_exists( 'WPNA_Admin' ) ) {
			require WPNA_BASE_PATH . '/includes/admin/class-admin.php';
		}
		$classes->wpna_admin = new WPNA_Admin();

		// Load the support admin section.
		if ( ! class_exists( 'WPNA_Admin_Premium' ) ) {
			require WPNA_BASE_PATH . '/includes/admin/class-admin-premium.php';
		}
		$classes->wpna_admin_premium = new WPNA_Admin_Premium();

		// Load the support admin section.
		if ( ! class_exists( 'WPNA_Admin_Support' ) ) {
			require WPNA_BASE_PATH . '/includes/admin/class-admin-support.php';
		}
		$classes->wpna_admin_support = new WPNA_Admin_Support();

		// Load Facebook Instant Articles functionality.
		if ( ! class_exists( 'WPNA_Admin_Facebook' ) ) {
			require WPNA_BASE_PATH . '/includes/admin/facebook/class-admin-facebook.php';
		}
		$classes->wpna_admin_facebook = new WPNA_Admin_Facebook();

		if ( ! class_exists( 'WPNA_Admin_Facebook_Styling' ) ) {
			require WPNA_BASE_PATH . '/includes/admin/facebook/class-admin-facebook-styling.php';
		}
		$classes->wpna_admin_facebook_styling = new WPNA_Admin_Facebook_Styling();

		if ( ! class_exists( 'WPNA_Admin_Facebook_Feed' ) ) {
			require WPNA_BASE_PATH . '/includes/admin/facebook/class-admin-facebook-feed.php';
		}
		$classes->wpna_admin_facebook_feed = new WPNA_Admin_Facebook_Feed();

		if ( ! class_exists( 'WPNA_Admin_Facebook_API' ) ) {
			require WPNA_BASE_PATH . '/includes/admin/facebook/class-admin-facebook-api.php';
		}
		// Preparation for pro refactor.
		$classes->wpna_admin_facebook_api = new WPNA_Admin_Facebook_API();

		if ( ! class_exists( 'WPNA_Admin_Facebook_Crawler_Ingestion' ) ) {
			require WPNA_BASE_PATH . '/includes/admin/facebook/class-admin-facebook-crawler-ingestion.php';
		}
		// Preparation for pro refactor.
		$GLOBALS['wpna_pro']->wpna_admin_facebook_crawler_ingestion = new WPNA_Admin_Facebook_Crawler_Ingestion();

		if ( ! class_exists( 'WPNA_Admin_Placements' ) ) {
			require WPNA_BASE_PATH . '/includes/placements/class-admin-placements.php';
		}
		// Preparation for pro refactor.
		$GLOBALS['wpna_pro']->wpna_admin_placements = new WPNA_Admin_Placements();

		if ( ! class_exists( 'WPNA_Admin_Facebook_Custom_Content' ) ) {
			require WPNA_BASE_PATH . '/includes/admin/facebook/class-admin-facebook-custom-content.php';
		}
		$classes->wpna_facebook_custom_content = new WPNA_Admin_Facebook_Custom_Content();

		// Load the Facebook post content parser.
		if ( ! class_exists( 'WPNA_Facebook_Content_Parser' ) ) {
			if ( 'v2' === wpna_get_option( 'fbia_content_parser' ) ) {
				define( 'WPNA_PARSER_VERSION', '2.0.0' );
				require WPNA_BASE_PATH . '/includes/class-facebook-content-parser-v2.php';
			} else {
				define( 'WPNA_PARSER_VERSION', '1.0.0' );
				require WPNA_BASE_PATH . '/includes/class-facebook-content-parser.php';
			}
		}
		$classes->wpna_facebook_content_parser = new WPNA_Facebook_Content_Parser();

		// Load the shortcodes class.
		if ( ! class_exists( 'WPNA_Shortcodes' ) ) {
			require WPNA_BASE_PATH . '/includes/class-shortcodes.php';
		}
		$classes->wpna_shortcodes = new WPNA_Shortcodes();

		/**
		 * Third party compatibility functions
		 */

		if ( defined( 'WPNA_PARSER_VERSION' ) && '1.0.0' !== WPNA_PARSER_VERSION ) {
			include WPNA_BASE_PATH . '/includes/compat/wordpress-caption.php';
			include WPNA_BASE_PATH . '/includes/compat/wordpress-gallery.php';

			include WPNA_BASE_PATH . '/includes/compat/embeds-gist.php';
			include WPNA_BASE_PATH . '/includes/compat/embeds-instagram.php';
			include WPNA_BASE_PATH . '/includes/compat/embeds-twitter.php';

			include WPNA_BASE_PATH . '/includes/compat/fvplayer.php';
		}

		include WPNA_BASE_PATH . '/includes/compat/playbuzz.php';
		include WPNA_BASE_PATH . '/includes/compat/yoast-seo.php';
		include WPNA_BASE_PATH . '/includes/compat/co-authors-plus.php';
		include WPNA_BASE_PATH . '/includes/compat/infogram.php';
		include WPNA_BASE_PATH . '/includes/compat/visual-bakery.php';
		include WPNA_BASE_PATH . '/includes/compat/newsmag.php';
		include WPNA_BASE_PATH . '/includes/compat/wp-quads.php';
		include WPNA_BASE_PATH . '/includes/compat/pro-theme.php';
		include WPNA_BASE_PATH . '/includes/compat/adace.php';
		include WPNA_BASE_PATH . '/includes/compat/easyazon.php';
		include WPNA_BASE_PATH . '/includes/compat/wp-recipe-maker.php';
		include WPNA_BASE_PATH . '/includes/compat/tve-editor.php';
		include WPNA_BASE_PATH . '/includes/compat/newrelic.php';
		include WPNA_BASE_PATH . '/includes/compat/media-ace.php';
		include WPNA_BASE_PATH . '/includes/compat/spider-facebook.php';

		// Load the plugin text domain. For i18n.
		add_action( 'plugins_loaded', 'wpna_load_textdomain', 10, 0 );

		return $classes;
	}
endif;

// Check the pro version isn't active then kick everything off.
// Assign all instantiated classes to a variable. If anyone needs
// to unhook anything then they can simply grab it.
if ( ! function_exists( 'wpna_initialise_pro' ) ) {
	$wpna = wpna_initialise();
}

/**
 * Disables the current plugin and shows a die message.
 *
 * To be shown if this plugin is trying to be activated over the Pro one.
 *
 * @since 1.0.0
 * @return void
 */
function wpna_disable_pro_plugin_check() {

	if ( is_plugin_active( 'wp-native-articles-pro/wp-native-articles.php' ) ) {

		// Deactivate the current plugin.
		deactivate_plugins( plugin_basename( __FILE__ ) );

		// Show an error message with a back link.
		wp_die(
			esc_html__( 'Please disable the Pro version before activating the Free version.', 'wp-native-articles' ),
			esc_html__( 'Plugin Activation Error', 'wp-native-articles' ),
			array( 'back_link' => true )
		);

	}

}

// If the Pro plugin is active register the notice function to both the plugin
// activation hook and admin_init (incase it was activated in an obscure manner).
register_activation_hook( __FILE__, 'wpna_disable_pro_plugin_check' );
add_action( 'admin_init', 'wpna_disable_pro_plugin_check', 1, 0 );
