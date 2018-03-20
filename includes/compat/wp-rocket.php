<?php
/**
 * WP Rocket Compatibility.
 *
 * It sets incorrect headers when loading the RSS feed.
 * This might be an issue. Also disable image lazyload.
 *
 * @since 1.4.0.
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_wp_rocket_reject_rss' ) ) :

	/**
	 * Exclude RSS Feed from cache
	 *
	 * @param array $urls Array of URLs to exclude from cache.
	 * @return array Updated array of URLs
	 */
	function wpna_wp_rocket_reject_rss( $urls ) {
		$feed      = '(.*)/' . $GLOBALS['wp_rewrite']->feed_base . '/';
		$feed_slug = wpna_get_option( 'fbia_feed_slug' );

		$urls[] = $feed . $feed_slug;

		return $urls;
	}
endif;
add_filter( 'rocket_cache_reject_uri', 'wpna_wp_rocket_reject_rss', 10, 1 );

if ( ! function_exists( 'wpna_wp_rocket_disable_lazyload' ) ) :

	/**
	 * Explicitly disable lazyload for wp-rocket images.
	 * I don't think this is needed as WP Rocket's own checks disable
	 * it for pretty much everyway we sync articles but it's best to be sure.
	 *
	 * @return void
	 */
	function wpna_wp_rocket_disable_lazyload() {
		add_filter( 'do_rocket_lazyload', '__return_false' );
	}
endif;
add_action( 'wpna_pre_get_fbia_post', 'wpna_wp_rocket_disable_lazyload', 10 );
