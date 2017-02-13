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
		self::flush_rewrite_rules();
		self::add_default_options();
		self::add_default_site_options();
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

			$default_options = array(
				'fbia_enable'            => 'on',
				'fbia_authorise_id'      => '0',
				'fbia_style'             => 'default',
				'fbia_sponsored'         => 'off',
				'fbia_image_likes'       => 'off',
				'fbia_image_comments'    => 'off',
				'fbia_credits'           => '',
				'fbia_copyright'         => '',
				'fbia_analytics'         => '',
				'fbia_enable_ads'        => 'off',
				'fbia_auto_ad_placement' => 'off',
				'fbia_ad_code'           => '',
				'fbia_feed_slug'         => 'facebook-instant-articles',
				'fbia_posts_per_feed'    => '25',
				'fbia_article_caching'   => '1',
				'fbia_modified_only'     => '1',
				'fbia_app_id'            => '',
				'fbia_app_secret'        => '',
				'fbia_sync_articles'     => 'on',
				'fbia_enviroment'        => 'development',
			);

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
		}
	}

}
