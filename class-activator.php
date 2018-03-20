<?php
/**
 * The activator class for the plugin.
 *
 * Anything that should happen when the plugin is
 * activated should be here. Will only be run the once.
 *
 * @author OzTheGreat
 * @since 1.0.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activator class.
 */
class WPNA_Activator {

	/**
	 * Main method to be run in this class to fire off
	 * all the other methods. Handles everything the plugin
	 * should do upon activation.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public static function run() {
		self::add_default_options();
		self::add_default_site_options();
		self::flush_rewrite_rules();
		self::run_database_scripts();
	}

	/**
	 * Flushes the rewrite rules.
	 *
	 * We're adding a custom endpoint to the permalinks API
	 * so we need to flush the rewrite rules for it to work.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public static function flush_rewrite_rules() {
		flush_rewrite_rules();
	}

	/**
	 * Adds default options for the plugin.
	 *
	 * If no options already exist for the plugin then this
	 * creates the default ones.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public static function add_default_options() {
		if ( false === get_option( 'wpna_options' ) ) {

			// Default to showing the subtitle.
			$fbia_show_subtitle = 'on';

			// Get the most recent published post.
			$args = array(
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'ignore_sticky_posts'    => true,
				'post_status'            => 'publish',
				'orderby'                => 'date',
				'order'                  => 'desc',
				'fields'                 => 'ids',
				'posts_per_page'         => 1,
			);

			$query = new WP_Query( $args );

			if ( $query->have_posts() ) {
				// Check if it has an excerpt set or not.
				if ( ! has_excerpt( $query->posts[0] ) ) {
					$fbia_show_subtitle = 'off';
				}
			}

			$default_options = array(
				'fbia_enable'              => 'on',
				'fbia_authorise_id'        => '0',
				'fbia_content_parser'      => 'v2',
				'fbia_style'               => 'default',
				'fbia_sponsored'           => 'off',
				'fbia_image_likes'         => 'off',
				'fbia_image_comments'      => 'off',
				'fbia_credits'             => '',
				'fbia_copyright'           => '',
				'fbia_analytics'           => '',
				'fbia_analytics_providers' => array(),
				'fbia_analytics_ga'        => array(),
				'fbia_enable_ads'          => 'off',
				'fbia_auto_ad_placement'   => 'off',
				'fbia_ad_code'             => '',

				'fbia_show_subtitle'       => $fbia_show_subtitle,
				'fbia_show_authors'        => 'on',
				'fbia_show_kicker'         => 'on',
				'fbia_show_media'          => 'on',
				'fbia_caption_title'       => 'off',

				'fbia_feed_slug'           => 'facebook-instant-articles',
				'fbia_posts_per_feed'      => '25',
				'fbia_article_caching'     => '1',
				'fbia_modified_only'       => '1',

				'fbia_app_id'              => '',
				'fbia_app_secret'          => '',
				'fbia_sync_articles'       => 'on',
				'fbia_sync_cron'           => '0',
				'fbia_enviroment'          => 'development',
				'fbia_crawler_ingestion'   => 'on',
			);

			// Check for any default analytics providers setup.
			$analytics = self::get_default_analytics();

			$default_options = array_merge( $default_options, $analytics );

			// Add in the default options.
			add_option( 'wpna_options', $default_options );
		}

	}

	/**
	 * Adds some default site options.
	 *
	 * Log when the plugin was first activated + intervals of when to
	 * send rating prompt messages.
	 *
	 * @since 1.0.3
	 *
	 * @access public
	 * @return void
	 */
	public static function add_default_site_options() {
		// They may deactivate / re-activate. Let's try not to be annoying.
		if ( ! get_site_option( 'wpna_activation_time' ) ) {
			add_site_option( 'wpna_activation_time', date( 'c' ) );
			// When to provide prompts for plugin ratings, in days.
			add_site_option( 'wpna_rating_prompts', array( 7, 30, 90 ) );

			// Add the transient to redirect after install. Don't bother if network activated.
			if ( ! is_network_admin() && ! isset( $_GET['activate-multi'] ) ) { // WPCS: CSRF ok.
				set_transient( '_wpna_activation_redirect', true, MINUTE_IN_SECONDS / 2 );
			}
		}
	}

