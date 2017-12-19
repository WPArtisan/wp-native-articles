<?php
/**
 * Class for overriding analytics for a particular post.
 *
 * @since  1.3.5
 * @package wp-native-articles
 */

/**
 * Class initalises itself at the end.
 * If the class is loaded it's assumed to be needed.
 * Analytics classes are registered in admin/facebook/class-admin-facebook-analytics.php.
 */
class WPNA_Analytics_Custom_Post_Analytics {

	/**
	 * Constructor.
	 *
	 * Triggers the hooks method straight away.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Hooks registered in this class.
	 *
	 * @access public
	 * @return void
	 */
	public function hooks() {
		// Note: Hooking in later than usual.
		add_filter( 'wpna_facebook_post_analytics', array( $this, 'output' ), 15, 1 );
	}

	/**
	 * Format an analytics string ready for output.
	 *
	 * @access public
	 * @param  string $analytics Custom analytics string.
	 * @return string
	 */
	public function format_output( $analytics ) {
		// It may or may not be wrapped in figure tags. Ensure it isn't.
		$analytics = str_ireplace( array( '<figure class="op-tracker">', '</figure>' ), '', $analytics );

		$post_placeholders = wpna_get_post_placeholders();

		// Replace all possible post placeholders.
		foreach ( $post_placeholders as $placeholder => $replacement ) {
			$analytics = str_replace( sprintf( '{%s}', esc_js( $placeholder ) ), $replacement, $analytics );
		}

		return $analytics;
	}

	/**
	 * Hooks into the article analytics code and appends this class analytics.
	 *
	 * @access public
	 * @param  string $analytics_code Analytics code to use in the article.
	 * @return string
	 */
	public function output( $analytics_code ) {

		// Check for analytics overridden on a per post basis.
		$post_analytics = get_post_meta( get_the_ID(), '_wpna_fbia_analytics', true );

		if ( ! empty( $post_analytics ) ) {
			// Format an append the analytics.
			$analytics_code .= PHP_EOL . $this->format_output( $post_analytics );
		}

		// Return the analytics code.
		return $analytics_code;
	}

}

// Initalise this class automatically.
new WPNA_Analytics_Custom_Post_Analytics();
