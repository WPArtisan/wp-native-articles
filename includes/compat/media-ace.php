<?php
/**
 * Disables Media Ace's lazy image loading when converting to IA.
 *
 * @since 1.3.3
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_media_ace_disable_lazy_loading' ) ) :

	/**
	 * Disable Media Ace Lazyloader for IA conversion.
	 *
	 * @return void
	 */
	function wpna_media_ace_disable_lazy_loading() {
		add_filter( 'mace_lazy_load_image', '__return_false', 10 );
	}
endif;
add_action( 'wpna_facebook_article_pre_the_content_transform', 'wpna_media_ace_disable_lazy_loading', 10 );

if ( ! function_exists( 'wpna_media_ace_enable_lazy_loading' ) ) :

	/**
	 * Re-enable the Media Ace Lazyloader after IA conversion has finished.
	 *
	 * @return void
	 */
	function wpna_media_ace_enable_lazy_loading() {
		remove_filter( 'mace_lazy_load_image', '__return_false', 10 );
	}
endif;
add_action( 'wpna_facebook_article_after_the_content_transform', 'wpna_media_ace_enable_lazy_loading', 10 );