	/**
	 * Holds all scripts for creating the custom tables in the database.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 * @return void
	 */
	public static function run_database_scripts() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$scripts = array();
		if ( ! empty( $scripts ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			foreach ( $scripts as $script ) {
				dbDelta( $scripts );
			}
		}

		$current_version = get_site_option( 'wpna_db_version' );

		if ( WPNA_VERSION !== $current_version ) {
			update_site_option( 'wpna_previous_db_version', $current_version );
		}

		// Set the current DB version.
		update_site_option( 'wpna_db_version', WPNA_VERSION );
	}

	/**
	 * Checks to see if any compatible analytics plugins are installed and can
	 * be used.
	 *
	 * @since 1.3.5
	 *
	 * @access public
	 * @return array Analytics integrations to activate.
	 */
	public static function get_default_analytics() {
		// Make sure providers & GA integrations are arrays.
		$analytics_options = array(
			'fbia_analytics_providers' => array(),
			'fbia_analytics_ga'        => array(),
		);

		// Chartbeat.
		if ( function_exists( 'chartbeat_fbia_analytics' ) ) {
			if ( get_option( 'chartbeat_userid' ) ) {
				// Enable Chartbeat analytics.
				$analytics_options['fbia_analytics_providers'][] = 'chartbeat';
				// Set it to use the Chatbeat Plugin settings.
				$analytics_options['fbia_analytics_chartbeat'] = 'chartbeat_plugin';
			}
		}

		// Google Analytics.
		if ( function_exists( 'GADWP' ) ) {
			// Try and grab the selected profile.
			$profile = GADWP_Tools::get_selected_profile( GADWP()->config->options['ga_dash_profile_list'], GADWP()->config->options['ga_dash_tableid_jail'] );
			// If a profile exists, then enable this provider.
			if ( $profile ) {
				$analytics_options['fbia_analytics_ga'][] = 'gadwp';
			}
		}

		if ( function_exists( 'MonsterInsights' ) ) {
			// Check if a tracking code exists.
			if ( monsterinsights_get_ua() ) {
				$analytics_options['fbia_analytics_ga'][] = 'monsterinsights';
			}
		}

		if ( class_exists( 'Ga_Admin' ) ) {
			if ( ! Ga_Helper::is_all_feature_disabled() && Ga_Frontend::get_web_property_id() ) {
				$analytics_options['fbia_analytics_ga'][] = 'googleanalytics';
			}
		}

		if ( function_exists( 'gap_init' ) ) {
			// Get the gap options.
			$gap_options = get_option( 'gap_options' );
			// If gap is enabled and an ID is set.
			if ( isset( $gap_options['gap_enable'] ) && isset( $gap_options['gap_id'] ) && 'UA-XXXXX-X' !== $gap_options['gap_id'] ) {
				$analytics_options['fbia_analytics_ga'][] = 'gap';
			}
		}

		// If any Google Analytics providers were found, enable GA.
		if ( ! empty( $analytics_options['fbia_analytics_ga'] ) ) {
			$analytics_options['fbia_analytics_providers'][] = 'google-analytics';
		}

		// Jetpack.
		if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'stats' ) ) {
			// Enable Jetpack analytics.
			$analytics_options['fbia_analytics_providers'][] = 'jetpack';
		}

		// Parse.ly.
		if ( class_exists( 'Parsely' ) ) {
			$parsley_options = get_option( Parsely::OPTIONS_KEY );
			if ( ! empty( $parsley_options['apikey'] ) ) {
				// Enable Parse.ly analytics.
				$analytics_options['fbia_analytics_providers'][] = 'parsely';
				// Set it to use the Parse.ly Plugin settings.
				$analytics_options['fbia_analytics_parsely'] = 'parsely_plugin';
			}
		}

		// SimpleReach.
		if ( defined( 'SRANALYTICS_PLUGIN_VERSION' ) ) {
			if ( get_option( 'sranalytics_pid' ) ) {
				// Enable SimpleReach analytics.
				$analytics_options['fbia_analytics_providers'][] = 'simple-reach';
				// Set it to use the SimpleReach Plugin settings.
				$analytics_options['fbia_analytics_simple_reach'] = 'simplereach_plugin';
			}
		}

		return $analytics_options;

	}

}
