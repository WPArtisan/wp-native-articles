<?php
/**
 * Visual Bakery compatibility.
 *
 * @link https://vc.wpbakery.com/
 * @since 1.2.2
 * @package wp-native-articles
 */

/**
 * WPBakery doesn't load the shortcodes in like a normal plugin.
 * Have to manually register them for the API method or they won't exist.
 *
 * @since 1.5.0
 * @return void
 */
function wpna_visual_bakery_load_shortcodes() {
	if ( class_exists( 'WPBMap' ) ) {
		WPBMap::addAllMappedShortcodes();
	}
}
add_action( 'wpna_facebook_article_pre_the_content_transform', 'wpna_visual_bakery_load_shortcodes', 10 );

if ( ! function_exists( 'wpna_visual_bakery_add_ignore_shortcodes' ) ) :

	/**
	 * Stop Visual Bakery shortcodes getting wrapped.
	 *
	 * A lot of the Visual Bakery stuff can't be translated to IA. This stops
	 * the shortcodes getting wrapped in iFrames so the parser can take care
	 * of it.
	 *
	 * @since 1.2.2
	 *
	 * @param  array  $disabled_tags Array of shortcode tags to NOT iFrame wrap.
	 * @param  string $content The current post content.
	 * @return array
	 */
	function wpna_visual_bakery_add_ignore_shortcodes( $disabled_tags, $content ) {
		// Ensure it's an array (should always be).
		if ( ! is_array( $disabled_tags ) ) {
			$disabled_tags = array();
		}

		$ignore_shortcodes = wpna_get_visual_bakery_ignore_shortcodes();

		// By default, let the parser tackle the shortcodes. Gives it a little
		// bit of future proofing if new shortcodes are added.
		$disabled_tags = array_merge( $disabled_tags, $ignore_shortcodes );

		return $disabled_tags;
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_disabled_tags', 'wpna_visual_bakery_add_ignore_shortcodes', 10, 2 );

if ( ! function_exists( 'wpna_visual_bakery_add_override_shortcodes' ) ) :

	/**
	 * Custom output for some Visual Bakery shortcodes.
	 *
	 * Some of the Visual Bakery shortcodes are fairly indepth and can't be
	 * translated to IA. This maps them to custom outputs so we can control the
	 * output
	 *
	 * @since 1.2.2
	 *
	 * @param  array  $override_tags Array of shortcode tags with custom functions.
	 * @param  string $content The current post content.
	 * @return array
	 */
	function wpna_visual_bakery_add_override_shortcodes( $override_tags, $content ) {

		// Ensure it's an array (should always be).
		if ( ! is_array( $override_tags ) ) {
			$override_tags = array();
		}

		$override_shortcodes = wpna_get_visual_bakery_override_shortcodes();

		$override_tags = array_merge( $override_tags, $override_shortcodes );

		return $override_tags;
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', 'wpna_visual_bakery_add_override_shortcodes', 10, 2 );

if ( ! function_exists( 'wpna_get_visual_bakery_colors' ) ) :

	/**
	 * Colors from Visual Bakery.
	 *
	 * Visual Bakery uses custom CSS color variables, this loads them in.
	 *
	 * @return array
	 */
	function wpna_get_visual_bakery_colors() {
		return array(
			'blue'        => '#5472D2',
			'turquoise'   => '#00C1CF',
			'pink'        => '#FE6C61',
			'violet'      => '#8D6DC4',
			'peacoc'      => '#4CADC9',
			'chino'       => '#CEC2AB',
			'mulled_wine' => '#50485B',
			'vista_blue'  => '#75D69C',
			'black'       => '#2A2A2A',
			'grey'        => '#EBEBEB',
			'orange'      => '#F7BE68',
			'sky'         => '#5AA1E3',
			'green'       => '#6DAB3C',
			'juicy_pink'  => '#F4524D',
			'sandy_brown' => '#F79468',
			'purple'      => '#B97EBB',
			'white'       => '#FFFFFF',
			'grace'       => '#AED13B',
		);
	}
endif;

if ( ! function_exists( 'wpna_get_visual_bakery_ignore_shortcodes' ) ) :

	/**
	 * Returns an array of shortcodes from Visual Bakery that shouldn't be wrapped.
	 *
	 * By default all shortcodes are exempt from the content transformer and
	 * are wrapped in iFrames, these ones aren't and go through the transformer.
	 *
	 * @return array
	 */
	function wpna_get_visual_bakery_ignore_shortcodes() {
		// Ensure the mapping class exists.
		if ( ! class_exists( 'WPBMap' ) ) {
			return array();
		}

		// Get all the shortcodes.
		$ignore_shortcodes = WPBMap::getShortCodes();

		// We only want the keys.
		$ignore_shortcodes = array_keys( $ignore_shortcodes );

		// Get the overridden shortcodes.
		$override_shortcodes = wpna_get_visual_bakery_override_shortcodes();

		// Remove them from the ignore ones.
		$ignore_shortcodes = array_diff( $ignore_shortcodes, array_keys( $override_shortcodes ) );

		return $ignore_shortcodes;
	}
endif;

if ( ! function_exists( 'wpna_get_visual_bakery_override_shortcodes' ) ) :

	/**
	 * Returns an array of shortcodes from Visual Bakery that we are overriding
	 * the output functions for.
	 *
	 * Fr these shortcodes we're transforming the content into IA compatible syntax.
	 *
	 * @return array
	 */
	function wpna_get_visual_bakery_override_shortcodes() {
		return array(
			'vc_row'            => 'wpna_vc_row_shortcode',
			'vc_column'         => 'wpna_vc_column_shortcode',
			'vc_column_text'    => 'wpna_vc_column_text_shortcode',
			'vc_separator'      => 'wpna_vc_separator_shortcode',
			'vc_text_separator' => 'wpna_vc_text_separator_shortcode',
			'vc_gmaps'          => 'wpna_vc_gmaps_shortcode',
			'vc_video'          => 'wpna_vc_video_shortcode',
			'vc_single_image'   => 'wpna_vc_single_image_shortcode',
			'vc_tta_tabs'       => 'wpna_vc_tta_tabs_shortcode',
			'vc_tta_section'    => 'wpna_vc_tta_section_shortcode',
			'vc_flickr'         => 'wpna_vc_flickr_shortcode',

			// Social.
			'vc_facebook'       => 'wpna_vc_facebook_shortcode',
			'vc_tweetmeme'      => 'wpna_vc_tweetmeme_shortcode',
			'vc_googleplus'     => 'wpna_vc_googleplus_shortcode',
			'vc_pinterest'      => 'wpna_vc_pinterest_shortcode',

			// Structure.
			'vc_raw_html'       => 'wpna_vc_raw_html_shortcode',
			'vc_raw_js'         => 'wpna_vc_raw_js_shortcode',
			'vc_widget_sidebar' => 'wpna_vc_widget_sidebar_shortcode',

			// WordPress Widgets.
			'vc_wp_search'      => 'wpna_vc_wp_search_shortcode',
			'vc_wp_meta'        => 'wpna_vc_wp_meta_shortcode',
			'vc_wp_tagcloud'    => 'wpna_vc_wp_tagcloud_shortcode',
			'vc_wp_categories'  => 'wpna_vc_wp_categories_shortcode',
			'vc_wp_archives'    => 'wpna_vc_wp_archives_shortcode',
			'vc_wp_calendar'    => 'wpna_vc_wp_calendar_shortcode',
		);
	}
endif;

if ( ! function_exists( 'wpna_vc_row_shortcode' ) ) :

	/**
	 * Shortcode override vc_row.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_row_shortcode( $atts, $content = '' ) {
		return wpb_js_remove_wpautop( $content );
	}
endif;

if ( ! function_exists( 'wpna_vc_column_shortcode' ) ) :

	/**
	 * Shortcode override vc_column.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_column_shortcode( $atts, $content = '' ) {
		return wpb_js_remove_wpautop( $content );
	}
endif;

if ( ! function_exists( 'wpna_vc_column_text_shortcode' ) ) :

	/**
	 * Shortcode override vc_column_text.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_column_text_shortcode( $atts, $content = '' ) {
		return wpb_js_remove_wpautop( $content, true ) . PHP_EOL;
	}
endif;

if ( ! function_exists( 'wpna_vc_separator_shortcode' ) ) :

	/**
	 * Shortcode override vc_separator.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_separator_shortcode( $atts, $content = '' ) {
		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_separator', $atts );

		$colors = wpna_get_visual_bakery_colors();

		// Workout the border style.
		$border_width = ! empty( $atts['border_width'] ) ? absint( $atts['border_width'] ) : 1;
		$border_style = ! empty( $atts['style'] ) ? sanitize_text_field( $atts['style'] ) : 'solid';

		$border_color = '000000';
		if ( ! empty( $atts['color'] ) && isset( $colors[ $atts['color'] ] ) ) {
			$border_color = sanitize_text_field( $colors[ $atts['color'] ] );
		} elseif ( ! empty( $atts['color'] ) && 'custom' === $atts['color'] && ! empty( $atts['accent_color'] ) ) {
			$border_color = sanitize_text_field( $atts['accent_color'] );
		}

		$border_style = "{$border_width}px {$border_style} {$border_color}";

		// Default to full width.
		$div_classes = 'no-margin';

		$output = '';

		// The outer iFrame.
		$output .= '<iframe class="no-margin" width="960" height="' . $border_width . '">' . PHP_EOL;

		// If there's a CSS animation load in the CSS and setup the classes.
		if ( ! empty( $atts['css_animation'] ) && 'none' !== $atts['css_animation'] ) {
			// Add the animation classes to the div.
			$div_classes .= ' animate ' . $atts['css_animation'];
			// Add in the animation CSS.
			// @codingStandardsIgnoreLine
			$output .= '<link rel="stylesheet" href="' . vc_asset_url( 'lib/bower/animate-css/animate.min.css' ) . '">' . PHP_EOL;
		}

		// Overall width of the element.
		$div_width = ! empty( $atts['el_width'] ) ? absint( $atts['el_width'] ) : 100;

		// How to align the overall element.
		$div_margin = '0 auto';
		if ( ! empty( $atts['align'] ) ) {
			if ( 'align_left' === $atts['align'] ) {
				$div_margin = '0 auto 0 0';
			} elseif ( 'align_right' === $atts['align'] ) {
				$div_margin = '0 0 0 auto';
			}
		}

		// The wrapper div. Deals with width and overall alignment.
		$output .= '<div class="' . esc_attr( $div_classes ) . '" style="width: ' . $div_width . '%; margin: ' . esc_attr( $div_margin ) . ';">' . PHP_EOL;

			$output .= '<span style="min-width: 10%; height: ' . $border_width . 'px;">' . PHP_EOL;
			$output .= '<span style="height: ' . $border_width . 'px; border-top: ' . $border_style . ';display: block;position: relative;top: 1px; width: 100%;"></span>' . PHP_EOL;
			$output .= '</span>' . PHP_EOL;

		// Wrapper div.
		$output .= '</div>' . PHP_EOL;

		$output .= '</iframe>';

		// Get the placeholder.
		$placeholder = wpna_content_parser_get_placeholder( '<figure class="op-interactive">' . PHP_EOL . $output . PHP_EOL . '</figure>' );

		return '<pre>' . $placeholder . '</pre>';
	}
endif;

if ( ! function_exists( 'wpna_vc_text_separator_shortcode' ) ) :

	/**
	 * Shortcode override vc_text_separator.
	 *
	 * Slightly different to vc_separator annoyingly.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_text_separator_shortcode( $atts, $content = '' ) {
		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_text_separator', $atts );

		$colors = wpna_get_visual_bakery_colors();

		// Workout the border style.
		$border_width = ! empty( $atts['border_width'] ) ? absint( $atts['border_width'] ) : 1;
		$border_style = ! empty( $atts['style'] ) ? sanitize_text_field( $atts['style'] ) : 'solid';

		$border_color = '000000';
		if ( ! empty( $atts['color'] ) && isset( $colors[ $atts['color'] ] ) ) {
			$border_color = sanitize_text_field( $colors[ $atts['color'] ] );
		} elseif ( ! empty( $atts['color'] ) && 'custom' === $atts['color'] && ! empty( $atts['accent_color'] ) ) {
			$border_color = sanitize_text_field( $atts['accent_color'] );
		}

		$border_style = "{$border_width}px {$border_style} {$border_color}";

		// Default to full width.
		$div_classes = 'no-margin';

		$output = '';

		// The outer iFrame.
		$output .= '<iframe class="no-margin" width="960" height="' . $border_width . '">' . PHP_EOL;

		// If there's a CSS animation load in the CSS and setup the classes.
		if ( ! empty( $atts['css_animation'] ) && 'none' !== $atts['css_animation'] ) {
			// Add the animation classes to the div.
			$div_classes .= ' animate ' . $atts['css_animation'];
			// Add in the animation CSS.
			// @codingStandardsIgnoreLine
			$output .= '<link rel="stylesheet" href="' . vc_asset_url( 'lib/bower/animate-css/animate.min.css' ) . '">' . PHP_EOL;
		}

		// Overall width of the element.
		$div_width = ! empty( $atts['el_width'] ) ? absint( $atts['el_width'] ) : 100;

		// How to align the overall element.
		$div_margin = '0 auto';
		if ( ! empty( $atts['align'] ) ) {
			if ( 'align_left' === $atts['align'] ) {
				$div_margin = '0 auto 0 0';
			} elseif ( 'align_right' === $atts['align'] ) {
				$div_margin = '0 0 0 auto';
			}
		}

		// The wrapper div. Deals with width and overall alignment.
		$output .= '<div class="' . esc_attr( $div_classes ) . '" style="width: ' . $div_width . '%; margin: ' . esc_attr( $div_margin ) . '; display: flex; flex-direction: row; flex-wrap: nowrap; align-items: center;">' . PHP_EOL;

		// Show the first rule if it's required.
		if ( ! empty( $atts['title_align'] ) && 'separator_align_left' !== $atts['title_align'] ) {
			$output .= '<span style="flex: 1 1 auto; min-width: 10%; height: 1px; padding-right:0.8em;">' . PHP_EOL;
			$output .= '<span style="height: 1px; border-top: ' . $border_style . ';display: block;position: relative;top: 1px;width: 100%;"></span>' . PHP_EOL;
			$output .= '</span>' . PHP_EOL;
		}

		// Add in the title.
		$output .= '<h2 style="flex: 0 1 auto; margin: 15px 0px; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;font-size:1em;">' . esc_html( $atts['title'] ) . '</h2>' . PHP_EOL;

		// Show the second rule if it's required.
		if ( ! empty( $atts['title_align'] ) && 'separator_align_right' !== $atts['title_align'] ) {
			$output .= '<span style="flex: 1 1 auto; min-width: 10%; height: 1px; padding-left:0.8em;">' . PHP_EOL;
			$output .= '<span style="height: 1px; border-top: ' . $border_style . ';display: block;position: relative;top: 1px;width: 100%;"></span>' . PHP_EOL;
			$output .= '</span>' . PHP_EOL;
		}

		// Wrapper div.
		$output .= '</div>' . PHP_EOL;

		$output .= '</iframe>';

		// Get the placeholder.
		$placeholder = wpna_content_parser_get_placeholder( '<figure class="op-interactive">' . PHP_EOL . $output . PHP_EOL . '</figure>' );

		return '<pre>' . $placeholder . '</pre>';
	}
endif;

if ( ! function_exists( 'wpna_vc_gmaps_shortcode' ) ) :

	/**
	 * Shortcode override vc_gmaps.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_gmaps_shortcode( $atts, $content = '' ) {
		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_gmaps', $atts );

		if ( empty( $atts['link'] ) ) {
			return '';
		}

		$link = trim( vc_value_from_safe( $atts['link'] ) );

		// 2s location name
		// 1d zoom level
		// 2d lat
		// 3d lng
		// 5e map type
		preg_match_all( '/!?(2s|1d|2d|3d|5e)(.+?)!/', $link, $matches );

		$map_params = array();

		foreach ( $matches[1] as $key => $value ) {
			if ( ! isset( $map_params[ $value ] ) ) {
				$map_params[ $value ] = $matches[2][ $key ];
			} else {
				$map_params[ $value ] .= ', ' . $matches[2][ $key ];
			}
		}

		// Work out the map type.
		$style = 'standard';
		if ( 1 === (int) $map_params['5e'] ) {
			$style = 'hybrid'; // satellite.
		}

		// Construct the map.
		$map = array(
			'type'       => 'Feature',
			'geometry'   => array(
				'type'        => 'Point',
				'coordinates' => array(
					$map_params['3d'],
					$map_params['2d'],
				),
			),
			'properties' => array(
				'title'  => urldecode( $map_params['2s'] ),
				'radius' => $map_params['1d'],
				'pivot'  => true,
				'style'  => $style,
			),
		);

		// Generate the output.
		$output  = '<script type="application/json" class="op-geotag">' . PHP_EOL;
		$output .= wp_json_encode( $map ) . PHP_EOL;
		$output .= '</script>';

		// Check if there's a title.
		if ( ! empty( $atts['title'] ) ) {
			$output .= PHP_EOL . '<figcaption><h1>' . esc_html( $atts['title'] ) . '</h1></figcaption>';
		}

		// Get the placeholder.
		$placeholder = wpna_content_parser_get_placeholder( '<figure class="op-map">' . PHP_EOL . $output . PHP_EOL . '</figure>' );

		return '<pre>' . $placeholder . '</pre>';
	}
endif;

if ( ! function_exists( 'wpna_vc_video_shortcode' ) ) :

	/**
	 * Shortcode override vc_video.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_video_shortcode( $atts, $content = '' ) {
		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_video', $atts );

		if ( empty( $atts['link'] ) ) {
			return '';
		}

		// Format the video.
		$output = sprintf( '<iframe class="no-margin" width="320" height="180" src="%s"></iframe>', esc_url( $atts['link'] ) );

		// If there's a title add that in.
		if ( ! empty( $atts['title'] ) ) {
			$output .= PHP_EOL . '<figcaption>' . esc_html( $atts['title'] ) . '</figcaption>';
		}

		// Get the placeholder.
		$placeholder = wpna_content_parser_get_placeholder( '<figure class="op-interactive">' . PHP_EOL . $output . PHP_EOL . '</figure>' );

		return '<pre>' . $placeholder . '</pre>';
	}
endif;

if ( ! function_exists( 'wpna_vc_single_image_shortcode' ) ) :

	/**
	 * Shortcode override vc_single_image.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_single_image_shortcode( $atts, $content = '' ) {
		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_single_image', $atts );

		$output = '';

		$source = $atts['source'];

		// Get the caption options.
		$caption_settings = array_filter( array(
			wpna_get_post_option( get_the_ID(), 'fbia_caption_font_size', null ),
			wpna_get_post_option( get_the_ID(), 'fbia_caption_vertical_position', null ),
			wpna_get_post_option( get_the_ID(), 'fbia_caption_horizontal_position', null ),
		) );

		switch ( $source ) {
			case 'media_library':
			case 'featured_image':
				if ( 'featured_image' === $source ) {
					$post_id = get_the_ID();
					if ( $post_id && has_post_thumbnail( $post_id ) ) {
						$img_id = get_post_thumbnail_id( $post_id );
					} else {
						$img_id = 0;
					}
				} else {
					$img_id = preg_replace( '/[^\d]/', '', $atts['image'] );
				}

				if ( $img_id && $wp_image = wp_get_attachment_image_src( $img_id, 'full' ) ) {
					$output .= sprintf( '<img src="%s">', esc_url( $wp_image[0] ) );
				}

				if ( 'yes' === $atts['add_caption'] ) {
					$media_post = get_post( $img_id );
					$caption    = $media_post->post_excerpt;
					if ( ! empty( $caption ) ) {
						$output .= sprintf( '<figcaption>%s</figcaption>', esc_html( $caption ) );
					}
				}

				break;

			case 'external_link':
				if ( ! empty( $atts['custom_src'] ) ) {
					$output .= sprintf( '<img src="%s">', esc_url( $atts['custom_src'] ) );
				}

				if ( ! empty( $atts['caption'] ) ) {
					$output .= sprintf( '<figcaption>%s</figcaption>', esc_html( $atts['caption'] ) );
				}

				break;

			default:
				$img = false;
		}

		if ( empty( $output ) ) {
			return '';
		}

		// Set the caption settings, position, size etc.
		if ( ! empty( $caption_settings ) ) {
			$output = str_ireplace( '<figcaption>', '<figcaption class="' . implode( ', ', array_filter( $caption_settings ) ) . '">', $output );
		}

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

		// Get the placeholder.
		$placeholder = wpna_content_parser_get_placeholder( $figure_opening . $output . '</figure>' );

		return '<pre>' . $placeholder . '</pre>';
	}
endif;

if ( ! function_exists( 'wpna_vc_facebook_shortcode' ) ) :

	/**
	 * Shortcode override vc_facebook.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_facebook_shortcode( $atts, $content = '' ) {
		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_facebook', $atts );

		$output = '';

		// Get the URL to like.
		$post_url = get_permalink();

		// Construct the like box URL.
		$iframe_src = add_query_arg( array(
			'href'        => $post_url,
			'layout'      => $atts['type'],
			'show_faces'  => 'false',
			'action'      => 'like',
			'colorscheme' => 'light',
		), 'https://www.facebook.com/plugins/like.php' );

		// We double wrap the iFrame so we can apply vertical spacing.
		$output .= '<iframe class="column-width" width="320" height="180" scrolling="no" frameborder="0" allowTransparency="true">' . PHP_EOL;
		$output .= '<div class="width:100%; height:100%;">' . PHP_EOL;
		// The like box.
		$output .= '<iframe width="100%" height="100%" src="' . esc_url( $iframe_src ) . '" scrolling="no" frameborder="0" allowTransparency="true"></iframe>' . PHP_EOL;
		$output .= '</div>' . PHP_EOL;
		$output .= '</iframe>';

		// Get the placeholder.
		$placeholder = wpna_content_parser_get_placeholder( '<figure class="op-interactive">' . PHP_EOL . $output . PHP_EOL . '</figure>' );

		return '<pre>' . $placeholder . '</pre>';
	}
endif;

if ( ! function_exists( 'wpna_vc_tweetmeme_shortcode' ) ) :

	/**
	 * Shortcode override vc_tweetmeme.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_tweetmeme_shortcode( $atts, $content = '' ) {
		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_tweetmeme', $atts );

		$output = '';

		$tweet_btn_text = '';
		$type           = $atts['type'];

		switch ( $type ) {
			case 'follow':
				$tweet_btn_text = esc_html__( 'Follow', 'wp-native-articles' );
				break;

			case 'mention':
				$tweet_btn_text = esc_html__( 'Tweet to', 'wp-native-articles' );
				break;

			case 'share':
			case 'hashtag':
				$tweet_btn_text = esc_html__( 'Tweet', 'wp-native-articles' );
				break;
			default:
				$type           = 'share';
				$tweet_btn_text = esc_html__( 'Tweet', 'wp-native-articles' );
				break;
		}
		$data    = array();
		$classes = array();

		if ( ! empty( $atts['large_button'] ) ) {
			$data['data-size'] = 'large';
		}

		$url = 'https://twitter.com/';
		if ( 'share' === $type ) {
			$url       = 'https://twitter.com/share';
			$classes[] = 'twitter-share-button';

			if ( 'page_url' !== $atts['share_use_page_url'] ) {
				$data['data-url'] = $atts['share_use_custom_url'];
			}
			if ( 'page_title' !== $atts['share_text_page_title'] ) {
				$data['data-text'] = $atts['share_text_custom_text'];
			}
			if ( ! empty( $atts['share_via'] ) ) {
				$data['data-via'] = $atts['share_via'];
			}
			if ( ! empty( $atts['share_recommend'] ) ) {
				$data['data-related'] = $atts['share_recommend'];
			}
			if ( ! empty( $atts['share_hashtag'] ) ) {
				$data['data-hashtags'] = $atts['share_hashtag'];
			}
		} elseif ( 'follow' === $type ) {
			$url       = 'https://twitter.com/';
			$classes[] = 'twitter-follow-button';
			if ( ! empty( $atts['follow_user'] ) ) {
				$url            .= esc_attr( $atts['follow_user'] );
				$tweet_btn_text .= ' @' . esc_attr( $atts['follow_user'] );
			}
			if ( 'yes' !== (string) $atts['follow_show_username'] ) {
				$data['data-show-screen-name'] = 'false';
			}
			$data['data-show-count'] = ( ! ! $atts['show_followers_count'] ) ? 'true' : 'false';

		} elseif ( 'hashtag' === $type ) {
			$url       = 'https://twitter.com/intent/tweet?';
			$classes[] = 'twitter-hashtag-button';
			$url_atts  = array();
			if ( ! empty( $atts['hashtag_hash'] ) ) {
				$url_atts[]      = 'button_hashtag =' . esc_attr( $atts['hashtag_hash'] );
				$tweet_btn_text .= ' #' . esc_attr( $atts['hashtag_hash'] );
			}
			if ( 'yes' !== $atts['hashtag_no_default'] ) {
				$url_atts[] = 'text=' . esc_attr( $atts['hashtag_custom_tweet_text'] );
			}
			$url .= implode( '&', $url_atts );

			$rel = array();
			if ( ! empty( $atts['hashtag_recommend_1'] ) ) {
				$rel[] = $atts['hashtag_recommend_1'];
			}
			if ( ! empty( $atts['hashtag_recommend_1'] ) ) {
				$rel[] = $atts['hashtag_recommend_2'];
			}
			if ( ! empty( $rel ) ) {
				$data['data-related'] = implode( ',', $rel );
			}
			if ( 'yes' !== $atts['hashtag_no_url'] ) {
				$data['data-url'] = $atts['hashtag_custom_tweet_url'];
			}
		} elseif ( 'mention' === $type ) {
			$url       = 'https://twitter.com/intent/tweet?';
			$classes[] = 'twitter-mention-button';
			$url_atts  = array();
			if ( ! empty( $atts['mention_tweet_to'] ) ) {
				$url_atts[]      = 'screen_name =' . esc_attr( $atts['mention_tweet_to'] );
				$tweet_btn_text .= ' @' . esc_attr( $atts['mention_tweet_to'] );
			}
			if ( 'yes' !== $atts['mention_no_default'] ) {
				$url_atts[] = 'text=' . esc_attr( $atts['mention_custom_tweet_text'] );
			}
			$url .= implode( '&', $url_atts );

			$rel = array();
			if ( ! empty( $atts['mention_recommend_1'] ) ) {
				$rel[] = $atts['mention_recommend_1'];
			}
			if ( ! empty( $atts['mention_recommend_1'] ) ) {
				$rel[] = $atts['mention_recommend_1'];
			}
			if ( ! empty( $rel ) ) {
				$data['data-related'] = implode( ',', $rel );
			}
		}

		if ( ! empty( $atts['lang'] ) ) {
			$data['data-lang'] = $atts['lang'];
		}
		$data_imploded = array();
		foreach ( $data as $k => $v ) {
			$data_imploded[] = $k . '="' . esc_attr( $v ) . '"';
		}
		$wrapper_attributes = array();
		if ( ! empty( $atts['el_id'] ) ) {
			$wrapper_attributes[] = 'id="' . esc_attr( $atts['el_id'] ) . '"';
		}

		$output .= '<iframe class="column-width" width="320" height="20" scrolling="no" frameborder="0" allowTransparency="true">' . PHP_EOL;
		$output .= '<a href="' . esc_url( $url ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '" ' . implode( ' ', $data_imploded ) . '>' . $tweet_btn_text . '</a>' . PHP_EOL;
		$output .= '<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\'://platform.twitter.com/widgets.js\';fjs.parentNode.insertBefore(js,fjs);}}(document, \'script\', \'twitter-wjs\');</script>' . PHP_EOL;
		$output .= '</iframe>';

		// Get the placeholder.
		$placeholder = wpna_content_parser_get_placeholder( '<figure class="op-interactive">' . PHP_EOL . $output . PHP_EOL . '</figure>' );

		return '<pre>' . $placeholder . '</pre>';
	}
endif;

if ( ! function_exists( 'wpna_vc_googleplus_shortcode' ) ) :

	/**
	 * Shortcode override vc_googleplus.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_googleplus_shortcode( $atts, $content = '' ) {
		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_googleplus', $atts );

		$output = '';

		$type = $annotation = $widget_width = $css = $el_class = $el_id = $css_animation = '';
		// @codingStandardsIgnoreLine
		extract( $atts );

		if ( empty( $annotation ) ) {
			$annotation = 'bubble';
		}
		$params  = '';
		$params .= ( '' !== $type ) ? ' data-size="' . $type . '"' : '';
		$params .= ( '' !== $annotation ) ? ' data-annotation="' . $annotation . '"' : '';

		if ( empty( $type ) ) {
			$type = 'standard';
		}
		if ( 'inline' === $annotation && strlen( $widget_width ) > 0 ) {
			$params .= ' data-width="' . (int) $widget_width . '"';
		}

		// Switch to HTML5 compat elemetns.
		$output .= '<iframe class="column-width" width="320" height="20" scrolling="no" frameborder="0" allowTransparency="true">' . PHP_EOL;
		// @codingStandardsIgnoreLine
		$output .= '<script src="https://apis.google.com/js/platform.js" async defer></script>' . PHP_EOL;
		$output .= '<div class="g-plusone"' . $params . '></div>' . PHP_EOL;
		$output .= '</iframe>';

		// Get the placeholder.
		$placeholder = wpna_content_parser_get_placeholder( '<figure class="op-interactive">' . PHP_EOL . $output . PHP_EOL . '</figure>' );

		return '<pre>' . $placeholder . '</pre>';
	}
endif;

if ( ! function_exists( 'wpna_vc_pinterest_shortcode' ) ) :

	/**
	 * Shortcode override vc_pinterest.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_pinterest_shortcode( $atts, $content = '' ) {
		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_googleplus', $atts );

		$type = $annotation = '';
		// @codingStandardsIgnoreLine
		extract( $atts );

		$output = '';

		$url = rawurlencode( get_permalink() );

		if ( has_post_thumbnail() ) {
			$img_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
			$media   = ( is_array( $img_url ) ) ? '&amp;media=' . rawurlencode( $img_url[0] ) : '';
		} else {
			$media = '';
		}
		$excerpt     = is_object( $post ) && isset( $post->post_excerpt ) ? $post->post_excerpt : '';
		$description = ( '' !== $excerpt ) ? '&amp;description=' . rawurlencode( strip_tags( $excerpt ) ) : '';

		$output .= '<iframe class="column-width" width="320" height="20" scrolling="no" frameborder="0" allowTransparency="true">' . PHP_EOL;
		// @codingStandardsIgnoreLine
		$output .= '<script async defer src="https://assets.pinterest.com/js/pinit.js"></script>' . PHP_EOL;
		$output .= '<a href="https://pinterest.com/pin/create/button/?url=' . $url . $media . $description . '" class="pin-it-button" count-layout="' . $type . '"><img border="0" src="https://assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>' . PHP_EOL;
		$output .= '</iframe>';

		// Get the placeholder.
		$placeholder = wpna_content_parser_get_placeholder( '<figure class="op-interactive">' . PHP_EOL . $output . PHP_EOL . '</figure>' );

		return '<pre>' . $placeholder . '</pre>';
	}
endif;

if ( ! function_exists( 'wpna_vc_raw_html_shortcode' ) ) :

	/**
	 * Shortcode override vc_raw_html.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_raw_html_shortcode( $atts, $content = '' ) {
		$content = rawurldecode( base64_decode( strip_tags( $content ) ) );

		// We want the RAW html to go through that content parser.
		return $content;
	}
endif;

if ( ! function_exists( 'wpna_vc_raw_js_shortcode' ) ) :

	/**
	 * Shortcode override vc_raw_js.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_raw_js_shortcode( $atts, $content = '' ) {
		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_raw_js', $atts );

		$content = rawurldecode( base64_decode( strip_tags( $content ) ) );

		// There's very little we can do here. Just wrap it in an iFrame so it'll run.
		$output  = '<iframe class="column-width" width="320" height="20" scrolling="no" frameborder="0" allowTransparency="true">' . PHP_EOL;
		$output .= $content . PHP_EOL;
		$output .= '</iframe>';

		// Get the placeholder.
		$placeholder = wpna_content_parser_get_placeholder( '<figure class="op-interactive">' . PHP_EOL . $output . PHP_EOL . '</figure>' );

		return '<pre>' . $placeholder . '</pre>';
	}
endif;

if ( ! function_exists( 'wpna_vc_widget_sidebar_shortcode' ) ) :

	/**
	 * Shortcode override vc_widget_sidebar.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_widget_sidebar_shortcode( $atts, $content = '' ) {
		// Return an empty string. This won't work in IA.
		return '';
	}
endif;

if ( ! function_exists( 'wpna_vc_wp_search_shortcode' ) ) :

	/**
	 * Shortcode override vc_wp_search.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_wp_search_shortcode( $atts, $content = '' ) {
		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_wp_search', $atts );

		$output = '';

		$default_styling = '* { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 1em }';

		/**
		 * Filter the default CSS applied to the search form.
		 *
		 * @since 1.2.2
		 * @var string
		 */
		$default_styling = apply_filters( 'wpna_vc_wp_search_shortcode_styles', $default_styling );

		$output .= '<iframe class="column-width" width="320" height="180" scrolling="no" frameborder="0" allowTransparency="true">' . PHP_EOL;
		$output .= '<style>' . $default_styling . '</style>' . PHP_EOL;
		$output .= get_search_form( false ) . PHP_EOL;
		$output .= '</iframe>';

		$title = '';
		if ( ! empty( $atts['title'] ) ) {
			$title = '<h2>' . esc_html( $atts['title'] ) . '</h2>' . PHP_EOL;
		}

		// Get the placeholder.
		$placeholder = wpna_content_parser_get_placeholder( '<figure class="op-interactive">' . PHP_EOL . $output . PHP_EOL . '</figure>' );

		return '<pre>' . $placeholder . '</pre>';
	}
endif;

if ( ! function_exists( 'wpna_vc_wp_meta_shortcode' ) ) :

	/**
	 * Shortcode override vc_wp_meta.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_wp_meta_shortcode( $atts, $content = '' ) {
		// We can't display this.
		// If a user is logged in when the article is synced via the API then
		// it will show the logged in URLs which will be nonsense.
		return '';
	}
endif;

if ( ! function_exists( 'wpna_vc_wp_tagcloud_shortcode' ) ) :

	/**
	 * Shortcode override vc_wp_tagcloud.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_wp_tagcloud_shortcode( $atts, $content = '' ) {
		global $wp_widget_factory;

		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_wp_tagcloud', $atts );

		$type = 'WP_Widget_Tag_Cloud';

		$default_styling = '* { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 1em } a { color: grey; text-decoration:none; }';

		/**
		 * Filter the default CSS applied to the search form.
		 *
		 * @since 1.2.2.
		 * @var string.
		 */
		$default_styling = apply_filters( 'wpna_vc_wp_tagcloud_shortcode_styles', $default_styling );

		$output = '';

		// To avoid unwanted warnings let's check before using widget.
		if ( is_object( $wp_widget_factory ) && isset( $wp_widget_factory->widgets, $wp_widget_factory->widgets[ $type ] ) ) {
			ob_start();
			the_widget( $type, $atts );
			$widget = ob_get_clean();
		} else {
			return '';
		}

		$output .= '<iframe class="column-width" width="320" height="180" scrolling="no" frameborder="0" allowTransparency="true">' . PHP_EOL;
		$output .= '<style>' . $default_styling . '</style>' . PHP_EOL;
		$output .= $widget . PHP_EOL;
		$output .= '</iframe>';

		// Remove the widget title. Add the title before the cloud.
		$title = '';
		if ( ! empty( $atts['title'] ) ) {
			$output = str_ireplace( '<h2 class ="widgettitle">' . $atts['title'] . '</h2>', '', $output );
			$title  = '<h2>' . esc_html( $atts['title'] ) . '</h2>';
		}

		// Get the placeholder.
		$placeholder = wpna_content_parser_get_placeholder( '<figure class="op-interactive">' . PHP_EOL . $output . PHP_EOL . '</figure>' );

		return '<pre>' . $placeholder . '</pre>';
	}
