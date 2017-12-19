<?php
/**
 * Facebook Admin class for the analytics.
 *
 * @since  1.3.5
 * @package wp-native-articles
 */

/**
 * Extends the Admin Base and adds all the analytics options.
 */
class WPNA_Admin_Facebook_Analytics extends WPNA_Admin_Base implements WPNA_Admin_Interface {

	/**
	 * Hooks registered in this class.
	 *
	 * This method is auto called from WPNA_Admin_Base.
	 *
	 * @access public
	 * @return void
	 */
	public function hooks() {
		$this->setup_integrations();

		add_action( 'admin_init', array( $this, 'setup_settings' ), 10, 0 );
	}

	/**
	 * All available analytics methods.
	 *
	 * $key is the name of the class to load.
	 * $value is the name displayed.
	 *
	 * @access public
	 * @return array
	 */
	public function get_integrations() {

		/**
		 * All available analytics integrations.
		 *
		 * $key is the name of the class to load.
		 * $value is the name displayed.
		 *
		 * @var array
		 */
		return apply_filters( 'wpna_analytics_integrations', array(
			'google-analytics' => 'Google Analytics',
			'google-site-tag'  => 'Google Site Tag',
			'jetpack'          => 'Jetpack',
			'simple-reach'     => 'Simple Reach',
			'parsely'          => 'Parse.ly',
			'chartbeat'        => 'Chartbeat',
			'custom-analytics' => 'Custom Analytics',
		) );
	}

	/**
	 * Returns all currently active analytics integrations.
	 *
	 * @access public
	 * @return array Active analytics integrations.
	 */
	public function get_active_integrations() {

		$active_integrations = wpna_get_option( 'fbia_analytics_providers' );

		// Custom Post analytics is always active.
		$active_integrations[] = 'custom-post-analytics';

		return $active_integrations;
	}

	/**
	 * Adds the analytics sub section to the General Tab.
	 *
	 * All custom integrations hook into here to add in their own
	 * settings fields.
	 *
	 * @access public
	 * @return void
	 */
	public function setup_settings() {
		// Unique ID for this section.
		$section_general_analytics = 'wpna_facebook-general-analytics';

		register_setting( $section_general_analytics, 'wpna_options', 'wpna_sanitize_options' );

		add_settings_section(
			$section_general_analytics,
			esc_html__( 'Analytics', 'wp-native-articles' ),
			array( $this, 'section_general_analytics_callback' ),
			$section_general_analytics
		);

		add_settings_field(
			'fbia_analytics_providers',
			'<label for="fbia_analytics_providers">' . esc_html__( 'Integrations', 'wp-native-articles' ) . '</label>',
			array( $this, 'fbia_analytics_providers_callback' ),
			$section_general_analytics,
			$section_general_analytics
		);

		/**
		 * Fires immediately after the inital analytics fields are setup.
		 *
		 * This is the action the integrations use to add custom fields in.
		 *
		 * @param $section_general_analytics Name of the analytics section.
		 */
		do_action( 'wpna_analytics_integrations_settings_fields', $section_general_analytics );
	}

	/**
	 * Outputs the HTML displayed at the top of the settings section.
	 *
	 * @access public
	 * @return void
	 */
	public function section_general_analytics_callback() {
		?>
		<p><?php esc_html_e( 'Add analytics integrations to your articles. You can enable as many or as few as you like.', 'wp-native-articles' ); ?></p>
		<?php
	}

	/**
	 * Outputs the HTML for the 'fbia_analytics_providers' settings field.
	 *
	 * Sets the analytics code to use in each article. Can be overridden on a
	 * per article basis.
	 *
	 * @access public
	 * @return void
	 */
	public function fbia_analytics_providers_callback() {
		$integrations = $this->get_integrations();
		?>
		<input type="hidden" name="wpna_options[fbia_analytics_providers][]" value="none" />
		<?php foreach ( $integrations as $integration => $name ) : ?>
			<?php $key = 'fbia_analytics_' . $integration; ?>
			<label>
				<input type="checkbox" name="wpna_options[fbia_analytics_providers][]" id="<?php echo esc_attr( $key ); ?>" class="" value="<?php echo esc_attr( $integration ); ?>" <?php checked( in_array( $integration, (array) wpna_get_option( 'fbia_analytics_providers' ), true ) ); ?> />
				<?php echo esc_html( $name ); ?>
			</label>
			<br />
		<?php endforeach; ?>

		<?php
		// Show a notice if the option has been overridden.
		wpna_option_overridden_notice( 'fbia_analytics_providers' );
		?>

		<?php
	}

	/**
	 * Loads in all the active analytics classes.
	 * The classes are self-initialising and are responsible for adding
	 * their own settings fields and appedning their own analytics code.
	 *
	 * @access public
	 * @return void
	 */
	public function setup_integrations() {

		// Get all the active analytics providers.
		$integrations = $this->get_active_integrations();

		/**
		 * Fires immediately prior to analytics integrations being loaded.
		 */
		do_action( 'wpna_analytics_integrations_load' );

		foreach ( (array) $integrations as $integration ) {

			if ( is_readable( WPNA_BASE_PATH . '/includes/analytics/class-' . $integration . '.php' ) ) {
				require_once WPNA_BASE_PATH . '/includes/analytics/class-' . $integration . '.php';
			}
		}

		/**
		 * Fires immediately after all analytics integrations are loaded.
		 */
		do_action( 'wpna_analytics_integrations_loaded' );
	}

}
