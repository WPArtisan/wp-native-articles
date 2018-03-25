<?php
/**
 * Applies Facebook transformers to the post content when it's being converted.
 * This is in a seperate file so it's not included if the PHP version isn't high enough.
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


/**
 * Apply any custom Facebook transformer rules to the dom.
 *
 * @param  DOMDocument $dom_document Dom representation of the content.
 * @return DOMDocument
 */
function wpna_apply_custom_transformers( $dom_document ) {

	// Don't bother if the PHP version isn't high enough.
	if ( version_compare( PHP_VERSION, '5.4', '<' ) ) {
		return $dom_document;
	}

	// Grab all the transformers. Aggressively cached.
	// This is quicker than doing a meta search.
	$transformers = wpna_get_transformers(
		array(
			// @codingStandardsIgnoreLine
			'posts_per_page' => 250,
			'post_status'    => array( 'active' ),
		)
	);

	if ( ! $transformers ) {
		return $dom_document;
	}

	// These are the minimum transformer rules the Facebook parser needs.
	$default_transformer_rules = array(
		array(
			'class' => 'TextNodeRule',
		),
	);

	// Setup xpath.
	$xpath = new DOMXPath( $dom_document );

	// Setup the CSS to xpath converter.
	// @codingStandardsIgnoreLine
	$converter = new Symfony\Component\CssSelector\CssSelectorConverter();

	// Setup a new facebook transformer.
	// @codingStandardsIgnoreLine
	$facebook_transformer = new Facebook\InstantArticles\Transformer\Transformer(); // PHPCompatibility: PHP ok.

	// Get the Facebook SDK rules.
	$facebook_transformers = wpna_facebook_sdk_rules();

	// Apply any shortcode transforemrs set to remove.
	foreach ( $transformers as $transformer ) {
		// Only want custom transformers.
		if ( 'custom' !== $transformer->type ) {
			continue;
		}

		// Make sure we have a selector.
		if ( empty( $transformer->selector ) ) {
			continue;
		}

		// Setup the custom transfor rules.
		$custom_transformer_rules = array(
			array(
				'class'    => $transformer->rule,
				'selector' => $transformer->selector,
			),
		);

		$properties = $transformer->get_meta( 'properties' );

		if ( ! empty( $properties ) ) {
			$properties                                = json_decode( trim( $properties ), true );
			$custom_transformer_rules[0]['properties'] = $properties;
		}

		// Check if it's xpath or CSS.
		if ( '/' === substr( $transformer->selector, 0, 1 ) ) {
			$xpath_query = $transformer->selector;
		} else {
			// Convert CSS to Xpath.
			$xpath_query = $converter->toXPath( $transformer->selector );
		}

		// Grab the matching nodes.
		$nodes = $xpath->query( $xpath_query );

		$i = $nodes->length - 1;

		// Using a regressive loop. When Removing elements with a
		// foreach loop the index changing can confuse it.
		while ( $i > -1 ) {

			// Setup the node.
			$node = $nodes->item( $i );

			// if the node is being completely removed.
			if ( 'remove' === $transformer->rule ) {
				$node->parentNode->removeChild( $node );

			} else {

				$new_node = $new_element = $child_node = null;

				// Merge the default and custom rules.
				$transformer_rules = array(
					'rules' => array_merge( $default_transformer_rules, $custom_transformer_rules ),
				);

				// Setup the new rules.
				$facebook_transformer->loadRules( wp_json_encode( $transformer_rules ) );

				if ( ! isset( $facebook_transformers[ $transformer->rule ] ) ) {
					continue;
				}

				// Create the new element class.
				$context = 'Facebook\InstantArticles\Elements\\' . $facebook_transformers[ $transformer->rule ]['context'];

				// Create the pesude element to wrap it.
				// @codingStandardsIgnoreLine
				$new_element = $context::create();

				// Clone the node.
				$node_clone = clone $node;
				// Create a fragment.
				$fragment = $dom_document->createDocumentFragment();
				// Add the cloned node to the fragment.
				$fragment->appendChild( $node_clone );

				// Create the new element. This will parse the entire content
				// given a chance. That's wy we isolate it with the fragment.
				$new_node = $facebook_transformer->transform( $new_element, $fragment );
				// Make sure the new node is part of our DOM.
				$new_node = $new_node->toDOMElement( $dom_document );

				// If we create a new InstantArticle then we have to remove all the rubbish we don't need.
				if ( 'InstantArticle' === $facebook_transformers[ $transformer->rule ]['context'] ) {
					// Grab the new node we actually want.
					// Note: Has to be assigned to a different var.
					$child_node = $new_node->getElementsByTagName( 'article' )->item( 0 )->childNodes->item( 0 );
					// Replace the found node with the new one.
					$node->parentNode->replaceChild( $child_node, $node );
				} else {
					// Replace the found node with the new one.
					$node->parentNode->replaceChild( $new_node, $node );
				}
			}

			$i--;
		}
	}

	return $dom_document;
}
add_filter( 'wpna_facebook_article_content_transform', 'wpna_apply_custom_transformers', 5, 1 );
