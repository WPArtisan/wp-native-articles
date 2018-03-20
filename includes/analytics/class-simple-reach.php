<?php
/**
 * Class for injecting Simple Reach analytics into an article.
 *
 * @since  1.3.5
 * @package wp-native-articles
 */

/**
 * Class initalises itself at the end.
 * If the class is loaded it's assumed to be needed.
 * Analytics classes are registered in admin/facebook/class-admin-facebook-analytics.php.
 */
class WPNA_Analytics_Simple_Reach {

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
			'fbia_analytics_simple_reach',
			'<label for="fbia_analytics_simple_reach">' . esc_html__( 'Simple Reach', 'wp-native-articles' ) . '</label>',
			array( $this, 'callback' ),
			$section_id,
			$section_id
		);

		add_settings_field(
			'fbia_analytics_simple_reach_custom',
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
		<?php if ( defined( 'SRANALYTICS_PLUGIN_VERSION' ) ) : ?>
			<div>
				<h3>
					<?php esc_html_e( 'SimpleReach Analytics Plugin', 'wp-native-articles' ); ?>
				</h3>
				<?php
				$sranalytics_pid = get_option( 'sranalytics_pid' );
				?>
				<?php if ( ! $sranalytics_pid ) : ?>
					<p>
						<?php echo sprintf(
							wp_kses(
								// translators: Placeholder is the URL to the plugin.
								__( 'SimpleReach Analytics Plugin found but no <a target="_blank" href="%s">Publisher ID</a> has been set.', 'wp-native-articles' ),
								array(
									'a' => array(
										'href'   => array(),
										'target' => array(),
									),
								)
							),
							esc_url( admin_url( '/options-general.php?page=SimpleReach-Analytics' ) )
						); ?>
					</p>
				<?php else : ?>
					<p><strong><?php esc_html_e( 'Publisher ID: ', 'wp-native-articles' ); ?></strong> <code><?php echo esc_html( $sranalytics_pid ); ?></code></p>
					<br />
					<label>
						<input type="radio" name="wpna_options[fbia_analytics_simple_reach]" id="fbia_analytics_simple_reach" class="" value="simplereach_plugin" <?php checked( 'simplereach_plugin', wpna_get_option( 'fbia_analytics_simple_reach' ) ); ?> />
						<?php esc_html_e( 'Use this Publisher ID', 'wp-native-articles' ); ?>
					</label>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div>
			<h3><?php esc_html_e( 'Manual Publisher ID', 'wp-native-articles' ); ?></h3>
			<input type="text" name="wpna_options[fbia_analytics_simple_reach_custom]" id="fbia_analytics_simple_reach_custom" placeholder="" class="regular-text" value="<?php echo esc_attr( wpna_get_option( 'fbia_analytics_simple_reach_custom' ) ); ?>">
			<p class="description"><?php esc_html_e( 'SimpleReach Publisher ID', 'wp-native-articles' ); ?></p>
			<br />
			<label>
				<input type="radio" name="wpna_options[fbia_analytics_simple_reach]" id="fbia_analytics_simple_reach" class="" value="wpna_simplereach" <?php checked( 'wpna_simplereach', wpna_get_option( 'fbia_analytics_simple_reach' ) ); ?> />
				<?php esc_html_e( 'Use this Publisher ID', 'wp-native-articles' ); ?>
			</label>
			<?php
			// Show a notice if the option has been overridden.
			wpna_option_overridden_notice( 'fbia_analytics_simple_reach_custom' );
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

		$simplereach_source = wpna_get_option( 'fbia_analytics_simple_reach' );

		if ( 'simplereach_plugin' === $simplereach_source ) {

			// Check for the ID from the Simple Reach Plugin.
			$sr_pid = get_option( 'sranalytics_pid' );

		} elseif ( 'wpna_simplereach' === $simplereach_source ) {

			// Checks for post options, then global options then default.
			$sr_pid = wpna_get_post_option( get_the_ID(), 'fbia_analytics_simple_reach_custom', false );
		}

		if ( ! empty( $sr_pid ) ) {

			$post = get_post( get_the_ID() );

			$title           = $post->post_title;
			$published_date  = $post->post_date_gmt;
			$authors         = array( get_the_author_meta( 'display_name' ) );
			$post_categories = get_the_terms( $post->ID, 'category' );
			$channels        = wp_list_pluck( $post_categories, 'slug' );
			$post_tags       = get_the_terms( $post->ID, 'post_tag' );
			$tags            = wp_list_pluck( $post_tags, 'name' );

			$simple_reach_analytics = sprintf( "
				<script>
				__reach_config = {
					pid: '%s',
					title: '%s',
					date: '%s',
					authors: %s,
					channels: %s,
					tags: %s,
					ref_url: 'https://www.facebook.com/',
					ignore_errors: false
				};
				(function(){
					var s = document.createElement('script');
					s.async = true;
					s.type = 'text/javascript';
					s.src = document.location.protocol + '//d8rk54i4mohrb.cloudfront.net/js/reach.js';
					(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(s);
				})();
				</script>",
				esc_js( $sr_pid ),
				esc_js( $title ),
				esc_js( $published_date ),
				wp_json_encode( $authors ),
				wp_json_encode( $channels ),
				wp_json_encode( $tags )
			);

			$analytics_code .= $simple_reach_analytics . PHP_EOL;
		}

		return $analytics_code;
	}

}

// Initalise this class automatically.
new WPNA_Analytics_Simple_Reach();
