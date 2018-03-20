<?php
/**
 * Aesop content builder compatibility.
 * https://wordpress.org/plugins/aesop-story-engine/
 *
 * @since 1.5.0
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_aesop_add_override_shortcodes' ) ) :

	/**
	 * Override the `aesop` content shortcodes in IA.
	 *
	 * @param array  $override_tags Shortocde tags to override.
	 * @param string $content       Current post content.
	 * @return array $override_tags
	 */
	function wpna_aesop_add_override_shortcodes( $override_tags, $content ) {
		$override_tags['aesop_chapter']       = 'wpna_aesop_chapter_shortcode_override';
		$override_tags['aesop_audio']         = 'wpna_aesop_audio_shortcode_override';
		$override_tags['aesop_video']         = 'wpna_aesop_video_shortcode_override';
		$override_tags['aesop_character']     = 'wpna_aesop_character_shortcode_override';
		$override_tags['aesop_collection']    = 'wpna_aesop_collection_shortcode_override';
		$override_tags['aesop_document']      = 'wpna_aesop_document_shortcode_override';
		$override_tags['aesop_image']         = 'wpna_aesop_image_shortcode_override';
		$override_tags['aesop_parallax']      = 'wpna_aesop_parallax_shortcode_override';
		$override_tags['aesop_quote']         = 'wpna_aesop_quote_shortcode_override';
		$override_tags['aesop_timeline_stop'] = 'wpna_aesop_timeline_stop_shortcode_override';
		$override_tags['aesop_map']           = 'wpna_aesop_map_shortcode_override';
		$override_tags['aesop_content']       = 'wpna_aesop_content_shortcode_override';
		$override_tags['aesop_gallery']       = 'wpna_aesop_gallery_shortcode_override';
		return $override_tags;
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', 'wpna_aesop_add_override_shortcodes', 10, 2 );

/**
 * Override the chapter shortcode for Instant Articles.
 *
 * @param array $atts Shortcode attributes.
 * @return string The shortcode content.
 */
function wpna_aesop_chapter_shortcode_override( $atts ) {
	$defaults = array(
		'title'            => '',
		'subtitle'         => '',
		'bgtype'           => 'img',
		'img'              => '',
		'alternate_img'    => '',
		'full'             => '',
		'bgcolor'          => '',
		'minheight'        => '260px',
		'maxheight'        => '100%',
		'fixed_height'     => '',
		'force_fullwidth'  => 'off',
		'overlay_content'  => '',
		'revealfx'         => '',
		'video_autoplay'   => 'on',
		'overlay_revealfx' => '',
	);

	$atts = apply_filters( 'aesop_chapter_defaults', shortcode_atts( $defaults, $atts ) );

	$output = '';

	if ( 'img' === $atts['bgtype'] && $atts['img'] ) {
		$output = sprintf( '<figure><img src="%s"/></figure>', esc_url( $atts['img'] ) );
	}

	if ( $atts['title'] ) {
		$output .= sprintf( '<h1>%s</h1>', $atts['title'] );
	}

	if ( $atts['subtitle'] ) {
		$output .= sprintf( '<h2>%s</h2>', $atts['subtitle'] );
	}

	// Get the placeholder.
	$placeholder = wpna_content_parser_get_placeholder( $output );

	return '<pre>' . $placeholder . '</pre>';
}

/**
 * Override the audio shortcode for Instant Articles.
 *
 * @param array $atts Shortcode attributes.
 * @return string The shortcode content.
 */
function wpna_aesop_audio_shortcode_override( $atts ) {
	$defaults = array(
		'title'     => '',
		'src'       => '',
		'viewstart' => 'off',
		'viewend'   => 'off',
		'loop'      => 'off',
		'hidden'    => '',
	);
	$atts     = apply_filters( 'aesop_audio_defaults', shortcode_atts( $defaults, $atts ) );

	$title = $atts['title'] ? apply_filters( 'aesop_audio_component_title', sprintf( '<h2>%s</h2>', strip_tags( $atts['title'] ) ) ) : '';

	$output = '';

	$output .= '<iframe>' . wp_audio_shortcode(
		array(
			'src'  => $atts['src'],
			'loop' => ( 'on' === $atts['loop'] ),
		)
	) . '</iframe>';

	if ( $title ) {
		$title;
	}

	// Get the placeholder.
	$placeholder = wpna_content_parser_get_placeholder( $title . '<figure class="op-interactive">' . $output . '</figure>' );

	return '<pre>' . $placeholder . '</pre>';
}

/**
 * Override the video shortcode for Instant Articles.
 *
 * @param array $atts Shortcode attributes.
 * @return string The shortcode content.
 */
function wpna_aesop_video_shortcode_override( $atts ) {

	$defaults = array(
		'width'              => '100%',
		'align'              => 'center',
		'src'                => 'youtube',
		'hosted'             => '',
		'id'                 => '',
		'disable_for_mobile' => 'on',
		'loop'               => 'on',
		'autoplay'           => 'on',
		'controls'           => 'off',
		'viewstart'          => 'off',
		'viewend'            => 'off',
		'caption'            => '',
		'overlay_content'    => '',
		'vidwidth'           => '',
		'vidheight'          => '',
		'poster_frame'       => '',
		'force_fullwidth'    => 'off',
		'revealfx'           => '',
		'overlay_revealfx'   => '',
	);
	$atts     = apply_filters( 'aesop_video_defaults', shortcode_atts( $defaults, $atts ) );
	// iFrame params.
	$iframe_height = $atts['vidheight'] ? sprintf( 'height="%d"', preg_replace( '/[^0-9]/', '', $atts['vidheight'] ) ) : sprintf( 'height="315"' );
	$iframe_width  = $atts['vidwidth'] ? sprintf( 'width="%d"', preg_replace( '/[^0-9]/', '', $atts['vidwidth'] ) ) : sprintf( 'width="560"' );
	$iframe_size   = sprintf( '%s %s', $iframe_height, $iframe_width );

	$output = '';

	switch ( $atts['src'] ) {

		case 'vimeo':
			$output = sprintf( '<iframe src="https://player.vimeo.com/video/%s?byline=0&controls=0%s" %s></iframe>', esc_attr( $atts['id'] ), $iframe_size );
			break;
		case 'dailymotion':
			$output = sprintf( '<iframe src="https://www.dailymotion.com/embed/video/%s" %s></iframe>', esc_attr( $atts['id'] ), $iframe_size );
			break;
		case 'youtube':
			$output = sprintf( '<iframe src="https://www.youtube.com/embed/%s" %s></iframe>', esc_attr( $atts['id'] ), $iframe_size );
			break;
		case 'kickstarter':
			$output = sprintf( '<iframe src="%s" %s></iframe>', esc_attr( $atts['id'] ), $iframe_size );
			break;
		case 'viddler':
			$output = sprintf( '<iframe src="https://www.viddler.com/embed/%s/" %s></iframe>', esc_attr( $atts['id'] ), $iframe_size );
			break;
		case 'vine':
			$output = sprintf( '<iframe src="https://vine.co/v/%s/embed/simple" width="480" height="480"></iframe>', esc_attr( $atts['id'] ) );
			break;
		case 'wistia':
			// @codingStandardsIgnoreStart
			$output = sprintf( '<iframe width="640" height="360">
								<div id="wistia_%s" class="wistia_embed" style="width:640px;height:360px;">&nbsp;</div>
								<script charset="ISO-8859-1" src="//fast.wistia.com/assets/external/E-v1.js"></script>
								<script>wistiaEmbed = Wistia.embed("%s",{videoFoam: true });</script>
								</iframe>
								', esc_attr( $atts['id'] ), esc_attr( $atts['id'] ) );
			// @codingStandardsIgnoreEnd
			break;
		case 'instagram':
			$output = sprintf( '<iframe src="https://instagram.com/p/%s/embed" width="612" height="710" frameborder="0"></iframe>', esc_attr( $atts['id'] ) );
			break;
		case 'self':
			$output = sprintf(
				'<video%s%s><source src="%s" type="%s" /></video>',
				( 'on' === $atts['loop'] ? ' loop' : '' ),
				( 'on' === $atts['autoplay'] ? 'data-fb-disable-autoplay' : '' ),
				$atts['hosted'],
				wp_check_filetype( $atts['hosted'], wp_get_mime_types() )
			);
			break;
	}

	// Get the placeholder.
	$placeholder = wpna_content_parser_get_placeholder( '<figure class="op-interactive">' . $output . '</figure>' );

	return '<pre>' . $placeholder . '</pre>';
}

/**
 * Override the character shortcode for Instant Articles.
 *
 * @param array  $atts Shortcode attributes.
 * @param string $content Shortcode content.
 * @return string The shortcode content.
 */
function wpna_aesop_character_shortcode_override( $atts, $content = null ) {

	$defaults = array(
		'img'          => '',
		'name'         => '',
		'caption'      => '',
		'align'        => 'left',
		'width'        => '',
		'force_circle' => 'off',
		'revealfx'     => '',
		'link'         => '',
	);

	$atts = apply_filters( 'aesop_character_defaults', shortcode_atts( $defaults, $atts ) );

	$output = '<figure>';

	if ( $atts['img'] ) {
		$output .= sprintf( '<img src="%s">', esc_url( $atts['img'] ) );
	}

	if ( $atts['caption'] || $atts['name'] ) {
		$output .= '<figcaption>';
		$output .= sprintf( '<h1>%s</h1>', strip_tags( $atts['name'] ) );

		if ( $atts['caption'] ) {
			$output .= esc_html( $atts['caption'] );
		}

		$output .= '</figcaption>';
	}

	$output .= '</figure>';

	if ( $content ) {
		$output .= sprintf( '<p>%s</p>', $content );
	}

	// Get the placeholder.
	$placeholder = wpna_content_parser_get_placeholder( $output );

	return '<pre>' . $placeholder . '</pre>';
}

/**
 * Override the collection shortcode for Instant Articles.
 *
 * @param array $atts Shortcode attributes.
 * @return string The shortcode content.
 */
function wpna_aesop_collection_shortcode_override( $atts ) {
	$defaults = array(
		'collection' => 1,
		'title'      => '',
		'columns'    => 2,
		'limit'      => -1,
		'splash'     => '',
		'loadmore'   => 'off',
		'order'      => 'default',
		'revealfx'   => '',
	);
	$atts     = apply_filters( 'aesop_collection_defaults', shortcode_atts( $defaults, $atts ) );

	if ( ! $atts['collection'] ) {
		return '';
	}

	$output = '';

	if ( $atts['title'] ) {
		$output .= sprintf( '<h1>%s</h1>', esc_html( $atts['title'] ) );
	}

	// Splashmode only shows categories.
	if ( 'on' === $atts['splash'] ) {

		// Get the categories.
		if ( false === ( $cats = wp_cache_get( 'aesop_splash_query_' . $atts['collection'] ) ) ) {

			// Cat query args.
			$cat_args = array(
				'orderby' => 'name',
				'order'   => 'ASC',
			);

			$cats = get_categories( apply_filters( 'aesop_splash_query', $cat_args ) );

			wp_cache_set( 'aesop_splash_query_' . $atts['collection'], $cats );
		}

		if ( ! $cats ) {
			return '';
		}

		foreach ( $cats as $cat ) {
			$output .= sprintf( '<h2><a href="%s">%s</a></h2>', esc_url( get_category_link( $cat->term_id ) ), esc_html( $cat->name ) );
			if ( $cat->category_description ) {
				$output .= sprintf( '<p><i>%s</i><p>', esc_html( $cat->category_description ) );
			}
		}
	} else {

		$args = array(
			'orderby'        => array(
				'date' => ( 'default' === $atts['order'] ? 'DESC' : 'ASC' ),
			),
			'posts_per_page' => $atts['limit'],
			'cat'            => $atts['collection'],
			'ignore_sticky'  => true,
			'paged'          => 1,
			'no_found_rows'  => true,
		);

		$query = new wp_query( apply_filters( 'aesop_collection_query', $args ) );

		if ( ! $query->have_posts() ) {
			return '';
		}

		foreach ( $query->posts as $post ) {
			$output .= sprintf( '<h2><a href="%s">%s</a></h2>', esc_url( get_the_permalink( $post->ID ) ), esc_html( get_the_title( $post->ID ) ) );
			if ( has_excerpt( $post->ID ) ) {
				$output .= sprintf( '<p><i>%s</i><p>', esc_html( wp_trim_words( get_the_excerpt( $post->ID ), 16, '...' ) ) );
			}
		}
	}

	return $output;
}

/**
 * Override the document shortcode for Instant Articles.
 *
 * @param array $atts Shortcode attributes.
 * @return string The shortcode content.
 */
function wpna_aesop_document_shortcode_override( $atts ) {
	$defaults = array(
		'type'    => 'pdf',
		'src'     => '',
		'caption' => '',
	);

	$atts = apply_filters( 'aesop_document_defaults', shortcode_atts( $defaults, $atts ) );

	$output = sprintf(
		'<p><a href="%s">%s</a>%s</p>',
		esc_url( $atts['src'] ),
		esc_html__( 'DOCUMENT', 'wp-native-articles' ),
		( $atts['caption'] ? '<br />' . $atts['caption'] : '' )
	);

	// Get the placeholder.
	$placeholder = wpna_content_parser_get_placeholder( $output );

	return '<pre>' . $placeholder . '</pre>';
}

/**
 * Override the image shortcode for Instant Articles.
 *
 * @param array $atts Shortcode attributes.
 * @return string The shortcode content.
 */
function wpna_aesop_image_shortcode_override( $atts ) {

	$defaults = array(
		'img'              => '',
		'imgwidth'         => '300px',
		'offset'           => '',
		'alt'              => '',
		'align'            => 'left',
		'caption'          => '',
		'credit'           => '',
		'captionposition'  => 'left',
		'lightbox'         => 'off',
		'force_fullwidth'  => 'off',
		'overlay_content'  => '',
		'revealfx'         => '',
		'overlay_revealfx' => '',
	);

	$atts = apply_filters( 'aesop_image_defaults', shortcode_atts( $defaults, $atts ) );

	$output = sprintf( '<img src="%s">', esc_url( $atts['img'] ) );

	if ( $atts['caption'] ) {
		$output .= '<figcaption>';
		$output .= esc_html( $atts['caption'] );

		if ( $atts['credit'] ) {
			$output .= sprintf( '<cite>%s</cite>', esc_html( $atts['credit'] ) );
		}

		$output .= '</figcaption>';
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

/**
 * Override the parallax shortcode for Instant Articles.
 *
 * @param array $atts Shortcode attributes.
 * @return string The shortcode content.
 */
function wpna_aesop_parallax_shortcode_override( $atts ) {
	$defaults = array(
		'img'              => '',
		'parallaxspeed'    => 1,
		'height'           => '',
		'parallaxbg'       => 'on',
		'floater'          => '',
		'floatermedia'     => '',
		'floaterposition'  => 'right',
		'floaterdirection' => 'up',
		'caption'          => '',
		'captionposition'  => 'bottom-left',
		'lightbox'         => false,
		'floaterdistance'  => '',
		'overlay_revealfx' => '',
		'floaterspeed'     => 1,
	);

	$atts = apply_filters( 'aesop_parallax_defaults', shortcode_atts( $defaults, $atts ) );

	$output = '';

	$output .= '<figure data-mode="fullscreen">';
	$output .= sprintf( '<img src="%s">', esc_url( $atts['img'] ) );

	if ( $atts['caption'] ) {
		$output .= '<figcaption>';
		$output .= $atts['caption'];
		$output .= '</figcaption>';
	}

	$output .= '</figure>';

	// Get the placeholder.
	$placeholder = wpna_content_parser_get_placeholder( $output );

	return '<pre>' . $placeholder . '</pre>';
}

/**
 * Override the quote shortcode for Instant Articles.
 *
 * @param array $atts Shortcode attributes.
 * @return string The shortcode content.
 */
function wpna_aesop_quote_shortcode_override( $atts ) {
	$defaults = array(
		'width'      => '100%',
		'background' => '#222222',
		'img'        => '',
		'text'       => '',
		'height'     => 'auto',
		'align'      => 'center',
		'size'       => '1',
		'parallax'   => '',
		'direction'  => '',
		'quote'      => esc_html__( 'People are made of stories, not atoms.', 'wp-native-articles' ),
		'cite'       => '',
		'type'       => 'block',
		'revealfx'   => '',

	);
	$atts     = apply_filters( 'aesop_quote_defaults', shortcode_atts( $defaults, $atts ) );

	// Get the output.
	$output = strip_tags( $atts['quote'] );

	// Get the placeholder.
	$placeholder = wpna_content_parser_get_placeholder( '<blockquote>' . $output . '</blockquote>' );

	return '<pre>' . $placeholder . '</pre>';
}

/**
 * Override the timeline shortcode for Instant Articles.
 *
 * @param array $atts Shortcode attributes.
 * @return string The shortcode content.
 */
function wpna_aesop_timeline_stop_shortcode_override( $atts ) {
	$defaults = array(
		'num'   => '2007',
		'title' => '',
	);
	$atts     = apply_filters( 'aesop_timeline_defaults', shortcode_atts( $defaults, $atts ) );

	// Get the output.
	$output = sprintf( '<h2>%s</h2>', strip_tags( $atts['title'] ) );

	// Get the placeholder.
	$placeholder = wpna_content_parser_get_placeholder( $output );

	return '<pre>' . $placeholder . '</pre>';
}

/**
 * Override the map shortcode for Instant Articles.
 *
 * @param array $atts Shortcode attributes.
 * @return string The shortcode content.
 */
function wpna_aesop_map_shortcode_override( $atts ) {
	$defaults = array(
		'height' => 500,
		'sticky' => 'off',
	);
	$atts     = apply_filters( 'aesop_map_defaults', shortcode_atts( $defaults, $atts ) );

	// get markers - since 1.3.
	if ( ! $markers = get_post_meta( get_the_ID(), 'ase_map_component_locations', false ) ) {
		return '';
	}

	$map = array(
		'type' => 'FeatureCollection',
	);

	foreach ( $markers as $marker ) {
		// Construct the map.
		$map['features'][] = array(
			'type'       => 'Feature',
			'geometry'   => array(
				'type'        => 'Point',
				'coordinates' => array(
					$marker['lat'],
					$marker['lng'],
				),
			),
			'properties' => array(
				'pivot'  => false,
				'radius' => 75000,
				'style'  => 'standard',
			),
		);
	}

	// Generate the output.
	$output  = '<figure class="op-map">' . PHP_EOL;
	$output .= '<script type="application/json" class="op-geotag">' . PHP_EOL;
	$output .= wp_json_encode( $map ) . PHP_EOL;
	$output .= '</script>' . PHP_EOL;
	$output .= '</figure>';

	// Get the placeholder.
	$placeholder = wpna_content_parser_get_placeholder( $output );

	return '<pre>' . $placeholder . '</pre>';
}

/**
 * Override the content shortcode for Instant Articles.
 *
 * @param array  $atts Shortcode attributes.
 * @param string $content Shortcode content.
 * @return string The shortcode content.
 */
function wpna_aesop_content_shortcode_override( $atts, $content = null ) {
	$defaults = array(
		'height'            => '',
		'width'             => '100%',
		'component_width'   => '100%',
		'columns'           => '',
		'position'          => 'center',
		'innerposition'     => '',
		'img'               => '',
		'imgrepeat'         => 'no-repeat',
		'imgposition'       => 'center center',
		'imgsize'           => 'cover',
		'floatermedia'      => '',
		'floaterdirection'  => 'down',
		'floaterposition'   => 'left',
		'color'             => '#FFFFFF',
		'background'        => '#222222',
		'disable_bgshading' => 'off',
		'revealfx'          => '',
		'overlay_revealfx'  => '',
	);

	$atts = apply_filters( 'aesop_cbox_defaults', shortcode_atts( $defaults, $atts ) );

	$output = do_shortcode( wpautop( html_entity_decode( $content ) ) );

	return $output;
}

/**
 * Override the gallery shortcode for Instant Articles.
 *
 * @param array $atts Shortcode attributes.
 * @return string The shortcode content.
 */
function wpna_aesop_gallery_shortcode_override( $atts ) {
	$defaults = array(
		'id'       => '',
		'a_type'   => '',
		'revealfx' => 'off',
	);
	$atts     = shortcode_atts( $defaults, $atts );
	// Get the ID of the gallery.
	$gallery_id = isset( $atts['id'] ) ? (int) $atts['id'] : false;

	if ( ! $gallery_id ) {
		return '';
	}

	// Grab the images for the gallery.
	$image_ids = get_post_meta( $gallery_id, '_ase_gallery_images', true );

	// Nothing fancy, just convert it to a WP gallery (that's already got special IA rules).
	$output = do_shortcode( sprintf( '[gallery ids="%s"]', $image_ids ) );

	return $output;
}
