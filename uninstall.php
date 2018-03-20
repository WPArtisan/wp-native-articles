<?php
/**
 * Handles uninstalling the plugin
 *
 * When the plugin is removed make sure it cleans up after itself.
 *
 * @since 1.0.0
 * @package wp-native-articles
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! function_exists( 'wpna_uninstall' ) ) :

	/**
	 * We need to clean up options from every possible
	 * site on the network including the network.
	 *
	 * Don't run this at the moment. Going to add an option in.
	 *
	 * Transients should clean themselves up.
	 *
	 * @return void
	 */
	function wpna_uninstall() {
		global $wpdb;

		// Delete from the global network options or main site if not multiesite.
		delete_site_option( 'wpna_options' );

		// Check if it's a multisite install or not.
		if ( is_multisite() ) {

			/**
			 * Delete from everysite in the network.
			 * get_sites was only added in 4.6.
			 *
			 * @todo Switch to background CRON.
			 * @todo Do something about wp_is_large_network().
			 */
			if ( function_exists( 'get_sites' ) && class_exists( 'WP_Site_Query' ) ) {
				$sites = get_sites();
				foreach ( $sites as $site ) {
					delete_blog_option( absint( $site->blog_id ), 'wpna_options' );
				}
			} elseif ( function_exists( 'wp_get_sites' ) ) {
				// @codingStandardsIgnoreLine
				$sites = wp_get_sites();
				foreach ( $sites as $site ) {
					delete_blog_option( absint( $site['blog_id'] ), 'wpna_options' );
				}
			}
		}

		// Flush the rewrite rules to clear any endpoints.
		flush_rewrite_rules();
	}
endif;