endif;

if ( ! function_exists( 'wpna_vc_wp_categories_shortcode' ) ) :

	/**
	 * Shortcode override vc_wp_categories.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_wp_categories_shortcode( $atts, $content = '' ) {
		global $wp_widget_factory;

		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_wp_categories', $atts );

		$type = 'WP_Widget_Categories';

		$output = '';
		// @codingStandardsIgnoreLine
		extract( $atts );

		$options = explode( ',', $options );

		$atts['dropdown'] = false; // We never want a dropwdown.

		if ( in_array( 'count', $options, true ) ) {
			$atts['count'] = true;
		}
		if ( in_array( 'hierarchical', $options, true ) ) {
			$atts['hierarchical'] = true;
		}

		// to avoid unwanted warnings let's check before using widget.
		if ( is_object( $wp_widget_factory ) && isset( $wp_widget_factory->widgets, $wp_widget_factory->widgets[ $type ] ) ) {
			ob_start();
			the_widget( $type, $atts );
			$output .= ob_get_clean();
		}

		// this is pure HTML, we want it to go through the parser.
		return $output;
	}
endif;

if ( ! function_exists( 'wpna_vc_wp_archives_shortcode' ) ) :

	/**
	 * Shortcode override vc_wp_archives.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_wp_archives_shortcode( $atts, $content = '' ) {
		global $wp_widget_factory;

		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_wp_archives', $atts );

		$type = 'WP_Widget_Archives';

		$output = '';
		// @codingStandardsIgnoreLine
		extract( $atts );

		$options = explode( ',', $options );

		$atts['dropdown'] = false; // We never want a dropwdown.

		if ( in_array( 'count', $options, true ) ) {
			$atts['count'] = true;
		}

		// to avoid unwanted warnings let's check before using widget.
		if ( is_object( $wp_widget_factory ) && isset( $wp_widget_factory->widgets, $wp_widget_factory->widgets[ $type ] ) ) {
			ob_start();
			the_widget( $type, $atts );
			$output .= ob_get_clean();
		}

		// this is pure HTML, we want it to go through the parser.
		return $output;
	}
endif;

if ( ! function_exists( 'wpna_vc_wp_calendar_shortcode' ) ) :

	/**
	 * Shortcode override vc_wp_calendar.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_wp_calendar_shortcode( $atts, $content = '' ) {
		global $wp_widget_factory;

		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_wp_calendar', $atts );

		$type = 'WP_Widget_Calendar';

		$default_styling = '* { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 1em } a { color: grey; text-decoration:none; }';

		/**
		 * Filter the default CSS applied to the search form.
		 *
		 * @since 1.2.2
		 * @var string
		 */
		$default_styling = apply_filters( 'wpna_vc_wp_calendar_shortcode_styles', $default_styling );

		$output = '';

		// to avoid unwanted warnings let's check before using widget.
		if ( is_object( $wp_widget_factory ) && isset( $wp_widget_factory->widgets, $wp_widget_factory->widgets[ $type ] ) ) {
			ob_start();
			the_widget( $type, $atts );
			$widget = ob_get_clean();
		} else {
			return '';
		}

		$output .= '<iframe class="column-width" width="320" height="180" scrolling="no" frameborder="0" allowTransparency="true">' . PHP_EOL;
		$output .= '<style>' . $default_styling . '</style>' . PHP_EOL;
		$output .= $widget . PHP_EOL;
		$output .= '</iframe>';

		// Remove the widget title. Add the title before the cloud.
		$title = '';
		if ( ! empty( $atts['title'] ) ) {
			$output = str_ireplace( '<h2 class ="widgettitle">' . $atts['title'] . '</h2>', '', $output );
			$title  = '<h2>' . esc_html( $atts['title'] ) . '</h2>';
		}

		// Get the placeholder.
		$placeholder = wpna_content_parser_get_placeholder( '<figure class="op-interactive">' . PHP_EOL . $output . PHP_EOL . '</figure>' );

		return '<pre>' . $placeholder . '</pre>';
	}
