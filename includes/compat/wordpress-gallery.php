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
		$atts = shortcode_atts( array(
			'order'      => 'ASC',
			'orderby'    => 'menu_order ID',
			'id'         => $post ? $post->ID : 0,
			'itemtag'    => $html5 ? 'figure'     : 'dl',
			'icontag'    => $html5 ? 'div'        : 'dt',
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

		$output = '<figure class="op-slideshow">' . PHP_EOL;
		foreach ( $attachments as $att_id => $attachment ) {
			$output .= '<figure>' . PHP_EOL;
			$output .= sprintf( '<img src="%s" />', wp_get_attachment_image_url( $att_id, 'full', false ) ) . PHP_EOL;
			$output .= '</figure>' . PHP_EOL;
		}
		$output .= '</figure>';

		// Grab a placement for this code.
		$placement_id = wpna_content_parser_get_placeholder( $output );

		return '<pre>' . $placement_id . '</pre>';
	}
endif;
