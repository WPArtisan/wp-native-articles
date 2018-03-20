<?php
/**
 * Class for injecting chartbeat analytics into an article.
 *
 * @since  1.3.5
 * @package wp-native-articles
 */

/**
 * Class initalises itself at the end.
 * If the class is loaded it's assumed to be needed.
 * Analytics classes are registered in admin/facebook/class-admin-facebook-analytics.php.
 */
class WPNA_Analytics_Chartbeat {

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
			'fbia_analytics_chartbeat',
			'<label for="fbia_analytics_chartbeat">' . esc_html__( 'Chartbeat', 'wp-native-articles' ) . '</label>',
			array( $this, 'callback' ),
			$section_id,
			$section_id
		);
		// Field for the custom chartbeat account id.
		add_settings_field(
			'fbia_analytics_chartbeat_custom',
			null,
			'__return_null',
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
		<?php if ( function_exists( 'chartbeat_fbia_analytics' ) ) : ?>
			<div>
				<h3>
					<?php esc_html_e( 'Chartbeat Plugin', 'wp-native-articles' ); ?>
				</h3>
				<?php
				$chartbeat_userid = get_option( 'chartbeat_userid' );
				?>
				<?php if ( ! $chartbeat_userid ) : ?>
					<p>
						<?php echo sprintf(
							wp_kses(
								// translators: Placeholder is the URL to the plugin.
								__( 'Chartbeat Plugin found but no <a target="_blank" href="%s">Account ID</a> has been set.', 'wp-native-articles' ),
								array(
									'a' => array(
										'href'   => array(),
										'target' => array(),
									),
								)
							),
							esc_url( admin_url( '/options-general.php?page=chartbeat-options' ) )
						); ?>
					</p>
				<?php else : ?>
					<p><strong><?php esc_html_e( 'Account ID: ', 'wp-native-articles' ); ?></strong> <code><?php echo esc_html( $chartbeat_userid ); ?></code></p>
					<br />
					<label>
						<input type="radio" name="wpna_options[fbia_analytics_chartbeat]" id="fbia_analytics_chartbeat" class="" value="chartbeat_plugin" <?php checked( 'chartbeat_plugin', wpna_get_option( 'fbia_analytics_chartbeat' ) ); ?> />
						<?php esc_html_e( 'Use this Account ID', 'wp-native-articles' ); ?>
					</label>
				<?php endif; ?>
			</div>
		<?php endif; ?>


		<div>
			<h3><?php esc_html_e( 'Manual Account ID', 'wp-native-articles' ); ?></h3>
			<input type="text" name="wpna_options[fbia_analytics_chartbeat_custom]" id="fbia_analytics_chartbeat_custom" placeholder="" class="regular-text" value="<?php echo esc_attr( wpna_get_option( 'fbia_analytics_chartbeat_custom' ) ); ?>">
			<p class="description"><?php esc_html_e( 'Chartbeat Account ID', 'wp-native-articles' ); ?></p>
			<br />
			<label>
				<input type="radio" name="wpna_options[fbia_analytics_chartbeat]" id="fbia_analytics_chartbeat" class="" value="wpna_chartbeat" <?php checked( 'wpna_chartbeat', wpna_get_option( 'fbia_analytics_chartbeat' ) ); ?> />
				<?php esc_html_e( 'Use this Account ID', 'wp-native-articles' ); ?>
			</label>
			<?php
			// Show a notice if the option has been overridden.
			wpna_option_overridden_notice( 'fbia_analytics_chartbeat' );
			?>
		</div>

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

		// Get the account type to use.
		$chatbeat_source = wpna_get_option( 'fbia_analytics_chartbeat' );

		if ( 'chartbeat_plugin' === $chatbeat_source && function_exists( 'chartbeat_fbia_analytics' ) ) {

			// $user_id is the variable name it's expecting.
			$user_id = get_option( 'chartbeat_userid' );

			if ( $user_id ) {

				// Capture the config output by the plugin.
				ob_start();
				add_chartbeat_config();
				$chartbeat_config = ob_get_clean();

				// @codingStandardsIgnoreStart
				$chartbeat_analytics = '<script type="text/javascript">';
				$chartbeat_analytics .= $chartbeat_config;
				$chartbeat_analytics .= '
				window._sf_endpt=(new Date()).getTime();
				</script>
				<script defer src="//static.chartbeat.com/js/chartbeat_fia.js"></script>
				';
				// @codingStandardsIgnoreEnd

				$analytics_code .= $chartbeat_analytics;
			}
		} elseif ( 'wpna_chartbeat' === $chatbeat_source ) {

			// Grab the Chartbeat user ID.
			$user_id = wpna_get_option( 'fbia_analytics_chartbeat_custom' );

			if ( $user_id ) {

				// Workout the domain for thie site to track.
				$url_parts = wpna_parse_url( home_url() );

				$domain = $url_parts['host'];

				// @codingStandardsIgnoreStart
				$chartbeat_analytics = sprintf( "
					<script type='text/javascript'>
						var _sf_async_config = {};
						_sf_async_config.uid = %s;
						_sf_async_config.domain = '%s';
						_sf_async_config.title = ia_document.title;
						_sf_async_config.useCanonical = true;
						window._sf_endpt = (new Date()).getTime();
					</script>
					<script defer src='//static.chartbeat.com/js/chartbeat_fia.js'></script>",
					$user_id,
					$domain
				);
				// @codingStandardsIgnoreEnd

				$analytics_code .= $chartbeat_analytics;
			}
		}

		return $analytics_code;
	}

}

// Initalise this class automatically.
new WPNA_Analytics_Chartbeat();
