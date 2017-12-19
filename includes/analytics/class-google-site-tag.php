<?php
/**
 * Class for injecting Google Site Tag analytics into an article.
 *
 * @since  1.3.5
 * @package wp-native-articles
 */

/**
 * Class initalises itself at the end.
 * If the class is loaded it's assumed to be needed.
 * Analytics classes are registered in admin/facebook/class-admin-facebook-analytics.php.
 */
class WPNA_Analytics_Google_Site_Tag {

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
			'fbia_analytics_google_site_tag',
			'<label for="fbia_analytics_google_site_tag">' . esc_html__( 'Google Site Tag', 'wp-native-articles' ) . '</label>',
			array( $this, 'callback' ),
			$section_id,
			$section_id
		);

		add_settings_field(
			'fbia_analytics_google_site_tag_custom',
			null,
			'__return_empty_string',
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
		<h3><?php esc_html_e( 'Tracking ID', 'wp-native-articles' ); ?></h3>
		<input type="text" name="wpna_options[fbia_analytics_google_site_tag_custom]" id="fbia_analytics_google_site_tag_custom" placeholder="" class="regular-text" value="<?php echo esc_attr( wpna_get_option( 'fbia_analytics_google_site_tag_custom' ) ); ?>">
		<p class="description"><?php esc_html_e( 'Your Google Site Tag Tracking ID', 'wp-native-articles' ); ?></p>
		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_analytics_google_site_tag_custom' );
		?>

		<?php // For future compatibility, if we ever add other options in. ?>
		<input type="hidden" name="wpna_options[fbia_analytics_google_site_tag]" value="wpna_google_site_tag">

		<?php
	}

	/**
	 * Hooks into the article analytics code and appends this class analytics.
	 *
	 * @access public
	 * @param  string $analytics_code Analytics code to use in the article.
	 * @return string
	 */
	public function output( $analytics_code ) {

		// Check we want to use the manually set tag.
		if ( 'wpna_google_site_tag' === wpna_get_option( 'fbia_analytics_google_site_tag' ) ) {
			// Checks for post options, then global options then default.
			$ga_tracking_id = wpna_get_post_option( get_the_ID(), 'fbia_analytics_google_site_tag_custom' );

			if ( ! empty( $ga_tracking_id ) ) {
				// @codingStandardsIgnoreLine
				$analytics_code .= sprintf( "<script async src='https://www.googletagmanager.com/gtag/js?id=%s'></script>
				<script>
					window.dataLayer = window.dataLayer || [];
					function gtag(){dataLayer.push(arguments)};
					gtag('js', new Date());
					gtag('set', 'page_title', 'Instant Articles: '+ia_document.title);
					gtag('config', '%s');
				</script>", esc_js( $ga_tracking_id ), esc_js( $ga_tracking_id ) ) . PHP_EOL;
			}
		}

		return $analytics_code;
	}

}

// Initalise this class automatically.
new WPNA_Analytics_Google_Site_Tag();
