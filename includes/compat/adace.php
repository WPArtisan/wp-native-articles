<?php
/**
 * Ensures that the AdAce ads get removed.
 *
 * @since 1.3.0
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_adace_override_tags' ) ) :

	/**
	 * Override the following tags with custom output functions.
	 *
	 * @param  array  $override_tags Shortcode functions to override.
	 * @param  string $content      Current post content.
	 * @return array  Shortcodes to ignore
	 */
	function wpna_adace_override_tags( $override_tags, $content ) {

		$override_tags['adace-ad'] = '__return_empty_string';

		return $override_tags;
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', 'wpna_adace_override_tags', 10, 2 );
