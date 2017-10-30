<?php
/**
 * Make articles created witht eh TVE editor compatible.
 *
 * @since 1.3.3
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_disable_tve_editor' ) ) :

	/**
	 * TVE editor duplciates the content. This stops it for IA.
	 *
	 * @return void
	 */
	function wpna_disable_tve_editor() {
		if ( has_filter( 'the_content', 'tve_editor_content' ) ) {
			remove_filter( 'the_content', 'tve_editor_content', 10 );
		}
	}
endif;
add_action( 'wpna_facebook_article_pre_the_content_transform', 'wpna_disable_tve_editor' );

if ( ! function_exists( 'wpna_enable_tve_editor' ) ) :

	/**
	 * Add the TVE editor hook back in after the IA transformation is complete.
	 *
	 * @return void
	 */
	function wpna_enable_tve_editor() {
		if ( function_exists( 'tve_editor_content' ) && ! has_filter( 'the_content', 'tve_editor_content' ) ) {
			add_filter( 'the_content', 'tve_editor_content', 10 );
		}
	}
endif;
add_action( 'wpna_facebook_article_after_the_content_transform', 'wpna_enable_tve_editor' );