endif;

if ( ! function_exists( 'wpna_vc_tta_tabs_shortcode' ) ) :

	/**
	 * Shortcode override vc_tta_tabs.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_tta_tabs_shortcode( $atts, $content = '' ) {
		return wpb_js_remove_wpautop( $content );
	}
endif;

if ( ! function_exists( 'wpna_vc_tta_section_shortcode' ) ) :

	/**
	 * Shortcode override vc_tta_section.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_tta_section_shortcode( $atts, $content = '' ) {
		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_tta_section', $atts );

		$output = '';

		if ( ! empty( $atts['title'] ) ) {
			$output .= '<h2>' . esc_html( $atts['title'] ) . '</h2>' . PHP_EOL;
		}

		$output .= wpb_js_remove_wpautop( $content );

		return $output;
	}
endif;

if ( ! function_exists( 'wpna_vc_flickr_shortcode' ) ) :

	/**
	 * Shortcode override vc_flickr.
	 *
	 * @param  array  $atts Shortcode attributes.
	 * @param  string $content Post content.
	 * @return string
	 */
	function wpna_vc_flickr_shortcode( $atts, $content = '' ) {
		global $wp_widget_factory;

		// Workout the default shortcode atts.
		$atts = vc_map_get_attributes( 'vc_flickr', $atts );

		$output = '';

		$title = $flickr_id = $css = $css_animation = $count = $type = $display = '';
		// @codingStandardsIgnoreLine
		extract( $atts );

		$output .= '<iframe class="no-margin" width="960" height="180" scrolling="no" frameborder="0" allowTransparency="true">' . PHP_EOL;
		// @codingStandardsIgnoreLine
		$output .= '<style type="text/css">.flickr_badge_image {margin:0px;display:inline;}.flickr_badge_image img {border: 0px !important; padding:1px; margin:2px;} .wpb_follow_btn { color: grey; font-size: 0.8em; text-decoration: none; font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; }</style>' . PHP_EOL;
		// @codingStandardsIgnoreLine
		$output .= '<script type="text/javascript" src="//www.flickr.com/badge_code_v2.gne?count=' . $count . '&amp;display=' . $display . '&amp;size=s&amp;layout=x&amp;source=' . $type . '&amp;' . $type . '=' . $flickr_id . '"></script>' . PHP_EOL;
		$output .= '<p class="flickr_stream_wrap"><a class="wpb_follow_btn wpb_flickr_stream" href="//www.flickr.com/photos/' . $flickr_id . '">' . esc_html__( 'View stream on flickr', 'wp-native-articles' ) . '</a></p>' . PHP_EOL;
		$output .= '</iframe>';

		// Remove the widget title. Add the title before the cloud.
		$title = '';
		if ( ! empty( $atts['title'] ) ) {
			$output = str_ireplace( '<h2 class ="widgettitle">' . $atts['title'] . '</h2>', '', $output );
			$title  = '<h2>' . esc_html( $atts['title'] ) . '</h2>';
		}

		// Get the placeholder.
		$placeholder = wpna_content_parser_get_placeholder( '<figure class="op-interactive">' . PHP_EOL . $output . PHP_EOL . '</figure>' );

		return '<pre>' . $placeholder . '</pre>';
	}
endif;
