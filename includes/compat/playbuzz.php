<?php
/**
 * Playbuzz compatibility. By default the Playbuzz plugin won't parse Playbuzz
 * shortcode in a feed. The plugin does come with an option to enable it, this
 * ensures it's enabled if it's on the feed.
 *
 * @link https://wordpress.org/plugins/playbuzz/
 * @since 1.0.0.
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_enable_playbuzz_hook' ) ) :

	/**
	 * Add the playbuzz hook in for Instant Articles only.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	function wpna_enable_playbuzz_hook() {
		add_filter( 'pre_option_playbuzz', 'wpna_enable_playbuzz', 10, 1 );
	}
endif;
add_action( 'wpna_pre_get_fbia_post', 'wpna_enable_playbuzz_hook', 10 );

if ( ! function_exists( 'wpna_enable_playbuzz' ) ) :

	/**
	 * Ensure Playbuzz embeds are parsed.
	 *
	 * Playbuzz is only parsed in single articles by default.
	 * Because feeds are classed as categories the embed code won't
	 * be inserted. This will enable it.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array $playbuzz Playbuzz plugin options.
	 * @return array
	 */
	function wpna_enable_playbuzz( $playbuzz ) {
		$playbuzz['embeddedon'] = 'all';

		return $playbuzz;
	}
endif;
