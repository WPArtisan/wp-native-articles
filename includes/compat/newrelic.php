<?php
/**
 * Disable the NewRelic JS script added to the page header for IA.
 *
 * @since 1.3.3
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_newrelic_disable_autorum' ) ) :

	/**
	 * If the NewRelic extension is loaded then disable it.
	 *
	 * @return void
	 */
	function wpna_newrelic_disable_autorum() {
		if ( extension_loaded( 'newrelic' ) ) {
			newrelic_disable_autorum();
		}
	}
endif;
add_action( 'wpna_pre_crawler_ingestion_post', 'wpna_newrelic_disable_autorum', 10 );
