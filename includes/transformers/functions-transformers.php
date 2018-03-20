<?php
/**
 * Generic Transformer functions.
 *
 * @package     wp-native-articles
 * @subpackage  Includes/Transformers
 * @copyright   Copyright (c) 2017, WPArtisan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpna_get_transformer' ) ) :

	/**
	 * Retrieves a transformer from the DB.
	 *
	 * @param int $transformer_id ID of the transformer to retrieve.
	 * @return object WPNA_Transformer
	 */
	function wpna_get_transformer( $transformer_id ) {
		$transformer = new WPNA_Transformer( $transformer_id );
		return $transformer;
	}
endif;

if ( ! function_exists( 'wpna_get_transformers' ) ) :

	/**
	 * Retrieves a transformer from the DB.
	 *
	 * @param array $args Custom WP_Query arguments for retrieving transformers.
	 * @return object WPNA_Transformer
	 */
	function wpna_get_transformers( $args = array() ) {
		$defaults = array(
			'post_type'      => 'wpna_transformer',
			'posts_per_page' => 30,
			'paged'          => null,
			'post_status'    => array( 'active', 'inactive' ),
		);

		$args = wp_parse_args( $args, $defaults );

		// Workout the cache key.
		$cache_key = md5( wp_json_encode( $args ) );

		// Check the key.
		if ( $transformers = wp_cache_get( $cache_key, 'wpna' ) ) {
			return $transformers;
		}

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() && ! empty( $args['s'] ) ) {
			// Add in the meta search.
			// @codingStandardsIgnoreStart
			$args['meta_key']     = '_wpna_transformer_selector';
			$args['meta_value']   = $args['s'];
			$args['meta_compare'] = 'LIKE';
			// Remove the search.
			$args['s'] = null;
			// @codingStandardsIgnoreEnd

			// Try again.
			$query = new WP_Query( $args );
		}

		if ( ! $query->have_posts() ) {
			return null;
		}

		$transformers = array();
		foreach ( $query->get_posts() as $key => $transformer ) {
			$transformers[ $key ] = new WPNA_Transformer( $transformer->ID );
		}

		// Set the cache.
		wp_cache_set( $cache_key, $transformers, 'wpna' );

		// Update the cache keys.
		$cache_keys = wp_cache_get( 'wpna_transformers_cache_keys', 'wpna' );

		$cache_keys[] = $cache_key;

		$cache_keys = array_unique( $cache_keys );
		$cache_keys = array_filter( $cache_keys );

		wp_cache_set( 'wpna_transformers_cache_keys', $cache_keys, 'wpna' );

		return $transformers;
	}
endif;

if ( ! function_exists( 'wpna_add_transformer' ) ) :

	/**
	 * Inserts a transformer into the DB.
	 *
	 * @param array $data Data to be inserted.
	 * @return mixed. ID on success, false on failure.
	 */
	function wpna_add_transformer( $data ) {
		$transformer = new WPNA_Transformer();
		return $transformer->add( $data );
	}
endif;

if ( ! function_exists( 'wpna_update_transformer' ) ) :

	/**
	 * Updates a transformer already in the DB.
	 *
	 * @param int   $transformer_id ID of the transformer to update.
	 * @param array $data Data to be inserted.
	 * @return mixed. ID on success, false on failure.
	 */
	function wpna_update_transformer( $transformer_id, $data ) {
		$transformer = new WPNA_Transformer( $transformer_id );
		return $transformer->update( $data );
	}
endif;

if ( ! function_exists( 'wpna_delete_transformer' ) ) :

	/**
	 * Removes a transformer completely.
	 *
	 * @param int $transformer_id ID of the transformer to remove.
	 * @return void
	 */
	function wpna_delete_transformer( $transformer_id ) {
		do_action( 'wpna_pre_delete_transformer', $transformer_id );

		wp_delete_post( $transformer_id, true );

		wpna_clear_transformer_cache();

		do_action( 'wpna_post_delete_transformer', $transformer_id );
	}
endif;

if ( ! function_exists( 'wpna_clear_transformer_cache' ) ) :

	/**
	 * Clear all the transformer cache.
	 *
	 * @return void
	 */
	function wpna_clear_transformer_cache() {
		// Get all the cache keys.
		$cache_keys = wp_cache_get( 'wpna_transformers_cache_keys', 'wpna' );

		if ( $cache_keys && is_array( $cache_keys ) ) {
			foreach ( $cache_keys as $cache_key ) {
				wp_cache_delete( $cache_key, 'wpna' );
			}
		}

		// Clear the cache keys as well.
		wp_cache_delete( 'wpna_transformers_cache_keys', 'wpna' );
	}
endif;

if ( ! function_exists( 'wpna_facebook_sdk_rules' ) ) :

	/**
	 * Get all the Rules from the Facebook SDK that are allowed.
	 *
	 * @return array Facebook SDK rules.
	 */
	function wpna_facebook_sdk_rules() {
		// Setup the Facebook Transformers.
		$facebook_transformers = array(
			'AdRule'              => array(
				'context'    => 'InstantArticle',
				'properties' => array( 'url', 'height', 'width', 'embed' ),
			),
			'AnchorRule'          => array(
				'context'    => 'Paragraph',
				'properties' => array( 'href', 'rel' ),
			),
			'BlockquoteRule'      => array(
				'context' => 'InstantArticle',
			),
			'BoldRule'            => array(
				'context' => 'Paragraph',
			),
			'CaptionCreditRule'   => array(
				'context' => 'Caption',
			),
			'CaptionRule'         => array(
				'context' => 'Paragraph',
			),
			'H1Rule'              => array(
				'context' => 'InstantArticle',
			),
			'H2Rule'              => array(
				'context' => 'InstantArticle',
			),
			'ImageRule'           => array(
				'context'    => 'InstantArticle',
				'properties' => array( 'url', 'like', 'comments' ),
			),
			'InteractiveRule'     => array(
				'context'    => 'InstantArticle',
				'properties' => array( 'iframe', 'url', 'height' ),
			),
			'ItalicRule'          => array(
				'context' => 'Paragraph',
			),
			'LineBreakRule'       => array(
				'context' => 'Paragraph',
			),
			'ListElementRule'     => array(
				'context' => 'LineBreakRule',
			),
			'ListItemRule'        => array(
				'context' => 'ListElement',
			),
			'MapRule'             => array(
				'context' => 'InstantArticle',
			),
			'ParagraphRule'       => array(
				'context' => 'InstantArticle',
			),
			'PullquoteCiteRule'   => array(
				'context' => 'InstantArticle',
			),
			'PullquoteRule'       => array(
				'context' => 'Pullquote',
			),
			'RelatedArticlesRule' => array(
				'context'    => 'Paragraph',
				'properties' => array( 'title' ),
			),
			'RelatedItemRule'     => array(
				'context'    => 'InstantArticle',
				'properties' => array( 'sponsored', 'url' ),
			),
			'SlideshowImageRule'  => array(
				'context'    => 'Slideshow',
				'properties' => array( 'url', 'title', 'credit' ),
			),
			'SlideshowRule'       => array(
				'context' => 'InstantArticle',
			),
			'VideoRule'           => array(
				'context'    => 'InstantArticle',
				'properties' => array( 'url', 'type', 'playback', 'controls', 'like', 'comments' ),
			),
		);

		/**
		 * Filter the Facebook SDK rules that are allowed.
		 *
		 * @var array
		 */
		$facebook_transformers = apply_filters( 'wpna_facebook_sdk_rules', $facebook_transformers );

		return $facebook_transformers;
	}
endif;
