<?php
/**
 * Admin class
 *
 * @since 1.2.5
 * @package wp-native-articles
 */

/**
 * Registers and deals with custom shortcodes to be used in the plugin.
 *
 * Initially just the shortcode to show different content in WP or IA.
 *
 * @since  1.2.5
 */
class WPNA_Shortcodes extends WPNA_Admin_Base {

	/**
	 * Hooks registered in this class.
	 *
	 * This method is auto called from WPNA_Admin_Base.
	 *
	 * @since 1.2.5
	 * @todo Change meta box hook
	 *
	 * @access public
	 * @return void
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'register_wpna_shortcode' ), 10, 1 );

		add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', array( $this, 'add_ia_shortcode_override' ), 10, 2 );
	}

	/**
	 * Register the custom `wpna` shortcode tag with WP.
	 *
	 * @access public
	 * @return void
	 */
	public function register_wpna_shortcode() {
		add_shortcode( 'wpna', array( $this, 'do_shortcode_wp' ) );
	}

	/**
	 * Overrides the default shortcode function when in an IA.
	 *
	 * @access public
	 * @param  array  $override_tags Shortocde tags to override.
	 * @param  string $content       Current post content.
	 * @return array $override_tags
	 */
	public function add_ia_shortcode_override( $override_tags, $content ) {
		$override_tags['wpna'] = array( $this, 'do_shortcode_ia' );
		return $override_tags;
	}

	/**
	 * This method deals with the shortcode in a regular WordPress post.
	 *
	 * Checks to see if any content should be hidden.
	 *
	 * @access public
	 * @param  array  $atts Attributes passed to the shortcode.
	 * @param  string $content Any content between the shortcode tags.
	 * @return function do_shortcode()
	 */
	public function do_shortcode_wp( $atts, $content = '' ) {
		$atts = shortcode_atts( array(
			'hide' => '',
		), $atts, 'wpna' );

		// If the content should be hidden in WP then return an empty string.
		if ( 'wp' === strtolower( trim( $atts['hide'] ) ) ) {
			$content = '';
		}

		return do_shortcode( $content );
	}

	/**
	 * This method deals with the shortcode in a regular WordPress post.
	 *
	 * Checks to see if any content should be hidden.
	 *
	 * @access public
	 * @param  array  $atts Attributes passed to the shortcode.
	 * @param  string $content Any content between the shortcode tags.
	 * @return function do_shortcode()
	 */
	public function do_shortcode_ia( $atts, $content = '' ) {
		$atts = shortcode_atts( array(
			'hide' => '',
		), $atts, 'wpna' );

		// If the content should be hidden in IA then return an empty string.
		if ( 'ia' === strtolower( trim( $atts['hide'] ) ) ) {
			$content = '';
		}

		return do_shortcode( $content );
	}

}
