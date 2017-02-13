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
		add_filter( 'wpna_facebook_article_pre_the_content_filter', array( $this, 'setup_wrap_shortcodes' ), 5, 1 );
		add_filter( 'wpna_facebook_article_pre_the_content_filter', array( $this, 'setup_wrap_oembeds' ), 5, 1 );

		add_filter( 'wpna_facebook_article_after_the_content_filter', array( $this, 'convert_headings' ), 10, 1 );

		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'unique_images' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'featured_images' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'images_exist' ), 10, 1 );
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
	 * Wrap shortcodes in <figure> elements.
	 *
	 * Shortcodes can output anything. There's no sane way to try and
	 * anticipate them, this ensures they're always wrapped in <figure> tags.
	 *
	 * Hijacks the global array of shortcodes and funtions. Replaces the functions
	 * with a custom  method that wraps the the shortcode before calling the
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
		$disabled_tags = array( 'gallery', 'caption', 'wp_caption' );

		/**
		 * Add a filter allowing alteration of the $disabled_tags array.
		 *
		 * @since 1.0.0
		 * @param array   $disabled_tags
		 * @param content $content
		 */
		$disabled_tags = apply_filters( 'wpna_facebook_article_setup_wrap_shortcodes_disabled_tags', $disabled_tags, $content );

		foreach ( $shortcode_tags as $tag => $cb ) {
			if ( in_array( $tag, $disabled_tags, true ) ) {
				continue;
			}
			// Overwrite the callback function.
			$shortcode_tags[ $tag ] = array( $this, 'wrap_shortcode' );
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

		// Wrap it in an iframe if it isn't already.
		if ( '<iframe' !== substr( $content, 0, 7 ) ) {
			$content = '<iframe>' . $content . '</iframe>';
		}

		// Store the shortocde content in the global array.
		$_shortcode_content[ $shortcode_key ] = $content;

		// Return the unique key wrapped in a figure element.
		return '<figure class="op-interactive">' . $shortcode_key . '</figure>';
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
		if ( '<iframe' !== substr( $cache, 0, 7 ) ) {
			$cache = '<iframe>' . $cache . '</iframe>';
		}

		$_shortcode_content[ $shortcode_key ] = $cache;

		return '<figure class="op-interactive">' . $shortcode_key . '</figure>';
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
			'<hr>',
			'<i>',
		);

		return strip_tags( $content, implode( '', $allowed_tags ) );
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

		foreach ( $images = $dom_document->getElementsByTagName( 'img' ) as $image ) {

			// If the image has been used before remove it.
			if ( in_array( $image->getAttribute( 'src' ), $found_images, true ) ) {

				$element_to_remove = $image;

				// If the image has a caption we also wish to remove that.
				if ( 'div' === $image->parentNode->nodeName && false !== strpos( $image->parentNode->getAttribute( 'class' ), 'wp-caption' ) ) {
					$element_to_remove = $image->parentNode;
				}

				// Remove the element.
				$element_to_remove->parentNode->removeChild( $element_to_remove );

			} else {

				// Add it to the found images array.
				$found_images[] = $image->getAttribute( 'src' );

			}
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
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
			$featured_image_path = wp_parse_url( $image[0], PHP_URL_PATH );
			$featured_image_path_ext = '.' . pathinfo( $featured_image_path, PATHINFO_EXTENSION );
			$featured_image_path = substr( $featured_image_path, 0, strrpos( $featured_image_path, $featured_image_path_ext ) );
			$regex = sprintf( '/%s[-x0-9]*%s/', preg_quote( $featured_image_path, '/' ), preg_quote( $featured_image_path_ext, '/' ) );

			// Get all images.
			foreach ( $images = $dom_document->getElementsByTagName( 'img' ) as $element ) {
				// Check if the src is the same as the featured image.
				if ( preg_match( $regex, $element->getAttribute( 'src' ) ) ) {

					// Get the parent node.
					$parent_node = $element->parentNode;

					// If the image has a caption remove that as well.
					if ( in_array( $parent_node->nodeName, array( 'div', 'figure' ), true ) && false !== strpos( $parent_node->getAttribute( 'class' ), 'wp-caption' ) ) {
						$element = $parent_node;
						$parent_node = $parent_node->parentNode;
					}

					// Remove the element.
					$parent_node->removeChild( $element );
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
		foreach ( $images = $dom_document->getElementsByTagName( 'img' ) as $element ) {

			// This is obviously less than ideal as it can be slow.
			$response = wp_remote_head( $element->getAttribute( 'src' ) );
			$response_code = wp_remote_retrieve_response_code( $response );

			// The image doesn't exist, remove it.
			if ( 200 !== $response_code ) {

				// Get the parent node.
				$parent_node = $element->parentNode;

				// If the image has a caption remove that as well.
				if ( in_array( $parent_node->nodeName, array( 'div', 'figure' ), true ) && false !== strpos( $parent_node->getAttribute( 'class' ), 'wp-caption' ) ) {
					$element = $parent_node;
					$parent_node = $parent_node->parentNode;
				}

				// Remove the element.
				$parent_node->removeChild( $element );

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
		$figure_template_base = $dom_document->createElement( 'figure' );
		$image_template_base = $dom_document->createElement( 'img' );

		// If they've enabled Likes or Comments on images.
		$figure_attr = array();

		// Check the post for an overide options else use global.
		if ( ! $image_likes = get_post_meta( get_the_ID(), 'fbna_image_likes', true ) ) {
			$image_likes = wpna_get_option( 'fbna_image_likes' );
		}

		if ( wpna_switch_to_boolean( $image_likes ) ) {
			$figure_attr[] = 'fb:likes';
		}

		if ( ! $image_comments = get_post_meta( get_the_ID(), 'fbna_image_comments', true ) ) {
			$image_comments = wpna_get_option( 'fbna_image_comments' );
		}

		if ( wpna_switch_to_boolean( $image_comments ) ) {
			$figure_attr[] = 'fb:comments';
		}

		foreach ( $dom_document->getElementsByTagName( 'img' ) as $image ) {

			// Marginally faster than creating everytime.
			$fragment_template = clone $fragment_template_base;
			$figure_template = clone $figure_template_base;
			$image_template = clone $image_template_base;

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

				// If they've added theme support for HTML5 try that first.
				$caption = $image->getElementsByTagName( 'figcaption' );

				// If no HTML5 elements have been found try the default p elements.
				if ( 1 !== $caption->length ) {
					$caption = $image->getElementsByTagName( 'p' );
				}

				// If we've found anything add the contents to the template.
				if ( 1 === $caption->length ) {
					$figcaption_template->nodeValue = htmlspecialchars( $caption->item( 0 )->nodeValue );
				}

				/**
				 * Use this filter at add attributes to the image caption.
				 *
				 * @since 1.0.0
				 * @param DOMDocumentFragment $figcaption_template
				 */
				apply_filters( 'wpna_facebook_article_image_figurecaption', $figcaption_template );

				$figure_template->appendChild( $figcaption_template );

			}

			/**
			 * Add a filter allowing people alter the figure_template
			 *
			 * @since 1.0.0
			 * @param DOMDocumentFragment $figure_template
			 */
			apply_filters( 'wpna_facebook_article_image_figure', $figure_template );

			// Replace the element we found with the new one.
			$image->parentNode->replaceChild( $fragment_template, $image );
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
		$elements_to_wrap = apply_filters( 'wpna_facebook_article_setup_move_elements', $elements_to_move );

		foreach ( $elements_to_move as $element_to_move ) {

			foreach ( $elements = $dom_document->getElementsByTagName( $element_to_move ) as $element ) {

				$parent_node = $element->parentNode;

				// If it's an image it has special rules.
				if ( 'img' === $element_to_move ) {
					// Take account of images wrapped in captions.
					if ( in_array( $parent_node->nodeName, array( 'div', 'figure' ), true ) && false !== strpos( $parent_node->getAttribute( 'class' ), 'wp-caption' ) ) {
						$element = $parent_node;
						$parent_node = $parent_node->parentNode;
					}
				}

				// If it's already top level then let's not worry.
				if ( 'body' === $parent_node->nodeName ) {
					continue;
				}

				// Get the parent nearest to the body element.
				// Keep track of how many elements deep the image is nested.
				$parents = array();
				while ( 'body' !== $parent_node->nodeName ) {
					$parents[] = sprintf( '%s>', $parent_node->nodeName );
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
				$dom_document_temp = new DOMDocument( '1.0', get_option( 'blog_charset' ) );

				// Make sure it's the correct encoding.
				if ( function_exists( 'mb_convert_encoding' ) ) {
					$parent_node_html = mb_convert_encoding( $parent_node_html, 'HTML-ENTITIES', get_option( 'blog_charset' ) );
				}

				$dom_document_temp->loadHTML( '<!doctype html><html><body>' . $parent_node_html . '</body></html>' );
				libxml_clear_errors();
				libxml_use_internal_errors( $libxml_previous_state );
				$body_temp = $dom_document_temp->getElementsByTagName( 'body' )->item( 0 );
				$imported_node = $dom_document->importNode( $body_temp, true );

				// Now replace the existing element with the new element in the real DOMDocument.
				$parent_node->parentNode->replaceChild( $imported_node, $parent_node );
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

			foreach ( $elements = $dom_document->getElementsByTagName( $element_to_wrap ) as $element ) {
				if ( 'figure' !== $element->parentNode->tagName ) {

					$figure_template = clone $figure_template_base;
					$element->parentNode->replaceChild( $figure_template, $element );
					$figure_template->appendChild( $element );

				}
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

			if ( $node instanceof DOMElement && ! in_array( $node->tagName, array( 'figure' ), true ) ) {
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
		$body = $dom_document->getElementsByTagName( 'body' )->item( 0 );
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

		// Holds the empty nodes that will need removing.
		$nodes_to_remove = array();

		foreach ( $dom_document->getElementsByTagName( '*' ) as $node ) {

			// Ensure there's no empty paragraphs.
			$trimmed_content = trim( $node->textContent );

			// If the node is completely empty queue it for removal.
			if (
				! in_array( $node->tagName, array( 'img', 'figure', 'iframe', 'script' ), true ) &&
				empty( $trimmed_content )
			) {
				$nodes_to_remove[] = $node;
			}
		}

		// Remove all the empty nodes we found.
		// WARNING :: Don't attempt to do this inline in the loop above,
		// it won't work for all nodes.
		foreach ( $nodes_to_remove as $node ) {
			$node->parentNode->removeChild( $node );
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
	 *
	 * @access public
	 * @param  string $content The content of the post.
	 * @return string
	 */
	public function remove_shortcode_wrapper( $content ) {
		global $shortcode_tags, $_shortcode_tags;

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
	 * This places the m back.
	 *
	 * @since 0.0.1
	 *
	 * @access public
	 * @param  string $content The content of the post.
	 * @return string
	 */
	public function restore_embeds( $content ) {
		global $_shortcode_content;

		return str_replace( array_keys( $_shortcode_content ), array_values( $_shortcode_content ), $content );
	}

}
