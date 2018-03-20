<?php
/**
 * Handles all plugin upgrades between versions.
 *
 * @since 1.3.0
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_update_db_check' ) ) :

	/**
	 * Compares the current plugin version with the version in the DB.
	 * If they're not equal then it runs the DB script in the install file.
	 *
	 * This runs fairly late on so other update checks can run.
	 *
	 * @return void
	 */
	function wpna_update_db_check() {
		// Grab the Plugin DB version.
		$db_version = get_site_option( 'wpna_db_version' );

		// If there's no DB version or it's less than the current version, upgrade.
		if ( ! $db_version || version_compare( $db_version, WPNA_VERSION, '<' ) ) {
			WPNA_Activator::run_database_scripts();

			// Add the transient to redirect after install. Don't bother if network activated.
			if ( ! is_network_admin() && ! isset( $_GET['activate-multi'] ) ) { // WPCS: CSRF ok.
				set_transient( '_wpna_activation_redirect', true, MINUTE_IN_SECONDS / 2 );
			}
		}
	}
endif;
add_action( 'plugins_loaded', 'wpna_update_db_check', 9999, 0 );

/**
 * Update the old analytics settings.
 *
 * @since 1.3.5
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_update_analytics_check' ) ) :

	/**
	 * Compares the current plugin version with the version in the DB.
	 * If they're not equal then it runs the DB script in the install file.
	 *
	 * @return void
	 */
	function wpna_update_analytics_check() {
		// Grab the Plugin DB version.
		$db_version = get_site_option( 'wpna_db_version' );

		// If there's no DB version or it's less than 1.3.5, upgrade the analytics.
		if ( ! $db_version || version_compare( $db_version, '1.3.5', '<' ) ) {
			// Get the current analytics script.
			$current_analytics = wpna_get_option( 'fbia_analytics' );

			if ( ! empty( $current_analytics ) ) {
				// Workout the new options.
				$new_analytics = array(
					'fbia_analytics_custom'    => $current_analytics,
					'fbia_analytics_providers' => array( 'custom-analytics' ),
				);

				// Get all the options.
				$wpna_options = (array) wpna_get_options();

				// Remove the old analytics field.
				unset( $wpna_options['fbia_analytics'] );

				// Merge in the analytics values.
				$options = array_merge( $wpna_options, $new_analytics );

				// flush_rewrite_rules doesn't exist at this point.
				remove_action( 'update_option_wpna_options', 'flush_rewrite_rules', 10 );

				// Update the options.
				update_option( 'wpna_options', $options );
			}
		}
	}
endif;
add_action( 'plugins_loaded', 'wpna_update_analytics_check', 10, 0 );
