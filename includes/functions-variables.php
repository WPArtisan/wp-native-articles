<?php
/**
 * Functions that return some useful default variables.
 *
 * @author OzTheGreat
 * @since  1.1.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpna_get_font_sizes' ) ) :

	/**
	 * Returns valid font sizes for IA.
	 *
	 * Returns an array of possible font sizes for use in IAs.
	 *
	 * @link https://developers.facebook.com/docs/instant-articles/reference/caption#options
	 *
	 * @since 1.0.1
	 *
	 * @return array Valid font sizes
	 */
	function wpna_get_font_sizes() {
		$font_sizes = array(
			'op-small',
			'op-medium',
			'op-large',
			'op-extra-large',
		);

		/**
		 * Filter all the values before they're returned
		 *
		 * @since 1.0.1
		 *
		 * @param array $default Possible font sizes.
		 */
		$font_sizes = apply_filters( 'wpna_get_font_sizes', $font_sizes );

		return $font_sizes;
	}

endif;


if ( ! function_exists( 'wpna_get_vertical_alignments' ) ) :

	/**
	 * Returns valid vertical alignment positions for IAs.
	 *
	 * Vertical alignment is where the caption text is positioned in relation
	 * to the contain it is in. e.g. If it overlays the image this will position
	 * it within that image.
	 *
	 * @link https://developers.facebook.com/docs/instant-articles/reference/caption#options
	 *
	 * @since 1.0.1
	 *
	 * @return array Valid vertical alignments.
	 */
	function wpna_get_vertical_alignments() {
		$alignments = array(
			'op-vertical-bottom',
			'op-vertical-top',
			'op-vertical-center',
			'op-vertical-below',
			'op-vertical-above',
			'op-vertical-center',
		);

		/**
		 * Filter all the values before they're returned
		 *
		 * @since 1.0.1
		 *
		 * @param array $default Possible vertical alignments.
		 */
		$alignments = apply_filters( 'wpna_get_vertical_alignments', $alignments );

		return $alignments;
	}

endif;

if ( ! function_exists( 'wpna_get_horizontal_alignments' ) ) :

	/**
	 * Returns valid horizontal alignment positions for IAs.
	 *
	 * Horizontal alignment is where the caption text is positioned in relation
	 * to the contain it is in. e.g. If it overlays the image this will position
	 * it within that image.
	 *
	 * @link https://developers.facebook.com/docs/instant-articles/reference/caption#options
	 *
	 * @since 1.0.1
	 *
	 * @return array Valid horizontal alignments.
	 */
	function wpna_get_horizontal_alignments() {
		$alignments = array(
			'op-left',
			'op-center',
			'op-right',
		);

		/**
		 * Filter all the values before they're returned
		 *
		 * @since 1.0.1
		 *
		 * @param array $default Possible horizontal alignments.
		 */
		$alignments = apply_filters( 'wpna_get_horizontal_alignments', $alignments );

		return $alignments;
	}

endif;

if ( ! function_exists( 'wpna_get_switch_values' ) ) :

	/**
	 * Returns valid switch values for various options.
	 *
	 * Switch values are used a WordPress doesn't store 'false' in the DB
	 * but rather deletes the row making it would be hard to tell if a setting
	 * was being overriden or not.
	 *
	 * @since 1.1.0
	 *
	 * @return array Valid switch values.
	 */
	function wpna_get_switch_values() {
		$values = array(
			'on',
			'off',
		);

		/**
		 * Filter all the values before they're returned
		 *
		 * @since 1.0.1
		 *
		 * @param array $default Possible switch values.
		 */
		$values = apply_filters( 'wpna_get_switch_values', $values );

		return $values;
	}

endif;
