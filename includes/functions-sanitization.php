<?php
/**
 * Helper functions for sanitization
 *
 * @author OzTheGreat
 * @since  1.0.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpna_sanitize_options' ) ) :

	/**
	 * Setups sanitization for the options passed.
	 *
	 * For every option passed creates a filter that can be hooked into for
	 * validating. Once done gets the current global options and merges them
	 * into them so they're not lost.
	 *
	 * @since 1.0.0
	 *
	 * @param array $values {
	 *     Array of values to sanitize
	 *     e.g.
	 *         'fbia_app_id' => '000000000000'
	 * }.
	 * @return array The values passed merged into the global values.
	 */
	function wpna_sanitize_options( $values ) {
		$wpna_options = (array) wpna_get_options();

		$sanitized_values = array();

		foreach ( $values as $key => $value ) {

			/**
			 * DEPRECATED.
			 *
			 * The old sanitization hook.
			 * apply_filters_deprecated() was only introduced in 4.6.
			 *
			 * @since 1.0.0
			 *
			 * @param mixed  $value  The value to sanitize.
			 * @param string $key    The option name.
			 * @param array  $values All options.
			 */
			if ( function_exists( 'apply_filters_deprecated' ) ) {
				// @codingStandardsIgnoreLine.
				$sanitized_values[ $key ] = apply_filters_deprecated( 'wpna_sanitize_option-' . $key, array( $value, $key, $values ), '1.1.0', 'wpna_sanitize_option_' . $key );
			}

			/**
			 * Sanitize the an options value
			 *
			 * @since 1.1.0
			 *
			 * @param mixed  $value  The value to sanitize.
			 * @param string $key    The option name.
			 * @param array  $values All options.
			 */
			$sanitized_values[ $key ] = apply_filters( 'wpna_sanitize_option_' . $key, $value, $key, $values );

		}

		// Merge them in with any existing options.
		$options = array_merge( $wpna_options, $sanitized_values );

		return $options;
	}
endif;

if ( ! function_exists( 'wpna_switchval' ) ) :

	/**
	 * Validates a switch value.
	 *
	 * Due to WordPress limitations in storing true / false we use
	 * on / off instead. This validates that an option is equal to on / off.
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed $value The value to sanitize.
	 * @return string       Either on / off.
	 */
	function wpna_switchval( $value ) {
		return 'on' === $value ? 'on' : 'off';
	}
endif;

if ( ! function_exists( 'wpna_to_switch' ) ) :

	/**
	 * Takes a value and converts it to a switch value.
	 *
	 * Due to WordPress limitations in storing true / false we use
	 * on / off instead. This takes a value and converts it to on / off.
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed $value The value to sanitize.
	 * @return string       Either on / off.
	 */
	function wpna_to_switch( $value ) {
		return $value ? 'on' : 'off';
	}
endif;

if ( ! function_exists( 'wpna_switch_to_boolean' ) ) :

	/**
	 * Converts a switch value to a boolean.
	 *
	 * Due to WordPress limitations in storing true / false we use
	 * on / off instead.  Takes a switch value (on / off) and converts
	 * it to a boolean.
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed $value The value to sanitize.
	 * @return bool  on == true / off == false.
	 */
	function wpna_switch_to_boolean( $value ) {
		return 'on' === $value ? true : false;
	}
endif;

if ( ! function_exists( 'wpna_sanitize_unsafe_html' ) ) :

	/**
	 * Try and sanitize dangerous HTML.
	 *
	 * Some fields (analytics & ad code specifically) can really allow almost
	 * all HTML including <script> and <iframe> tags. This tries to sanitize
	 * this as much as possible.
	 *
	 * @since 1.1.3
	 *
	 * @param  string $value The HTML to sanitize.
	 * @return string The sanitized HTML.
	 */
	function wpna_sanitize_unsafe_html( $value ) {
		// Get the allowed HTML for kses.
		$allowed_html = wp_kses_allowed_html( 'post' );

		// Add the script tag in.
		$allowed_html['script'] = array(
			'type'   => true,
			'src'    => true,
			'height' => true,
			'width'  => true,
		);

		// Add the iframe permission in.
		$allowed_html['iframe'] = array(
			'align'        => true,
			'width'        => true,
			'height'       => true,
			'frameborder'  => true,
			'name'         => true,
			'src'          => true,
			'id'           => true,
			'class'        => true,
			'style'        => true,
			'scrolling'    => true,
			'marginwidth'  => true,
			'marginheight' => true,
		);

		return wp_kses( $value, $allowed_html );
	}
endif;

if ( ! function_exists( 'wpna_valid_date' ) ) :

	/**
	 * Validates a date in the Y-m-d format.
	 *
	 * Ensures that a date exists and is valid. Needs to be the in the
	 * yyyy-mm-dd format.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $date A date string in Y-m-d format.
	 * @return boolean      Whether the date is valid or not.
	 */
	function wpna_valid_date( $date ) {
		list( $year, $month, $day ) = sscanf( $date, '%04d-%02d-%02d' );
		return checkdate( $month, $day, $year );
	}
endif;

if ( ! function_exists( 'wpna_validate_font_size' ) ) :

	/**
	 * Validates a font size.
	 *
	 * Checks that the passed variable exactly matches one of the ones
	 * it should be.
	 *
	 * @since 1.1.0
	 *
	 * @param  string $variable The variable to check.
	 * @return mixed
	 */
	function wpna_validate_font_size( $variable ) {
		// Get the font sizes.
		$font_sizes = wpna_get_font_sizes();

		// Check it exists as expected.
		if ( in_array( $variable, $font_sizes, true ) ) {
			return $variable;
		} else {
			return null;
		}
	}
endif;

if ( ! function_exists( 'wpna_validate_vertical_alignment' ) ) :

	/**
	 * Validates a vertical alignment.
	 *
	 * Checks that the passed variable exactly matches one of the ones
	 * it should be.
	 *
	 * @since 1.1.0
	 *
	 * @param  string $variable The variable to check.
	 * @return mixed
	 */
	function wpna_validate_vertical_alignment( $variable ) {
		// Get the alignments.
		$alignments = wpna_get_vertical_alignments();

		// Check it exists as expected.
		if ( in_array( $variable, $alignments, true ) ) {
			return $variable;
		} else {
			return null;
		}
	}
endif;

if ( ! function_exists( 'wpna_validate_horizontal_alignment' ) ) :

	/**
	 * Validates a horizontal alignment.
	 *
	 * Checks that the passed variable exactly matches one of the ones
	 * it should be.
	 *
	 * @since 1.1.0
	 *
	 * @param  string $variable The variable to check.
	 * @return mixed
	 */
	function wpna_validate_horizontal_alignment( $variable ) {
		// Get the alignments.
		$alignments = wpna_get_horizontal_alignments();

		// Check it exists as expected.
		if ( in_array( $variable, $alignments, true ) ) {
			return $variable;
		} else {
			return null;
		}
	}
endif;
