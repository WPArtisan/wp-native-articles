<?php
/**
 * Plugin Name: WP Native Articles
 * Description: Advanced Facebook Instant Articles integration for Wordpress
 * Author: OzTheGreat (WPArtisan)
 * Author URI: https://wpartisan.me
 * Version: 1.0.0
 * Plugin URI: https://wp-native-articles.com
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Define the current version
define( 'WPNA_VERSION', '1.0.0' );

// Define the plugin base path
define( 'WPNA_BASE_PATH', dirname( __FILE__ ) );

// Define the URL to check for updates
define( 'WPNA_STORE_URL', 'https://wp-native-articles.com' );

// Define the item name
define( 'WPNA_ITEM_NAME', 'WP Native Articles' );


/**
 * This function kicks everything off.
 *
 * It includes all the necessary files and initalises all classes that need
 * to be initalised
 *
 * @since 0.0.1
 *
 * @return null
 */
function wpna_initialise() {

	// Register the plugin activation file
	require WPNA_BASE_PATH . '/class-activator.php';
	register_activation_hook( __FILE__, array( 'WPNA_Activator', 'run' ) );

	// Register the plugin deactivation file
	require WPNA_BASE_PATH . '/class-deactivator.php';
	register_deactivation_hook( __FILE__, array( 'WPNA_Deactivator', 'run' ) );


	//
	// Global functions and helper files
	//

	// Load the helper class
	require WPNA_BASE_PATH . '/includes/functions-helper.php';

	/**
	 * Setup the global options array.
	 * Although they are stored in here they are never accessed
	 * directly, only through the helper functions.
	 */
	$GLOBALS['wpna_options'] = wpna_get_options();

	// Load the sanitization class
	require WPNA_BASE_PATH . '/includes/functions-sanitization.php';


	//
	// Classes that register hooks and do stuff
	//

	// Load the admin tabs helper class
	require WPNA_BASE_PATH . '/includes/class-helper-tabs.php';

	// Load the base admin class & interface
	require WPNA_BASE_PATH . '/includes/class-admin-base.php';
	require WPNA_BASE_PATH . '/includes/interface-admin-base.php';

	// Facebook post object. Used in the templates
	require WPNA_BASE_PATH . '/includes/class-facebook-post.php';

	// Load the multisite functionality
	if ( is_multisite() ) {
		require WPNA_BASE_PATH . '/includes/class-multisite-admin.php';
		$wpna_multisite_admin = new WPNA_Multisite_Admin();
	}

	// Load the main admin section
	require WPNA_BASE_PATH . '/includes/class-admin.php';
	$wpna_admin = new WPNA_Admin();

	// Load the support admin section
	require WPNA_BASE_PATH . '/includes/class-admin-support.php';
	$wpna_support_admin = new WPNA_Admin_Support();

	// Load Facebook Instant Articles functionality
	require WPNA_BASE_PATH . '/includes/class-admin-facebook.php';
	require WPNA_BASE_PATH . '/includes/class-admin-facebook-feed.php';
	$wpna_facebook_admin = new WPNA_Admin_Facebook();
	$wpna_facebook_feed = new WPNA_Admin_Facebook_Feed();

	// Load the Facebook post content parser
	require WPNA_BASE_PATH . '/includes/class-facebook-content-parser.php';
	$wpna_facebook_content = new WPNA_Facebook_Content_Parser();


	//
	// Third party compatibility functions
	//

	include WPNA_BASE_PATH . '/includes/compat/playbuzz.php';
	include WPNA_BASE_PATH . '/includes/compat/yoast-seo.php';
	include WPNA_BASE_PATH . '/includes/compat/co-authors-plus.php';

}

wpna_initialise();
