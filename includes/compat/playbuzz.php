<?php

/**
 * Playbuzz compatibility
 *
 * @link https://wordpress.org/plugins/playbuzz/
 * @since 0.0.1
 */

add_filter( 'pre_option_playbuzz', 'wpna_enable_playbuzz', 10, 1 );

/**
 * Ensure Playbuzz embeds are parsed.
 *
 * Playbuzz is only parsed in single articles by default.
 * Because feeds are classed as categories the embed code won't
 * be inserted. This will enable it.
 *
 * @since 0.0.1
 *
 * @access public
 * @param  array $playbuzz
 * @return array
 */
function wpna_enable_playbuzz( $playbuzz ) {

	$feed_slug = wpna_get_option( 'fbia_feed_slug' );

	if ( is_feed( $feed_slug ) ) {
		$playbuzz['embeddedon'] = 'all';
	}

	return $playbuzz;
}
