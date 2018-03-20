<?php
/**
 * Post class for Facebook Instant Articles.
 *
 * @since  1.0.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post class for Facebook Instant Articles.
 * A wrapper around the WP_Post class that provides access to all the fields
 * needed for instant articles as well as adding in filters.
 *
 * @since 1.0.0
 */
class WPNA_Facebook_Post {

	/**
	 * The post id we're currently working with.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var ints
	 */
	public $post_id;

	/**
	 * Constructor.
	 *
	 * Sets the $post_id variable.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param int $id ID of the post to parse.
	 * @return null
	 */
	public function __construct( $id ) {
		$this->post_id = $id;
	}

	/**
	 * Returns the post ID.
	 * Method name mirrors the WP one, ignore from PHPCS.
	 * @codingStandardsIgnoreStart
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return int
	 */
	public function get_the_ID() {
		// codingStandardsIgnoreEnd
		return $this->post_id;
	}

	/**
	 * Returns the style template to be used for the post.
	 *
	 * First checks the post meta to see if it's been overridden.
	 * If not it returns the global default.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_style() {

		// Checks for post options, then global options then default.
		$style = wpna_get_post_option( get_the_ID(), 'fbia_style', 'default' );

		/**
		 * Filter the article style.
		 *
		 * @since 1.0.0
		 * @param string $style The style to be used for the post.
		 */
		$style = apply_filters( 'wpna_facebook_post_get_style', $style );

