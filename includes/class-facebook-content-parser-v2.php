<?php
/**
 * Facebook Content parsing class Version 2.
 *
 * @since 1.3.2
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This handles the formatting of the post content for Facebook
 * Facebook is very strict/fussy about how it has to be formatted
 * so this can get quite involved.
 *
 * This is the second iteration of the content parser. Where the first one
 * targeted speicifc elements and transformed them in place, this one loops
 * over every element and applies a callback to it.
 *
 * @since  1.3.2
 */
// @codingStandardsIgnoreLine
class WPNA_Facebook_Content_Parser {

	/**
	 * Holds all the images found in the current post.
	 *
	 * @access public
	 * @var array
	 */
	public $images_in_post = array();

	/**
	 * Any <figure> elements with one of these classes isn't parsed.
	 *
	 * @access public
	 * @var array
	 */
	public $figure_classes_to_placeholder = array(
		'op-ad',
		'op-interactive',
		'op-tracker',
		'op-map',
		'op-slideshow',
	);

	/**
	 * Constructor.
	 *
	 * Triggers the hooks method straight away.
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
	 * @access public
	 * @return void
	 */
	public function hooks() {
		add_action( 'the_post', array( $this, 'post_hooks' ), 10, 1 );
	}

	/**
	 * Actions & hooks that are only fired when a post object is being
	 * setup for Instant Article conversion.
	 *
	 * @link https://developers.facebook.com/docs/instant-articles/reference
	 *
	 * @access public
	 * @return void
	 */
	public function post_hooks() {
		add_action( 'wpna_facebook_article_pre_the_content_transform', array( $this, 'add_instant_article_content_filters' ), 10 );
		add_action( 'wpna_facebook_article_pre_the_content_transform', array( $this, 'override_shortcode_callbacks' ), 10 );

		add_action( 'wpna_facebook_article_after_the_content_transform', array( $this, 'remove_shortcode_wrapper' ), 10 );
		add_action( 'wpna_facebook_article_after_the_content_transform', array( $this, 'remove_oembed_wrapper' ), 10 );
	}

