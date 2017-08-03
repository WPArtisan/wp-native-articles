<?php
/**
 * Theme.co have their own Editor. This ensures compatibility.
 *
 * @since 1.2.5
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_pro_theme_editor_tags' ) ) :

	/**
	 * Disables auto shortcode filtering in WPNA for the Pro theme editor.
	 *
	 * @param  array  $disabled_tags Shortcodes to ignore.
	 * @param  string $content      Current psot content.
	 * @return array  Shortcodes to ignore
	 */
	function wpna_pro_theme_editor_tags( $disabled_tags, $content ) {

		$pro_shortcodes = array(
			'cs_section',
			'x_section',
			'cs_render_wrapper',
			'cs_content',
			'cs_acf',
			'x_alert',
			'x_icon_list',
			'x_icon_list_item',
			'x_text',
			'x_accordion',
			'x_accordion_item',
			'x_audio_embed',
			'x_audio_player',
			'x_author',
			'x_block_grid',
			'cs_block_grid',
			'x_block_grid_item',
			'cs_block_grid_item',
			'x_blockquote',
			'x_button',
			'x_callout',
			'x_card',
			'x_clear',
			'x_code',
			'cs_column',
			'x_column',
			'x_columnize',
			'x_container',
			'x_content_band',
			'x_counter',
			'x_creative_cta',
			'x_custom_headline',
			'x_dropcap',
			'x_extra',
			'x_feature_box',
			'x_feature_headline',
			'x_feature_list',
			'x_gap',
			'x_google_map',
			'x_google_map_marker',
			'x_highlight',
			'x_icon',
			'x_image',
			'x_lightbox',
			'x_line',
			'x_map',
			'cs_pricing_table',
			'x_pricing_table',
			'cs_pricing_table_column',
			'x_pricing_table_column',
			'x_promo',
			'x_prompt',
			'x_protect',
			'x_pullquote',
			'x_raw_content',
			'x_raw_output',
			'x_recent_posts',
			'x_responsive_text',
			'cs_responsive_text',
			'cs_row',
			'x_row',
			'x_search',
			'x_share',
			'x_skill_bar',
			'x_slider',
			'x_slide',
			'x_tab_nav',
			'x_tab_nav_item',
			'x_tabs',
			'x_tab',
			'text_output',
			'x_text_type',
			'x_toc',
			'x_toc_item',
			'x_video_embed',
			'x_video_player',
			'x_visibility',
			'x_widget_area',
			'cs_text',
			'cs_alert',
			'cs_icon_list',
			'cs_icon_list_item',
		);

		return array_merge( $disabled_tags, $pro_shortcodes );
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_disabled_tags', 'wpna_pro_theme_editor_tags', 10, 2 );
