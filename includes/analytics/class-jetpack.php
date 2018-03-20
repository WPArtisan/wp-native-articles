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
class WPNA_Analytics_Jetpack {

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
			'fbia_analytics_jetpack',
			'<label for="fbia_analytics_jetpack">' . esc_html__( 'Jetpack', 'wp-native-articles' ) . '</label>',
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
		<?php if ( ! class_exists( 'Jetpack' ) ) : ?>
			<p class="description"><?php esc_html_e( 'Jetpack not found.', 'wp-native-articles' ); ?></p>
		<?php elseif ( ! Jetpack::is_module_active( 'stats' ) ) : ?>
			<p class="description"><?php esc_html_e( 'Jetpack stats is not active.', 'wp-native-articles' ); ?></p>
		<?php else : ?>
			<p class="description"><?php esc_html_e( 'Jetpack Enabled', 'wp-native-articles' ); ?></p>
		<?php endif; ?>

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

		if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'stats' ) ) {

			$jetpack_code = sprintf( '<script>
				var x = new Image();
				x.src = "%s";
			</script>', esc_url( $this->jetpack_url() ) );

			$analytics_code .= $jetpack_code . PHP_EOL;
		}

		return $analytics_code;
	}

	/**
	 * Construct the URL for Jetpack stats tracking.
	 * Credits: https://github.com/Automattic/amp-wp/blob/6879e7d98e804924b0567baa77d9d189890a991d/jetpack-helper.php#L51
	 *
	 * @access public
	 * @return string
	 */
	public function jetpack_url() {
		global $wp_the_query;

		// Added in https://github.com/Automattic/jetpack/pull/3445.
		if ( function_exists( 'stats_build_view_data' ) ) {
			$data = stats_build_view_data();
		} else {
			$blog = Jetpack_Options::get_option( 'id' );
			$tz   = get_option( 'gmt_offset' );
			$v    = 'ext';

			$blog_url = wpna_parse_url( site_url() );
			$srv      = $blog_url['host'];
			$j        = sprintf( '%s:%s', JETPACK__API_VERSION, JETPACK__VERSION );
			$post     = $wp_the_query->get_queried_object_id();
			$data     = compact( 'v', 'j', 'blog', 'post', 'tz', 'srv' );
		}

		if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
			// @codingStandardsIgnoreLine
			$data['host'] = rawurlencode( $_SERVER['HTTP_HOST'] );
		}
		$data['rand'] = 'RANDOM'; // ia placeholder.
		$data['ref']  = 'DOCUMENT_REFERRER'; // ia placeholder.

		// Encode the data ready for the URL.
		$data = array_map( 'rawurlencode', $data );

		return add_query_arg( $data, 'https://pixel.wp.com/g.gif' );
	}

}

// Initalise this class automatically.
new WPNA_Analytics_Jetpack();
