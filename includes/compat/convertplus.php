<?php
/**
 * Ensures that the ConvertPlus models get correctly parsed.
 *
 * @since 1.2.5
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_convert_plus_override_tags' ) ) :

	/**
	 * Override the following tags with custom output functions.
	 *
	 * @param  array  $override_tags Shortcode functions to override.
	 * @param  string $content      Current post content.
	 * @return array  Shortcodes to ignore
	 */
	function wpna_convert_plus_override_tags( $override_tags, $content ) {

		$override_tags['cp_modal'] = 'wpna_cp_modal_shortcode';

		return $override_tags;
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', 'wpna_convert_plus_override_tags', 10, 2 );

if ( ! function_exists( 'wpna_cp_modal_shortcode' ) ) :

	/**
	 * Shortcode override cp_modal.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_cp_modal_shortcode( $atts, $content = '' ) {
		// @codingStandardsIgnoreLine
		extract( shortcode_atts( array(
			'id'      => '',
			'display' => '',
		), $atts ) );

		$output = '<figure class="op-interactive">';

		// Wrap it in an iFrame so we can achieve the desired effect.
		// We can't do model popups on IA so juut link the content to the original post.
		$output .= '<iframe class="no-margin" scrolling="no" frameborder="0" allowTransparency="true">' . PHP_EOL;
		$output .= sprintf( '<a target="_blank" href="%s">', esc_url( get_the_permalink() ) ) . PHP_EOL;
		$output .= $content . PHP_EOL;
		$output .= '</a>' . PHP_EOL;
		$output .= '</iframe>' . PHP_EOL;
		$output .= '</figure>';

		// Grab a placement for this code.
		$placement_id = wpna_content_parser_get_placeholder( $output );

		return '<pre>' . $placement_id . '</pre>';
	}
endif;
