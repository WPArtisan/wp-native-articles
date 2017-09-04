<?php
/**
 * Newsmag allows videos as featured images. This ensures compatibility.
 *
 * /Newsmag/includes/wp_booster/td_module_single_base.php:ln109.
 *
 * @since 1.2.4
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_newsmag_compatibility' ) ) :

	/**
	 * If it's a video post with a video featured image then add the video
	 * at the top of the content.
	 *
	 * @since 1.2.4
	 *
	 * @access public
	 * @param  string $content The content of the post.
	 * @return string
	 */
	function wpna_newsmag_compatibility( $content ) {
		if ( class_exists( 'td_util' ) && 'video' === get_post_format( get_the_ID() ) ) {
			// Try and get the video.
			$td_post_video = get_post_meta( get_the_ID(), 'td_post_video', true );

			// Render the video if the post has a video in the featured video section of the post.
			if ( ! empty( $td_post_video['td_video'] ) ) {
				// Format the video.
				$video_output = sprintf( '<figure class="op-interactive"><iframe width="560" height="315" src="%s"></iframe></figure>', esc_url( $td_post_video['td_video'] ) );

				// Prepend it to the content.
				$content = $video_output . PHP_EOL . $content;
			}
		}

		return $content;
	}
endif;
add_filter( 'wpna_facebook_article_content_after_transform', 'wpna_newsmag_compatibility', 11, 1 );
