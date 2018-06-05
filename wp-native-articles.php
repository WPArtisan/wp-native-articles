<?php
/**
 * Plugin Name: WP Native Articles
 * Description: Advanced Facebook Instant Articles integration for WordPress
 * Author: OzTheGreat (WPArtisan)
 * Author URI: https://wpartisan.me
 * Version: 1.5.3
 * Plugin URI: https://wp-native-articles.com
 *
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Native_Articles' ) ) :

	/**
	 * Main WP_Native_Articles Class
	 *
	 * Code layout inspired by Affiliate_WP
	 *
	 * @since  1.5.0
	 */
	final class WP_Native_Articles {
		/** Singleton *************************************************************/

		/**
		 * WP_Native_Articles instance.
		 *
		 * @access private
		 * @since  1.5.0
		 * @var    WP_Native_Articles The one true WP_Native_Articles
		 */
		private static $instance;

		/**
		 * The version number of WP_Native_Articles.
		 *
		 * @access private
		 * @since  1.5.0
		 * @var    string
		 */
		private $version = '1.5.3';

		/**
		 * The shortcodes class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Shortcodes
		 */
		public $shortcodes;

		/**
		 * The main admin class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Admin
		 */
		public $admin;

		/**
		 * The main admin class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Admin_General
		 */
		public $admin_general;

		/**
		 * The general -> Getting Started class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Admin_General_Getting_Started
		 */
		public $admin_general_getting_started;

		/**
		 * The general -> what is new class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Admin_General_What_Is_New
		 */
		public $admin_general_what_is_new;

		/**
		 * The main admin class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Admin_Facebook
		 */
		public $admin_facebook;

		/**
		 * The main admin class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Admin_Facebook_Analytics
		 */
		public $admin_facebook_analytics;

		/**
		 * The main admin class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Admin_Facebook_Styling
		 */
		public $admin_facebook_styling;

		/**
		 * The main admin class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Admin_Facebook_Feed
		 */
		public $admin_facebook_feed;

		/**
		 * The main admin class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Admin_Facebook_Api
		 */
		public $admin_facebook_api;

		/**
		 * The main admin class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Admin_Facebook_Crawler_Ingestion
		 */
		public $admin_facebook_crawler_ingestion;

		/**
		 * The main admin class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Admin_Facebook_Post_Syncer
		 */
		public $admin_facebook_post_syncer;

		/**
		 * The main admin class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Admin_Facebook_Custom_Content
		 */
		public $facebook_custom_content;

		/**
		 * The main admin class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Facebook_Content_Parser
		 */
		public $facebook_content_parser;

		/**
		 * The main admin class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Admin_Placements
		 */
		public $admin_placements;

		/**
		 * The main admin class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Admin_Transformers
		 */
		public $admin_transformers;

		/**
		 * The main admin class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Admin_Support
		 */
		public $admin_support;

		/**
		 * The admin premium class.
		 *
		 * @access public
		 * @since  1.5.0
		 * @var    WPNA_Admin_Premium
		 */
		public $admin_premium;

		/**
		 * Main WP_Native_Articles Instance
		 *
		 * Insures that only one instance of WP_Native_Articles exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since  1.5.0
		 * @static
		 * @staticvar array $instance
		 * @return WP_Native_Articles
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WP_Native_Articles ) ) {
				self::$instance = new WP_Native_Articles();

				self::$instance->setup_constants();
				self::$instance->includes();

				register_activation_hook( __FILE__,   array( 'WPNA_Activator', 'run' ) );
				register_deactivation_hook( __FILE__, array( 'WPNA_Deactivator', 'run' ) );

				add_action( 'plugins_loaded', array( self::$instance, 'setup_objects' ), -1 );
				add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
			}
			return self::$instance;
		}

		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since  1.5.0
		 * @access protected
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'wp-native-articles' ), '1.4.1' );
		}

		/**
		 * Disable unserializing of the class
		 *
		 * @since  1.5.0
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'wp-native-articles' ), '1.4.1' );
		}

		/**
		 * Setup plugin constants
		 *
		 * @access private
		 * @since  1.5.0
		 * @return void
		 */
		private function setup_constants() {
			// Plugin version.
			if ( ! defined( 'WPNA_VERSION' ) ) {
				define( 'WPNA_VERSION', $this->version );
			}

			// Plugin base file.
			if ( ! defined( 'WPNA_BASE_FILE' ) ) {
				define( 'WPNA_BASE_FILE', __FILE__ );
			}

			// Plugin base path.
			if ( ! defined( 'WPNA_BASE_PATH' ) ) {
				define( 'WPNA_BASE_PATH', plugin_dir_path( __FILE__ ) );
			}
		}

		/**
		 * Include required files
		 *
		 * @access private
		 * @since 1.4.1
		 * @return void
		 */
		private function includes() {
			// Only incldue these if the PHP version is high enough.
			if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
				require WPNA_BASE_PATH . '/vendor/autoload.php';
				require WPNA_BASE_PATH . '/includes/transformers/actions-transformers-facebook.php';
			}
			require WPNA_BASE_PATH . '/class-activator.php';
			require WPNA_BASE_PATH . '/class-deactivator.php';
			require WPNA_BASE_PATH . '/includes/functions-helper.php';
			require WPNA_BASE_PATH . '/includes/functions-sanitization.php';
			require WPNA_BASE_PATH . '/includes/functions-variables.php';
			require WPNA_BASE_PATH . '/includes/deprecated.php';
			require WPNA_BASE_PATH . '/upgrade.php';
			require WPNA_BASE_PATH . '/includes/admin/admin-actions.php';
			require WPNA_BASE_PATH . '/includes/admin/admin-notices.php';
			require WPNA_BASE_PATH . '/includes/class-helper-tabs.php';
			require WPNA_BASE_PATH . '/includes/admin/interface-admin-base.php';
			require WPNA_BASE_PATH . '/includes/admin/class-admin-base.php';
			require WPNA_BASE_PATH . '/includes/class-facebook-post.php';
			require WPNA_BASE_PATH . '/includes/class-shortcodes.php';
			if ( 'v2' === wpna_get_option( 'fbia_content_parser' ) ) {
				define( 'WPNA_PARSER_VERSION', '2.0.0' );
				require WPNA_BASE_PATH . '/includes/class-facebook-content-parser-v2.php';
			} else {
				define( 'WPNA_PARSER_VERSION', '1.0.0' );
				require WPNA_BASE_PATH . '/includes/class-facebook-content-parser.php';
			}
			if ( is_multisite() ) {
				require WPNA_BASE_PATH . '/includes/class-multisite-admin.php';
			}
			require WPNA_BASE_PATH . '/includes/admin/class-admin.php';
			require WPNA_BASE_PATH . '/includes/admin/general/class-admin-general.php';
			require WPNA_BASE_PATH . '/includes/admin/general/class-admin-general-getting-started.php';
			require WPNA_BASE_PATH . '/includes/admin/general/class-admin-general-what-is-new.php';
			require WPNA_BASE_PATH . '/includes/admin/facebook/class-admin-facebook.php';
			require WPNA_BASE_PATH . '/includes/admin/facebook/class-admin-facebook-analytics.php';
			require WPNA_BASE_PATH . '/includes/admin/facebook/class-admin-facebook-styling.php';
			require WPNA_BASE_PATH . '/includes/admin/facebook/class-admin-facebook-feed.php';
			require WPNA_BASE_PATH . '/includes/admin/facebook/class-admin-facebook-api.php';
			require WPNA_BASE_PATH . '/includes/admin/facebook/class-admin-facebook-crawler-ingestion.php';
			require WPNA_BASE_PATH . '/includes/admin/facebook/class-admin-facebook-post-syncer.php';
			require WPNA_BASE_PATH . '/includes/admin/facebook/class-admin-facebook-custom-content.php';
			require WPNA_BASE_PATH . '/includes/placements/contextual-help.php';
			require WPNA_BASE_PATH . '/includes/placements/class-admin-placements-list-table.php';
			require WPNA_BASE_PATH . '/includes/placements/class-admin-placements.php';
			require WPNA_BASE_PATH . '/includes/transformers/functions-transformers.php';
			require WPNA_BASE_PATH . '/includes/transformers/actions-transformers.php';
			require WPNA_BASE_PATH . '/includes/transformers/class-transformer.php';
			require WPNA_BASE_PATH . '/includes/transformers/class-admin-transformers-list-table.php';
			require WPNA_BASE_PATH . '/includes/transformers/class-admin-transformers.php';
			require WPNA_BASE_PATH . '/includes/admin/class-admin-support.php';
			require WPNA_BASE_PATH . '/includes/admin/class-admin-premium.php';
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
			include WPNA_BASE_PATH . '/includes/compat/easy-video-player.php';
			include WPNA_BASE_PATH . '/includes/compat/nextgen-gallery.php';
			include WPNA_BASE_PATH . '/includes/compat/wp-rocket.php';
			include WPNA_BASE_PATH . '/includes/compat/mythemeshop.php';
			include WPNA_BASE_PATH . '/includes/compat/zombify.php';
			include WPNA_BASE_PATH . '/includes/compat/jannah.php';
			include WPNA_BASE_PATH . '/includes/compat/aesop.php';
		}

		/**
		 * Setup all objects
		 *
		 * @access public
		 * @since 1.5.0
		 * @return void
		 */
		public function setup_objects() {
			if ( is_multisite() ) {
				self::$instance->wpna_multisite_admin = new WPNA_Multisite_Admin();
			}
			self::$instance->shortcodes                       = new WPNA_Shortcodes();
			self::$instance->admin                            = new WPNA_Admin();
			self::$instance->admin_general                    = new WPNA_Admin_General();
			self::$instance->admin_general_what_is_new        = new WPNA_Admin_General_What_Is_New();
			self::$instance->admin_general_getting_started    = new WPNA_Admin_General_Getting_Started();
			self::$instance->admin_facebook                   = new WPNA_Admin_Facebook();
			self::$instance->admin_facebook_analytics         = new WPNA_Admin_Facebook_Analytics();
			self::$instance->admin_facebook_styling           = new WPNA_Admin_Facebook_Styling();
			self::$instance->admin_facebook_feed              = new WPNA_Admin_Facebook_Feed();
			self::$instance->admin_facebook_api               = new WPNA_Admin_Facebook_API();
			self::$instance->admin_facebook_crawler_ingestion = new WPNA_Admin_Facebook_Crawler_Ingestion();
			self::$instance->admin_facebook_post_syncer       = new WPNA_Admin_Facebook_Post_Syncer();
			self::$instance->facebook_custom_content          = new WPNA_Admin_Facebook_Custom_Content();
			self::$instance->facebook_content_parser          = new WPNA_Facebook_Content_Parser();
			self::$instance->admin_placements                 = new WPNA_Admin_Placements();
			self::$instance->admin_transformers               = new WPNA_Admin_Transformers();
			self::$instance->admin_support                    = new WPNA_Admin_Support();
			self::$instance->admin_premium                    = new WPNA_Admin_Premium();
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access public
		 * @since 1.4
		 * @return void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'wp-native-articles', false, plugin_basename( WPNA_BASE_PATH ) . '/languages/' );
		}

	}
endif;

// Check the free version isn't active then kick everything off.
if ( ! function_exists( 'wpna' ) ) :

	/**
	 * The main function responsible for returning the one true WP_Native_Articles
	 * Instance to functions everywhere.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * Example: <?php $wpna = wpna(); ?>
	 *
	 * @since  1.3.5
	 * @return WP_Native_Articles The one true WP_Native_Articles Instance
	 */
	function wpna() {
		global $wpna;

		// Backwards compatibility.
		if ( ! $wpna ) {
			// Grab the inialising function.
			$wpna = WP_Native_Articles::instance();
		}

		return $wpna;
	}

endif;

// Kick everything off.
wpna();

/**
 * Disables the current plugin and shows a die message.
 *
 * To be shown if this plugin is trying to be activated over the Pro one.
 *
 * @since  1.0.0
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
