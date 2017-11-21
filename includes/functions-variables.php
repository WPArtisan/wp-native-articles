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

if ( ! function_exists( 'wpna_allowed_post_types' ) ) :

	/**
	 * Returns the default post types to convert to IA.
	 *
	 * These can be overriden in the RSS method through URL params.
	 *
	 * @since 1.3.0
	 *
	 * @return array Post types to sync.
	 */
	function wpna_allowed_post_types() {
		$values = array(
			'post',
		);

		/**
		 * Filter all the values before they're returned
		 *
		 * @since 1.3.0
		 *
		 * @param array $values Post types to convert.
		 */
		$values = apply_filters( 'wpna_allowed_post_types', $values );

		return $values;
	}

endif;

if ( ! function_exists( 'wpna_get_post_placeholders' ) ) :

	/**
	 * Returns placeholders with values that can be substituted.
	 *
	 * This jsut returns a key value array. The substitution needs to be
	 * applied elsewhere.
	 *
	 * @since 1.3.4
	 *
	 * @return array Keys with values that can be substituted.
	 */
	function wpna_get_post_placeholders() {
		$values = array(
			'post_title'          => get_the_title(),
			'post_id'             => get_the_ID(),
			'post_type'           => get_post_type(),
			'post_status'         => get_post_status(),
			'post_published_date' => get_post_time( get_option( 'date_format' ), true, get_the_ID(), false ),
			'post_modified_date'  => get_post_modified_time( get_option( 'date_format' ), true, get_the_ID(), false ),
			'post_author'         => get_the_author_meta( 'display_name' ),
			'post_author_id'      => get_the_author_meta( 'ID' ),
			'blog_id'             => get_current_blog_id(),
		);

		/**
		 * Filter all the values before they're returned
		 *
		 * @since 1.3.0
		 *
		 * @param array $values Post types to convert.
		 */
		$values = apply_filters( 'wpna_get_post_placeholders', $values );

		return $values;
	}

endif;
