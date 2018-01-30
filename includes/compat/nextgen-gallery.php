<?php
/**
 * Nextgen-gallery can add galleries to RSS Feed.
 * Also adds related images, these need removing.
 *
 * @since 1.0.8
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_nextgen_gallery_related_gallery_compat' ) ) :

	/**
	 * Stops the related images being added to posts.
	 * Deregisteres the style.
	 *
	 * @return void
	 */
	function wpna_nextgen_gallery_related_gallery_compat() {
		wp_deregister_style( 'nextgen_gallery_related_images' );
		add_filter( 'ngg_show_related_gallery_content', '__return_empty_string', 99, 0 );

		if ( class_exists( 'C_Photocrati_Resource_Manager' ) ) {
			remove_action( 'wp_print_footer_scripts', array( C_Photocrati_Resource_Manager::$instance, 'get_resources' ), 1 );
			remove_action( 'admin_print_footer_scripts', array( C_Photocrati_Resource_Manager::$instance, 'get_resources' ), 1 );
		}
	}
endif;
add_action( 'wpna_pre_get_fbia_post', 'wpna_nextgen_gallery_related_gallery_compat', 999, 0 );
