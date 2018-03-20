<?php
/**
 * Class for injecting Google analytics into an article.
 *
 * @since  1.3.5
 * @package wp-native-articles
 */

/**
 * Class initalises itself at the end.
 * If the class is loaded it's assumed to be needed.
 * Analytics classes are registered in admin/facebook/class-admin-facebook-analytics.php.
 */
class WPNA_Analytics_Google_Analytics {

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
			'fbia_analytics_ga',
			'<label for="fbia_analytics_ga">' . esc_html__( 'Google Analytics', 'wp-native-articles' ) . '</label>',
			array( $this, 'callback' ),
			$section_id,
			$section_id
		);
		// Field for the custom ga tag.
		add_settings_field(
			'fbia_analytics_ga_custom',
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
		<?php if ( function_exists( 'GADWP' ) ) : ?>
			<div>
				<h3>
					<img style="vertical-align:middle;width:32px;height: 32px;" src="<?php echo esc_url( plugins_url( '/assets/img/google-analytics-dashboard-for-wp.png', WPNA_BASE_FILE ) ); ?>">
					<?php esc_html_e( 'Google Analytics Dashboard for WP', 'wp-native-articles' ); ?>
				</h3>
				<?php
				$profile = GADWP_Tools::get_selected_profile( GADWP()->config->options['ga_dash_profile_list'], GADWP()->config->options['ga_dash_tableid_jail'] );
				?>
				<?php if ( ! $profile ) : ?>
					<p>
						<?php echo sprintf(
							wp_kses(
								// translators: Placeholder is the URL to the plugin.
								__( 'GADWP Plugin found but it has not yet been <a target="_blank" href="%s">authorised</a>.', 'wp-native-articles' ),
								array(
									'a' => array(
										'href'   => array(),
										'target' => array(),
									),
								)
							),
							esc_url( admin_url( '/admin.php?page=gadash_settings' ) )
						); ?>
					</p>
				<?php else : ?>
					<?php $tracking_code = $profile[2]; ?>
					<p><strong><?php esc_html_e( 'Tracking Code: ', 'wp-native-articles' ); ?></strong> <code><?php echo esc_html( $tracking_code ); ?></code></p>
					<br />
					<label>
						<input type="checkbox" name="wpna_options[fbia_analytics_ga][]" id="gadwp" class="" value="gadwp" <?php checked( in_array( 'gadwp', (array) wpna_get_option( 'fbia_analytics_ga' ), true ) ); ?> />
						<?php esc_html_e( 'Enable this tracking code', 'wp-native-articles' ); ?>
					</label>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( function_exists( 'MonsterInsights' ) ) : ?>
			<div>
				<h3>
					<img style="vertical-align:middle;width:32px;height: 32px;" src="<?php echo esc_url( plugins_url( '/assets/img/monsterinsights.png', WPNA_BASE_FILE ) ); ?>">
					<?php esc_html_e( 'Google Analytics for WordPress by MonsterInsights', 'wp-native-articles' ); ?>
				</h3>
				<?php
				$tracking_code = monsterinsights_get_ua();
				?>
				<?php if ( ! $tracking_code ) : ?>
					<p>
						<?php echo sprintf(
							wp_kses(
								// translators: Placeholder is the URL to the plugin.
								__( 'MonsterInsights Plugin found but no <a target="_blank" href="%s">tracking code</a> has been set yet.', 'wp-native-articles' ),
								array(
									'a' => array(
										'href'   => array(),
										'target' => array(),
									),
								)
							),
							esc_url( admin_url( '/admin.php?page=monsterinsights_settings' ) )
						); ?>
					</p>
				<?php else : ?>
					<p><strong><?php esc_html_e( 'Tracking Code: ', 'wp-native-articles' ); ?></strong> <code><?php echo esc_html( $tracking_code ); ?></code></p>
					<br />
					<label>
						<input type="checkbox" name="wpna_options[fbia_analytics_ga][]" id="monsterinsights" class="" value="monsterinsights" <?php checked( in_array( 'monsterinsights', (array) wpna_get_option( 'fbia_analytics_ga' ), true ) ); ?> />
						<?php esc_html_e( 'Enable this tracking code', 'wp-native-articles' ); ?>
					</label>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( class_exists( 'Ga_Admin' ) ) : ?>
				<div>
					<h3>
						<img style="vertical-align:middle;width:32px;height: 32px;" src="<?php echo esc_url( plugins_url( '/assets/img/googleanalytics.png', WPNA_BASE_FILE ) ); ?>">
						<?php esc_html_e( 'Google Analytics', 'wp-native-articles' ); ?>
					</h3>

					<?php
					$tracking_code = Ga_Frontend::get_web_property_id();
					?>

					<?php if ( Ga_Helper::is_all_feature_disabled() ) : ?>
						<p>
							<?php echo sprintf(
								wp_kses(
									// translators: Placeholder is the URL to the plugin.
									__( 'Google Analytics Plugin found but <a target="_blank" href="%s">All Features</a> are disabled.', 'wp-native-articles' ),
									array(
										'a' => array(
											'href'   => array(),
											'target' => array(),
										),
									)
								),
								esc_url( admin_url( '/admin.php?page=googleanalytics/settings' ) )
							); ?>
						</p>
					<?php elseif ( ! $tracking_code ) : ?>
						<p>
							<?php echo sprintf(
								wp_kses(
									// translators: Placeholder is the URL to the plugin.
									__( 'Google Analytics Plugin found but no <a target="_blank" href="%s">Tracking Code</a> was found.', 'wp-native-articles' ),
									array(
										'a' => array(
											'href'   => array(),
											'target' => array(),
										),
									)
								),
								esc_url( admin_url( '/admin.php?page=googleanalytics/settings' ) )
							); ?>
						</p>
					<?php else : ?>

						<p><strong><?php esc_html_e( 'Tracking Code: ', 'wp-native-articles' ); ?></strong> <code><?php echo esc_html( $tracking_code ); ?></code></p>
						<br />
						<label>
							<input type="checkbox" name="wpna_options[fbia_analytics_ga][]" id="googleanalytics" class="" value="googleanalytics" <?php checked( in_array( 'googleanalytics', (array) wpna_get_option( 'fbia_analytics_ga' ), true ) ); ?> />
							<?php esc_html_e( 'Enable this tracking code', 'wp-native-articles' ); ?>
						</label>

					<?php endif; ?>
				</div>
		<?php endif; ?>

		<?php if ( function_exists( 'gap_init' ) ) : ?>
			<div>
				<h3>
					<img style="vertical-align:middle;width:32px;height: 32px;" src="<?php echo esc_url( plugins_url( '/assets/img/ga-google-analytics.png', WPNA_BASE_FILE ) ); ?>">
					<?php esc_html_e( 'GA Google Analytics', 'wp-native-articles' ); ?>
				</h3>
				<?php
				$gap_options = get_option( 'gap_options' );

				if ( isset( $gap_options['gap_id'] ) && 'UA-XXXXX-X' !== $gap_options['gap_id'] ) {
					$tracking_code = $gap_options['gap_id'];
				}
				?>
				<?php if ( ! isset( $gap_options['gap_enable'] ) || ! $gap_options['gap_enable'] ) : ?>
					<p>
						<?php echo sprintf(
							wp_kses(
								// translators: Placeholder is the URL to the plugin.
								__( 'GA Google Analytics Plugin found but <a target="_blank" href="%s">analytics are disabled</a>.', 'wp-native-articles' ),
								array(
									'a' => array(
										'href'   => array(),
										'target' => array(),
									),
								)
							),
							esc_url( admin_url( '/options-general.php?page=ga-google-analytics/ga-google-analytics.php' ) )
						); ?>
					</p>
				<?php elseif ( ! $tracking_code ) : ?>
					<p>
						<?php echo sprintf(
							wp_kses(
								// translators: Placeholder is the URL to the plugin.
								__( 'GA Google Analytics Plugin found but no <a target="_blank" href="%s">tracking code</a>.', 'wp-native-articles' ),
								array(
									'a' => array(
										'href'   => array(),
										'target' => array(),
									),
								)
							),
							esc_url( admin_url( '/options-general.php?page=ga-google-analytics/ga-google-analytics.php' ) )
						); ?>
					</p>
				<?php else : ?>
					<p><strong><?php esc_html_e( 'Tracking Code: ', 'wp-native-articles' ); ?></strong> <code><?php echo esc_html( $tracking_code ); ?></code></p>
					<br />
					<label>
						<input type="checkbox" name="wpna_options[fbia_analytics_ga][]" id="gap" class="" value="gap" <?php checked( in_array( 'gap', (array) wpna_get_option( 'fbia_analytics_ga' ), true ) ); ?> />
						<?php esc_html_e( 'Enable this tracking code', 'wp-native-articles' ); ?>
					</label>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div>
			<h3><?php esc_html_e( 'Manual Tracking Code', 'wp-native-articles' ); ?></h3>
			<input type="text" name="wpna_options[fbia_analytics_ga_custom]" id="fbia_analytics_ga_custom" placeholder="UA-12345678-0" class="regular-text" value="<?php echo esc_attr( wpna_get_option( 'fbia_analytics_ga_custom' ) ); ?>">
			<p class="description"><?php esc_html_e( 'Separate multiple tracking codes with commas.', 'wp-native-articles' ); ?></p>
			<br />
			<label>
				<input type="checkbox" name="wpna_options[fbia_analytics_ga][]" id="wpna_google_analytics" class="" value="wpna_google_analytics" <?php checked( in_array( 'wpna_google_analytics', (array) wpna_get_option( 'fbia_analytics_ga' ), true ) ); ?> />
				<?php esc_html_e( 'Enable this tracking code', 'wp-native-articles' ); ?>
			</label>
			<?php
			// Show a notice if the option has been overridden.
			wpna_option_overridden_notice( 'fbia_analytics_ga_custom' );
			?>
		</div>

		<?php // Ensures that a value is always set so other codes can be un-ticked. ?>
		<input type="hidden" name="wpna_options[fbia_analytics_ga][]" value="false">
		<?php
	}

	/**
	 * Workout which tracking code to use.
	 *
	 * @access public
	 * @return string
	 */
	public function get_tracking_ids() {
		$tracking_codes = array();

		// Get all the google analytics integrations.
		$google_analytics_integrations = wpna_get_post_option( get_the_ID(), 'fbia_analytics_ga' );

		if ( ! empty( $google_analytics_integrations ) ) {
			foreach ( $google_analytics_integrations as $integration ) {
				// Check a method exists for getting the IDs from the integration.
				if ( method_exists( $this, $integration ) ) {
					// Grab the ids. Always make sure it's an array.
					$tracking_ids = (array) $this->{$integration}();

					// If ids were returned then add them in.
					if ( ! empty( $tracking_ids ) ) {
						$tracking_codes = array_merge( $tracking_codes, $tracking_ids );
					}
				}
			}
		}

		// Make sure there are no duplicates.
		$tracking_codes = array_unique( $tracking_codes );
		// Remove empties.
		$tracking_codes = array_filter( $tracking_codes );

		return $tracking_codes;
	}

	/**
	 * Google Analytice Dashboard for WP.
	 * Return the tracking code from the authorisation.
	 *
	 * @link https://wordpress.org/plugins/google-analytics-dashboard-for-wp/
	 *
	 * @access public
	 * @return string|false
	 */
	public function gadwp() {
		if ( function_exists( 'GADWP' ) ) {
			$profile = GADWP_Tools::get_selected_profile( GADWP()->config->options['ga_dash_profile_list'], GADWP()->config->options['ga_dash_tableid_jail'] );
			return $profile[2];
		}
		return false;
	}

	/**
	 * Google Analytics for WordPress by MonsterInsights.
	 * Return the tracking code from the authorisation.
	 *
	 * @link https://wordpress.org/plugins/google-analytics-for-wordpress/
	 *
	 * @access public
	 * @return string|false
	 */
	public function monsterinsights() {
		if ( function_exists( 'MonsterInsights' ) ) {
			if ( $tracking_code = monsterinsights_get_ua() ) {
				return $tracking_code;
			}
		}
		return false;
	}

	/**
	 * GoogleAnalytics Plugin.
	 * Returns the tracking code from the Auto integration.
	 *
	 * @link https://wordpress.org/plugins/googleanalytics/
	 *
	 * @access public
	 * @return string|false
	 */
	public function googleanalytics() {
		if ( class_exists( 'Ga_Admin' ) && ! Ga_Helper::is_all_feature_disabled() ) {
			if ( $web_property_id = Ga_Frontend::get_web_property_id() ) {
				return $web_property_id;
			}
		}

		return false;
	}

	/**
	 * GA Google Analytics Plugin.
	 * Returns the tracking code set.
	 *
	 * @link https://wordpress.org/plugins/ga-google-analytics/
	 *
	 * @access public
	 * @return string|false
	 */
	public function gap() {

		if ( function_exists( 'gap_init' ) ) {
			$gap_options = get_option( 'gap_options', array() );

			if ( isset( $gap_options['gap_enable'] ) && $gap_options['gap_enable'] ) {
				if ( isset( $gap_options['gap_id'] ) && 'UA-XXXXX-X' !== $gap_options['gap_id'] ) {
					return $gap_options['gap_id'];
				}
			}
		}

		return false;
	}

	/**
	 * Our own implementation of Google Analytics.
	 * Return the tracking code set.
	 *
	 * @access public
	 * @return string|false
	 */
	public function wpna_google_analytics() {
		// Grab the ID set.
		$analytics_ids = wpna_get_option( 'fbia_analytics_ga_custom' );

		// Check if there are multiple IDs set.
		$analytics_ids = explode( ',', $analytics_ids );

		// Trim all the elements.
		$analytics_ids = array_map( 'trim', $analytics_ids );

		return $analytics_ids;
	}

	/**
	 * Hooks into the article analytics code and appends this class analytics.
	 *
	 * @access public
	 * @param  string $analytics_code Analytics code to use in the article.
	 * @return string
	 */
	public function output( $analytics_code ) {
		// Checks for post options, then global options then default.
		// Returns an array.
		$ga_tracking_ids = $this->get_tracking_ids();

		if ( ! empty( $ga_tracking_ids ) ) {

			// Base code.
			$ga_code = "
				<script>
				(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','//www.google-analytics.com/analytics.js','ga');";

			// @codingStandardsIgnoreLine
			for ( $i = 0; $i < $count = count( $ga_tracking_ids ); $i++ ) {

				if ( 0 === $i ) {
					$ga_code .= sprintf( "
						ga('create', '%s', 'auto');
						ga('require', 'displayfeatures');
						ga('set', 'campaignSource', 'Facebook');
						ga('set', 'campaignMedium', 'Social Instant Article');
						ga('send', 'pageview', {title: ia_document.title});",
						esc_js( $ga_tracking_ids[ $i ] )
					);
				} else {
					$unique_name = 'tracker_' . $i;
					// Multiple GA tags need to be named differently.
					// https://developers.google.com/analytics/devguides/collection/analyticsjs/creating-trackers#working_with_multiple_trackers.
					$ga_code .= sprintf( "
						ga('create', '%s', 'auto', '%s');
						ga('%s.require', 'displayfeatures');
						ga('%s.set', 'campaignSource', 'Facebook');
						ga('%s.set', 'campaignMedium', 'Social Instant Article');
						ga('%s.send', 'pageview', {title: ia_document.title});",
						esc_js( $ga_tracking_ids[ $i ] ),
						esc_js( $unique_name ),
						esc_js( $unique_name ),
						esc_js( $unique_name ),
						esc_js( $unique_name ),
						esc_js( $unique_name )
					);
				}
			}

			$ga_code .= '</script>' . PHP_EOL;

			$analytics_code .= $ga_code;
		}

		return $analytics_code;
	}

}

// Initalise this class automatically.
new WPNA_Analytics_Google_Analytics();
