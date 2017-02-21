<?php
/**
 * The deactivator class for the plugin.
 *
 * Anything that should happen when the plugin is
 * deactivated should be here. Will only be run the once.
 *
 * @author OzTheGreat
 * @since 1.0.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deactivator class.
 */
class WPNA_Deactivator {

	/**
	 * Kicks everything off regarding deactivation.
	 *
	 * Main method to be run in this class to fire off
	 * all the other methods. Handles everything the plugin
	 * should do upon deactivation.
	 *
	 * @access public
	 * @return void
	 */
	public static function run() {
		self::flush_rewrite_rules();
	}

	/**
	 * Flushes the rewrite rules.
	 *
	 * We're adding a custom endpoint with the permalinks API
	 * so we need to flush the rewrite rules to clean it up and remove it.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public static function flush_rewrite_rules() {
		flush_rewrite_rules();
	}

}
