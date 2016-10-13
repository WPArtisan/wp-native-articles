<?php

/**
 * The deactivator class for the plugin.
 *
 * Anything that should happen when the plugin is
 * deactivated should be here. Will only be run the once.
 *
 * @author OzTheGreat
 * @since 0.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 *
 * @since 0.0.1
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
	 * @return null
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
	 * @since 0.0.1
	 *
	 * @access public
	 * @return null
	 */
	public function flush_rewrite_rules() {
		flush_rewrite_rules();
	}

}
