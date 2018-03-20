<?php
/**
 * FV Player Compatibility.
 *
 * @link https://en-gb.wordpress.org/plugins/fv-wordpress-flowplayer/
 * @since 1.2.4.
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_fvplayer_add_override_shortcodes' ) ) :

	/**
	 * Override the `quads` shortcode in IA.
	 *
	 * @param array  $override_tags Shortocde tags to override.
	 * @param string $content       Current post content.
	 * @return array $override_tags
	 */
	function wpna_fvplayer_add_override_shortcodes( $override_tags, $content ) {
		$override_tags['fvplayer'] = 'wpna_fvplayer_shortcode_override';
		return $override_tags;
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', 'wpna_fvplayer_add_override_shortcodes', 10, 2 );

if ( ! function_exists( 'wpna_fvplayer_shortcode_override' ) ) :

	/**
	 * Wraps [fvplayer] shortcodes.
	 *
	 * Ensures they're always properly formatted and don't get caught up
	 * in the other parts of the content parser.
	 *
	 * @param array $attr Shortcode attributes.
	 * @return string
	 */
	function wpna_fvplayer_shortcode_override( $attr ) {
		global $post, $fv_fp;

		// If the global flowplay class isn't there, bail.
		if ( ! $fv_fp ) {
			return;
		}

		// Get the global flowplayer options.
		$flowplayer_options = get_option( 'fvwpflowplayer' );

		// Setup the shortcodes with some defaults.
		$atts = shortcode_atts( array(
			'src'            => '',
			'src1'           => '',
			'src2'           => '',
			'mobile'         => null,
			'splash'         => '',
			'caption'        => '',
			'autoplay'       => null,
			'loop'           => null,
			'disablesharing' => null,
		), $attr, 'fvplayer' );

		$output = '';

		// Workout autoplay value.
		$autoplay = true;

		if ( ! empty( $atts['autoplay'] ) ) {
			$autoplay = 'false' !== $atts['autoplay'];
		} elseif ( ! empty( $flowplayer_options['autoplay'] ) ) {
			$autoplay = 'false' !== $flowplayer_options['autoplay'];
		}

		// Parse the URL.
		$parsed_url = wpna_parse_url( $atts['src'] );

		$video_id = null;

		// First check if it's a YouTube video.
		if ( isset( $parsed_url['host'] ) && in_array( $parsed_url['host'], array( 'www.youtube.com', 'youtube.com', 'youtu.be' ), true ) ) {

			if ( isset( $parsed_url['path'] ) ) {

				// Format: https://www.youtube.com/watch?v=-PVNKvIDRwI.
				if ( false !== strripos( $parsed_url['path'], 'watch' ) ) {
					parse_str( $parsed_url['query'], $parsed_query );
					$video_id = ! empty( $parsed_query['v'] ) ? $parsed_query['v'] : null;
				} elseif ( false !== strripos( $parsed_url['path'], 'embed' ) ) {
					// Format: https://www.youtube.com/embed/-PVNKvIDRwI.
					$video_id = str_ireplace( 'embed', '', $parsed_url['path'] );
				} else {
					// Format: https://youtu.be/-PVNKvIDRwI.
					$video_id = $parsed_url['path'];
				}
			}

			$video_id = trim( $video_id, '/' );

			// Construct the correct embed URL.
			$url = sprintf( 'https://www.youtube.com/embed/%s', $video_id );

			// Output the valid iFrame.
			$output = PHP_EOL . sprintf( '<iframe width="560" height="315" src="%s"></iframe>', esc_url( $url ) ) . PHP_EOL;

			// Check if it's a Vimeo video.
			// Possible formats:
			// https://vimeo.com/225479756.
			// https://player.vimeo.com/video/225479756.
			// https://vimeo.com/groups/timelapsevideos/videos/201545098.
			// https://vimeo.com/channels/musicvideoland/225008552/.
		} elseif ( isset( $parsed_url['host'] ) && in_array( $parsed_url['host'], array( 'www.vimeo.com', 'vimeo.com', 'player.vimeo.com' ), true ) ) {

			// Vimeo videos are easy. The last part of the path is always the video ID.
			$path_parts = explode( '/', $parsed_url['path'] );

			// Remove empty elements incase the URL ends with a slash.
			$path_parts = array_filter( $path_parts );

			// Video ID is the last part of the path.
			$video_id = array_pop( $path_parts );

			// Construct the correct embed URL.
			$url = sprintf( 'https://player.vimeo.com/video/%s', $video_id );

			// Output the valid iFrame.
			$output = PHP_EOL . sprintf( '<iframe width="560" height="315" src="%s"></iframe>', esc_url( $url ) ) . PHP_EOL;

			// It's a regular video. Embed as normal.
		} else {

			$splash = false;

			// First check if a splash was set for this video.
			if ( ! empty( $atts['splash'] ) ) {
				// Get the img url.
				$ig_src = flowplayer::get_encoded_url( $splash );

				// This is obviously less than ideal as it can be slow.
				$response      = wp_remote_head( $ig_src );
				$response_code = wp_remote_retrieve_response_code( $response );

				// The image exists.
				if ( 200 === $response_code ) {
					$splash = $ig_src;
				}
			}

			// If no splash was set for this video or it can't be found try the global.
			if ( ! $splash && ! empty( $flowplayer_options['splash'] ) ) {
				// Get the img url.
				$ig_src = flowplayer::get_encoded_url( $flowplayer_options['splash'] );

				// This is obviously less than ideal as it can be slow.
				$response      = wp_remote_head( $ig_src );
				$response_code = wp_remote_retrieve_response_code( $response );

				// The image exists.
				if ( 200 === $response_code ) {
					$splash = $ig_src;
				}
			}

			// If all the splash checks pass.
			if ( $splash ) {
				$output .= '<img src="' . $splash . '" />' . PHP_EOL;
			}

			// Start the video element.
			$output .= '<video';

			// Check for Autoplay.
			if ( ! $autoplay ) {
				$output .= ' data-fb-disable-autoplay';
			}

			// Check for loop.
			// These options are overrides, can't combine unfortunatly.
			if ( ! empty( $atts['loop'] ) ) {
				if ( 'true' === $atts['loop'] ) {
					$output .= ' loop';
				}
			} elseif ( ! empty( $flowplayer_options['loop'] ) ) {
				if ( 'true' === $flowplayer_options['loop'] ) {
					$output .= ' loop';
				}
			}

			// Close the video element.
			$output .= '>' . PHP_EOL;

			// If a mobile source has been set then use that.
			if ( ! empty( $atts['mobile'] ) ) {
				$output .= $fv_fp->get_video_src( $atts['mobile'], array( 'mobileUserAgent' => true ) );
			} else {
				// Get the src elements for each file.
				foreach ( apply_filters( 'fv_player_media', array( $atts['src'], $atts['src1'], $atts['src2'] ), $fv_fp ) as $media_item ) {
					$output .= $fv_fp->get_video_src( $media_item, array( 'mobileUserAgent' => true ) );
				}
			}

			// Close the video tag.
			$output .= '</video>' . PHP_EOL;

			// Check for caption.
			if ( ! empty( $atts['caption'] ) ) {

				// Get the caption options.
				$caption_settings = array_filter( array(
					wpna_get_post_option( get_the_ID(), 'fbia_caption_font_size', null ),
					wpna_get_post_option( get_the_ID(), 'fbia_caption_vertical_position', null ),
					wpna_get_post_option( get_the_ID(), 'fbia_caption_horizontal_position', null ),
				) );

				// Get the caption title options.
				$caption_title_settings = array_filter( array(
					wpna_get_post_option( get_the_ID(), 'fbia_caption_title_font_size', null ),
					wpna_get_post_option( get_the_ID(), 'fbia_caption_title_vertical_position', null ),
					wpna_get_post_option( get_the_ID(), 'fbia_caption_title_horizontal_position', null ),
				) );

				$output .= '<figcaption';
				if ( ! empty( $caption_settings ) ) {
					$output .= ' class="' . implode( ', ', $caption_settings ) . '"';
				}
				$output .= '>' . PHP_EOL;

				$output .= '<h1';
				if ( ! empty( $caption_title_settings ) ) {
					$output .= ' class="' . implode( ', ', $caption_title_settings ) . '"';
				}
				$output .= '>' . $atts['caption'] . '</h1>' . PHP_EOL;
				$output .= '</figcaption>' . PHP_EOL;
			}
		}

		// Work out whether we want share buttons or not.
		$figure = '<figure';

		// If it's a YouTube or Vimeo video then we want it interactive.
		if ( $video_id ) {
			$figure .= ' class="op-interactive"';
		}

		// These options are overrides, can't combine unfortunatly.
		if ( ! empty( $atts['disablesharing'] ) ) {
			if ( 'false' === $atts['disablesharing'] ) {
				$figure .= ' data-feedback="fb:likes, fb:comments"';
			}
		} elseif ( ! empty( $flowplayer_options['disablesharing'] ) ) {
			if ( 'false' === $flowplayer_options['disablesharing'] ) {
				$figure .= ' data-feedback="fb:likes, fb:comments"';
			}
		}

		$figure .= '>';

		$output = $figure . $output . '</figure>';

		// Grab a placement for this code.
		$placement_id = wpna_content_parser_get_placeholder( $output );

		return '<pre>' . $placement_id . '</pre>';
	}
endif;
