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
	 * @param  string $content      Current post content.
	 * @return array  Shortcodes to ignore
	 */
	function wpna_pro_theme_editor_tags( $disabled_tags, $content ) {

		$pro_shortcodes = array(
			'cs_render_wrapper',
			'cs_acf',
			'x_alert',
			'x_icon_list',
			'x_icon_list_item',
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
			'x_columnize',
			'x_container',
			'x_content_band',
			'x_counter',
			'x_creative_cta',
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
			'x_raw_output',
			'x_recent_posts',
			'x_responsive_text',
			'cs_responsive_text',
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
			'x_toc',
			'x_toc_item',
			'x_video_embed',
			'x_video_player',
			'x_visibility',
			'x_widget_area',
			'cs_alert',
			'cs_icon_list',
			'cs_icon_list_item',
		);

		return array_merge( $disabled_tags, $pro_shortcodes );
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_disabled_tags', 'wpna_pro_theme_editor_tags', 10, 2 );


if ( ! function_exists( 'wpna_pro_theme_editor_override_tags' ) ) :

	/**
	 * Override the following tags with custom output functions.
	 *
	 * @param  array  $override_tags Shortcode functions to override.
	 * @param  string $content      Current post content.
	 * @return array  Shortcodes to ignore
	 */
	function wpna_pro_theme_editor_override_tags( $override_tags, $content ) {

		$override_tags['cs_content']        = 'wpna_pro_theme_do_shortcode';
		$override_tags['cs_section']        = 'wpna_pro_theme_do_shortcode';
		$override_tags['x_section']         = 'wpna_pro_theme_do_shortcode';
		$override_tags['cs_row']            = 'wpna_pro_theme_do_shortcode';
		$override_tags['x_row']             = 'wpna_pro_theme_do_shortcode';
		$override_tags['cs_column']         = 'wpna_pro_theme_do_shortcode';
		$override_tags['x_column']          = 'wpna_pro_theme_do_shortcode';
		$override_tags['x_raw_content']     = 'wpna_pro_theme_do_shortcode';
		$override_tags['cs_text']           = 'wpna_pro_theme_text_shortcode';
		$override_tags['x_text']            = 'wpna_pro_theme_text_shortcode';
		$override_tags['x_text_type']       = 'wpna_pro_theme_x_text_type_shortcode';
		$override_tags['x_custom_headline'] = 'wpna_pro_theme_x_custom_headline_shortcode';
		$override_tags['x_image']           = 'wpna_pro_theme_x_image_shortcode';

		return $override_tags;
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', 'wpna_pro_theme_editor_override_tags', 10, 2 );


if ( ! function_exists( 'wpna_pro_theme_do_shortcode' ) ) :

	/**
	 * Generic do_shortcode function, used for removing layout items in the editor.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_pro_theme_do_shortcode( $atts, $content = '' ) {
		return do_shortcode( $content ) . PHP_EOL;
	}
endif;

if ( ! function_exists( 'wpna_pro_theme_text_shortcode' ) ) :

	/**
	 * Generic do_shortcode function, used for removing layout items in the editor.
	 * Very similair to wpna_pro_theme_do_shortcode but uses wpautop first.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_pro_theme_text_shortcode( $atts, $content = '' ) {
		return wpna_pro_theme_do_shortcode( $atts, wpautop( $content ) ) . PHP_EOL;
	}
endif;

if ( ! function_exists( 'wpna_pro_theme_x_text_type_shortcode' ) ) :

	/**
	 * Shortcode override x_text_type.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_pro_theme_x_text_type_shortcode( $atts, $content = '' ) {
		global $_shortcode_content, $wp_scripts;

		$output = '';

		// Wrap it in an iFrame so we can achieve the desired effect.
		$output .= '<iframe class="column-width" width="320" height="60" scrolling="no" frameborder="0" allowTransparency="true">' . PHP_EOL;
		$output .= x_shortcode_text_type( $atts, $content ) . PHP_EOL;
		// Load in the JS for the fancy effects.
		if ( function_exists( 'CS' ) ) {
			ob_start();
			$wp_scripts->print_scripts( 'jquery' );
			$output .= ob_get_clean();
			// @codingStandardsIgnoreLine
			$output .= sprintf( '<script type="text/javascript" src="%s"></script>', esc_url( CS()->js( 'site/cs-body' ) . '?v=' . CS()->version() ) );
		}
		$output .= '</iframe>';

		// Generate a unique key.
		$shortcode_key = mt_rand();

		// Save the output next to the key.
		$_shortcode_content[ $shortcode_key ] = $output;

		// Return the placeholder.
		return '<figure class="op-interactive">' . PHP_EOL . $shortcode_key . PHP_EOL . '</figure>' . PHP_EOL;
	}
endif;

if ( ! function_exists( 'wpna_pro_theme_x_custom_headline_shortcode' ) ) :

	/**
	 * Shortcode override x_custom_headline.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_pro_theme_x_custom_headline_shortcode( $atts, $content = '' ) {
		// @codingStandardsIgnoreLine
		extract( shortcode_atts( array(
			'id'         => '',
			'class'      => '',
			'style'      => '',
			'type'       => '',
			'level'      => 'h2',
			'looks_like' => '',
			'accent'     => '',
		), $atts, 'x_custom_headline' ) );

		return "<{$level}>" . do_shortcode( $content ) . "</{$level}>" . PHP_EOL;
	}
endif;

if ( ! function_exists( 'wpna_pro_theme_x_image_shortcode' ) ) :

	/**
	 * Shortcode override x_image.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_pro_theme_x_image_shortcode( $atts, $content = '' ) {
		global $_shortcode_content;
		// @codingStandardsIgnoreLine
		extract( shortcode_atts( array(
			'id'               => '',
			'class'            => '',
			'style'            => '',
			'type'             => '',
			'float'            => '',
			'src'              => '',
			'alt'              => '',
			'link'             => '',
			'href'             => '',
			'title'            => '',
			'target'           => '',
			'info'             => '',
			'info_place'       => '',
			'info_trigger'     => '',
			'info_content'     => '',
			'lightbox_thumb'   => '',
			'lightbox_video'   => '',
			'lightbox_caption' => '',
		), $atts, 'x_image' ) );

		if ( is_numeric( $src ) ) {
			$src_info = wp_get_attachment_image_src( $src, 'full' );
			$src      = $src_info[0];
		}

		$output = sprintf( '<img src="%s">', esc_url( $src ) );

		// If enabled Likes or Comments on images has been enabled.
		$figure_attr = array();

		// Image likes. Check for post override then use global.
		$image_likes = wpna_get_post_option( get_the_ID(), 'fbia_image_likes' );

		if ( wpna_switch_to_boolean( $image_likes ) ) {
			$figure_attr[] = 'fb:likes';
		}

		// Image comments. Check for post override then use global.
		$image_comments = wpna_get_post_option( get_the_ID(), 'fbia_image_comments' );

		if ( wpna_switch_to_boolean( $image_comments ) ) {
			$figure_attr[] = 'fb:comments';
		}

		/**
		 * Allows filtering of the attributes set on the image figure.
		 *
		 * @since 1.0.0
		 * @param array $figure_attr Attributes for the figure element.
		 */
		$figure_attr = apply_filters( 'wpna_facebook_article_image_figure_attr', $figure_attr );

		if ( ! empty( $figure_attr ) ) {
			$figure_opening = '<figure data-feedback="' . implode( ', ', $figure_attr ) . '">';
		} else {
			$figure_opening = '<figure>';
		}

		// Generate a unique key.
		$shortcode_key = mt_rand();

		// Save the output next to the key.
		$_shortcode_content[ $shortcode_key ] = $output;

		// Return the placeholder.
		return $figure_opening . PHP_EOL . $shortcode_key . PHP_EOL . '</figure>' . PHP_EOL;
	}
endif;
