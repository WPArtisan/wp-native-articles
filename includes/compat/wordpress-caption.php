<?php
/**
 * WordPress caption Compatibility.
 *
 * @since 1.2.4.
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_wordpress_caption_add_override_shortcodes' ) ) :

	/**
	 * Override the `wp_caption` shortcode in IA.
	 *
	 * @param array  $override_tags Shortocde tags to override.
	 * @param string $content       Current post content.
	 * @return array $override_tags
	 */
	function wpna_wordpress_caption_add_override_shortcodes( $override_tags, $content ) {
		$override_tags['wp_caption'] = 'wpna_wordpress_caption_shortcode_override';
		$override_tags['caption']    = 'wpna_wordpress_caption_shortcode_override';
		return $override_tags;
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', 'wpna_wordpress_caption_add_override_shortcodes', 10, 2 );

if ( ! function_exists( 'wpna_wordpress_caption_shortcode_override' ) ) :

	/**
	 * Wraps [caption] shortcodes.
	 *
	 * Ensures they're always properly formatted and don't get caught up
	 * in the other parts of the content parser.
	 *
	 * @param array $attr Shortcode attributes.
	 * @param array $content The content of the shortcode..
	 * @return string
	 */
	function wpna_wordpress_caption_shortcode_override( $attr, $content ) {
		// New-style shortcode with the caption inside the shortcode with the link and image tags.
		if ( ! isset( $attr['caption'] ) ) {
			if ( preg_match( '#((?:<a [^>]+>\s*)?<img [^>]+>(?:\s*</a>)?)(.*)#is', $content, $matches ) ) {
				$content         = $matches[1];
				$attr['caption'] = trim( $matches[2] );
			}
		} elseif ( strpos( $attr['caption'], '<' ) !== false ) {
			$attr['caption'] = wp_kses( $attr['caption'], 'post' );
		}

		$atts = shortcode_atts( array(
			'id'      => '',
			'align'   => 'alignnone',
			'width'   => '',
			'caption' => '',
			'class'   => '',
		), $attr, 'caption' );

		// Get and validate the caption ID. Used to grab the title.
		$attachment_id = str_replace( 'attachment_', '', $atts['id'] );
		$attachment_id = absint( $attachment_id );

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

		// Start of output.
		$html = '<figure';

		/**
		 * Allows filtering of the attributes set on the image figure.
		 *
		 * @since 1.0.0
		 * @param array $figure_attr Attributes for the figure element.
		 */
		$figure_attr = apply_filters( 'wpna_facebook_article_image_figure_attr', $figure_attr );

		if ( ! empty( $figure_attr ) ) {
			$html .= ' data-feedback="' . implode( ', ', $figure_attr ) . '"';
		}

		// Close the figure element.
		$html .= '>';

		// The image element. Run do_shortcode over it just incase.
		$html .= strip_tags( do_shortcode( $content ), '<img>' );

		if ( ! empty( $atts['caption'] ) ) {

			// Set the caption settings, position, size etc.
			if ( ! empty( $caption_settings ) ) {
				$html .= '<figcaption class="' . implode( ' ', $caption_settings ) . '">';
			} else {
				$html .= '<figcaption>';
			}

			if ( $caption_title_enabled && $caption_title = get_the_title( $attachment_id ) ) {

				// Set the caption title settings.
				if ( ! empty( $caption_title_settings ) ) {
					$html .= '<h1 class="' . implode( ' ', $caption_title_settings ) . '">';
				} else {
					$html .= '<h1>';
				}

				// Add in the caption title.
				$html .= $caption_title;

				// Close the tag.
				$html .= '</h1>';

			}

			// Add in the caption.
			$html .= $atts['caption'];

			// Close the tag.
			$html .= '</figcaption>';
		}

		// Closing figure tag.
		$html .= '</figure>';

		// Grab a placement for this code.
		$placement_id = wpna_content_parser_get_placeholder( $html );

		return '<pre>' . $placement_id . '</pre>';
	}
endif;
