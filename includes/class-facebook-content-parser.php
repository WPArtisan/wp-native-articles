<?php
/**
 * Facebook Content parsing class.
 *
 * @since 0.0.1
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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
	 * @since 0.0.1
	 *
	 * @access public
	 * @return null
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Hooks registered in this class.
	 *
	 * @since 0.0.1
	 *
	 * @access public
	 * @return null
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
	 * @since 0.0.1
	 * @link https://developers.facebook.com/docs/instant-articles/reference
	 *
	 * @access public
	 * @return null
	 */
	public function content_hooks() {

		// We can help clean up the content before WP gets to it
		add_filter( 'wpna_facebook_article_pre_the_content_filter', array( $this, 'wrap_shortcodes' ), 5, 1 );

		add_filter( 'wpna_facebook_article_after_the_content_filter', array( $this, 'convert_headings' ), 10, 1 );
		add_filter( 'wpna_facebook_article_after_the_content_filter', array( $this, 'strip_elements' ), 10, 1 );

		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'unique_images' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'featured_images' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'images_exist' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'move_images' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'wrap_images' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'move_iframes' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'wrap_iframes' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'remove_attributes' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'wrap_text' ), 10, 1 );
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'remove_empty_elements' ), 10, 1 );

		add_filter( 'wpna_facebook_article_content_after_transform', array( $this, 'unwrap_shortcodes' ), 10, 1 );
	}

	/**
	 * Wraps shortcodes in pre tags to stop them getting getting wrapped in p tags.
	 *
	 * Wraps all custom shortcodes in <pre class="embed-wrap"> to stop
	 * them getting caught up in the filters and we can replace later.
	 * pre tags doesn't get parsed by wpautop.
	 *
	 * @since 0.0.1
	 *
	 * @access public
	 * @param  string $content
	 * @return string
	 */
	public function wrap_shortcodes( $content ) {
		// Add the filter so oembeds are auto wrapped
		add_filter( 'embed_oembed_html', array( $this, 'wrap_oembeds' ), 10, 4 );

		preg_match_all( '/' . get_shortcode_regex() . '/', $content, $matches, PREG_SET_ORDER );
		foreach ( (array) $matches as $match ) {
			if ( ! in_array( $match[2], array( 'caption' ) ) )
				$content = str_ireplace( $match[0], sprintf( '<pre data-embed-wrap="true">%s</pre>', $match[0] ), $content );
		}

		return $content;
	}

	/**
	 * Ensures all oembeds are properly wrapped in <figure> elements.
	 *
	 * @param  string  $cache
	 * @param  string  $url
	 * @param  array   $attr
	 * @param  int     $post_ID
	 * @return string
	 */
	public function wrap_oembeds( $cache, $url, $attr, $post_ID ) {
		return '<figure class="op-interactive">' . $cache . '</figure>';
	}

	/**
	 * Remove all invalid headings.
	 *
	 * FB IA only recognises h1 & h2 headings (Except in the header).
	 *
	 * @since 0.0.1
	 *
	 * @access public
	 * @param  string $content
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
	 * @since 0.0.1
	 *
	 * @access public
	 * @param  string $content
	 * @return string
	 */
	public function strip_elements( $content ) {
		$allowed_tags = array(
			// Mentioned in the FB IA docs
			// and thus are explicitly allowed
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

			// Unsure about these but seems likley
			'<acronym>',
			'<b>',
			'<br>',
			'<font>',
			'<hr>',
			'<i>',

			// Not allowed by FB but used to stop
			// shortcodes getting wrapped in <p> tags
			'<pre>',
		);

		return strip_tags( $content, implode( '', $allowed_tags ) );
	}

	/**
	 * Ensures article images are unique.
	 *
	 * We can only use each image once per article. This will remove any duplicates.
	 *
	 * @since 0.0.1
	 *
	 * @access public
	 * @param  DOMDocument $DOMDocument
	 * @return DOMDocument
	 */
	public function unique_images( DOMDocument $DOMDocument ) {
		$found_images = array();

		foreach ( $images = $DOMDocument->getElementsByTagName('img') as $image ) {

			// If the image has been used before remove it.
			if ( in_array( $image->getAttribute( 'src' ), $found_images ) ) {

				$element_to_remove = $image;

				// If the image has a caption we also wish to remove that
				if ( 'div' == $image->parentNode->nodeName && false !== strpos( $image->parentNode->getAttribute('class'), 'wp-caption' ) ) {
					$element_to_remove = $image->parentNode;
				}

				// Remove the element
				$element_to_remove->parentNode->removeChild( $element_to_remove );

			} else {

				// Add it to the found images array
				$found_images[] = $image->getAttribute( 'src' );

			}

		}

		return $DOMDocument;
	}

	/**
	 * Removes the featured image from article content.
	 *
	 * Facebook auto places the featured image of the article at the top. They
	 * don't like it being duplicated in the content as well. This attempts to
	 * remove it if it is.
	 *
	 * @since 0.0.1
	 *
	 * @access public
	 * @param  DOMDocument $DOMDocument
	 * @return string
	 */
	public function featured_images( DOMDocument $DOMDocument ) {
		global $post;
		// Setup the featured image regex if the post has one
		if ( has_post_thumbnail( $post->ID ) ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
			$featured_image_path = parse_url( $image[0], PHP_URL_PATH );
			$featured_image_path_ext = '.' . pathinfo( $featured_image_path, PATHINFO_EXTENSION );
			$featured_image_path = substr( $featured_image_path, 0, strrpos( $featured_image_path, $featured_image_path_ext ) );
			$regex = sprintf( '/%s[-x0-9]*%s/', preg_quote( $featured_image_path, '/' ), preg_quote( $featured_image_path_ext, '/' ) );

			foreach ( $images = $DOMDocument->getElementsByTagName('img') as $image ) {
				if ( preg_match( $regex, $image->getAttribute('src') ) ) {
					$parent = $image->parentNode;
					$parent->removeChild( $image );
				}
			}
		}

		return $DOMDocument;
	}

	/**
	 * Ensures all images exist.
	 *
	 * Facebook get cross if the images don't exist. Let's check they all exist.
	 *
	 * @since 0.0.1
	 * @todo Investigate curl multi exec, background cron and any other more
	 * performant methods.
	 *
	 * @access public
	 * @return null
	 */
	public function images_exist( DOMDocument $DOMDocument ) {
		foreach ( $images = $DOMDocument->getElementsByTagName('img') as $image ) {
			// This is obviously less than ideal
			$response = wp_remote_head( $image->getAttribute('src') );
			$response_code = wp_remote_retrieve_response_code( $response );
			if ( 200 != $response_code ) {
				// Remove the image
				$parent = $image->parentNode;
				$parent->removeChild( $image );
			}
		}

		return $DOMDocument;
	}

	/**
	 * Ensures images aren't wrapped in other elements.
	 *
	 * Images have to be top level, not nestled inside other elements.
	 * This does prove tricky with some of the markup generated by WP.
	 *
	 * @since 0.0.1
	 *
	 * @access public
	 * @param  DOMDocument $DOMDocument
	 * @return object
	 */
	public function move_images( DOMDocument $DOMDocument ) {

		foreach ( $images = $DOMDocument->getElementsByTagName('img') as $image ) {

			$parentNode = $image->parentNode;

			// Take account of images wrapped in captions
			if ( 'div' == $parentNode->nodeName && false !== strpos( $parentNode->getAttribute('class'), 'wp-caption' ) ) {
				$parentNode = $parentNode->parentNode;
			}

			// If it's already top level then let's not worry
			if ( 'body' === $parentNode->nodeName )
				continue;

			// Get the parent nearest to the body element
			// Keep track of how many elements deep the image is nested.
			$parents = array();
			while ( 'body' !== $parentNode->nodeName ) {
				$parents[] = sprintf( "%s>", $parentNode->nodeName );
				$parentNode = $parentNode->parentNode;
			}

			// Construct the opening and closing tags for before and after the image.
			$parents_closing_tags = '</' . implode( '</', $parents );
			$parents_opening_tags = '<' . implode( '<', array_reverse( $parents ) );

			// Get the string to replace the image element with
			$replace_with = sprintf( "%s%s%s%s%s", $parents_closing_tags, PHP_EOL, $DOMDocument->saveHTML( $image ), PHP_EOL, $parents_opening_tags );

			// Replace the image element with the new opening and closing tags
			$parent_node_html = str_replace( $DOMDocument->saveHTML( $image ), $replace_with, $DOMDocument->saveHTML( $parentNode ) );

			// To replace the current parent we need to load the new node
			// fragment into a new instance of DOMDocument
			$libxml_previous_state = libxml_use_internal_errors( true );
			$DOMDocument_temp = new \DOMDocument( '1.0', get_option( 'blog_charset' ) );

			// Make sure it's the correct encoding
			if ( function_exists( 'mb_convert_encoding' ) ) {
				$parent_node_html = mb_convert_encoding( $parent_node_html, 'HTML-ENTITIES', get_option( 'blog_charset' ) );
			}

			$DOMDocument_temp->loadHTML( '<!doctype html><html><body>' . $parent_node_html . '</body></html>' );
			libxml_clear_errors();
			libxml_use_internal_errors( $libxml_previous_state );
			$body_temp = $DOMDocument_temp->getElementsByTagName( 'body' )->item( 0 );
			$importedNode = $DOMDocument->importNode( $body_temp, TRUE );

			// Now replace the existing element with the new element in the real DOMDocument
			$parentNode->parentNode->replaceChild( $importedNode, $parentNode );
		}

		return $DOMDocument;
	}

	/**
	 * Ensures all images are wrapped in figure tags.
	 *
	 * Wraps all images in <figure> elements as specified by the Facebook spec.
	 *
	 * @since 0.0.1
	 *
	 * @access public
	 * @param  DOMDocument $DOMDocument
	 * @return object
	 */
	public function wrap_images( DOMDocument $DOMDocument ) {

		// The blank elements to create the new image with
		$fragment_template_base = $DOMDocument->createDocumentFragment();
		$figure_template_base = $DOMDocument->createElement('figure');
		$image_template_base = $DOMDocument->createElement('img');

		// If they've enabled Likes or Comments on images
		$figure_attr = array();

		// Check the post for an overide options else use global
		if ( ! $image_likes = get_post_meta( get_the_ID(), 'fbna_image_likes', true ) )
			$image_likes = wpna_get_option('fbna_image_likes');

		if ( wpna_switch_to_boolean( $image_likes ) )
			$figure_attr[] = 'fb:likes';

		if ( ! $image_comments = get_post_meta( get_the_ID(), 'fbna_image_comments', true ) )
			$image_comments = wpna_get_option('fbna_image_comments');

		if ( wpna_switch_to_boolean( $image_comments ) )
			$figure_attr[] = 'fb:comments';

		foreach ( $DOMDocument->getElementsByTagName('img') as $image ) {

			// Marginally faster than creating everytime
			$fragment_template = clone $fragment_template_base;
			$figure_template = clone $figure_template_base;
			$image_template = clone $image_template_base;

			/**
			 * Allows filtering of the attributes set on the image figure.
			 *
			 * @since 0.0.1
			 * @param array $figure_attr Attributes for the figure element.
			 */
			$figure_attr = apply_filters( 'wpna_facebook_article_image_figure_attr', $figure_attr );

			if ( ! empty( $figure_attr ) )
				$figure_template->setAttribute( 'data-feedback', implode( ', ', $figure_attr ) );

			$image_source = $image->getAttribute('src');

			// The recommended img size is 2048x2048.
			// We ideally need to workout the image size and get the largest possible
			$attachment_id = wpna_get_attachment_id_from_src( $image_source );

			if ( $attachment_id ) {
				// Try and get a larger version
				$img_props = wp_get_attachment_image_src( $attachment_id, array( 2048, 2048 ) );
				if ( is_array( $img_props ) )
					$image_source = $img_props[0];
			}

			// Create the new image element
			$image_template->setAttribute( 'src', $image_source );
			$figure_template->appendChild( $image_template );
			$fragment_template->appendChild( $figure_template );

			// If the image has a caption we also wish to wrap that
			if ( 'div' == $image->parentNode->nodeName && false !== strpos( $image->parentNode->getAttribute('class'), 'wp-caption' ) ) {

				$image = $image->parentNode;

				$caption = $image->getElementsByTagName('p');

				if ( 1 == $caption->length ) {

					$figcaption_template = $DOMDocument->createElement('figcaption');
					$figcaption_template->nodeValue = $caption->item(0)->nodeValue;

					/**
					 * Use this filter at add attributes to the image caption.
					 *
					 * @since 0.0.1
					 * @param DOMDocumentFragment $figcaption_template
					 */
					apply_filters( 'wpna_facebook_article_image_figurecaption', $figcaption_template );

					$figure_template->appendChild( $figcaption_template );
				}

			}

			/**
			 * Add a filter allowing people alter the figure_template
			 *
			 * @since 0.0.1
			 * @param DOMDocumentFragment $figure_template
			 */
			apply_filters( 'wpna_facebook_article_image_figure', $figure_template );

			// Replace the element we found with the new one
			$image->parentNode->replaceChild( $fragment_template, $image );
		}

		return $DOMDocument;
	}

	/**
	 * Ensures iFrames aren't wrapped in other elements.
	 *
	 * iFrames have to be top level, not nestled inside other elements.
	 * This does prove tricky with some of the markup generated by WP.
	 *
	 * @since 0.0.1
	 *
	 * @access public
	 * @param  DOMDocument $DOMDocument
	 * @return object
	 */
	public function move_iframes( DOMDocument $DOMDocument ) {

		foreach ( $iframes = $DOMDocument->getElementsByTagName('iframe') as $iframe ) {

			$parentNode = $iframe->parentNode;

			// If it's already top level then let's not worry
			if ( 'body' === $parentNode->nodeName )
				continue;

			// Get the parent nearest to the body element
			// Keep track of how many elements deep the image is nested.
			$parents = array();
			while ( 'body' !== $parentNode->nodeName ) {
				$parents[] = sprintf( "%s>", $parentNode->nodeName );
				$parentNode = $parentNode->parentNode;
			}

			// Construct the opening and closing tags for before and after the image.
			$parents_closing_tags = '</' . implode( '</', $parents );
			$parents_opening_tags = '<' . implode( '<', array_reverse( $parents ) );

			// Get the string to replace the image element with
			$replace_with = sprintf( "%s%s%s%s%s", $parents_closing_tags, PHP_EOL, $DOMDocument->saveHTML( $iframe ), PHP_EOL, $parents_opening_tags );

			// Replace the image element with the new opening and closing tags
			$parent_node_html = str_replace( $DOMDocument->saveHTML( $iframe ), $replace_with, $DOMDocument->saveHTML( $parentNode ) );

			// To replace the current parent we need to load the new node
			// fragment into a new instance of DOMDocument
			$libxml_previous_state = libxml_use_internal_errors( true );
			$DOMDocument_temp = new \DOMDocument( '1.0', get_option( 'blog_charset' ) );

			// Make sure it's the correct encoding
			if ( function_exists( 'mb_convert_encoding' ) ) {
				$parent_node_html = mb_convert_encoding( $parent_node_html, 'HTML-ENTITIES', get_option( 'blog_charset' ) );
			}

			$DOMDocument_temp->loadHTML( '<!doctype html><html><body>' . $parent_node_html . '</body></html>' );
			libxml_clear_errors();
			libxml_use_internal_errors( $libxml_previous_state );
			$body_temp = $DOMDocument_temp->getElementsByTagName( 'body' )->item( 0 );
			$importedNode = $DOMDocument->importNode( $body_temp, TRUE );

			// Now replace the existing element with the new element in the real DOMDocument
			$parentNode->parentNode->replaceChild( $importedNode, $parentNode );
		}

		return $DOMDocument;
	}

	/**
	 * Ensures all iFrames are wrapped in figure tags.
	 *
	 * Wraps all iFrames in <figure> elements as specified by the Facebook spec.
	 *
	 * @since 0.0.1
	 *
	 * @access public
	 * @param  DOMDocument $DOMDocument
	 * @return DOMDocument
	 */
	public function wrap_iframes( DOMDocument $DOMDocument ) {

		$figure_template_base = $DOMDocument->createElement('figure');
		$figure_template_base->setAttribute('class', 'op-interactive');

		foreach ( $DOMDocument->getElementsByTagName('iframe') as $iframe ) {
			if ( 'figure' != $iframe->parentNode->tagName ) {

				$figure_template = clone $figure_template_base;
				$iframe->parentNode->replaceChild( $figure_template, $iframe );
				$figure_template->appendChild( $iframe );

			}
		}

		return $DOMDocument;
	}

	/**
	 * Remove any normal attributes from elements.
	 *
	 * Strips style, class and id attributes from all elements.
	 *
	 * @since 0.0.1
	 *
	 * @access public
	 * @param  DOMDocument $DOMDocument
	 * @return DOMDocument
	 */
	public function remove_attributes( DOMDocument $DOMDocument ) {

		foreach ( $DOMDocument->getElementsByTagName('*') as $node ) {

			if ( $node instanceof \DOMElement && ! in_array( $node->tagName, array( 'figure' ) ) ) {
				$node->removeAttribute('style');
				$node->removeAttribute('class');
				$node->removeAttribute('id');
			}

		}

		return $DOMDocument;
	}

	/**
	 * Ensures all text is wrapped in p tags.
	 *
	 * Wraps all text in <p> elements as specified by the Facebook spec.
	 *
	 * @since 0.0.1
	 *
	 * @access public
	 * @param  DOMDocument $DOMDocument
	 * @return DOMDocument
	 */
	public function wrap_text( DOMDocument $DOMDocument ) {
		$body = $DOMDocument->getElementsByTagName( 'body' )->item( 0 );
		$p_template_base = $DOMDocument->createElement('p');

		foreach ( $body->childNodes as $node ) {
			if ( '#text' == $node->nodeName && '' != trim( $node->nodeValue ) ) {
				$p_template = clone $p_template_base;
				$node->parentNode->replaceChild( $p_template, $node );
				$p_template->appendChild( $node );
			}
		}

		return $DOMDocument;
	}

	/**
	 * Remove all empty elements from the content.
	 *
	 * @since 0.0.1
	 *
	 * @access public
	 * @param  DOMDocument $DOMDocument
	 * @return object
	 */
	public function remove_empty_elements( DOMDocument $DOMDocument ) {

		foreach ( $DOMDocument->getElementsByTagName('*') as $node ) {

			if ( ! in_array( $node->tagName, array( 'img', 'figure', 'iframe', 'script' ) ) && ! $node->hasChildNodes() && '' == $node->nodeValue ) {
				$node->parentNode->removeChild( $node );
			}

		}

		return $DOMDocument;
	}

	/**
	 * Remove pre tags from shortcodes.
	 *
	 * Replace any embed wraps that may exist but don't actually need wrapping.
	 * The pre tag isn't an authorised FB IA element so it can go as well.
	 *
	 * @since 0.0.1
	 *
	 * @access public
	 * @param  string $content
	 * @return string
	 */
	public function unwrap_shortcodes( $content ) {
		// Remove the oembed filter wrap
		remove_filter( 'embed_oembed_html', array( $this, 'wrap_oembeds' ) );

		return str_ireplace( array( '<pre data-embed-wrap="true">', '</pre>' ), '', $content );
	}

}
