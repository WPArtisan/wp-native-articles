<?php
/**
 * WordPress gallery Compatibility.
 *
 * @since 1.2.4.
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_wordpress_gallery_add_override_shortcodes' ) ) :

	/**
	 * Override the `gallery` shortcode in IA.
	 *
	 * @param array  $override_tags Shortocde tags to override.
	 * @param string $content       Current post content.
	 * @return array $override_tags
	 */
	function wpna_wordpress_gallery_add_override_shortcodes( $override_tags, $content ) {
		$override_tags['gallery'] = 'wpna_wordpress_gallery_shortcode_override';
		return $override_tags;
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', 'wpna_wordpress_gallery_add_override_shortcodes', 10, 2 );

if ( ! function_exists( 'wpna_wordpress_gallery_shortcode_override' ) ) :

	/**
	 * Wraps [gallery] shortcodes.
	 *
	 * Ensures they're always properly formatted and don't get caught up
	 * in the other parts of the content parser.
	 *
	 * @param array $attr Shortcode attributes.
	 * @return string
	 */
	function wpna_wordpress_gallery_shortcode_override( $attr ) {
		global $post;

		static $instance = 0;
		$instance++;

		if ( ! empty( $attr['ids'] ) ) {
			// 'ids' is explicitly ordered, unless you specify otherwise.
			if ( empty( $attr['orderby'] ) ) {
				$attr['orderby'] = 'post__in';
			}
			$attr['include'] = $attr['ids'];
		}

		$html5 = current_theme_supports( 'html5', 'gallery' );
		$atts  = shortcode_atts( array(
			'order'      => 'ASC',
			'orderby'    => 'menu_order ID',
			'id'         => $post ? $post->ID : 0,
			'itemtag'    => $html5 ? 'figure' : 'dl',
			'icontag'    => $html5 ? 'div' : 'dt',
			'captiontag' => $html5 ? 'figcaption' : 'dd',
			'columns'    => 3,
			'size'       => 'thumbnail',
			'include'    => '',
			'exclude'    => '',
			'link'       => '',
		), $attr, 'gallery' );

		$id = intval( $atts['id'] );

		// Copied from core. Might be better as WP_Query?
		// @codingStandardsIgnoreStart
		if ( ! empty( $atts['include'] ) ) {
			$_attachments = get_posts( array( 'include' => $atts['include'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );

			$attachments = array();
			foreach ( $_attachments as $key => $val ) {
				$attachments[ $val->ID ] = $_attachments[ $key ];
			}
		} elseif ( ! empty( $atts['exclude'] ) ) {
			$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $atts['exclude'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
		} else {
			$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
		}
		// @codingStandardsIgnoreEnd

		if ( empty( $attachments ) ) {
			return '';
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

		// Get the caption options.
		$caption_settings = array_filter( array(
			wpna_get_post_option( get_the_ID(), 'fbia_caption_font_size', null ),
			wpna_get_post_option( get_the_ID(), 'fbia_caption_vertical_position', null ),
			wpna_get_post_option( get_the_ID(), 'fbia_caption_horizontal_position', null ),
		) );

		// Whether to use the caption title.
		$caption_title_enabled = wpna_switch_to_boolean( wpna_get_post_option( get_the_ID(), 'fbia_caption_title' ) );

		// Get the caption title options.
		if ( $caption_title_enabled ) {
			$caption_title_settings = array_filter( array(
				wpna_get_post_option( get_the_ID(), 'fbia_caption_title_font_size', null ),
				wpna_get_post_option( get_the_ID(), 'fbia_caption_title_vertical_position', null ),
				wpna_get_post_option( get_the_ID(), 'fbia_caption_title_horizontal_position', null ),
			) );
		}

		$output = '<figure class="op-slideshow">' . PHP_EOL;
		foreach ( $attachments as $att_id => $attachment ) {

			// Start of output.
			$output .= '<figure';

			/**
			 * Allows filtering of the attributes set on the image figure.
			 *
			 * @since 1.0.0
			 * @param array $figure_attr Attributes for the figure element.
			 */
			$figure_attr = apply_filters( 'wpna_facebook_article_image_figure_attr', $figure_attr );

			if ( ! empty( $figure_attr ) ) {
				$output .= ' data-feedback="' . implode( ', ', $figure_attr ) . '"';
			}

			// Close the figure element.
			$output .= '>';

			$output .= sprintf( '<img src="%s" />', wp_get_attachment_image_url( $att_id, 'full', false ) ) . PHP_EOL;

			// Added in 4.6.
			if ( function_exists( 'wp_get_attachment_caption' ) ) {
				$attachment_caption = wp_get_attachment_caption( $att_id );
			} else {
				$attachment_caption = get_the_excerpt( $att_id );
			}

			if ( ! empty( $attachment_caption ) ) {

				// Set the caption settings, position, size etc.
				if ( ! empty( $caption_settings ) ) {
					$output .= '<figcaption class="' . implode( ' ', $caption_settings ) . '">';
				} else {
					$output .= '<figcaption>';
				}

				if ( $caption_title_enabled && $caption_title = get_the_title( $att_id ) ) {

					// Set the caption title settings.
					if ( ! empty( $caption_title_settings ) ) {
						$output .= '<h1 class="' . implode( ' ', $caption_title_settings ) . '">';
					} else {
						$output .= '<h1>';
					}

					// Add in the caption title.
					$output .= $caption_title;

					// Close the tag.
					$output .= '</h1>';

				}

				// Add in the caption.
				if ( ! empty( $atts['caption'] ) ) {
					$output .= $atts['caption'];
				} else {
					$output .= $attachment_caption;
				}

				// Close the tag.
				$output .= '</figcaption>';
			}

			$output .= '</figure>' . PHP_EOL;
		}
		$output .= '</figure>';

		// Grab a placement for this code.
		$placement_id = wpna_content_parser_get_placeholder( $output );

		return '<pre>' . $placement_id . '</pre>';
	}
endif;
