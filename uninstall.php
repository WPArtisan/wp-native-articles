<?php

/**
 * Handles uninstalling the plugin
 *
 * When the plugin is removed make sure it cleans up after itself.
 *
 * @since 1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * We need to clean up options from every possible
 * site on the network including the network.
 *
 * Transients should clean themselves up.
 *
 * - Manual query incase of wp_is_large_network()
 * - Manual delete as delete_site_options calls switch_to_blog()
 *   which is really slow
 */
global $wpdb;

// Delete from the base site
$wpdb->delete( $wpdb->base_prefix . 'options', array( 'option_name' => 'wpna_options' ) );

// Check if it's a multisite install or not
if ( is_multisite() ) {

	// Get an array of IDs for every site
	$sites = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}", 0 );

	// Delete from the global network options
	delete_site_option('wpna_options');

	/**
	 * Delete from everysite in the network.
	 *
	 * @todo Switch to background CRON
	 */
	if ( ! empty( $sites ) ) {
		foreach ( $sites as $site_id ) {
			$wpdb->delete( $wpdb->base_prefix . intval( $site_id ) '_options', array( 'option_name' => 'wpna_options' ) );
		}
	}

}

// Flush the rewrite rules to clear any endpoints
flush_rewrite_rules();
