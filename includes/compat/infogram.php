<?php

add_filter( 'wpna_facebook_article_pre_the_content_filter', 'wpna_setup_infogram', 4, 1 );

/**
 * Check if the Infogram shortcode exists or not.
 * If it does, unregister it and re-register the wpna one.
 * This is a filter not an action so return the content.
 *
 * @param  string $content
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
		), $atts, 'id' );

	if ( empty( $atts['id'] ) ){
		return esc_html_e( 'id is required', 'wp-native-articles' );
	}

	// Embed an image
	if ( ! empty( $atts['format'] ) && 'image' === $atts['format'] ) {
		$format = 'image';
	} else {
		$format = 'interactive';
	}

	// Construct the async JS.
	$embed_code = '<iframe>';
	$embed_code .= '<div class="infogram-embed" data-id="' . esc_attr( $atts['id'] ) . '" data-type="' . esc_attr( $format ) . '"></div>';
	$embed_code .= '<script>!function(e,t,n,s){var i="InfogramEmbeds",o=e.getElementsByTagName(t),d=o[0],a=/^http:/.test(e.location)?"http:":"https:";if(/^\/{2}/.test(s)&&(s=a+s),window[i]&&window[i].initialized)window[i].process&&window[i].process();else if(!e.getElementById(n)){var r=e.createElement(t);r.async=1,r.id=n,r.src=s,d.parentNode.insertBefore(r,d)}}(document,"script","infogram-async","//e.infogr.am/js/dist/embed-loader-min.js");</script>';
	$embed_code .= '</iframe>';

	return $embed_code;
}