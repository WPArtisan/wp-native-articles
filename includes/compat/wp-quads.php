<?php
/**
 * WP-Quads Compatibility.
 *
 * Auto injects ads into posts. For the moment just remove the shortcode
 * when in IA. In the future add proper compatibility?
 *
 * @link https://wordpress.org/plugins/quick-adsense-reloaded/
 * @since 1.2.4.
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_wp_quads_add_override_shortcodes' ) ) :

	/**
	 * Override the `quads` shortcode in IA.
	 *
	 * @param array  $override_tags Shortocde tags to override.
	 * @param string $content       Current post content.
	 * @return array $override_tags
	 */
	function wpna_wp_quads_add_override_shortcodes( $override_tags, $content ) {
		// Disables WP-Quads ads for Instant articles by returning an
		// empty string for the shortcode.
		$override_tags['quads'] = '__return_empty_string';
		return $override_tags;
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', 'wpna_wp_quads_add_override_shortcodes', 10, 2 );
