<?php
/**
 * Ensures that the Easy Video Player videos get converted.
 *
 * @since 1.3.0
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_evp_override_tags' ) ) :

	/**
	 * Override the following tags with custom output functions.
	 *
	 * @param  array  $override_tags Shortcode functions to override.
	 * @param  string $content      Current post content.
	 * @return array  Shortcodes to ignore
	 */
	function wpna_evp_override_tags( $override_tags, $content ) {

		$override_tags['evp_embed_video'] = 'wpna_evp_shortcode_override';

		return $override_tags;
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', 'wpna_evp_override_tags', 10, 2 );

if ( ! function_exists( 'wpna_evp_shortcode_override' ) ) :

	/**
	 * Wraps [$override_tags] shortcodes.
	 *
	 * Ensures they're always properly formatted and don't get caught up
	 * in the other parts of the content parser.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	function wpna_evp_shortcode_override( $atts ) {
		// @codingStandardsIgnoreLine
		extract( shortcode_atts( array(
			'url'      => '',
			'width'    => '',
			'height'   => '',
			'ratio'    => '0.417',
			'autoplay' => false,
			'poster'   => '',
			'loop'     => false,
			'muted'    => '',
			'preload'  => 'metadata',
			'share'    => 'true',
			'video_id' => '',
			'class'    => '',
			'template' => '',
		), $atts ) );

		$video_attrs = '';

		// If autoplay has been disabled.
		if ( ! $autoplay ) {
			$video_attrs .= ' data-fb-disable-autoplay';
		}

		// If loop has been disabled.
		if ( $loop ) {
			$video_attrs .= ' loop';
		}

		// Format the output.
		$output  = '<figure data-feedback="fb:likes, fb:comments">' . PHP_EOL;
		$output .= sprintf( '<video%s>', $video_attrs ) . PHP_EOL;
		$output .= sprintf( '<source src="%s" type="video/mp4" />', esc_url( $url ) ) . PHP_EOL;
		$output .= '</video>' . PHP_EOL;
		$output .= '</figure>' . PHP_EOL;

		// Grab a placement for this code.
		$placement_id = wpna_content_parser_get_placeholder( $output );

		return '<pre>' . $placement_id . '</pre>';
	}
endif;
