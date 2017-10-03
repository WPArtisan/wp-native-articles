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
	 * @return void
	 */
	function wpna_update_db_check() {
		if ( WPNA_VERSION !== get_site_option( 'wpna_db_version' ) ) {
			WPNA_Activator::run_database_scripts();
		}
	}
endif;
add_action( 'plugins_loaded', 'wpna_update_db_check', 10, 0 );
