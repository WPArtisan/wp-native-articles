<?php
/**
 * Spider Faceook. Stop auto injecting Like & Share boxes in posts in IA
 *
 * @link https://wordpress.org/plugins/spider-facebook/
 * @since 1.3.4
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_spider_facebook_override_tags' ) ) :

	/**
	 * Override the following tags with custom output functions.
	 *
	 * @param  array  $override_tags Shortcode functions to override.
	 * @param  string $content      Current post content.
	 * @return array  Shortcodes to ignore
	 */
	function wpna_spider_facebook_override_tags( $override_tags, $content ) {

		$override_tags['spider_facebook'] = '__return_empty_string';

		return $override_tags;
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', 'wpna_spider_facebook_override_tags', 10, 2 );


if ( ! function_exists( 'wpna_disable_spider_facebook' ) ) :

	/**
	 * Remove any filters they've set.
	 *
	 * @return void
	 */
	function wpna_disable_spider_facebook() {
		if ( has_filter( 'the_content', 'spider_facebook_front_end_short' ) ) {
			remove_filter( 'the_content', 'spider_facebook_front_end_short', 10 );
		}
		if ( has_filter( 'the_content', 'spider_facebook_front_end_shortcode' ) ) {
			remove_filter( 'the_content', 'spider_facebook_front_end_shortcode', 5000 );
		}

	}
endif;
add_action( 'wpna_facebook_article_pre_the_content_transform', 'wpna_disable_spider_facebook' );

if ( ! function_exists( 'wpna_enable_sider_facebook' ) ) :

	/**
	 * Add the filters back to tidy up after ourselves.
	 *
	 * @return void
	 */
	function wpna_enable_sider_facebook() {
		if ( function_exists( 'spider_facebook_front_end_short' ) && ! has_filter( 'the_content', 'spider_facebook_front_end_short' ) ) {
			add_filter( 'the_content', 'spider_facebook_front_end_short', 10 );
		}
		if ( function_exists( 'spider_facebook_front_end_shortcode' ) && ! has_filter( 'the_content', 'spider_facebook_front_end_shortcode' ) ) {
			add_filter( 'the_content', 'spider_facebook_front_end_shortcode', 5000 );
		}
	}
endif;
add_action( 'wpna_facebook_article_after_the_content_transform', 'wpna_enable_sider_facebook' );