	/**
	 * Apply content filters. These are the filters that do the actual
	 * DOM manipulation.
	 *
	 * Hook into the content in various places and apply the transformers.
	 * Tries to manipulate it to make in valid for Facebook Instant Articles.
	 *
	 * @access public
	 * @return void
	 */
	public function add_instant_article_content_filters() {
		add_filter( 'embed_oembed_html', array( $this, 'oembed_default' ), 10, 4 );
		add_filter( 'http_api_curl',     array( $this, 'set_follow_redirects' ), 10, 3 );

		// Quick and easy find and replace.
		add_filter( 'wpna_facebook_article_pre_the_content_filter', array( $this, 'remove_more_link' ), 10, 1 );

		// Strip out HTML comments.
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'remove_html_comments' ), 10, 1 );

		// Figure tags are used to wrap lots of different elements in IA. Let's placeholder them first.
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'parse_figure_tags' ), 10, 1 );

		// Looks over every element in the content and applies filters..
		add_filter( 'wpna_facebook_article_content_transform', array( $this, 'parse' ), 15, 1 );

		// Remove all these elements & their content. They're not allowed in IA.
		add_filter( 'wpna_facebook_article_content_transform_node_form',       array( $this, 'remove_element' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_map',        array( $this, 'remove_element' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_area',       array( $this, 'remove_element' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_math',       array( $this, 'remove_element' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_hr',         array( $this, 'remove_element' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_fieldset',   array( $this, 'remove_element' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_legend',     array( $this, 'remove_element' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_hgroup',     array( $this, 'remove_element' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_head',       array( $this, 'remove_element' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_header',     array( $this, 'remove_element' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_footer',     array( $this, 'remove_element' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_nav',        array( $this, 'remove_element' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_menu',       array( $this, 'remove_element' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_menuitem',   array( $this, 'remove_element' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_script',     array( $this, 'remove_element' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_style',      array( $this, 'remove_element' ), 10, 2 );

		// Remove these elements but keep their content.
		add_filter( 'wpna_facebook_article_content_transform_node_div',        array( $this, 'remove_element_keep_content' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_section',    array( $this, 'remove_element_keep_content' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_article',    array( $this, 'remove_element_keep_content' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_details',    array( $this, 'remove_element_keep_content' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_summary',    array( $this, 'remove_element_keep_content' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_span',       array( $this, 'remove_element_keep_content' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_small',      array( $this, 'remove_element_keep_content' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_ins',        array( $this, 'remove_element_keep_content' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_figure',     array( $this, 'remove_element_keep_content' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_mark',       array( $this, 'remove_element_keep_content' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_center',     array( $this, 'remove_element_keep_content' ), 10, 2 );

		// Add line breaks in after <p> tag content to ensure it's no covnerted to <br /> later.
		add_filter( 'wpna_facebook_article_content_transform_node_p',          array( $this, 'add_line_break' ), 9, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_p',          array( $this, 'remove_element_keep_content' ), 10, 2 );

		// Transform headings. Only <h1> & <h2> allowed.
		add_filter( 'wpna_facebook_article_content_transform_node_h1',         array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h1',         array( $this, 'unwrap_image' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h1',         array( $this, 'transform_heading' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h2',         array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h2',         array( $this, 'unwrap_image' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h2',         array( $this, 'transform_heading' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h3',         array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h3',         array( $this, 'unwrap_image' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h3',         array( $this, 'transform_heading' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h4',         array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h4',         array( $this, 'unwrap_image' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h4',         array( $this, 'transform_heading' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h5',         array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h5',         array( $this, 'unwrap_image' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h5',         array( $this, 'transform_heading' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h6',         array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h6',         array( $this, 'unwrap_image' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_h6',         array( $this, 'transform_heading' ), 10, 2 );

		add_filter( 'wpna_facebook_article_content_transform_node_table',      array( $this, 'transform_table' ), 10, 2 );

		add_filter( 'wpna_facebook_article_content_transform_node_a',          array( $this, 'remove_local_hyperlink' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_a',          array( $this, 'unwrap_image' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_a',          array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_a',          array( $this, 'transform_hyperlink' ), 10, 2 );

		add_filter( 'wpna_facebook_article_content_transform_node_ul',         array( $this, 'transform_list' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_ol',         array( $this, 'transform_list' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_dl',         array( $this, 'transform_description' ), 10, 2 );

		add_filter( 'wpna_facebook_article_content_transform_node_code',       array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_code',       array( $this, 'transform_to_italics' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_em',         array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_em',         array( $this, 'transform_to_italics' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_samp',       array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_samp',       array( $this, 'transform_to_italics' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_var',        array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_var',        array( $this, 'transform_to_italics' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_del',        array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_del',        array( $this, 'transform_to_italics' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_s',          array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_s',          array( $this, 'transform_to_italics' ), 10, 2 );

		add_filter( 'wpna_facebook_article_content_transform_node_strong',     array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_strong',     array( $this, 'unwrap_image' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_b',          array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_b',          array( $this, 'unwrap_image' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_i',          array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_i',          array( $this, 'unwrap_image' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_strike',     array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_strike',     array( $this, 'unwrap_image' ), 10, 2 );

		add_filter( 'wpna_facebook_article_content_transform_node_address',    array( $this, 'transform_address' ), 10, 2 );

		// Images can't be the same as the featured image.
		// Have to be as large as possible.
		// Have to exist.
		// Can't be repeated in the content.
		add_filter( 'wpna_facebook_article_content_transform_node_img',        array( $this, 'check_not_featured_image' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_img',        array( $this, 'get_large_image' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_img',        array( $this, 'check_image_exists' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_img',        array( $this, 'check_image_unique' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_img',        array( $this, 'transform_image' ), 10, 2 );

		add_filter( 'wpna_facebook_article_content_transform_node_blockquote', array( $this, 'check_not_empty' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_blockquote', array( $this, 'transform_blockquote' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_iframe',     array( $this, 'transform_iframe' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_pre',        array( $this, 'transform_pre' ), 10, 2 );

		add_filter( 'wpna_facebook_article_content_transform_node_aside',      array( $this, 'strip_attributes' ), 10, 2 );
		add_filter( 'wpna_facebook_article_content_transform_node_cite',       array( $this, 'strip_attributes' ), 10, 2 );

		add_filter( 'wpna_facebook_article_content_after_transform', 'wpautop', 10, 1 );
		add_filter( 'wpna_facebook_article_content_after_transform', array( $this, 'remove_empty_paragraphs' ), 11, 1 );
		add_filter( 'wpna_facebook_article_content_after_transform', array( $this, 'restore_placeholders' ), 12, 1 );

	}

	/**
	 * Adds the CURLOPT_FOLLOWLOCATION argument to the CURL lib as it's
	 * set to false by default. This is used when checking images exist.
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
	 * Hijacks the global array of shortcodes and allows a custom callbacks
	 * can be applied for Instant Articles so valid output can produced.
	 *
	 * This is used heavily to add compatibility with other plugins.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function override_shortcode_callbacks() {
		global $shortcode_tags, $_shortcode_tags;

		// Let's make a back-up of the shortcodes.
		$_shortcode_tags = $shortcode_tags;

		// Add any shortcode tags that we shouldn't touch here.
		$disabled_tags = array();

		/**
		 * Add a filter allowing alteration of the $disabled_tags array.
		 *
		 * @since 1.0.0
		 * @param array  $disabled_tags
		 * @param null Since 1.3.2
		 */
		$disabled_tags = apply_filters( 'wpna_facebook_article_setup_wrap_shortcodes_disabled_tags', $disabled_tags, null );

		// Add any shortcode tags that use a custom callback here.
		// View the /includes/compat folder for examples.
		$override_tags = array();

		/**
		 * Add a filter allowing alteration of the $override_tags array.
		 *
		 * @since 1.2.0
		 * @param array  $override_tags
		 * @param string $content
		 */
		$override_tags = apply_filters( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', $override_tags, null );

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
				$shortcode_tags[ $tag ] = array( $this, 'default_shortcode_callback' );
			}
		}
	}

	/**
	 * The default callback function used to format shortcoces if no custom
	 * one was found. Checks if it contains a <script> or <iframe> and formats
	 * correctly if so.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  array  $attr    Shortcode attributes.
	 * @param  string $content The content of the post.
	 * @param  string $tag     Tag used for the shortcode.
	 * @return string
	 */
	public function default_shortcode_callback( $attr, $content = null, $tag ) {
		global $_shortcode_tags;

		// Get the original shortcode output.
		$content = call_user_func( $_shortcode_tags[ $tag ], $attr, $content, $tag );

		// Only wrap shortcodes that return scripts or iframes.
		if ( false === strpos( $content, '</script>' ) && false === strpos( $content, '</iframe>' ) ) {
			return $content;
		}

		// Wrap it in an iframe if it isn't already.
		if ( '<iframe' !== substr( $content, 0, 7 ) ) {
			$content = '<iframe>' . $content . '</iframe>';
		}

		// Wrap it in a <figure> element.
		$content = '<figure class="op-interactive">' . $content . '</figure>';

		// Return a placeholder so it doesn't get parsed.
		return '<pre>' . wpna_content_parser_get_placeholder( $content ) . '</pre>';
	}

	/**
	 * Hooks into the default oEmbed output and correctly wraps it in
	 * <iframe> and <figure> elements as appropriate.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $embed Cached HTML for the embed.
	 * @param string $url The Oembed URl.
	 * @param array  $attr Atributes for the embed.
	 * @param int    $post_id ID for the post.
	 * @return string Placeholder to use in the content.
	 */
	public function oembed_default( $embed, $url, $attr, $post_id ) {
		// Wrap it in an iframe if it isn't already.
		if ( strpos( $embed, '<iframe' ) === false ) {
			$embed = '<iframe>' . $embed . '</iframe>';
		}

		// Wrap it in a <figure> element.
		$embed = '<figure class="op-interactive">' . $embed . '</figure>';

		// Return a placeholder so it doesn't get parsed.
		return '<pre>' . wpna_content_parser_get_placeholder( $embed ) . '</pre>';
	}

	/**
	 * Removes the annoying more <span> when it's in the content.
	 * e.g. <span id="more-{id}"></span>
	 *
	 * @access public
	 * @param  string $content Post content.
	 * @return string The post content.
	 */
	public function remove_more_link( $content ) {
		// Replace the <span> with an empty string.
		$content = str_replace( '<span id="more-' . get_the_ID() . '"></span>', '', $content );

		return $content;
	}

	/**
	 * Strips out all HTML comments from the post.
	 *
	 * Sometimes wpautop can mistake them for paragraphs.
	 *
	 * @access public
	 * @param  DOMDocument $dom_document DOM representation of the content.
	 * @return DOMDocument $dom_document DOM representation of the content.
	 */
	public function remove_html_comments( $dom_document ) {

		// Setup xpath.
		$xpath = new DOMXPath( $dom_document );

		// Grab all the comment elements.
		$nodes = $xpath->query( '//comment()' );

		$i = $nodes->length - 1;

		// Using a regressive loop. When Removing elements with a
		// foreach loop the index changing can confuse it.
		while ( $i > -1 ) {

			// Setup the node.
			$node = $nodes->item( $i );

			// Remove the comment.
			$node->parentNode->removeChild( $node );

			$i--;
		}

		return $dom_document;
	}

	/**
	 * Replaces certain <figure> tags with placeholders so they don't get parsed.
	 *
	 * Checks for <figure> elements with certain classes or that contain a video element.
	 *
	 * @access public
	 * @param  DOMDocument $dom_document DOM representation of the content.
	 * @return DOMDocument $dom_document DOM representation of the content.
	 */
	public function parse_figure_tags( $dom_document ) {
		// Grab figure elements.
		$nodes = $dom_document->getElementsByTagName( 'figure' );

		$i = $nodes->length - 1;

		// Using a regressive loop. When Removing elements with a
		// foreach loop the index changing can confuse it.
		while ( $i > -1 ) {

			// Setup the element.
			$node = $nodes->item( $i );

			$figure_classes = $node->getAttribute( 'class' );
			$figure_classes = explode( ' ', $figure_classes );

			// If it matches any of the approved IA classes.
			// If it contains a video element.
			if ( array_intersect( $this->figure_classes_to_placeholder, $figure_classes ) ||
				( $node->hasChildNodes() && $node->getElementsByTagName( 'video' )->length > 0 ) ) {
				$placeholder = wpna_content_parser_get_placeholder_node( $node );

				// Replace the element with the placeholder element.
				$node->parentNode->replaceChild( $placeholder, $node );
			}

			$i--;
		}

		return $dom_document;
	}

	/**
	 * Iterates over every element in the content and applies filters.
	 *
	 * This is the meat of the parser. The majority of our rules use the filters
	 * applied here to correctly convert elements.
	 *
	 * @access public
	 * @param  DOMDocument $dom_document DOM representation of the content.
	 * @return DOMDocument $dom_document DOM representation of the content.
	 */
	public function parse( $dom_document ) {
		// Grab all elements.
		$nodes = $dom_document->getElementsByTagName( '*' );

		$i = $nodes->length - 1;

		// Using a regressive loop. When Removing elements with a
		// foreach loop the index changing can confuse it.
		while ( $i > -1 ) {

			// Setup the element.
			$node = $nodes->item( $i );

			/**
			 * Filter all nodes of the document before they're returned.
			 * Technically, $nodes are passed by reference.
			 *
			 * @var DOMElement The current node being processed.
			 * @var DOMDocument DOM representation of the content.
			 */
			$node = apply_filters( 'wpna_facebook_article_content_transform_node', $node, $dom_document );

			/**
			 * Filter a specific node of the dom before it's returned.
			 * This is the filter we mostly use to apply transformations.
			 * Technically, $nodes are passed by reference.
			 *
			 * @var DOMElement The current node being processed.
			 * @var DOMDocument DOM representation of the content.
			 */
			$node = apply_filters( 'wpna_facebook_article_content_transform_node_' . $node->nodeName, $node, $dom_document );

			$i--;
		}

		return $dom_document;
	}

	/**
	 * Inserts a new line break at the end of the current node.
	 *
	 * This is used before <p> tags are removed to ensure wpautop adds them
	 * back correctly.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function add_line_break( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		$data = $dom_document->createTextNode( PHP_EOL );
		$node->appendChild( $data );

		return $node;
	}

	/**
	 * Completely removes an element and all its content.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function remove_element( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		// Remove the element completely.
		$node->parentNode->removeChild( $node );

		return false;
	}

	/**
	 * Removes and element but keeps its content.
	 *
	 * This is used for removeing elements around content we want to keep.
	 * E.g. Links that wrap round images.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function remove_element_keep_content( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		// Create a new document fragment.
		$fragment = $dom_document->createDocumentFragment();

		// Add all the elements children to the fragment.
		while ( $node->childNodes->length > 0 ) {
			$fragment->appendChild( $node->childNodes->item( 0 ) );
		}

		// Replace the element with the fragment.
		$node->parentNode->replaceChild( $fragment, $node );

		return false;
	}

	/**
	 * Remove the element if it is empty.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function check_not_empty( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		// Remove the element if it is empty.
		if ( 0 === $node->childNodes->length ) {
			$node->parentNode->removeChild( $node );

			return false;
		}

		return $node;
	}

	/**
	 * Replaces the current element with italics instead.
	 *
	 * Lots of elements aren't allowed in Instant Articles, this is used
	 * as an approximation in a lot of cases.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function transform_to_italics( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		// Construct a new element to ensure there are no unneeded attributes.
		$fragment = $dom_document->createElement( 'i' );

		// Add all the element's children to the fragment.
		while ( $node->childNodes->length > 0 ) {
			$fragment->appendChild( $node->childNodes->item( 0 ) );
		}

		// Replace old the element with the placeholder element.
		$node->parentNode->replaceChild( $fragment, $node );

		return $node;
	}

	/**
	 * Converts all headings to either h1 or h2.
	 *
	 * Only h1 & h2 elements are allowed. Creating a new element ensures
	 * attributes are stripped. Using a placeholder to make sure wpautop
	 * doesn't wrap it in <p> tags later on.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function transform_heading( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		// Construct a new element to ensure attributes are removed.
		// Only h1 & h2 are allowed. Default to h2.
		if ( 'h1' === $node->nodeName ) {
			$fragment = $dom_document->createElement( 'h1' );
		} else {
			$fragment = $dom_document->createElement( 'h2' );
		}

		// Add all the element's children to the fragment.
		while ( $node->childNodes->length > 0 ) {
			$fragment->appendChild( $node->childNodes->item( 0 ) );
		}

		$placeholder = wpna_content_parser_get_placeholder_node( $fragment );

		// Replace old the element with the placeholder element.
		$node->parentNode->replaceChild( $placeholder, $node );

		return $node;
	}

	/**
	 * Wraps tables in <figure><iframe> elements so they'll display.
	 *
	 * Tries to set the same width that is set on the table. Uses placeholders
	 * to ensure it doesn't get futher converted or wrapped in <p> tags later.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function transform_table( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		$width = $node->getAttribute( 'width' );

		if ( ! $width ) {
			$width = 550;
		}

		/**
		 * Use this filter the change the default stlying used for gist embeds.
		 *
		 * @var string The default styling for the tables.
		 * @var DOMElement $node The current node being transformed.
		 * @var DomDocument $dom_document Current DomDocument.
		 */
		$table_styling = apply_filters( 'wpna_facebook_article_content_table_styling', '', $node, $dom_document );

		// create all the templates we'll need.
		$figure_template = $dom_document->createElement( 'figure' );
		$figure_template->setAttribute( 'class', 'op-interactive' );

		$iframe_template = $dom_document->createElement( 'iframe' );
		$iframe_template->setAttribute( 'width', $width );
		$iframe_template->setAttribute( 'class', 'no-margin' );

		$style_template = $dom_document->createElement( 'style' );
		$style_template->setAttribute( 'type', 'text/css' );

		$style_template->nodeValue = $table_styling;

		// Replace the node with the empty template.
		$node->parentNode->replaceChild( $figure_template, $node );

		$iframe_template->appendChild( $style_template );

		$iframe_template->appendChild( $node );

		// Add the node template to the figure template.
		$figure_template->appendChild( $iframe_template );

		// Get a placeholder node for the figure template.
		$placeholder = wpna_content_parser_get_placeholder_node( $figure_template );

		// Replace the figure template with the new placeholder one.
		$figure_template->parentNode->replaceChild( $placeholder, $figure_template );

		return $node;
	}

	/**
	 * Checks if a link is a in-page hyperlink, removes it if so.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function remove_local_hyperlink( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		// Remove the link if:
		// - It doesn't have a href attribute
		// - It's a local hyperlink.
		if ( ! $node->hasAttribute( 'href' ) || 0 === strpos( $node->getAttribute( 'href' ), '#' ) ) {

			// Create a new document fragment.
			$fragment = $dom_document->createDocumentFragment();

			// Add all the element's children to the fragment.
			while ( $node->childNodes->length > 0 ) {
				$fragment->appendChild( $node->childNodes->item( 0 ) );
			}

			// Replace the element with the fragment.
			$node->parentNode->replaceChild( $fragment, $node );

			// Kill this node.
			return false;
		}

		return $node;
	}

	/**
	 * If a link is wrapped around an image, remove it.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function unwrap_image( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		$child_images = $node->getElementsByTagName( 'img' );

		// Remove the link if it's wrapped around an image.
		if ( $child_images->length > 0 ) {
			// Create a new document fragment.
			$fragment = $dom_document->createDocumentFragment();

			// Add all the element's children to the fragment.
			while ( $node->childNodes->length > 0 ) {
				$fragment->appendChild( $node->childNodes->item( 0 ) );
			}

			// Replace the element with the fragment.
			$node->parentNode->replaceChild( $fragment, $node );

			return false;
		}

		return $node;
	}

	/**
	 * Format link elements. Strips attributes.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function transform_hyperlink( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		$href = $node->getAttribute( 'href' );

		// Strip out any attributes.
		$node = $this->strip_attributes( $node, $dom_document );

		$node->setAttribute( 'href', esc_url( $href ) );

		return $node;
	}

	/**
	 * Transform <ul> elements.
	 *
	 * Strips attributes. Checks if it's a related articles list or not.
	 * Checks child elements for images and other media. If any are found
	 * then it splits the list at that point.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function transform_list( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		// Going to hold the new list(s).
		$document_fragment_template = $dom_document->createDocumentFragment();

		// The empty template for the new list.
		$new_list_node_template = $dom_document->createElement( $node->nodeName );

		// Get the class and title from the current list.
		$class = $node->getAttribute( 'class' );
		$title = $node->getAttribute( 'title' );

		// If it's a related articles block add it as a class.
		if ( 0 === strpos( $class, 'op-related-articles' ) ) {

			$new_list_node_template->setAttribute( 'class', 'op-related-articles' );

			// If it also had a title add it back.
			if ( $title ) {
				$new_list_node_template->setAttribute( 'title', esc_attr( $title ) );
			}
		}

		$new_list_fragment = clone $document_fragment_template;

		// Create our first list element.
		$new_list_node = clone $new_list_node_template;

		// Get all the li elements of the list. We're not interested
		// in any other elements that shouldn't be there.
		$list_elements = $node->getElementsByTagName( 'li' );

		// The order is important.
		$nodes_to_check_for = array( 'figure', 'img', 'pre' );

		$i = $list_elements->length - 1;

		// Using a regressive loop. When Removing elements with a
		// foreach loop the index changing can confuse it.
		while ( $i > -1 ) {

			// Setup the node.
			$child_node = $list_elements->item( 0 );

			// Add the node to the new list first.
			$new_list_node->appendChild( $child_node );

			foreach ( $nodes_to_check_for as $node_to_check_for ) {

				$child_child_nodes = $child_node->getElementsByTagName( $node_to_check_for );

				if ( $child_child_nodes->length > 0 ) {

					$fragment_nodes_to_move = clone $document_fragment_template;

					$j = $child_child_nodes->length - 1;

					// Using a regressive loop. When Removing elements with a
					// foreach loop the index changing can confuse it.
					while ( $j > -1 ) {
						// Setup the node.
						$child_child_node = $child_child_nodes->item( $j );

						$fragment_nodes_to_move->appendChild( $child_child_node );

						$j--;
					}

					// Add the new list node to the new fragment we're building.
					$new_list_fragment->appendChild( $new_list_node );

					// Add the images in after the new list node.
					$new_list_fragment->appendChild( $fragment_nodes_to_move );

					// Create a new list node and start building that.
					$new_list_node = clone $new_list_node_template;
				}
			}

			// If the image was the only content in the list element then
			// we can remove the now empty element from the fragment.
			if ( $child_node->childNodes->length <= 0 ) {
				$child_node->parentNode->removeChild( $child_node );
			} else {
				$sponsored = $child_node->getAttribute( 'data-sponsored' );

				$child_node_content = $dom_document->saveXML( $child_node );
				$child_node_content = strip_tags( $child_node_content, '<strong><b><i><em><a>' );

				$child_node_fragment = $dom_document->createDocumentFragment();
				$child_node_fragment->appendXML( $child_node_content );

				$new_list_element_node = $dom_document->createElement( 'li' );
				$new_list_element_node->appendChild( $child_node_fragment );

				// If it's a sponsored link add it back.
				if ( $sponsored ) {
					$new_list_element_node->setAttribute( 'data-sponsored', 'true' );
				}

				$child_node->parentNode->replaceChild( $new_list_element_node, $child_node );
			}

			$i--;
		}

		// Add the new list to the fragment if it's not empty.
		if ( '' !== $new_list_node->nodeValue && $new_list_node->childNodes->length > 0 ) {
			$new_list_fragment->appendChild( $new_list_node );
		}

		// Replace the list with the new, nicely formed one.
		$node->parentNode->replaceChild( $new_list_fragment, $node );

		return $node;
	}

	/**
	 * Transform <dd> elements.
	 *
	 * Converts them to <ul> tags with <strong> for emphasis.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function transform_description( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		// Just remove it if it's empty.
		if ( $node->childNodes->length <= 0 ) {
			$node->parentNode->removeChild( $node );
			return false;
		}

		// Create the new <ul> tag.
		$ul_node = $dom_document->createElement( 'ul' );

		// Grab all the <dd> elements.
		$dd_nodes = $node->getElementsByTagName( 'dd' );

		$i = $dd_nodes->length - 1;

		// Using a regressive loop. When Removing elements with a
		// foreach loop the index changing can confuse it.
		while ( $i > -1 ) {
			// Setup the node.
			$child_node = $dd_nodes->item( $i );
			// Create a new <li> tag.
			$li_node = $dom_document->createElement( 'li' );
			// Add in the value from the <dd> element.
			$li_node->nodeValue = $child_node->nodeValue;

			// Grab the previous sibling.
			$next = $child_node->previousSibling;

			// Cycle through all the previous sibligns looking for <dt>.
			while ( $next ) {
				// Search for the previous <dt> element.
				if ( 'dt' === $next->nodeName ) {
					// Check it's got some content (might've been an image that's been removed).
					if ( ! empty( $next->nodeValue ) ) {
						// Create a new <strong> tag.
						$new_strong_node = $dom_document->createElement( 'strong' );
						// Set the content to the <dt>.
						$new_strong_node->nodeValue = $next->nodeValue;
						// Add it in at the begining of the new <li> tag.
						$li_node->insertBefore( $new_strong_node, $li_node->firstChild );
					}
					break;
				}

				// If we hit another dd element we've gone too far.
				if ( 'dd' === $next->nodeName ) {
					break;
				}

				$next = $next->previousSibling;
			}

			// Add this new element to the new fragment.
			$ul_node->appendChild( $li_node );

			$i--;
		}

		// Create a new document fragment.
		$fragment = $dom_document->createDocumentFragment();
		// Add our new <ul> element to it.
		$fragment->appendChild( $ul_node );
		// Replace the entire <dd> list with the new element.
		$node->parentNode->replaceChild( $fragment, $node );

		return $node;
	}

	/**
	 * Transform <blockquote> elements.
	 *
	 * Strips attributes, sanitizes the contetns. Placeholders.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function transform_blockquote( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		// Construct a new element to ensure there are no unneeded attributes.
		$new_node = $dom_document->createElement( 'blockquote' );

		// Copy the contents of the original node but without html tags.
		$new_node->nodeValue = wp_kses_data( $node->nodeValue );

		$placeholder = wpna_content_parser_get_placeholder_node( $new_node );

		// Replace old the element with the placeholder element.
		$node->parentNode->replaceChild( $placeholder, $node );

		return $node;
	}

	/**
	 * Transform <iframe> elements.
	 *
	 * Wraps them in <figure> tags.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function transform_iframe( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		// Construct a new figure element.
		$figure = $dom_document->createElement( 'figure' );
		$figure->setAttribute( 'class', 'op-interactive' );

		// Replace the node with the empty figure element.
		$node->parentNode->replaceChild( $figure, $node );

		// Add the node in as the figure contents.
		$figure->appendChild( $node );

		// Get a placeholder for the figure.
		$placeholder = wpna_content_parser_get_placeholder_node( $figure );

		// Replace the new figure element with the placeholder element.
		$figure->parentNode->replaceChild( $placeholder, $figure );

		return false;
	}

	/**
	 * Transform <address> elements.
	 *
	 * Converts them to <p> tags.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function transform_address( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		// Construct a new p element.
		$p = $dom_document->createElement( 'p' );

		// Add all the element's children to the fragment.
		while ( $node->childNodes->length > 0 ) {
			$p->appendChild( $node->childNodes->item( 0 ) );
		}

		// Replace the element with the fragment.
		$node->parentNode->replaceChild( $p, $node );

		return $node;
	}

	/**
	 * Checks an image isn't also being used as the featured image.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function check_not_featured_image( $node, $dom_document ) {
		global $post;

		if ( ! $node ) {
			return false;
		}

		// Check media is shown for this post.
		$show_media = wpna_switch_to_boolean( wpna_get_post_option( $post->ID, 'fbia_show_media', 'on' ) );

		// Check it's not the featured image.
		// Setup the featured image regex if the post has one.
		if ( $show_media && has_post_thumbnail( $post->ID ) ) {
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
					$regex                   = sprintf( '/%s(?:-\d+[Xx]\d+){0,1}%s/', preg_quote( $featured_image_path, '/' ), preg_quote( $featured_image_path_ext, '/' ) );

					if ( preg_match( $regex, $node->getAttribute( 'src' ) ) ) {
						// Remove the element completely.
						$node->parentNode->removeChild( $node );

						return false;
					}
				}
			}
		}

		return $node;
	}

	/**
	 * Gets the largest image version available.
	 *
	 * Images should be large as possible. This can be slow so the result is cached.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function get_large_image( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		// Get the URL to the image.
		$image_source = $node->getAttribute( 'src' );

		// Construct a unique cache key.
		$cache_key = 'wpna_image_large_' . md5( $image_source );

		// Hit the cache first. Stops it being checked every time.
		if ( ! $image_src_large = wp_cache_get( $cache_key, 'wpna' ) ) {

			// The recommended img size is 2048x2048.
			// We ideally need to workout the image size and get the largest possible.
			// For WordPress embedded images, the image id can be in the class name,
			// which is a much cheaper lookup.
			if ( preg_match( '/wp-image-([\d]+)/', $node->getAttribute( 'class' ), $matches ) ) {
				$attachment_id = (int) $matches[1];
			} else {
				// This function is cached.
				$attachment_id = wpna_get_attachment_id_from_src( $image_source );
			}

			// If we managed to find the $attachment_id.
			if ( $attachment_id ) {
				// Try and get a larger version.
				$img_props = wp_get_attachment_image_src( $attachment_id, 'full' );

				if ( is_array( $img_props ) ) {
					// If it's a success update the $image_source.
					$possible_large_image = $img_props[0];

					// If we're reasonably confident that it's the same image.
					// (Some sites use a CDN, this is a sanity check).
					if ( strcmp( dirname( $possible_large_image ), dirname( $image_source ) ) === 0 ) {
						$image_src_large = $possible_large_image;
					}
				}
			} else {
				// Cache it anyway. Keep the speed up.
				$image_src_large = $image_source;
			}

			// Save it to the cache to stop this happening too often.
			wp_cache_set( $cache_key, $image_src_large, 'wpna', DAY_IN_SECONDS );

		}

		// Update the image src with the larger version.
		if ( $image_src_large ) {
			$node->setAttribute( 'src', $image_src_large );
		}

		return $node;
	}

	/**
	 * Check the image exists.
	 *
	 * This can be slow so the result is cached.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function check_image_exists( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		// Get the URL to the image.
		$image_source = $node->getAttribute( 'src' );

		// Construct a unique cache key.
		$cache_key = 'wpna_image_exists_' . md5( $image_source );

		// Try and get status from the cache.
		$image_status = wp_cache_get( $cache_key, 'wpna' );

		// Hit the cache first. Stops it being checked every time.
		if ( ! $image_status ) {

			// Assume it exists.
			$image_status = 'exists';

			// Check it exists & (optionally) is large enough.
			// This is obviously less than ideal as it can be slow.
			if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
				$response = vip_safe_wp_remote_get( $image_source );
			} else {
				// @codingStandardsIgnoreLine
				$response = wp_remote_get( $image_source, array( 'sslverify' => false, 'timeout' => 5 ) );
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			$headers       = wp_remote_retrieve_headers( $response );

			// The image doesn't exist, remove it.
			// If the image is smaller than 1024 bytes, remove it.
			// content-length value might not exist.
			if ( $response_code >= 400 || ( isset( $headers['content-length'] ) && absint( $headers['content-length'] ) < 1028 ) ) {
				$image_status = 'not_exists';
			}

			// Cache the result.
			wp_cache_set( $cache_key, $image_status, 'wpna', DAY_IN_SECONDS );

		}

		// Remove the element completely if checks fail.
		if ( 'not_exists' === $image_status ) {
			$node->parentNode->removeChild( $node );

			return false;
		}

		return $node;
	}

	/**
	 * Check an image has only been used once.
	 *
	 * Checks against all the other images in the post.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function check_image_unique( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		$src = $node->getAttribute( 'src' );

		if ( empty( $src ) ) {
			$src = $node->getAttribute( 'data-src' );
		}

		if ( empty( $src ) ) {
			$src = $node->getAttribute( 'data-lazy-src' );
		}

		if ( $src ) {

			// Check if it's been used before in this post.
			if ( in_array( $src, $this->images_in_post, true ) ) {
				// Remove the element completely.
				$node->parentNode->removeChild( $node );

				return false;
			}

			// Add it to the array of images used.
			$this->images_in_post[] = $src;

		}

		return $node;
	}

	/**
	 * Transform image elements.
	 *
	 * Checks global and post specific styling options.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function transform_image( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		// Construct the figure wrapper.
		$figure = $dom_document->createElement( 'figure' );

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
			$figure->setAttribute( 'data-feedback', implode( ', ', $figure_attr ) );
		}

		// Construct a new element to ensure there are no unneeded attributes.
		$new_node = $dom_document->createElement( 'img' );

		// Try for the image src.
		$src = $node->getAttribute( 'src' );

		// If it doesn't exist try the data-src.
		if ( empty( $src ) ) {
			$src = $node->getAttribute( 'data-src' );
		}

		// If it doesn't exist try the data-lazy-src.
		if ( empty( $src ) ) {
			$src = $node->getAttribute( 'data-lazy-src' );
		}

		// Set the image src.
		$new_node->setAttribute( 'src', $src );

		// Add the img to the figure template.
		$figure->appendChild( $new_node );

		// Replace the element with the fragment.
		$node->parentNode->replaceChild( $figure, $node );

		return $node;
	}

	/**
	 * Strips attributes from the element.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function strip_attributes( $node, $dom_document ) {
		if ( ! $node ) {
			return false;
		}

		// Strip out any attributes.
		$attributes = $node->attributes;
		while ( $attributes->length ) {
			$node->removeAttribute( $attributes->item( 0 )->name );
		}

		return $node;
	}

	/**
	 * Transform <pre> elements.
	 *
	 * Wraps them in <figure><iframe> with some custom styling to prettify.
	 *
	 * @access public
	 * @param DOMElement $node DOMElement representation of the current node.
	 * @param DOMElement $dom_document DOM representation of the content.
	 * @return false|DOMDocument $dom_document DOM representation of the content.
	 */
	public function transform_pre( $node, $dom_document ) {
		global $_shortcode_content;

		if ( ! $node ) {
			return false;
		}

		if ( ! isset( $_shortcode_content[ $node->nodeValue ] ) ) {

			// Basic styling for the <pre> elements.
			$pre_tag_styling = 'pre { overflow-x: auto; width: 100%; padding: 1em; font-size: 1.1em; line-height: 1.6em; background: #f7f7f7; color: #333; }';

			/**
			 * Allow filtering of the <pre> element styling.
			 *
			 * @since 1.2.3
			 * @param string The default <pre> element styles.
			 */
			$pre_tag_styling = apply_filters( 'wpna_facebook_article_pre_block_styling', $pre_tag_styling );

			// Construct a new figure element.
			$figure = $dom_document->createElement( 'figure' );

			$iframe = $dom_document->createElement( 'iframe' );
			$iframe->setAttribute( 'width', '500' );
			$iframe->setAttribute( 'class', 'no-margin' );

			$style = $dom_document->createElement( 'style' );
			$style->setAttribute( 'type', 'text/css' );

			$style->nodeValue = $pre_tag_styling;

			$iframe->appendChild( $style );

			// Remove any unneeded spacing.
			$node->nodeValue = preg_replace( '/[ ]{2,}|[\t]/', ' ', trim( $node->nodeValue ) );

			$figure->appendChild( $iframe );

			// Replace the node with the empty figure element.
			$node->parentNode->replaceChild( $figure, $node );

			// Add the node in as the figure contents.
			$iframe->appendChild( $node );

			// Get a placeholder for the pre element.
			$placeholder = wpna_content_parser_get_placeholder_node( $figure );

			// Replace the new figure element with the placeholder element.
			$figure->parentNode->replaceChild( $placeholder, $figure );

		}

		return $node;
	}

	/**
	 * Replaces embeds.
	 *
	 * Shortcodes and embeds were removed while we format the article.
	 * This places them back.
	 *
	 * @since 1.3.2 Search for placeholders wrapped in <pre> tags.
	 *
	 * @access public
	 * @param  string $content The content of the post.
	 * @return string $content The content of the post.
	 */
	public function restore_placeholders( $content ) {
		global $_shortcode_content;

		// V2 embeds are wrapped in <pre> tags.
		foreach ( $_shortcode_content as $placeholder => $shortcode_content ) {
			$content = str_replace( '<pre>' . $placeholder . '</pre>', $shortcode_content, $content );
		}

		// V1 embeds aren't wrapped at all.
		$content = str_replace( array_keys( $_shortcode_content ), array_values( $_shortcode_content ), $content );

		return $content;
	}

	/**
	 * Strips our empty paragraph elements that may have been added.
	 *
	 * @access public
	 * @param  string $content The content of the post.
	 * @return string $content The content of the post.
	 */
	public function remove_empty_paragraphs( $content ) {
		return str_replace( '<p></p>', '', $content );
	}

	/**
	 * Remove the shortcode wrap filter.
	 *
	 * Cleaning up after ourselves. Restore the shortcode original functions.
	 *
	 * @since 1.0.0
	 * @todo Not overwrite the global array.
	 *
	 * @access public
	 * @return void
	 */
	public function remove_shortcode_wrapper() {
		global $shortcode_tags, $_shortcode_tags;

		// @codingStandardsIgnoreLine
		$shortcode_tags = $_shortcode_tags;
	}

	/**
	 * Remove the oembed wrap filter.
	 *
	 * Cleaning up after ourselves.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function remove_oembed_wrapper() {
		remove_filter( 'embed_oembed_html', array( $this, 'wrap_oembed' ) );
	}

}
