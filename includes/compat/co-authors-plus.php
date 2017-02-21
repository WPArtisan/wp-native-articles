<?php
/**
 * Co Authors Plus compatibility.
 *
 * @link https://en-gb.wordpress.org/plugins/co-authors-plus/
 * @since 1.0.0
 * @package wp-native-articles
 */

add_filter( 'wpna_facebook_post_authors', 'wpna_co_authors_plus', 10, 1 );

if ( ! function_exists( 'wpna_co_authors_plus' ) ) :

	/**
	 * Check for multiple authors.
	 *
	 * The co-authors plus plugin is still fairly popular. If it's installed
	 * check for multiple authors.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $authors  Array of authors for the post.
	 * @return array
	 */
	function wpna_co_authors_plus( $authors ) {

		if ( function_exists( 'get_coauthors' ) ) {
			$authors = get_coauthors( get_the_ID() );
		}

		return $authors;
	}
endif;
