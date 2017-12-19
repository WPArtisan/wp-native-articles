<?php
/**
 * Class for injecting custom analytics into an article.
 *
 * @since  1.3.5
 * @package wp-native-articles
 */

/**
 * Class initalises itself at the end.
 * If the class is loaded it's assumed to be needed.
 * Analytics classes are registered in admin/facebook/class-admin-facebook-analytics.php.
 */
class WPNA_Analytics_Custom_Analytics {

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
		add_action( 'wpna_analytics_integrations_settings_fields', array( $this, 'setup_settings' ), 10, 1 );
		add_filter( 'wpna_facebook_post_analytics', array( $this, 'output' ), 10, 1 );
	}

	/**
	 * Adds in a settings fields for this analytics class.
	 *
	 * @access public
	 * @param  string $section_id Name of the analytics section in the WP Admin.
	 * @return void
	 */
	public function setup_settings( $section_id ) {
		add_settings_field(
			'fbia_analytics_custom',
			'<label for="fbia_analytics_custom">' . esc_html__( 'Custom Analytics', 'wp-native-articles' ) . '</label>',
			array( $this, 'callback' ),
			$section_id,
			$section_id
		);
	}

	/**
	 * Outputs the HTML for the settings field.
	 *
	 * @access public
	 * @return void
	 */
	public function callback() {
		?>
		<textarea name="wpna_options[fbia_analytics_custom]" rows="10" cols="50"  id="fbia_analytics_custom" class="large-text code"><?php echo esc_textarea( wpna_get_option( 'fbia_analytics_custom' ) ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Custom analytics code to be used. Auto wrapped in an iFrame. Available template tags:', 'wp-native-articles' ); ?>

			<?php $post_placeholders = wpna_get_post_placeholders(); ?>
			<?php foreach ( (array) $post_placeholders as $placeholder => $value ) : ?>
				<br />
				<?php echo esc_html( sprintf( '{%s}', $placeholder ) ); ?>
			<?php endforeach; ?>
		</p>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_analytics_custom' );
		?>

		<?php
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

		// Check for global analytics.
		$global_analytics = wpna_get_option( 'fbia_analytics_custom' );

		if ( ! empty( $global_analytics ) ) {
			// Format an append the analytics.
			$analytics_code .= PHP_EOL . $this->format_output( $global_analytics );
		}

		// Return the analytics code.
		return $analytics_code;
	}

}

// Initalise this class automatically.
new WPNA_Analytics_Custom_Analytics();
