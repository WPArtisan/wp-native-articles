<?php
/**
 * The Infogram embed returned by the plugin doesn't work in Instant Articles.
 * This overrides the default shortcode callback function and uses the default
 * Infrogram async script that does.
 *
 * @since 1.0.8
 * @package wp-native-articles
 */

add_filter( 'wpna_facebook_article_pre_the_content_filter', 'wpna_setup_infogram', 4, 1 );

if ( ! function_exists( 'wpna_setup_infogram' ) ) :

	/**
	 * Check if the Infogram shortcode exists or not.
	 * If it does, unregister it and re-register the wpna one.
	 * This is a filter not an action so return the content.
	 *
	 * @param  string $content The post content.
	 * @return string
	 */
	function wpna_setup_infogram( $content ) {

		// Check if the `infrogram` shortcode has been registered.
		if ( shortcode_exists( 'infogram' ) ) {
			// Remove it.
			remove_shortcode( 'infogram' );
			// Add our new shortcode in.
			add_shortcode( 'infogram', 'wpna_infogram_embed' );
		}
		return $content;
	}
endif;

if ( ! function_exists( 'wpna_infogram_embed' ) ) :

	/**
	 * Replaces the default Infogram shortcode generator which uses the JS async
	 * function to use the iFrame one.
	 *
	 * @todo This needs expanding. Bit basic at the moment.
	 * @param  array $atts Attributes passed to the shortcode.
	 * @return string
	 */
	function wpna_infogram_embed( $atts ) {

		$atts = shortcode_atts(
			array(
				'id'     => '',
				'prefix' => '',
				'format' => 'interactive',
			),
			$atts,
			'id'
		);

		if ( empty( $atts['id'] ) ) {
			return esc_html_e( 'id is required', 'wp-native-articles' );
		}

		// Embed an image.
		if ( ! empty( $atts['format'] ) && 'image' === $atts['format'] ) {
			$format = 'image';
		} else {
			$format = 'interactive';
		}

		// Construct the async JS.
		$output  = '<figure class="op-interactive">';
		$output .= '<iframe>';
		$output .= '<div class="infogram-embed" data-id="' . esc_attr( $atts['id'] ) . '" data-type="' . esc_attr( $format ) . '"></div>';
		$output .= '<script>!function(e,t,n,s){var i="InfogramEmbeds",o=e.getElementsByTagName(t),d=o[0],a=/^http:/.test(e.location)?"http:":"https:";if(/^\/{2}/.test(s)&&(s=a+s),window[i]&&window[i].initialized)window[i].process&&window[i].process();else if(!e.getElementById(n)){var r=e.createElement(t);r.async=1,r.id=n,r.src=s,d.parentNode.insertBefore(r,d)}}(document,"script","infogram-async","//e.infogr.am/js/dist/embed-loader-min.js");</script>';
		$output .= '</iframe>';
		$output .= '</figure>';

		// Grab a placement for this code.
		$placement_id = wpna_content_parser_get_placeholder( $output );

		return '<pre>' . $placement_id . '</pre>';

	}
endif;
