<?php
/**
 * Add in compatibility for some Jannah theme items.
 *
 * @since 1.5.0
 * @package wp-native-articles
 */

/**
 * Depending on what the Jannah theme post type is set to,
 * add some of the extra content it has set in at the begining
 * of the normal post content.
 *
 * @todo Add slideshow and image to the header instead.
 *
 * @param string $content The main post content.
 */
function wpna_jannah_add_post_format_content( $content ) {
	$post_format = jannah_get_postdata( 'tie_post_head' ) ? jannah_get_postdata( 'tie_post_head' ) : 'standard';

	$output = '';

	// If it's a map.
	if ( 'map' === $post_format ) {
		$link = jannah_get_postdata( 'tie_googlemap_url' );

		preg_match_all( '/![34]d(-?[\d\.]+)/', $link, $matches );

		if ( isset( $matches[1][0] ) && isset( $matches[1][1] ) ) {
			$lat   = $matches[1][0];
			$lng   = $matches[1][1];
			$style = 'standard';

			// Construct the map.
			$map = array(
				'type'       => 'Feature',
				'geometry'   => array(
					'type'        => 'Point',
					'coordinates' => array(
						$lat,
						$lng,
					),
				),
				'properties' => array(
					'pivot' => true,
					'style' => $style,
				),
			);

			// Generate the output.
			$output  = '<figure class="op-map">' . PHP_EOL;
			$output .= '<script type="application/json" class="op-geotag">' . PHP_EOL;
			$output .= wp_json_encode( $map ) . PHP_EOL;
			$output .= '</script>' . PHP_EOL;
			$output .= '</figure>';

			// Grab a placement for this code.
			$placement_id = wpna_content_parser_get_placeholder( $output );

			$output = '<pre>' . $placement_id . '</pre>';
		}
	} elseif ( 'video' === $post_format ) {
		// If it's a video. If self hosted do nothing.
		if ( $embed_code = jannah_get_postdata( 'tie_embed_code' ) ) {
			$output  = '<figure class="op-interactive">';
			$output .= $embed_code;
			$output .= '</figure>';

			// Grab a placement for this code.
			$placement_id = wpna_content_parser_get_placeholder( $output );

			$output = '<pre>' . $placement_id . '</pre>';
		} elseif ( $video_url = jannah_get_postdata( 'tie_video_url' ) ) {

			$wp_embed = new WP_Embed();

			if ( $video_output = $wp_embed->autoembed( $video_url ) ) {
				$output = $video_output;
			}
		}
	} elseif ( 'slider' === $post_format ) {
		// If it's a slideshow type.
		// Custom slider.
		if ( jannah_get_postdata( 'tie_post_slider' ) ) {
			$slider     = jannah_get_postdata( 'tie_post_slider' );
			$get_slider = get_post_custom( $slider );

			if ( ! empty( $get_slider['custom_slider'][0] ) ) {
				$images = maybe_unserialize( $get_slider['custom_slider'][0] );
			}
		} elseif ( jannah_get_postdata( 'tie_post_gallery' ) ) {
			// Uploaded images.
			$images = maybe_unserialize( jannah_get_postdata( 'tie_post_gallery' ) );
		}

		if ( ! empty( $images ) && is_array( $images ) ) {

			$output = '<figure class="op-slideshow">';

			foreach ( $images as $image ) {

				$image = wp_get_attachment_image_src( $image['id'], 'full' );

				$output .= sprintf( '<figure><img src="%s" /></figure>', esc_url( $image[0] ) );

			}

			$output .= '</figure>';

		}
	}

	return $output . $content;
}

/**
 * If the Jannah theme is enabled add the filter in.
 *
 * @return void.
 */
function wpna_jannah_setup() {
	if ( function_exists( 'jannah_get_postdata' ) ) {
		add_filter( 'wpna_facebook_article_content_after_transform', 'wpna_jannah_add_post_format_content', 8, 1 );
	}
}
add_action( 'wpna_facebook_article_pre_the_content_transform', 'wpna_jannah_setup', 10, 0 );
