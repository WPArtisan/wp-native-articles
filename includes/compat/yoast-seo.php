<?php

/**
 * Yoast SEO compatibility
 *
 * @link https://en-gb.wordpress.org/plugins/wordpress-seo/
 * @since 0.0.1
 */
add_filter( 'wpna_facebook_post_authors', 'wpna_yoast_fb_link', 10, 1 );
add_filter( 'wpna_facebook_post_get_the_featured_image', 'wpna_yoast_featured_image', 10, 1 );

/**
 * Add author Facebook link.
 *
 * For each author add a link to their FB profile if it has been set.
 *
 * @since 0.0.1
 *
 * @access public
 * @param  array $authors
 * @return array
 */
function wpna_yoast_fb_link( $authors ) {
	// Loop through all authors and check their
	// user meta data for a facebook URL
	foreach ( (array) $authors as $author ) {
		if ( ! strlen( $author->user_url ) && $fb_url = get_user_meta( get_the_author_meta( 'ID' ), 'facebook', true ) ) {
			$author->user_url = $fb_url;
			$author->user_url_rel = 'facebook';
		}
	}

	return $authors;
}

/**
 * Checks to see if the featured image has been overridden.
 *
 * The Yoast SEO plugin allows you to set an Opengraph image. Defaults
 * to that if it's set.
 *
 * @since 0.0.1
 *
 * @access public
 * @param  array  $image
 * @return array
 */
function wpna_yoast_featured_image( $image ) {
	// Check the post meta for opengraph image
	if ( $image_url = get_post_meta( get_the_ID(), '_yoast_wpseo_opengraph-image', true ) ) {
		$image[ 'url' ] = $image_url;

		// If we've found an image let's update the caption
		if ( $caption = get_post_meta( get_the_ID(), '_yoast_wpseo_opengraph-description', true ) )
			$image[ 'caption' ] = $caption;
	}

	return $image;
}
