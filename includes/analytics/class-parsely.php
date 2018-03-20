<?php
/**
 * Class for injecting Parse.ly analytics into an article.
 *
 * @since  1.3.5
 * @package wp-native-articles
 */

/**
 * Class initalises itself at the end.
 * If the class is loaded it's assumed to be needed.
 * Analytics classes are registered in admin/facebook/class-admin-facebook-analytics.php.
 */
class WPNA_Analytics_Parsely {

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
			'fbia_analytics_parsely',
			'<label for="fbia_analytics_parsely">' . esc_html__( 'Parse.ly', 'wp-native-articles' ) . '</label>',
			array( $this, 'callback' ),
			$section_id,
			$section_id
		);

		add_settings_field(
			'fbia_analytics_parsely_custom',
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
		<?php if ( class_exists( 'Parsely' ) ) : ?>
			<div>
				<h3>
					<?php esc_html_e( 'Parsely Plugin', 'wp-native-articles' ); ?>
				</h3>
				<?php
				$parsley_options = get_option( Parsely::OPTIONS_KEY );
				?>
				<?php if ( empty( $parsley_options['apikey'] ) ) : ?>
					<p>
						<?php echo sprintf(
							wp_kses(
								// translators: Placeholder is the URL to the plugin.
								__( 'Parse.ly Plugin found but no <a target="_blank" href="%s">Parse.ly Site ID</a> has been set.', 'wp-native-articles' ),
								array(
									'a' => array(
										'href'   => array(),
										'target' => array(),
									),
								)
							),
							esc_url( admin_url( '/options-general.php?page=parsely' ) )
						); ?>
					</p>
				<?php else : ?>
					<p><strong><?php esc_html_e( 'Parse.ly Site ID: ', 'wp-native-articles' ); ?></strong> <code><?php echo esc_html( $parsley_options['apikey'] ); ?></code></p>
					<br />
					<label>
						<input type="radio" name="wpna_options[fbia_analytics_parsely]" id="fbia_analytics_parsely" class="" value="parsely_plugin" <?php checked( 'parsely_plugin', wpna_get_option( 'fbia_analytics_parsely' ) ); ?> />
						<?php esc_html_e( 'Use this Site ID', 'wp-native-articles' ); ?>
					</label>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div>
			<h3><?php esc_html_e( 'Manual Site ID', 'wp-native-articles' ); ?></h3>
			<input type="text" name="wpna_options[fbia_analytics_parsely_custom]" id="fbia_analytics_parsely_custom" placeholder="" class="regular-text" value="<?php echo esc_attr( wpna_get_option( 'fbia_analytics_parsely_custom' ) ); ?>">
			<p class="description"><?php esc_html_e( 'Parse.ly Site ID', 'wp-native-articles' ); ?></p>
			<br />
			<label>
				<input type="radio" name="wpna_options[fbia_analytics_parsely]" id="fbia_analytics_parsely" class="" value="wpna_parsely" <?php checked( 'wpna_parsely', wpna_get_option( 'fbia_analytics_parsely' ) ); ?> />
				<?php esc_html_e( 'Use this Site ID', 'wp-native-articles' ); ?>
			</label>
			<?php
			// Show a notice if the option has been overridden.
			wpna_option_overridden_notice( 'fbia_analytics_parsely' );
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

		$parsely_source = wpna_get_option( 'fbia_analytics_parsely' );

		if ( 'parsely_plugin' === $parsely_source && class_exists( 'Parsely' ) ) {

			// Grab all the options from Parse.ly.
			$parsley_options = get_option( Parsely::OPTIONS_KEY );

			// If the API key is set then use that.
			if ( ! empty( $parsley_options['apikey'] ) ) {
				$api_key = $parsley_options['apikey'];
			}
		} elseif ( 'wpna_parsely' === $parsely_source ) {

			// Checks for post options, then global options then default.
			$api_key = wpna_get_post_option( get_the_ID(), 'fbia_analytics_parsely_custom', false );

		}

		if ( ! empty( $api_key ) ) {

			$parsely_analytics = sprintf( "
				<script>
					PARSELY = {
						autotrack: false,
						onload: function() {
							PARSELY.beacon.trackPageView({
								urlref: 'http://facebook.com/instantarticles'
							});
							return true;
						}
					}
				</script>
				<div id='parsely-root' style='display: none'>
					<span id='parsely-cfg' data-parsely-site='%s'></span>
				</div>
				<script>
					(function(s, p, d) {
					var h=d.location.protocol, i=p+'-'+s,
					e=d.getElementById(i), r=d.getElementById(p+'-root'),
					u=h==='https:'?'d1z2jf7jlzjs58.cloudfront.net'
					:'static.'+p+'.com';
					if (e) return;
					e = d.createElement(s); e.id = i; e.async = true;
					e.src = h+'//'+u+'/p.js'; r.appendChild(e);
					})('script', 'parsely', document);
				</script>",
				esc_js( $api_key )
			);

			$analytics_code .= $parsely_analytics . PHP_EOL;
		}

		return $analytics_code;
	}

}

// Initalise this class automatically.
new WPNA_Analytics_Parsely();
