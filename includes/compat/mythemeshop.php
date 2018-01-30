<?php
/**
 * Stops the MyThemeShop lazy image load function.
 * e.g. https://mythemeshop.com/themes/ad-sense/.
 *
 * @since 1.4.0
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_mts_remove_lazy_load' ) ) :

	/**
	 * Deregister the lazyload functions before the post renders.
	 *
	 * @return void
	 */
	function wpna_mts_remove_lazy_load() {
		remove_filter( 'wp_get_attachment_image_attributes', 'mts_image_lazy_load_attr', 10, 3 );
		remove_filter( 'the_content', 'mts_content_image_lazy_load_attr' );
	}
endif;
add_action( 'wpna_facebook_article_pre_the_content_transform', 'wpna_mts_remove_lazy_load' );
