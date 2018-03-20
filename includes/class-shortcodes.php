<?php
/**
 * Admin class
 *
 * @since 1.2.5
 * @package wp-native-articles
 */

/**
 * Registers and deals with custom shortcodes to be used in the plugin.
 *
 * Initially just the shortcode to show different content in WP or IA.
 *
 * @since  1.2.5
 */
class WPNA_Shortcodes extends WPNA_Admin_Base {

	/**
	 * Hooks registered in this class.
	 *
	 * This method is auto called from WPNA_Admin_Base.
	 *
	 * @since 1.2.5
	 * @todo Change meta box hook
	 *
	 * @access public
	 * @return void
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'register_wpna_shortcode' ), 10, 1 );
		add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', array( $this, 'add_ia_shortcode_override' ), 10, 2 );
	}

	/**
	 * Register the custom `wpna` shortcode tag with WP.
	 *
	 * @access public
	 * @return void
	 */
	public function register_wpna_shortcode() {
		add_shortcode( 'wpna', array( $this, 'do_shortcode_wp' ) );
		add_shortcode( 'wpna_related_articles', '__return_empty_string' );
		add_shortcode( 'wpna_ad', '__return_empty_string' );
	}

	/**
	 * Overrides the default shortcode function when in an IA.
	 *
	 * @access public
	 * @param  array  $override_tags Shortocde tags to override.
	 * @param  string $content       Current post content.
	 * @return array $override_tags
	 */
	public function add_ia_shortcode_override( $override_tags, $content ) {
		$override_tags['wpna']                  = array( $this, 'do_shortcode_ia' );
		$override_tags['wpna_related_articles'] = array( $this, 'do_related_articles_shortcode_ia' );
		$override_tags['wpna_ad']               = array( $this, 'do_ad_shortcode_ia' );
		return $override_tags;
	}

	/**
	 * This method deals with the shortcode in a regular WordPress post.
	 *
	 * Checks to see if any content should be hidden.
	 *
	 * @access public
	 * @param  array  $atts Attributes passed to the shortcode.
	 * @param  string $content Any content between the shortcode tags.
	 * @return function do_shortcode()
	 */
	public function do_shortcode_wp( $atts, $content = '' ) {
		$atts = shortcode_atts( array(
			'hide' => '',
		), $atts, 'wpna' );

		// If the content should be hidden in WP then return an empty string.
		if ( 'wp' === strtolower( trim( $atts['hide'] ) ) ) {
			$content = '';
		}

		return do_shortcode( $content );
	}

	/**
	 * This method deals with the shortcode in a regular WordPress post.
	 *
	 * Checks to see if any content should be hidden.
	 *
	 * @access public
	 * @param  array  $atts Attributes passed to the shortcode.
	 * @param  string $content Any content between the shortcode tags.
	 * @return function do_shortcode()
	 */
	public function do_shortcode_ia( $atts, $content = '' ) {
		$atts = shortcode_atts( array(
			'hide' => '',
		), $atts, 'wpna' );

		// If the content should be hidden in IA then return an empty string.
		if ( 'ia' === strtolower( trim( $atts['hide'] ) ) ) {
			$content = '';
		}

		return do_shortcode( $content );
	}
	/**
	 * This method deals with the shortcode in Instant Articles.
	 *
	 * Can set a related articles block.
	 *
	 * @access public
	 * @param  array  $atts Attributes passed to the shortcode.
	 * @param  string $content Any content between the shortcode tags.
	 * @return function do_shortcode()
	 */
	public function do_related_articles_shortcode_ia( $atts, $content = '' ) {
		$atts = shortcode_atts( array(
			'title' => null,
			'ids'   => null,
		), $atts, 'wpna_related_articles' );

		$args = null;

		// If they've specified IDs then only grab those posts.
		if ( ! empty( $atts['ids'] ) ) {
			$args['category__in'] = null;
			$args['post__in']     = explode( ',', $atts['ids'] );
		}

		// Grab the post.
		$wpna_post = new WPNA_Facebook_Post( get_the_ID() );

		// Grab the related articles loop.
		$related_articles_loop = $wpna_post->get_related_articles( $args );

		// Check if there's a title.
		if ( ! empty( $atts['title'] ) ) {
			$related_articles = sprintf( '<ul class="op-related-articles" title="%s">', esc_attr( $atts['title'] ) ) . PHP_EOL;
		} else {
			$related_articles = '<ul class="op-related-articles">' . PHP_EOL;
		}

		foreach ( $related_articles_loop->get_posts() as $related_article ) {
			$attrs = '';

			// Check if the related article is a sponsored one.
			if ( wpna_switch_to_boolean( wpna_get_post_option( $related_article->ID, 'fbia_sponsored' ) ) ) {
				$attrs = ' data-sponsored="true"';
			}

			/**
			 * Filter any attributes applied to the <li> element
			 * of the related articles. e.g. sponsored.
			 *
			 * @since 1.0.0
			 * @param $attrs List of attributes to add
			 * @param $related_article The current related articles
			 * @param $post The current post
			 */
			$attrs = apply_filters( 'wpna_facebook_article_related_articles_attributes', $attrs, $related_article, $wpna_post );

			$related_articles .= '<li' . $attrs . '><a href="' . esc_url( get_permalink( $related_article ) ) . '"></a></li>' . PHP_EOL;
		}

		$related_articles .= '</ul>';

		// Placeholder it so it bypasses the transformer.
		$placeholder = '<pre>' . wpna_content_parser_get_placeholder( $related_articles ) . '</pre>';

		return $placeholder;
	}

	/**
	 * This method deals with the shortcode in Instant Articles.
	 *
	 * Can set a custom Placement ID, use the global one by default.
	 *
	 * @access public
	 * @param  array  $atts Attributes passed to the shortcode.
	 * @param  string $content Any content between the shortcode tags.
	 * @return function do_shortcode()
	 */
	public function do_ad_shortcode_ia( $atts, $content = '' ) {
		$atts = shortcode_atts( array(
			'placement_id' => null,
		), $atts, 'wpna_ad' );

		$ad_code = '';

		if ( wpna_switch_to_boolean( wpna_get_post_option( get_the_ID(), 'fbia_enable_ads' ) ) ) {
			if ( ! empty( $atts['placement_id'] ) ) {
				// If a custom placement ID has been set.
				$placement_id = $atts['placement_id'];
				// Grab the HTML for the ad placement.
				// The $placement_id var is available inside the template.
				ob_start();

				include wpna_locate_template( 'wpna-audience-network-banner.php' );

				$ad_code = ob_get_clean();
			} else {
				// Get the converted post.
				$wpna_post = new WPNA_Facebook_Post( get_the_ID() );
				// Set the content to the ads.
				$ad_code = $wpna_post->get_ads();
			}
		}

		return $ad_code;
	}

}