		return $style;
	}

	/**
	 * Returns whether RTL should be used for the post.
	 *
	 * First checks the post meta to see if it's been overridden.
	 * If not it returns the global default.
	 *
	 * The Auto detect values uses the same value as the WordPress admin.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 * @return string
	 */
	public function is_rtl() {

		// Checks for post options, then global options then default.
		$rtl = wpna_get_post_option( get_the_ID(), 'fbia_rtl', 'off' );

		// If set to auto, use the WP detection function.
		if ( 'auto' === $rtl ) {
			$rtl = is_rtl();
		} else {
			$rtl = wpna_switch_to_boolean( $rtl );
		}

		/**
		 * Filter the article style.
		 *
		 * @since 1.3.0
		 * @param bool $rtl Whether this post should be RTL.
		 */
		$rtl = apply_filters( 'wpna_facebook_post_is_rtl', $rtl );

		return $rtl;
	}

	/**
	 * Returns the title of the post.
	 *
	 * First checks the post meta to see if it's been overridden.
	 * If not it returns the global default.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_the_title() {
		$title = get_the_title( $this->get_the_ID() );

		/**
		 * Filter the title.
		 *
		 * @since 1.0.0
		 * @param string $title The title to be used for the post.
		 */
		return apply_filters( 'wpna_facebook_post_get_the_title', $title );
	}

	/**
	 * Returns the permalink of the post.
	 *
	 * First checks the post meta to see if it's been overridden.
	 * If not it returns the global default.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_permalink() {
		$permalink = get_permalink( $this->get_the_ID() );

		/**
		 * Filter the permalink.
		 *
		 * @since 1.0.0
		 * @param string $permalink The permalink to be used for the post.
		 */
		return apply_filters( 'wpna_facebook_post_get_permalink', $permalink );
	}

	/**
	 * Returns the guid of the post
	 *
	 * First checks the post meta to see if it's been overridden.
	 * If not it returns the global default.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_the_guid() {
		$guid = get_the_guid( $this->get_the_ID() );

		/**
		 * Filter the guid.
		 *
		 * @since 1.0.0
		 * @param string $guid The guid to be used for the post.
		 */
		return apply_filters( 'wpna_facebook_post_get_the_guid', $guid );
	}

	/**
	 * Returns the URL to the post's featured image.
	 *
	 * Checks to see if the post a featured image, if so returns an associated
	 * array of the image properties.
	 * e.g. array(
	 *      'url'             => string,
	 *	    'width'           => string,
	 *	    'height'          => string,
	 *	    'is_intermediate' => bool,
	 *	    'caption'         => string,
	 *     );
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array Array of image properties.
	 */
	public function get_the_featured_image() {
		$image = null;

		if ( has_post_thumbnail( $this->get_the_ID() ) ) {

			$attachment_id = get_post_thumbnail_id( $this->get_the_ID() );

			// The recommended img size is 2048x2048.
			// Try and get the closest size to that.
			$img_props = wp_get_attachment_image_src( $attachment_id, array( 2048, 2048 ) );

			if ( is_array( $img_props ) ) {

				// Create a handy array of info.
				$image = array(
					'url'             => $img_props[0],
					'width'           => $img_props[1],
					'height'          => $img_props[2],
					'is_intermediate' => $img_props[3],
					'caption'         => null,
				);

				// Add the caption in if there is one.
				if ( $attachment = get_post( $attachment_id ) && ! empty( $attachment->excerpt ) ) {
					$image['caption'] = $attachment->excerpt;
				}
			}
		}

		/**
		 * Filter the featured image url.
		 * For example, set a default for articles that don't have one.
		 *
		 * @since 1.0.0
		 * @var array|null $image The image to use in the featured spot.
		 */
		return apply_filters( 'wpna_facebook_post_get_the_featured_image', $image );
	}

	/**
	 * Returns the kicker for the article.
	 *
	 * Defaults to the article's caregories
	 *
	 * @since 1.0.0
	 *
	 * @return string $kicker
	 */
	public function get_the_kicker() {
		$categories = get_the_category( $this->get_the_ID() );

		$kicker = implode( ', ', wp_list_pluck( $categories, 'cat_name' ) );

		/**
		 * Filter the kicker for an article.
		 *
		 * If empty or false then kicker is hidden.
		 *
		 * @since 1.0.0
		 * @var string $kicker The article kicker.
		 */
		$kicker = apply_filters( 'wpna_facebook_post_get_the_kicker', $kicker );

		return $kicker;
	}

	/**
	 * Returns the excerpt of the post.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_the_excerpt() {
		$post = get_post( $this->get_the_ID() );

		// Make sure no “read more” link is added.
		add_filter( 'excerpt_more', '__return_empty_string', 999 );

		/**
		 * Apply the default WP Filters for the post excerpt.
		 *
		 * @since 1.0.0
		 * @param string $post_excerpt The post excerpt.
		 */
		$excerpt = apply_filters( 'get_the_excerpt', $post->post_excerpt );

		/**
		 * Apply custom wpna filters for the post excerpt.
		 *
		 * @since 1.0.0
		 * @param string $post_excerpt The post excerpt.
		 */
		$excerpt = apply_filters( 'wpna_facebook_post_get_the_excerpt', $excerpt );

		return $excerpt;
	}

	/**
	 * Returns the publish data of the post in ISO 8601 format.
	 *
	 * @access public
	 *
	 * @access public
	 * @return string
	 */
	public function get_publish_date_iso() {
		$publish_date = get_post_time( 'c', true, $this->get_the_ID(), false );

		/**
		 * Filter the publish date ISO format.
		 *
		 * @since 1.0.0
		 * @param string $publish_date The publish date of the article in ISO 8601 format.
		 */
		$publish_date = apply_filters( 'wpna_facebook_post_get_publish_date_iso', $publish_date );

		return $publish_date;
	}

	/**
	 * Returns the publish data of the post in pretty format.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_publish_date() {
		$publish_date = get_post_time( get_option( 'date_format' ), true, $this->get_the_ID(), false );

		/**
		 * Filter the publish date.
		 *
		 * @since 1.0.0
		 * @param string $publish_date The publish date of the article in display format.
		 */
		$publish_date = apply_filters( 'wpna_facebook_post_get_publish_date', $publish_date );

		 return $publish_date;
	}

	/**
	 * Returns the modified date of the post in ISO 8601 format.
	 *
	 * @access public
	 * @return string
	 */
	public function get_modified_date_iso() {
		$modified_date = get_post_modified_time( 'c', true, $this->get_the_ID(), false );

		/**
		 * Filter the modified date ISO format.
		 *
		 * @since 1.0.0
		 * @param string $modified_date The modified date of the article in iso format.
		 */
		$modified_date = apply_filters( 'wpna_facebook_post_get_modified_date_iso', $modified_date );

		return $modified_date;
	}

	/**
	 * Returns the modified date of the post in pretty format.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_modified_date() {
		$modified_date = get_post_modified_time( get_option( 'date_format' ), true, $this->get_the_ID(), false );

		/**
		 * Filter the modified date.
		 *
		 * @since 1.0.0
		 * @param string $modified_date The modified date of the article in display format.
		 */
		$modified_date = apply_filters( 'wpna_facebook_post_get_modified_date', $modified_date );

		return $modified_date;
	}

	/**
	 * Returns the authors of the post.
	 *
	 * Coauthors plus is hooked in in the transformer class.
	 * Should return an array of WP_User objects.
	 *
	 * @access public
	 * @return array
	 */
	public function get_authors() {
		$authors = array();

		// Sometimes authors don't exist but are still assigned to posts.
		if ( $author = get_userdata( get_the_author_meta( 'ID' ) ) ) {
			$authors[] = $author;
		}

		/**
		 * Apply a filter to the post authors.
		 *
		 * We check for the co-author plus plugin in the transformer class.
		 *
		 * @since 1.0.0
		 * @param array $authors Array of author data.
		 */
		$authors = apply_filters( 'wpna_facebook_post_authors', $authors );

		return $authors;
	}

	/**
	 * Returns the content of the post.
	 *
	 * First ensures all the correct WordPress default filters are applied.
	 * Then uses DOMDocument to provide better parsing hooks, (which the transformer
	 * class makes extensive use of). Tries to cache the result in transients.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_the_content() {

		// Pull from cache if it's allowed.
		if ( wpna_get_option( 'fbia_article_caching' ) && $content = $this->get_cache( $this->get_the_ID() ) ) {
			return $content;
		}

		// Get the global $more value.
		global $more, $multipage, $pages;

		// Save the current $more value.
		$default_more = $more;

		// Ensure it's set to 1. Shows the whole post.
		$more = 1;

		// Respect the <!--noteaser--> tag.
		$content = get_the_content( null, false );

		// Reset the $more value just incase.
		$more = $default_more;

		// If they've used the <!--nextpage--> tag then
		// make sure we get all the content.
		if ( $multipage && count( $pages ) > 1 ) {
			$content = implode( $pages, PHP_EOL );
		}

		/**
		 * Fired before the content is converted.
		 *
		 * @since 2.0.0
		 */
		do_action( 'wpna_facebook_article_pre_the_content_transform', $content );

		/**
		 * Filter the post content before WP has its fun.
		 *
		 * The transformer class uses this to wrap shortcodes before they're parsed.
		 *
		 * @since 1.0.0
		 * @param string $content The post content.
		 */
		$content = apply_filters( 'wpna_facebook_article_pre_the_content_filter', $content );

		// We don't need these filters.
		if ( has_filter( 'the_content', 'prepend_attachment' ) ) {
			remove_filter( 'the_content', 'prepend_attachment' );
		}

		// Or these ones.
		if ( has_filter( 'the_content', 'wp_make_content_images_responsive' ) ) {
			remove_filter( 'the_content', 'wp_make_content_images_responsive' );
		}

		// We need these though.
		if ( ! has_filter( 'the_content', 'wptexturize' ) ) {
			add_filter( 'the_content', 'wptexturize', 10, 1 );
		}

		if ( ! has_filter( 'the_content', 'convert_smilies' ) ) {
			add_filter( 'the_content', 'convert_smilies', 10, 1 );
		}

		if ( ! has_filter( 'the_content', 'wpautop' ) ) {
			add_filter( 'the_content', 'wpautop', 10, 1 );
		}

		if ( ! has_filter( 'the_content', 'shortcode_unautop' ) ) {
			add_filter( 'the_content', 'shortcode_unautop', 10, 1 );
		}

		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );

		/**
		 * Filter the post content straight after WP has had its fun.
		 *
		 * The transformer classes uses this to clean up unnecessary elements.
		 *
		 * @since 1.0.0.
		 * @param string $content The post content.
		 */
		$content = apply_filters( 'wpna_facebook_article_after_the_content_filter', $content );

		// We'd like to use DOMDocument at this point.
		if ( class_exists( 'DOMDocument' ) ) {

			$libxml_previous_state = libxml_use_internal_errors( true );
			$dom_document = new DOMDocument( '1.0' );

			// Remove any existing tags that may or may not be there.
			$content = str_ireplace( array( '<body>', '</body>', '<html>', '</html>' ), '', $content );

			// Make sure body tags are there.
			$content = '<body>' . $content . '</body>';

			// Convert encoding.
			if ( function_exists( 'mb_convert_encoding' ) ) {
				$content = mb_convert_encoding( $content, 'HTML-ENTITIES', get_option( 'blog_charset' ) );
			} else {
				// If mb_convert_encoding() isn't installed then try and detect
				// encoding automatically via meta tag in the head.
				$content = '<head>
					<meta http-equiv="Content-Type" content="text/html; charset=' . get_option( 'blog_charset' ) . '">
					</head>' . $content;
			}

			$dom_document->loadHTML( '<!doctype html public "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml">' . $content . '</html>' );
			libxml_clear_errors();
			libxml_use_internal_errors( $libxml_previous_state );

			/**
			 * This is where you should apply all custom filters to the content.
			 *
			 * The transformer class uses this filter extensively to parse the content
			 * and make it instant article compatible.
			 *
			 * @since 1.0.0
			 * @param DOMDocument $dom_document
			 */
			$dom_document = apply_filters( 'wpna_facebook_article_content_transform', $dom_document );

			// Get just the body content.
			$body = $dom_document->getElementsByTagName( 'body' )->item( 0 );

			// Save the content.
			$content = $dom_document->saveXML( $body );

			// Strip out the cdata added by saveXML().
			$content = str_replace( array( '<![CDATA[', ']]>' ), '', $content );

			// Remove <body> from start and end of string.
			$content = str_replace( array( '<body>', '</body>' ), '', $content );
		}

		/**
		 * Run a final string filter just incase.
		 *
		 * The transformer class uses this to clean up after itself.
		 *
		 * @since 1.0.0
		 * @param string $content The post content.
		 */
		$content = apply_filters( 'wpna_facebook_article_content_after_transform', $content );

		$this->set_cache( $this->post_id, $content );

		/**
		 * Fired after the content is converted.
		 *
		 * @since 2.0.0
		 */
		do_action( 'wpna_facebook_article_after_the_content_transform', $content );

		return $content;
	}

	/**
	 * Get the credits for each article.
	 *
	 * First checks the post meta to see if it's been overridden.
	 * If not it returns the global default.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_credits() {

		// Checks for post options, then global options then default.
		$original_credits = wpna_get_post_option( get_the_ID(), 'fbia_credits', '' );

		// Parse the string for any date placeholders.
		$credits = wpna_replace_date_placeholders( $original_credits );

		/**
		 * Filter the credits for each article.
		 *
		 * @since 1.0.0
		 * @var string $credits The article credits.
		 * @var string $original_credits The orignal, unparsed, article credits.
		 */
		$credits = apply_filters( 'wpna_facebook_article_content_credits', $credits, $original_credits );

		return $credits;
	}

	/**
	 * Get the copyright for the article
	 *
	 * First checks the post meta to see if it's been overridden.
	 * If not it returns the global default.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_copyright() {

		// Checks for post options, then global options then default.
		$original_copyright = wpna_get_post_option( get_the_ID(), 'fbia_copyright', '' );

		// Parse the string for any date placeholders.
		$copyright = wpna_replace_date_placeholders( $original_copyright );

		/**
		 * Filter the copyright for each article.
		 *
		 * @since 1.0.0
		 * @var string $copyright The article copyright.
		 * @var string $original_copyright The orignal, unparsed, article credits.
		 */
		$copyright = apply_filters( 'wpna_facebook_article_content_copyright', $copyright, $original_copyright );

		return $copyright;
	}

	/**
	 * Check to see if any manual related articles have been set.
	 *
	 * Facebook allows up to four related articles to be specified at the
	 * bottom of every article. If four aren't specified Facebook will auto fill them.
	 *
	 * @link https://developers.facebook.com/docs/instant-articles/reference/related-articles
	 *
	 * @since 1.2.5
	 *
	 * @access public
	 * @return array URLs to related articles.
	 */
	public function get_manual_related_articles() {

		$manual_related_articles = array();

		// Construct a quick loop to grab them all.
		foreach ( array( 'one', 'two', 'three', 'four' ) as $number ) {

			// Try and get the related article URL.
			$related_article_url = get_post_meta( get_the_ID(), sprintf( '_wpna_fbia_related_article_%s', $number ), true );

			// If it exists, add it to the array.
			if ( ! empty( $related_article_url ) ) {
				// Check whether it's sponsored or not. Can return 'on' or null.
				$sponsored = get_post_meta( get_the_ID(), sprintf( '_wpna_fbia_related_article_%s_sponsored', $number ), true );
				// Make an object and add it to the array.
				$manual_related_articles[] = (object) array(
					'url'       => $related_article_url,
					'sponsored' => wpna_switch_to_boolean( $sponsored ),
				);
			}
		}

		/**
		 * Filter the array of manual related articles before returning them.
		 *
		 * @since 1.2.5
		 * @var array $manual_related_articles Array of manual related articles.
		 */
		$manual_related_articles = apply_filters( 'wpna_facebook_article_manual_related_articles', $manual_related_articles );

		return $manual_related_articles;
	}

	/**
	 * Sets up the query to get the related articles.
	 *
	 * Facebook allows up to four related articles to be specified at the
	 * bottom of every article.
	 *
	 * @link https://developers.facebook.com/docs/instant-articles/reference/related-articles
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return object WP_Query
	 */
	public function get_related_articles( $args = null ) {

		// Get the categories of the post.
		// get_the_category() is cached, wp_get_the_category() is not.
		$post_categories = get_the_category( $this->get_the_ID() );
		$post_categories_ids = wp_list_pluck( $post_categories, 'term_id' );

		// Nothing fancy here. Just get the four latest posts.
		// that are in any of the same categories.
		$default_query_args = array(
			'category__in'           => $post_categories_ids,
			'post__not_in'           => array( $this->get_the_ID() ),
			'posts_per_page'         => 4, // FB uses 4 related articles.
			'ignore_sticky_posts'    => true, // Turn off sticky posts.
			'order'                  => 'DESC',
			'orderby'                => 'date',
			'no_found_rows'          => true, // Turn off pagination, we don't need it.
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'cache_results'          => false,
			'post_type'              => wpna_allowed_post_types(),
			'post_status'            => 'publish',
		);

		// Merge the passed arguments with the defaults.
		$query_args = wp_parse_args( $args, $default_query_args );

		/**
		 * Filter the query arguments used to generate the related posts links.
		 *
		 * @since 1.0.0
		 * @var array $query_args Associated array of query arguments.
		 */
		$query_args = apply_filters( 'wpna_facebook_article_related_articles_query', $query_args );

		return new WP_Query( $query_args );
	}

	/**
	 * Get the analytics code for the article.
	 *
	 * First checks the post meta to see if it's been overridden.
	 * If not it returns the global default.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_analytics() {

		// This will hold all the analytics code.
		$analytics_code = '';

		/**
		 * Filter the analytics code for the article.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		$analytics_code = apply_filters( 'wpna_facebook_post_analytics', $analytics_code );

		if ( ! empty( $analytics_code ) ) {
			// Ensure it's wrapped in figure tags.
			$analytics_code = sprintf( '<figure class="op-tracker"><iframe>%s</iframe></figure>', $analytics_code );
		}

		return $analytics_code;
	}

	/**
	 * Get the ad code for the article.
	 *
	 * First checks the post meta to see if it's been overridden.
	 * If not it returns the global default.
	 *
	 * Depending on the ad type set will either format be custom ad code
	 * or a placement id that will need formatting.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string
	 */
	public function get_ads() {

		if ( 'audience_network' === wpna_get_post_option( get_the_ID(), 'fbia_ad_code_type' ) ) {
			// Get the palcement ID.
			$placement_id = wpna_get_post_option( get_the_ID(), 'fbia_ad_code_placement_id' );

			// Grab the HTML for the ad placement.
			// The $placement_id var is available inside the template.
			ob_start();

			include wpna_locate_template( 'wpna-audience-network-banner.php' );

			$ad_code = ob_get_clean();

		} else {
			// Get the custom ad code.
			$ad_code = wpna_get_post_option( get_the_ID(), 'fbia_ad_code' );
		}

		/**
		 * Filter the ad code for the article.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		$ad_code = apply_filters( 'wpna_facebook_post_ads', $ad_code );

		return $ad_code;
	}

	/**
	 * Returns the cached contents of a post.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  int $id The ID of the post to retrieve the cache for.
	 * @return string
	 */
	public function get_cache( $id ) {
		return get_transient( 'wpna_facebook_post_content_' . $id );
	}

	/**
	 * Saves a post's content to transients.
	 *
	 * The post content is expensive to generate, ideally we want to generate
	 * it as few tiems as possible.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param int    $id      The ID of the post to set the cache for.
	 * @param string $content The content to save in cache.
	 * @return boolean
	 */
	public function set_cache( $id, $content ) {
		/**
		 * The length of time the transient is valid for.
		 *
		 * Default to 1 week.
		 *
		 * @since 1.0.0
		 * @var int Length of time in seconds.
		 */
		$cache_time = apply_filters( 'wpna_facebook_post_content_cache_time', WEEK_IN_SECONDS );

		return set_transient( 'wpna_facebook_post_content_' . $id, $content, $cache_time );
	}

}
