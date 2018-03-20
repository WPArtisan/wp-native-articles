<?php
/**
 * Facebook Content parsing class.
 *
 * @since 1.0.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This handles the formatting of the post content for Facebook
 * Facebook is very strict/fussy about how it has to be formatted
 * so this can get quite involved. It's also an expensive operation
 * so enabling the cache is highly recommended.
 *
 * @since  1.0.0
 */
// @codingStandardsIgnoreLine
class WPNA_Facebook_Content_Parser {

	/**
	 * Constructor.
	 *
	 * Triggers the hooks method straight away.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		// Used to store the shortcode & oEmbed content.
		$GLOBALS['_shortcode_content'] = array();

		$this->hooks();
	}

	/**
	 * Hooks registered in this class.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function hooks() {
		add_action( 'the_post', array( $this, 'content_hooks' ), 10, 1 );
	}

	/**
	 * Add hooks to format the post content.
	 *
	 * Hook into the content in various places and apply the transformers.
	 * Tries to manipulate it to make in valid for Facebook Instant Articles.
	 *
	 * @since 1.0.0
	 * @link https://developers.facebook.com/docs/instant-articles/reference
	 *
	 * @access public
	 * @return void
	 */
	public function content_hooks() {

		// We can help clean up the content before WP gets to it.
		add_filter( 'wpna_facebook_article_pre_the_content_filter', array( $this, 'add_follow_redirects_filter' ), 5, 1 );
		add_filter( 'wpna_facebook_article_pre_the_content_filter', array( $this, 'setup_wrap_shortcodes' ), 5, 1 );
		add_filter( 'wpna_facebook_article_pre_the_content_filter', array( $this, 'setup_wrap_oembeds' ), 5, 1 );
		add_filter( 'wpna_facebook_article_pre_the_content_filter', array( $this, 'remove_more_link' ), 10, 1 );

		add_filter( 'wpna_facebook_article_after_the_content_filter', array( $this, 'convert_headings' ), 10, 1 );
		add_filter( 'wpna_facebook_article_after_the_content_filter', array( $this, 'remove_local_hyperlinks' ), 10, 1 );

		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'instagram_embeds' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'twitter_embeds' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'gist_embeds' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'pre_tags' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'code_tags' ), 10, 1 );

		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'unique_images' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'featured_images' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'unlink_images' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'images_exist' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'remove_elements' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'move_elements' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'wrap_images' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'wrap_elements' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'remove_attributes' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'wrap_text' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'remove_empty_elements' ), 10, 1 );

		add_filter( 'wpna_facebook_article_content_after_transform', array( $this, 'strip_elements' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_after_transform', array( $this, 'remove_shortcode_wrapper' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_after_transform', array( $this, 'remove_oembed_wrapper' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_after_transform', array( $this, 'restore_embeds' ), 10, 1 );
	}

	/**
	 * Adds a filter into the HTTP lib to enable follow redirects.
	 *
	 * Though technically a filter this is being used more like an action.
	 *
	 * @since 1.2.5
	 *
	 * @access public
	 * @param  string $content The content of the post.
	 * @return string
	 */
	public function add_follow_redirects_filter( $content ) {
		add_filter( 'http_api_curl', array( $this, 'set_follow_redirects' ), 10, 3 );

		return $content;
	}

	/**
	 * Adds the CURLOPT_FOLLOWLOCATION argument to the CURL lib as it's
	 * set to false by default.
	 *
	 * /wp-includes/class-wp-http-curl.php:ln147.
	 *
	 * @since 1.2.5
	 *
	 * @access public
	 * @param resource $handle The cURL handle returned by curl_init().
	 * @param array    $r       The HTTP request arguments.
	 * @param string   $url     The request URL.
	 * @return void
	 */
	public function set_follow_redirects( &$handle, $r, $url ) {
		// @codingStandardsIgnoreLine
		curl_setopt( $handle, CURLOPT_FOLLOWLOCATION, true );
	}

	/**
	 * Wrap shortcodes in <figure> elements.
	 *
	 * Shortcodes can output anything. There's no sane way to try and
	 * anticipate them, this ensures they're always wrapped in <figure> tags.
	 *
	 * Hijacks the global array of shortcodes and funtions. Replaces the functions
	 * with a custom method that wraps the shortcode before calling the
	 * function.
	 *
	 * Though technically a filter this is being used more like an action.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $content The content of the post.
	 * @return string
	 */
	public function setup_wrap_shortcodes( $content ) {
		global $shortcode_tags, $_shortcode_tags;

		// Let's make a back-up of the shortcodes.
		$_shortcode_tags = $shortcode_tags;

		// Add any shortcode tags that we shouldn't touch here.
		$disabled_tags = array( 'caption', 'wp_caption' );

		/**
		 * Add a filter allowing alteration of the $disabled_tags array.
		 *
		 * @since 1.0.0
		 * @param array   $disabled_tags
		 * @param content $content
		 */
		$disabled_tags = apply_filters( 'wpna_facebook_article_setup_wrap_shortcodes_disabled_tags', $disabled_tags, $content );

		// Add any shortcode tags that use a custom callback here.
		// View the /includes/compat folder for examples.
		$override_tags = array(
			'gallery'  => array( $this, 'gallery_shortcode' ),
			'fvplayer' => array( $this, 'fvplayer_shortcode' ),
		);

		/**
		 * Add a filter allowing alteration of the $override_tags array.
		 *
		 * @since 1.2.0
		 * @param array   $override_tags
		 * @param content $content
		 */
		$override_tags = apply_filters( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', $override_tags, $content );

		foreach ( $shortcode_tags as $tag => $cb ) {

			if ( in_array( $tag, $disabled_tags, true ) ) {
				// If the tag is to be ignored then continue.
				continue;
			} elseif ( isset( $override_tags[ $tag ] ) ) {
				// If the tag has a custom callback, set that.
				// @codingStandardsIgnoreLine
				$shortcode_tags[ $tag ] = $override_tags[ $tag ];
			} else {
				// Overwrite the callback function.
				// @codingStandardsIgnoreLine
				$shortcode_tags[ $tag ] = array( $this, 'wrap_shortcode' );
			}
		}

		return $content;
	}

	/**
	 * Wrap a shortcode function result in a <figure> element.
	 *
	 * Ensures all shortcodes are wrapped in <figure> elements before replacing
	 * them with a unique key. Means they won't get caught up in the parsing.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $attr    Shortcode attributes.
	 * @param  string $content The content of the post.
	 * @param  string $tag     Tag used for the shortcode.
	 * @return string
	 */
	public function wrap_shortcode( $attr, $content = null, $tag ) {
		global $_shortcode_tags, $_shortcode_content;

		// Generate a unique (enough) key for this shortcode.
		$shortcode_key = mt_rand();

		$content = call_user_func( $_shortcode_tags[ $tag ], $attr, $content, $tag );

		// Only wrap shortcodes that return scripts or iframes.
		if ( false === strpos( $content, '</script>' ) && false === strpos( $content, '</iframe>' ) ) {
			return $content;
		}

		// Wrap it in an iframe if it isn't already.
		if ( '<iframe' !== substr( $content, 0, 7 ) ) {
			$content = '<iframe>' . $content . '</iframe>';
		}

		// Store the shortocde content in the global array so it can be replaced later.
		$_shortcode_content[ $shortcode_key ] = $content;

		// Return the unique key wrapped in a figure element.
		return '<figure class="op-interactive">' . $shortcode_key . '</figure>';
	}

	/**
	 * Wraps [gallery] shortcodes.
	 *
	 * Ensures they're always properly formatted and don't get caught up
	 * in the other parts of the content parser.
	 *
	 * @param array $attr Shortcode attributes.
	 * @return string
	 */
	public function gallery_shortcode( $attr ) {
		global $post, $_shortcode_content;

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

		$output = PHP_EOL;
		foreach ( $attachments as $att_id => $attachment ) {
			$output .= '<figure>' . PHP_EOL;
			$output .= sprintf( '<img src="%s" />', wp_get_attachment_image_url( $att_id, 'full', false ) ) . PHP_EOL;
			$output .= '</figure>' . PHP_EOL;
		}

		$shortcode_key = mt_rand();

		$_shortcode_content[ $shortcode_key ] = $output;

		return '<figure class="op-slideshow">' . $shortcode_key . '</figure>' . PHP_EOL;
	}

	/**
	 * Wraps [fvplayer] shortcodes.
	 *
	 * Ensures they're always properly formatted and don't get caught up
	 * in the other parts of the content parser.
	 *
	 * @param array $attr Shortcode attributes.
	 * @return string
	 */
	public function fvplayer_shortcode( $attr ) {
		global $post, $_shortcode_content, $fv_fp;

		// If the global flowplay class isn't there, bail.
		if ( ! $fv_fp ) {
			return;
		}

		// Get the global flowplayer options.
		$flowplayer_options = get_option( 'fvwpflowplayer' );

		// Setup the shortcodes with some defaults.
		$atts = shortcode_atts( array(
			'src'            => '',
			'src1'           => '',
			'src2'           => '',
			'mobile'         => null,
			'splash'         => '',
			'caption'        => '',
			'autoplay'       => null,
			'loop'           => null,
			'disablesharing' => null,
		), $attr, 'fvplayer' );

		$output = '';

		// Workout autoplay value.
		$autoplay = true;

		if ( ! empty( $atts['autoplay'] ) ) {
			$autoplay = 'false' !== $atts['autoplay'];
		} elseif ( ! empty( $flowplayer_options['autoplay'] ) ) {
			$autoplay = 'false' !== $flowplayer_options['autoplay'];
		}

		// Parse the URL.
		$parsed_url = wpna_parse_url( $atts['src'] );

		$video_id = null;

		// First check if it's a YouTube video.
		if ( isset( $parsed_url['host'] ) && in_array( $parsed_url['host'], array( 'www.youtube.com', 'youtube.com', 'youtu.be' ), true ) ) {

			if ( isset( $parsed_url['path'] ) ) {

				// Format: https://www.youtube.com/watch?v=-PVNKvIDRwI.
				if ( false !== strripos( $parsed_url['path'], 'watch' ) ) {
					parse_str( $parsed_url['query'], $parsed_query );
					$video_id = ! empty( $parsed_query['v'] ) ? $parsed_query['v'] : null;
				} elseif ( false !== strripos( $parsed_url['path'], 'embed' ) ) {
					// Format: https://www.youtube.com/embed/-PVNKvIDRwI.
					$video_id = str_ireplace( 'embed', '', $parsed_url['path'] );
				} else {
					// Format: https://youtu.be/-PVNKvIDRwI.
					$video_id = $parsed_url['path'];
				}
			}

			$video_id = trim( $video_id, '/' );

			// Construct the correct embed URL.
			$url = sprintf( 'https://www.youtube.com/embed/%s', $video_id );

			// Output the valid iFrame.
			$output = PHP_EOL . sprintf( '<iframe width="560" height="315" src="%s"></iframe>', esc_url( $url ) ) . PHP_EOL;

			// Check if it's a Vimeo video.
			// Possible formats:
			// https://vimeo.com/225479756.
			// https://player.vimeo.com/video/225479756.
			// https://vimeo.com/groups/timelapsevideos/videos/201545098.
			// https://vimeo.com/channels/musicvideoland/225008552/.
		} elseif ( isset( $parsed_url['host'] ) && in_array( $parsed_url['host'], array( 'www.vimeo.com', 'vimeo.com', 'player.vimeo.com' ), true ) ) {

			// Vimeo videos are easy. The last part of the path is always the video ID.
			$path_parts = explode( '/', $parsed_url['path'] );

			// Remove empty elements incase the URL ends with a slash.
			$path_parts = array_filter( $path_parts );

			// Video ID is the last part of the path.
			$video_id = array_pop( $path_parts );

			// Construct the correct embed URL.
			$url = sprintf( 'https://player.vimeo.com/video/%s', $video_id );

			// Output the valid iFrame.
			$output = PHP_EOL . sprintf( '<iframe width="560" height="315" src="%s"></iframe>', esc_url( $url ) ) . PHP_EOL;

			// It's a regular video. Embed as normal.
		} else {

			$splash = false;

			// First check if a splash was set for this video.
			if ( ! empty( $atts['splash'] ) ) {
				// Get the img url.
				$ig_src = flowplayer::get_encoded_url( $splash );

				// This is obviously less than ideal as it can be slow.
				$response      = wp_remote_head( $ig_src );
				$response_code = wp_remote_retrieve_response_code( $response );

				// The image exists.
				if ( 200 === $response_code ) {
					$splash = $ig_src;
				}
			}

			// If no splash was set for this video or it can't be found try the global.
			if ( ! $splash && ! empty( $flowplayer_options['splash'] ) ) {
				// Get the img url.
				$ig_src = flowplayer::get_encoded_url( $flowplayer_options['splash'] );

				// This is obviously less than ideal as it can be slow.
				$response      = wp_remote_head( $ig_src );
				$response_code = wp_remote_retrieve_response_code( $response );

				// The image exists.
				if ( 200 === $response_code ) {
					$splash = $ig_src;
				}
			}

			// If all the splash checks pass.
			if ( $splash ) {
				$output .= '<img src="' . $splash . '" />' . PHP_EOL;
			}

			// Start the video element.
			$output .= '<video';

			// Check for Autoplay.
			if ( ! $autoplay ) {
				$output .= ' data-fb-disable-autoplay';
			}

			// Check for loop.
			// These options are overrides, can't combine unfortunatly.
			if ( ! empty( $atts['loop'] ) ) {
				if ( 'true' === $atts['loop'] ) {
					$output .= ' loop';
				}
			} elseif ( ! empty( $flowplayer_options['loop'] ) ) {
				if ( 'true' === $flowplayer_options['loop'] ) {
					$output .= ' loop';
				}
			}

			// Close the video element.
			$output .= '>' . PHP_EOL;

			// If a mobile source has been set then use that.
			if ( ! empty( $atts['mobile'] ) ) {
				$output .= $fv_fp->get_video_src( $atts['mobile'], array( 'mobileUserAgent' => true ) );
			} else {
				// Get the src elements for each file.
				foreach ( apply_filters( 'fv_player_media', array( $atts['src'], $atts['src1'], $atts['src2'] ), $fv_fp ) as $media_item ) {
					$output .= $fv_fp->get_video_src( $media_item, array( 'mobileUserAgent' => true ) );
				}
			}

			// Close the video tag.
			$output .= '</video>' . PHP_EOL;

			// Check for caption.
			if ( ! empty( $atts['caption'] ) ) {

				// Get the caption options.
				$caption_settings = array_filter( array(
					wpna_get_post_option( get_the_ID(), 'fbia_caption_font_size', null ),
					wpna_get_post_option( get_the_ID(), 'fbia_caption_vertical_position', null ),
					wpna_get_post_option( get_the_ID(), 'fbia_caption_horizontal_position', null ),
				) );

				// Get the caption title options.
				$caption_title_settings = array_filter( array(
					wpna_get_post_option( get_the_ID(), 'fbia_caption_title_font_size', null ),
					wpna_get_post_option( get_the_ID(), 'fbia_caption_title_vertical_position', null ),
					wpna_get_post_option( get_the_ID(), 'fbia_caption_title_horizontal_position', null ),
				) );

				$output .= '<figcaption';
				if ( ! empty( $caption_settings ) ) {
					$output .= ' class="' . implode( ', ', $caption_settings ) . '"';
				}
				$output .= '>' . PHP_EOL;

				$output .= '<h1';
				if ( ! empty( $caption_title_settings ) ) {
					$output .= ' class="' . implode( ', ', $caption_title_settings ) . '"';
				}
				$output .= '>' . $atts['caption'] . '</h1>' . PHP_EOL;
				$output .= '</figcaption>' . PHP_EOL;
			}
		}

		$shortcode_key = mt_rand();

		$_shortcode_content[ $shortcode_key ] = $output;

		// Work out whether we want share buttons or not.
		$figure = '<figure';

		// If it's a YouTube or Vimeo video then we want it interactive.
		if ( $video_id ) {
			$figure .= ' class="op-interactive"';
		}

		// These options are overrides, can't combine unfortunatly.
		if ( ! empty( $atts['disablesharing'] ) ) {
			if ( 'false' === $atts['disablesharing'] ) {
				$figure .= ' data-feedback="fb:likes, fb:comments"';
			}
		} elseif ( ! empty( $flowplayer_options['disablesharing'] ) ) {
			if ( 'false' === $flowplayer_options['disablesharing'] ) {
				$figure .= ' data-feedback="fb:likes, fb:comments"';
			}
		}

		$figure .= '>';

		return $figure . $shortcode_key . '</figure>' . PHP_EOL;
	}

	/**
	 * Wraps oembeds in <figure> elements.
	 *
	 * Registers a filter metod for all oembeds so they get wrapped in <figure>
	 * elements before returning.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $content The content of the post.
	 * @return string
	 */
	public function setup_wrap_oembeds( $content ) {
		add_filter( 'embed_oembed_html', array( $this, 'wrap_oembed' ), 10, 4 );

		return $content;
	}

	/**
	 * Ensures all oembeds are properly wrapped in <figure> elements.
	 *
	 * Replaces them with a unique key to ensure they're not caught up in the
	 * content parsing.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $cache Cached HTML for the embed.
	 * @param string $url The Oembed URl.
	 * @param array  $attr Atributes for the embed.
	 * @param int    $post_id ID for the post.
	 * @return string
	 */
	public function wrap_oembed( $cache, $url, $attr, $post_id ) {
		global $_shortcode_content;

		$shortcode_key = mt_rand();

		// Wrap it in an iframe if it isn't already.
		if ( strpos( $cache, '<iframe' ) === false ) {
			$cache = '<iframe>' . $cache . '</iframe>';
		}

		$_shortcode_content[ $shortcode_key ] = $cache;

		return '<figure class="op-interactive">' . $shortcode_key . '</figure>';
	}

	/**
	 * Removes the annoying more <span> when it's in the content.
	 * e.g. <span id="more-{id}"></span>
	 *
	 * @access public
	 * @param  string $content Post content.
	 * @return string
	 */
	public function remove_more_link( $content ) {
		// Replace the <span> with an empty string.
		$content = str_replace( '<span id="more-' . get_the_ID() . '"></span>', '', $content );

		return $content;
	}

	/**
	 * Remove all invalid headings.
	 *
	 * FB IA only recognises h1 & h2 headings (Except in the header).
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $content The content of the post.
	 * @return string
	 */
	public function convert_headings( $content ) {
		return $content = str_ireplace( array( 'h3', 'h4', 'h5', 'h6' ), 'h2', $content );
	}

	/**
	 * Remove in page hyperlinks.
	 *
	 * FB IA doesn't like hyperlinks that start with #.
	 * (from http://wordpress.stackexchange.com/a/227332/19528)
	 *
	 * @since 1.2.4
	 *
	 * @access public
	 * @param  string $content The content of the post.
	 * @return string
	 */
	public function remove_local_hyperlinks( $content ) {
		// Match all hyperlinks starting with a #.
		preg_match_all( '!<a[^>]*? href=[\'"]#[^<]+</a>!i', $content, $matches );

		foreach ( $matches[0] as $link ) {
			$content = str_replace( $link, strip_tags( $link ), $content );
		}

		return $content;
	}

	/**
	 * Remove all invalid tags.
	 *
	 * Only allow specified elements. Facebook is very fussy.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $content The content of the post.
	 * @return string
	 */
	public function strip_elements( $content ) {
		$allowed_tags = array(
			// Mentioned in the FB IA docs
			// and thus are explicitly allowed.
			'<h1>',
			'<h2>',
			'<cite>',
			'<iframe>',
			'<img>',
			'<script>',
			'<audio>',
			'<source>',
			'<address>',
			'<a>',
			'<blockquote>',
			'<p>',
			'<small>',
			'<li>',
			'<ol>',
			'<ul>',
			'<aside>',
			'<em>',
			'<video>',
			'<figure>',
			'<figcaption>',
			'<small>',
			'<strike>',
			'<strong>',
			'<sub>',
			'<sup>',
			'<table>',
			'<tbody>',
			'<tb>',
			'<tfoot>',
			'<th>',
			'<tr>',

			// Unsure about these but seems likley.
			'<acronym>',
			'<b>',
			'<br>',
			'<i>',
		);

		return strip_tags( $content, implode( '', $allowed_tags ) );
	}

	/**
	 * Checks for manually added Instagram embed code.
	 *
	 * If the instagram embed code has been pasted in manually we need to deal
	 * with it as a special case.
	 *
	 * @since 1.2.2
	 *
	 * @access public
	 * @param  DOMDocument $dom_document Represents the HTML of the post content.
	 * @return DOMDocument
	 */
	public function instagram_embeds( DOMDocument $dom_document ) {

		global $_shortcode_content;

		$figure_template_base = $dom_document->createElement( 'figure' );
		$figure_template_base->setAttribute( 'class', 'op-interactive' );

		$elements = $dom_document->getElementsByTagName( 'blockquote' );

		$i = $elements->length - 1;

		// Using a regressive loop. Removing elements with a foreach can get confused
		// when the index changes.
		while ( $i > -1 ) {

			$element = $elements->item( $i );

			if ( 'instagram-media' === $element->getAttribute( 'class' ) ) {
				$shortcode_key = mt_rand();

				// Add the instagram embed script inside the iFrame. The parser will remove the one in the content.
				$embed_content  = '<iframe>' . PHP_EOL;
				$embed_content .= $dom_document->saveXML( $element ) . PHP_EOL;
				// @codingStandardsIgnoreLine
				$embed_content .= '<script async defer src="//platform.instagram.com/en_US/embeds.js"></script>' . PHP_EOL;
				$embed_content .= '</iframe>' . PHP_EOL;

				// Cache the embed so it doesn't go through the parser.
				$_shortcode_content[ $shortcode_key ] = $embed_content;

				$figure_template            = clone $figure_template_base;
				$figure_template->nodeValue = $shortcode_key;

				$element->parentNode->replaceChild( $figure_template, $element );
			}

			$i--;
		}

		return $dom_document;
	}

	/**
	 * Checks for manually added Instagram embed code.
	 *
	 * If the instagram embed code has been pasted in manually we need to deal
	 * with it as a special case.
	 *
	 * @since 1.2.2
	 *
	 * @access public
	 * @param  DOMDocument $dom_document Represents the HTML of the post content.
	 * @return DOMDocument
	 */
	public function twitter_embeds( DOMDocument $dom_document ) {

		global $_shortcode_content;

		$figure_template_base = $dom_document->createElement( 'figure' );
		$figure_template_base->setAttribute( 'class', 'op-interactive' );

		$elements = $dom_document->getElementsByTagName( 'blockquote' );

		$i = $elements->length - 1;

		// Using a regressive loop. Removing elements with a foreach can get confused
		// when the index changes.
		while ( $i > -1 ) {

			$element = $elements->item( $i );

			if ( 'twitter-tweet' === $element->getAttribute( 'class' ) ) {
				$shortcode_key = mt_rand();

				// Add the instagram embed script inside the iFrame. The parser will remove the one in the content.
				$embed_content  = '<iframe>' . PHP_EOL;
				$embed_content .= $dom_document->saveXML( $element ) . PHP_EOL;
				// @codingStandardsIgnoreLine
				$embed_content .= '<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>' . PHP_EOL;
				$embed_content .= '</iframe>' . PHP_EOL;

				// Cache the embed so it doesn't go through the parser.
				$_shortcode_content[ $shortcode_key ] = $embed_content;

				$figure_template            = clone $figure_template_base;
				$figure_template->nodeValue = $shortcode_key;

				$element->parentNode->replaceChild( $figure_template, $element );
			}

			$i--;
		}

		return $dom_document;
	}

	/**
	 * Ensures gists are properly embedded.
	 *
	 * @since 1.2.3
	 *
	 * @access public
	 * @param  DOMDocument $dom_document Represents the HTML of the post content.
	 * @return object
	 */
	public function gist_embeds( DOMDocument $dom_document ) {

		global $_shortcode_content;

		$figure_template_base = $dom_document->createElement( 'figure' );
		$figure_template_base->setAttribute( 'class', 'op-interactive' );

		$elements = $dom_document->getElementsByTagName( 'script' );

		$i = $elements->length - 1;

		// Using a regressive loop. Removing elements with a foreach can get confused
		// when the index changes.
		while ( $i > -1 ) {

			$element = $elements->item( $i );

			if ( false !== stripos( $element->getAttribute( 'src' ), 'gist.github.com' ) ) {
				$shortcode_key = mt_rand();

				// Add the gist embed script inside an iFrame.
				$embed_content = '<iframe width="560" class="no-margin">' . PHP_EOL;
				// Add some CSS in to match the size correctly.
				$embed_content .= '
				<style type="text/css">
					.gist {width:560px !important;}
					.gist-file,
					.gist-data {max-width: 560px;}
				</style>
				' . PHP_EOL;
				// @codingStandardsIgnoreLine
				$embed_content .= sprintf( '<script src="%s"></script>', esc_url( $element->getAttribute( 'src' ) ) ) . PHP_EOL;
				$embed_content .= '</iframe>' . PHP_EOL;

				// Cache the embed so it doesn't go through the parser.
				$_shortcode_content[ $shortcode_key ] = $embed_content;

				$figure_template            = clone $figure_template_base;
				$figure_template->nodeValue = $shortcode_key;

				$element->parentNode->replaceChild( $figure_template, $element );
			}

			$i--;
		}

		return $dom_document;
	}

	/**
	 * Ensures <pre> tags are properly formatted.
	 *
	 * @since 1.2.3
	 *
	 * @access public
	 * @param  DOMDocument $dom_document Represents the HTML of the post content.
	 * @return object
	 */
	public function pre_tags( DOMDocument $dom_document ) {

		global $_shortcode_content;

		$figure_template_base = $dom_document->createElement( 'figure' );
		$figure_template_base->setAttribute( 'class', 'op-interactive' );

		$elements = $dom_document->getElementsByTagName( 'pre' );

		$i = $elements->length - 1;

		// Basic styling for the <pre> elements.
		$pre_tag_styling = 'pre { padding: 1rem; font-size: 12px; line-height: 20px; background: #f7f7f7; color: #333; }';

		/**
		 * Allow filtering of the <pre> element styling.
		 *
		 * @since 1.2.3
		 * @param string The default <pre> element styles.
		 */
		$pre_tag_styling = apply_filters( 'wpna_facebook_article_pre_block_styling', $pre_tag_styling );

		// Using a regressive loop. Removing elements with a foreach can get confused
		// when the index changes.
		while ( $i > -1 ) {

			$element = $elements->item( $i );

			// Make sure it's not part of an embed.
			if ( ! in_array( $element->parentNode->nodeName, array( 'script', 'iframe', 'figure' ), true ) ) {
				$shortcode_key = mt_rand();

				// Add the pre block inside an iFrame.
				$embed_content = '<iframe width="560" class="no-margin">' . PHP_EOL;
				// Add some CSS in to match the size correctly.
				$embed_content .= '<style type="text/css">' . PHP_EOL;
				$embed_content .= $pre_tag_styling . PHP_EOL;
				$embed_content .= '</style>' . PHP_EOL;
				$embed_content .= $dom_document->saveXML( $element ) . PHP_EOL;
				$embed_content .= '</iframe>' . PHP_EOL;

				// Cache the embed so it doesn't go through the parser.
				$_shortcode_content[ $shortcode_key ] = $embed_content;

				$figure_template            = clone $figure_template_base;
				$figure_template->nodeValue = $shortcode_key;

				$element->parentNode->replaceChild( $figure_template, $element );
			}

			$i--;
		}

		return $dom_document;
	}

	/**
	 * Replace <code> (that aren't allowed) tags with <i> tags.
	 *
	 * @since 1.2.3
	 *
	 * @access public
	 * @param  DOMDocument $dom_document Represents the HTML of the post content.
	 * @return object
	 */
	public function code_tags( DOMDocument $dom_document ) {

		global $_shortcode_content;

		$figure_template_base = $dom_document->createElement( 'i' );

		$elements = $dom_document->getElementsByTagName( 'code' );

		$i = $elements->length - 1;

		// Using a regressive loop. Removing elements with a foreach can get confused
		// when the index changes.
		while ( $i > -1 ) {

			$element = $elements->item( $i );

			// Make sure it's not part of an embed.
			if ( ! in_array( $element->parentNode->nodeName, array( 'script', 'iframe', 'figure' ), true ) ) {
				$shortcode_key = mt_rand();

				// Add the pre block inside an iFrame.
				$embed_content = $element->nodeValue;

				// Cache the embed so it doesn't go through the parser.
				$_shortcode_content[ $shortcode_key ] = $embed_content;

				$figure_template            = clone $figure_template_base;
				$figure_template->nodeValue = $shortcode_key;

				$element->parentNode->replaceChild( $figure_template, $element );
			}

			$i--;
		}

		return $dom_document;
	}

	/**
	 * Ensures article images are unique.
	 *
	 * We can only use each image once per article. This will remove any duplicates.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  DOMDocument $dom_document Represents the HTML of the post content.
	 * @return DOMDocument
	 */
	public function unique_images( DOMDocument $dom_document ) {
		$found_images = array();

		$elements = $dom_document->getElementsByTagName( 'img' );

		$i = $elements->length - 1;

		// Using a regressive loop. Removing elements with a foreach can get confused
		// when the index changes.
		while ( $i > -1 ) {
			// Setup the current element.
			$element = $elements->item( $i );

			// If the image has been used before remove it.
			if ( in_array( $element->getAttribute( 'src' ), $found_images, true ) ) {

				$element_to_remove = $element;

				// If the image has a caption we also wish to remove that.
				if ( 'div' === $element->parentNode->nodeName && false !== strpos( $element->parentNode->getAttribute( 'class' ), 'wp-caption' ) ) {
					$element_to_remove = $element->parentNode;
				}

				// Remove the element.
				$element_to_remove->parentNode->removeChild( $element_to_remove );

			} else {

				// Add it to the found images array.
				$found_images[] = $element->getAttribute( 'src' );

			}

			$i--;
		}

		return $dom_document;
	}

	/**
	 * Removes the featured image from article content.
	 *
	 * Facebook auto places the featured image of the article at the top. They
	 * don't like it being duplicated in the content as well. This attempts to
	 * remove it if it is.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param DOMDocument $dom_document Represents the HTML of the post content.
	 * @return string
	 */
	public function featured_images( DOMDocument $dom_document ) {
		global $post;
		// Setup the featured image regex if the post has one.
		if ( has_post_thumbnail( $post->ID ) ) {
			// Get the featured image source.
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
			// Make sure we've got an image.
			if ( $image ) {

				// Parse the URL into parts.
				$url_parts = wpna_parse_url( $image[0] );

				if ( ! empty( $url_parts['path'] ) ) {
					$featured_image_path = $url_parts['path'];

					$featured_image_path_ext = '.' . pathinfo( $featured_image_path, PATHINFO_EXTENSION );
					$featured_image_path     = substr( $featured_image_path, 0, strrpos( $featured_image_path, $featured_image_path_ext ) );
					$regex                   = sprintf( '/%s[-x0-9]*%s/', preg_quote( $featured_image_path, '/' ), preg_quote( $featured_image_path_ext, '/' ) );

					$elements = $dom_document->getElementsByTagName( 'img' );

					$i = $elements->length - 1;

					// Using a regressive loop. Removing elements with a foreach can get confused
					// when the index changes.
					while ( $i > -1 ) {
						// Setup the current element.
						$element = $elements->item( $i );

						// Check if the src is the same as the featured image.
						if ( preg_match( $regex, $element->getAttribute( 'src' ) ) ) {

							// Get the parent node.
							$parent_node = $element->parentNode;

							// If the image has a caption remove that as well.
							if ( in_array( $parent_node->nodeName, array( 'div', 'figure' ), true ) && false !== strpos( $parent_node->getAttribute( 'class' ), 'wp-caption' ) ) {
								$element     = $parent_node;
								$parent_node = $parent_node->parentNode;
							}

							// Remove the element.
							$parent_node->removeChild( $element );
						}

						$i--;
					}
				}
			}
		}

		return $dom_document;
	}

	/**
	 * Ensures all images exist.
	 *
	 * Facebook gets cross if the images don't exist. Let's check they all exist.
	 *
	 * @since 1.0.0
	 * @todo Investigate curl multi exec, background cron and any other more
	 * performant methods.
	 *
	 * @access public
	 * @param DOMDocument $dom_document Represents the HTML of the post content.
	 * @return DOMDocument
	 */
	public function images_exist( DOMDocument $dom_document ) {

		// Find all images and loop through them.
		$elements = $dom_document->getElementsByTagName( 'img' );

		$i = $elements->length - 1;

		// Using a regressive loop. Removing elements with a foreach can get confused
		// when the index changes.
		while ( $i > -1 ) {
			// Setup the current element.
			$element = $elements->item( $i );

			// This is obviously less than ideal as it can be slow.
			// Can't use HEAD as some sites block it / doesn't follow redirects.
			if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
				$response = vip_safe_wp_remote_get( $element->getAttribute( 'src' ) );
			} else {
				// @codingStandardsIgnoreLine
				$response = wp_remote_head( $element->getAttribute( 'src' ), array( 'sslverify' => false, 'timeout' => 1 ) );
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			$headers       = wp_remote_retrieve_headers( $response );

			// The image doesn't exist, remove it.
			// If the image is smaller than 1024 bytes, remove it.
			if ( $response_code >= 400 || ! isset( $headers['content-length'] ) || absint( $headers['content-length'] ) < 1028 ) {

				// Get the parent node.
				$parent_node = $element->parentNode;

				// If the image has a caption remove that as well.
				if ( in_array( $parent_node->nodeName, array( 'div', 'figure' ), true ) && false !== strpos( $parent_node->getAttribute( 'class' ), 'wp-caption' ) ) {
					$element     = $parent_node;
					$parent_node = $parent_node->parentNode;
				}

				// Remove the element.
				$parent_node->removeChild( $element );

			}

			$i--;
		}

		return $dom_document;
	}

	/**
	 * Carefully remove links from around images.
	 *
	 * Images can't be wrapped in links and they really mess up the rest of the
	 * parser.
	 *
	 * @since 1.2.3
	 *
	 * @access public
	 * @param  DOMDocument $dom_document Represents the HTML of the post content.
	 * @return DOMDocument
	 */
	public function unlink_images( DOMDocument $dom_document ) {

		$elements = $dom_document->getElementsByTagName( 'img' );

		$i = $elements->length - 1;

		// Using a regressive loop. Removing elements with a foreach can get confused
		// when the index changes.
		while ( $i > -1 ) {

			$element = $elements->item( $i );

			if ( 'a' === $element->parentNode->tagName ) {

				// Move the image outside the link and remove it.
				$new_image = clone $element;
				$element->parentNode->parentNode->replaceChild( $new_image, $element->parentNode );

			}

			$i--;
		}

		return $dom_document;
	}

	/**
	 * Carefully remove certain elements that aren't wrapped in <iframes>.
	 *
	 * Scripts aren't allowed outside of iFrames. Any that need inserting can
	 * be added manually.
	 *
	 * @since 1.2.2
	 *
	 * @access public
	 * @param  DOMDocument $dom_document Represents the HTML of the post content.
	 * @return DOMDocument
	 */
	public function remove_elements( DOMDocument $dom_document ) {

		// Elements to remove.
		$elements_to_remove = array( 'script', 'style' );

		/**
		 * Elements to remove.
		 *
		 * @since 1.2.2
		 * @param array The elements to search for and remove.
		 */
		$elements_to_remove = apply_filters( 'wpna_facebook_article_setup_remove_elements', $elements_to_remove );

		foreach ( $elements_to_remove as $element_to_remove ) {

			$elements = $dom_document->getElementsByTagName( $element_to_remove );

			$i = $elements->length - 1;

			// Using a regressive loop. Removing elements with a foreach can get confused
			// when the index changes.
			while ( $i > -1 ) {

				$element = $elements->item( $i );

				if ( 'figure' !== $element->parentNode->tagName && 'iframe' !== $element->parentNode->tagName ) {
					// Remove the element.
					$element->parentNode->removeChild( $element );
				}

				$i--;
			}
		}

		return $dom_document;
	}

	/**
	 * Ensures all images are wrapped in figure tags.
	 *
	 * Wraps all images in <figure> elements as specified by the Facebook spec.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  DOMDocument $dom_document Represents the HTML of the post content.
	 * @return object
	 */
	public function wrap_images( DOMDocument $dom_document ) {

		// The blank elements to create the new image with.
		$fragment_template_base = $dom_document->createDocumentFragment();
		$figure_template_base   = $dom_document->createElement( 'figure' );
		$image_template_base    = $dom_document->createElement( 'img' );

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

		$elements = $dom_document->getElementsByTagName( 'img' );

		$i = $elements->length - 1;

		// Using a regressive loop. Removing elements with a foreach can get confused
		// when the index changes.
		while ( $i > -1 ) {

			$image = $elements->item( $i );

			// Marginally faster than creating everytime.
			$fragment_template = clone $fragment_template_base;
			$figure_template   = clone $figure_template_base;
			$image_template    = clone $image_template_base;

			/**
			 * Allows filtering of the attributes set on the image figure.
			 *
			 * @since 1.0.0
			 * @param array $figure_attr Attributes for the figure element.
			 */
			$figure_attr = apply_filters( 'wpna_facebook_article_image_figure_attr', $figure_attr );

			if ( ! empty( $figure_attr ) ) {
				$figure_template->setAttribute( 'data-feedback', implode( ', ', $figure_attr ) );
			}

			$image_source = $image->getAttribute( 'src' );

			// The recommended img size is 2048x2048.
			// We ideally need to workout the image size and get the largest possible.
			// For WordPress embedded images, the image id can be in the class name,
			// which is a much cheaper lookup.
			if ( preg_match( '/wp-image-([\d]+)/', $image->getAttribute( 'class' ), $matches ) ) {
				$attachment_id = (int) $matches[1];
			} else {
				$attachment_id = wpna_get_attachment_id_from_src( $image_source );
			}

			if ( $attachment_id ) {
				// Try and get a larger version.
				$img_props = wp_get_attachment_image_src( $attachment_id, array( 2048, 2048 ) );

				if ( is_array( $img_props ) ) {
					$image_source = $img_props[0];
				}
			}

			// Create the new image element.
			$image_template->setAttribute( 'src', $image_source );
			$figure_template->appendChild( $image_template );
			$fragment_template->appendChild( $figure_template );

			// If the image has a caption we also wish to wrap that.
			if ( in_array( $image->parentNode->nodeName, array( 'div', 'figure' ), true ) && false !== strpos( $image->parentNode->getAttribute( 'class' ), 'wp-caption' ) ) {

				// Images that have captions are wrapped, use the parent element.
				$image = $image->parentNode;

				// Create a blank template.
				$figcaption_template = $dom_document->createElement( 'figcaption' );

				// If theme support for HTML5 has been added try that first.
				$caption = $image->getElementsByTagName( 'figcaption' );

				// If no HTML5 elements have been found try the default p elements.
				if ( 1 !== $caption->length ) {
					$caption = $image->getElementsByTagName( 'p' );
				}

				// If caption title is enabled and exists.
				if ( $caption_title_enabled && $caption_title = get_the_title( $attachment_id ) ) {

					// Create a blank title template.
					$figcaption_title_template = $dom_document->createElement( 'h1' );

					// Set the caption title settings.
					if ( ! empty( $caption_title_settings ) ) {
						$figcaption_title_template->setAttribute( 'class', implode( ' ', array_filter( $caption_title_settings ) ) );
					}

					// Set the caption title.
					$figcaption_title_template->nodeValue = htmlspecialchars( $caption_title );

					/**
					 * Use this filter to add attributes to the image caption title.
					 *
					 * @since 1.1.0
					 * @param DOMDocumentFragment $figcaption_template
					 * @param DOMDocumentFragment $caption_title_settings
					 */
					apply_filters( 'wpna_facebook_article_image_figurecaption_title', $figcaption_title_template, $caption_title_settings );

					// Add the title to the caption template.
					$figcaption_template->appendChild( $figcaption_title_template );
				}

				// If a caption has been found add it to the template.
				if ( 1 === $caption->length ) {
					// Create a textnode template with the caption content.
					$figcaption_text_template = $dom_document->createTextNode( htmlspecialchars( $caption->item( 0 )->nodeValue ) );
					// Append it to the caption template (doesn't override the titel if it's set).
					$figcaption_template->appendChild( $figcaption_text_template );
				}

				// Set the caption settings, position, size etc.
				if ( ! empty( $caption_settings ) ) {
					$figcaption_template->setAttribute( 'class', implode( ' ', array_filter( $caption_settings ) ) );
				}

				/**
				 * Use this filter add custom attributes to the image caption.
				 *
				 * @since 1.0.0
				 * @param DOMDocumentFragment $figcaption_template
				 */
				apply_filters( 'wpna_facebook_article_image_figurecaption', $figcaption_template );

				$figure_template->appendChild( $figcaption_template );
			}

			/**
			 * Add a filter allowing people alter the figure_template.
			 *
			 * @since 1.0.0
			 * @param DOMDocumentFragment $figure_template
			 */
			apply_filters( 'wpna_facebook_article_image_figure', $figure_template );

			// Replace the element we found with the new one.
			$image->parentNode->replaceChild( $fragment_template, $image );

			$i--;
		}

		return $dom_document;
	}

	/**
	 * Ensures specified elements aren't wrapped in other elements.
	 *
	 * Some elements (iFrames, figures etc) have to be top level, not nestled
	 * inside other elements. This does prove tricky with some of the markup
	 * generated by WP.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  DOMDocument $dom_document Represents the HTML of the post content.
	 * @return object
	 */
	public function move_elements( DOMDocument $dom_document ) {

		// Elements to move.
		$elements_to_move = array( 'iframe', 'figure', 'img', 'table' );

		/**
		 * Elements to move
		 *
		 * @since 1.0.0
		 * @param array The elements to search for and wrap.
		 */
		$elements_to_move = apply_filters( 'wpna_facebook_article_setup_move_elements', $elements_to_move );

		foreach ( $elements_to_move as $element_to_move ) {

			$elements = $dom_document->getElementsByTagName( $element_to_move );

			$i = $elements->length - 1;

			// Using a regressive loop. Removing elements with a foreach can get confused
			// when the index changes.
			while ( $i > -1 ) {

				$element = $elements->item( $i );

				$parent_node = $element->parentNode;

				// If it's an image it has special rules.
				if ( 'img' === $element_to_move ) {
					// Take account of images wrapped in captions.
					if ( in_array( $parent_node->nodeName, array( 'div', 'figure' ), true ) && false !== strpos( $parent_node->getAttribute( 'class' ), 'wp-caption' ) ) {
						$element     = $parent_node;
						$parent_node = $parent_node->parentNode;
					}
				}

				// If it's already top level then let's not worry.
				if ( 'body' === $parent_node->nodeName ) {
					$i--;
					continue;
				}

				// Get the parent nearest to the body element.
				// Keep track of how many elements deep the image is nested.
				$parents = array();
				while ( 'body' !== $parent_node->nodeName ) {
					$parents[]   = sprintf( '%s>', $parent_node->nodeName );
					$parent_node = $parent_node->parentNode;
				}

				// Construct the opening and closing tags for before and after the element.
				$parents_closing_tags = '</' . implode( '</', $parents );
				$parents_opening_tags = '<' . implode( '<', array_reverse( $parents ) );

				// Get the string to replace the image element with.
				$replace_with = sprintf( '%s%s%s%s%s', $parents_closing_tags, PHP_EOL, $dom_document->saveXML( $element ), PHP_EOL, $parents_opening_tags );

				// Replace the image element with the new opening and closing tags.
				$parent_node_html = str_replace( $dom_document->saveXML( $element ), $replace_with, $dom_document->saveXML( $parent_node ) );

				// To replace the current parent we need to load the new node
				// fragment into a new instance of DOMDocument.
				$libxml_previous_state = libxml_use_internal_errors( true );
				$dom_document_temp     = new DOMDocument( '1.0', get_option( 'blog_charset' ) );

				// Make sure it's the correct encoding.
				if ( function_exists( 'mb_convert_encoding' ) ) {
					$parent_node_html = mb_convert_encoding( $parent_node_html, 'HTML-ENTITIES', get_option( 'blog_charset' ) );
				}

				// Remove cdata added by saveXML().
				$parent_node_html = str_replace( array( '<![CDATA[', ']]>' ), '', $parent_node_html );

				$dom_document_temp->loadHTML( '<?xml version="1.0" encoding="utf-8"?><root/>' . $parent_node_html );
				libxml_clear_errors();
				libxml_use_internal_errors( $libxml_previous_state );
				$body_temp     = $dom_document_temp->getElementsByTagName( 'body' )->item( 0 );
				$imported_node = $dom_document->importNode( $body_temp, true );

				// Now replace the existing element with the new element in the real DOMDocument.
				$parent_node->parentNode->replaceChild( $imported_node, $parent_node );

				$i--;
			}
		}

		return $dom_document;
	}

	/**
	 * Ensures certain elements are wrapped in figure tags.
	 *
	 * Wraps all specifiec elements in <figure> tags. E.g. iFrames.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  DOMDocument $dom_document Represents the HTML of the post content.
	 * @return DOMDocument
	 */
	public function wrap_elements( DOMDocument $dom_document ) {

		$figure_template_base = $dom_document->createElement( 'figure' );
		$figure_template_base->setAttribute( 'class', 'op-interactive' );

		// The elements to wrap.
		$elements_to_wrap = array( 'iframe', 'table' );

		/**
		 * Elements to wrap in <figure> tags.
		 *
		 * @since 1.0.0
		 * @param array The elements to search for and wrap.
		 */
		$elements_to_wrap = apply_filters( 'wpna_facebook_article_setup_wrap_elements', $elements_to_wrap );

		foreach ( $elements_to_wrap as $element_to_wrap ) {

			$elements = $dom_document->getElementsByTagName( $element_to_wrap );

			$i = $elements->length - 1;

			// Using a regressive loop. Removing elements with a foreach can get confused
			// when the index changes.
			while ( $i > -1 ) {

				$element = $elements->item( $i );

				if ( 'figure' !== $element->parentNode->tagName ) {

					$figure_template = clone $figure_template_base;
					$element->parentNode->replaceChild( $figure_template, $element );
					$figure_template->appendChild( $element );

				}

				$i--;
			}
		}

		return $dom_document;
	}

	/**
	 * Remove any normal attributes from elements.
	 *
	 * Strips style, class and id attributes from all elements.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  DOMDocument $dom_document Represents the HTML of the post content.
	 * @return DOMDocument
	 */
	public function remove_attributes( DOMDocument $dom_document ) {

		foreach ( $dom_document->getElementsByTagName( '*' ) as $node ) {

			if ( $node instanceof DOMElement && ! in_array( $node->tagName, array( 'figure', 'figcaption' ), true ) ) {
				$node->removeAttribute( 'style' );
				$node->removeAttribute( 'class' );
				$node->removeAttribute( 'id' );
			}
		}

		return $dom_document;
	}

	/**
	 * Ensures all text is wrapped in p tags.
	 *
	 * Wraps all text in <p> elements as specified by the Facebook spec.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  DOMDocument $dom_document Represents the HTML of the post content.
	 * @return DOMDocument
	 */
	public function wrap_text( DOMDocument $dom_document ) {
		$body            = $dom_document->getElementsByTagName( 'body' )->item( 0 );
		$p_template_base = $dom_document->createElement( 'p' );

		foreach ( $body->childNodes as $node ) {
			if ( '#text' === $node->nodeName && '' !== trim( $node->nodeValue ) ) {
				$p_template = clone $p_template_base;
				$node->parentNode->replaceChild( $p_template, $node );
				$p_template->appendChild( $node );
			}
		}

		return $dom_document;
	}

	/**
	 * Remove all empty elements from the content.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  DOMDocument $dom_document Represents the HTML of the post content.
	 * @return object
	 */
	public function remove_empty_elements( DOMDocument $dom_document ) {

		$elements = $dom_document->getElementsByTagName( 'body' )->item( 0 )->getElementsByTagName( '*' );

		$i = $elements->length - 1;

		// Using a regressive loop. Removing elements with a foreach can get confused
		// when the index changes.
		while ( $i > -1 ) {

			$element = $elements->item( $i );

			// Ensure there's no empty paragraphs.
			$trimmed_content = trim( $element->textContent );

			// If the node is completely empty queue it for removal.
			if (
				! in_array( $element->tagName, array( 'img', 'figure', 'iframe', 'script' ), true )
				&&
				(
					empty( $trimmed_content )
					||
					'&nbsp;' === htmlentities( $trimmed_content )
				)
			) {
				$element->parentNode->removeChild( $element );
			}

			$i--;
		}

		return $dom_document;
	}

	/**
	 * Remove the shortcode wrap filter.
	 *
	 * Cleaning up after ourselves. Restore the shortcode original functions.
	 * Though technically a filter this is being used more like an action.
	 *
	 * @since 1.0.0
	 * @todo Not overwrite the global array.
	 *
	 * @access public
	 * @param  string $content The content of the post.
	 * @return string
	 */
	public function remove_shortcode_wrapper( $content ) {
		global $shortcode_tags, $_shortcode_tags;

		// @codingStandardsIgnoreLine
		$shortcode_tags = $_shortcode_tags;

		return $content;
	}

	/**
	 * Remove the oembed wrap filter.
	 *
	 * Cleaning up after ourselves. Though technically a filter this is being
	 * used more like an action.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $content The content of the post.
	 * @return string
	 */
	public function remove_oembed_wrapper( $content ) {
		remove_filter( 'embed_oembed_html', array( $this, 'wrap_oembed' ) );

		return $content;
	}

	/**
	 * Repalces embeds.
	 *
	 * Shortcodes and embeds were removed while we format the article.
	 * This places them back.
	 *
	 * @since 0.0.1
	 *
	 * @access public
	 * @param  string $content The content of the post.
	 * @return string
	 */
	public function restore_embeds( $content ) {
		global $_shortcode_content;

		// V2 embeds are wrapped in <pre> tags.
		foreach ( $_shortcode_content as $placeholder => $shortcode_content ) {
			$content = str_replace( '<pre>' . $placeholder . '</pre>', $shortcode_content, $content );
		}

		// V1 embeds aren't wrapped at all.
		$content = str_replace( array_keys( $_shortcode_content ), array_values( $_shortcode_content ), $content );

		return $content;
	}

}
