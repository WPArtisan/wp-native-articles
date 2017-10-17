<?php
/**
 * Ensures that the EasyAzon plugin shortcode get correctly parsed.
 *
 * @since 1.3.0
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_easyazon_override_tags' ) ) :

	/**
	 * Override the following tags with custom output functions.
	 *
	 * @param  array  $override_tags Shortcode functions to override.
	 * @param  string $content      Current post content.
	 * @return array  Shortcodes to ignore
	 */
	function wpna_easyazon_override_tags( $override_tags, $content ) {

		if ( defined( 'EASYAZONPRO_SHORTCODE_CTA' ) ) {
			$override_tags[ EASYAZONPRO_SHORTCODE_CTA ] = 'wpna_easyazon_cta_shortcode';
		}
		if ( defined( 'EASYAZONPRO_SHORTCODE_CTA_LEGACY' ) ) {
			$override_tags[ EASYAZONPRO_SHORTCODE_CTA_LEGACY ] = 'wpna_easyazon_cta_shortcode';
		}
		if ( defined( 'EASYAZONPRO_SHORTCODE_IMAGE' ) ) {
			$override_tags[ EASYAZONPRO_SHORTCODE_IMAGE ] = 'wpna_easyazon_image_shortcode';
		}
		if ( defined( 'EASYAZONPRO_SHORTCODE_IMAGE_LEGACY' ) ) {
			$override_tags[ EASYAZONPRO_SHORTCODE_IMAGE_LEGACY ] = 'wpna_easyazon_image_shortcode';
		}
		if ( defined( 'EASYAZONPRO_SHORTCODE_IMAGE_LEGACY_ADDITIONAL' ) ) {
			$override_tags[ EASYAZONPRO_SHORTCODE_IMAGE_LEGACY_ADDITIONAL ] = 'wpna_easyazon_image_shortcode';
		}
		if ( defined( 'EASYAZONPRO_SHORTCODE_IMAGE_LEGACY_SIMPLEAZON' ) ) {
			$override_tags[ EASYAZONPRO_SHORTCODE_IMAGE_LEGACY_SIMPLEAZON ] = 'wpna_easyazon_image_shortcode';
		}
		if ( defined( 'EASYAZONPRO_SHORTCODE_INFO_BLOCK' ) ) {
			$override_tags[ EASYAZONPRO_SHORTCODE_INFO_BLOCK ] = 'wpna_easyazon_info_block_shortcode';
		}
		if ( defined( 'EASYAZONPRO_SHORTCODE_INFO_BLOCK_LEGACY' ) ) {
			$override_tags[ EASYAZONPRO_SHORTCODE_INFO_BLOCK_LEGACY ] = 'wpna_easyazon_info_block_shortcode';
		}
		if ( defined( 'EASYAZONPRO_SHORTCODE_INFO_BLOCK_LEGACY_ADDITIONAL' ) ) {
			$override_tags[ EASYAZONPRO_SHORTCODE_INFO_BLOCK_LEGACY_ADDITIONAL ] = 'wpna_easyazon_info_block_shortcode';
		}

		return $override_tags;
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', 'wpna_easyazon_override_tags', 10, 2 );

if ( ! function_exists( 'wpna_easyazon_cta_shortcode' ) ) :

	/**
	 * Shortcode override cp_modal.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_easyazon_cta_shortcode( $atts, $content = '' ) {
		$shortcode_content = EasyAzonPro_Components_Shortcodes_CTA::shortcode( $atts, $content );

		// It can return an empty string, bail if it does.
		if ( empty( $shortcode_content ) ) {
			return '';
		}

		$output = '<figure class="op-interactive">' . PHP_EOL;

		// Wrap it in an iFrame so we can achieve the desired effect.
		$output .= '<iframe class="no-margin">' . PHP_EOL;
		$output .= '<style>* {text-align: center;}</style>';
		$output .= $shortcode_content . PHP_EOL;
		$output .= '</iframe>' . PHP_EOL;
		$output .= '</figure>';

		// Grab a placement for this code.
		$placement_id = wpna_content_parser_get_placeholder( $output );

		return '<pre>' . $placement_id . '</pre>';
	}
endif;

if ( ! function_exists( 'wpna_easyazon_image_shortcode' ) ) :

	/**
	 * Shortcode override cp_modal.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_easyazon_image_shortcode( $atts, $content = '' ) {
		// @codingStandardsIgnoreLine
		extract( shortcode_atts( array(
			'align'  => '',
			'height' => '',
			'width'  => '',
		), $atts ) );

		$shortcode_content = EasyAzonPro_Components_Shortcodes_Image::shortcode( $atts, $content );

		// It can return an empty string, bail if it does.
		if ( empty( $shortcode_content ) ) {
			return '';
		}

		$output = '<figure class="op-interactive">';

		// Wrap it in an iFrame so we can achieve the desired effect.
		if ( $height && $width ) {
			if ( $width < 360 ) {
				$width = 360;
			}
			$output .= '<iframe class="no-margin" height="' . $height . '" width="' . $width . '">' . PHP_EOL;
		} else {
			$output .= '<iframe class="no-margin">' . PHP_EOL;
		}
		// This ensure large images display correctly.
		$output .= '<style>* {text-align: center;} img { width: auto; height: auto; max-width: 100%; max-height: 100%; }</style>' . PHP_EOL;
		$output .= $shortcode_content . PHP_EOL;
		$output .= '</iframe>' . PHP_EOL;
		$output .= '</figure>';

		// Grab a placement for this code.
		$placement_id = wpna_content_parser_get_placeholder( $output );

		return '<pre>' . $placement_id . '</pre>';
	}
endif;

if ( ! function_exists( 'wpna_easyazon_info_block_shortcode' ) ) :

	/**
	 * Shortcode override cp_modal.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_easyazon_info_block_shortcode( $atts, $content = '' ) {
		$shortcode_content = EasyAzonPro_Components_Shortcodes_InfoBlock::shortcode( $atts, $content );

		// It can return an empty string, bail if it does.
		if ( empty( $shortcode_content ) ) {
			return '';
		}

		$output = '<figure class="op-interactive">';

		// Wrap it in an iFrame so we can achieve the desired effect.
		$output .= '<iframe class="no-margin" width="360">' . PHP_EOL;
		$output .= '<style>* {font-family: Helvetica, sans-serif;font-size: 1rem;} .easyazon-attribute-name {text-align:left;}</style>';
		$output .= $shortcode_content . PHP_EOL;
		$output .= '</iframe>' . PHP_EOL;
		$output .= '</figure>' . PHP_EOL;

		// Grab a placement for this code.
		$placement_id = wpna_content_parser_get_placeholder( $output );

		return '<pre>' . $placement_id . '</pre>';
	}
endif;
