<?php
/**
 * Instagram embed script Compatibility.
 *
 * @since 1.3.2
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_transform_embed_instagram' ) ) :

	/**
	 * Check for any Twitter embed scripts and replace them with placeholders.
	 * We run this near the begining to make sure no other rules interfere.
	 *
	 * @param DomDocument $dom_document A do object for the content.
	 * @return DomDocument $dom_document
	 */
	function wpna_transform_embed_instagram( $dom_document ) {
		// Find all blockquote elements.
		$nodes = $dom_document->getElementsByTagName( 'blockquote' );

		$i = $nodes->length - 1;

		// Using a regressive loop. Removing elements with a foreach can get confused
		// when the index changes.
		while ( $i > -1 ) {

			$node = $nodes->item( $i );

			if ( 'instagram-media' === $node->getAttribute( 'class' ) ) {

				// create all the templates we'll need.
				$figure_template = $dom_document->createElement( 'figure' );
				$figure_template->setAttribute( 'class', 'op-interactive' );

				$iframe_template = $dom_document->createElement( 'iframe' );

				$script_template = $dom_document->createElement( 'script' );
				$script_template->setAttribute( 'async', 'true' );
				$script_template->setAttribute( 'src', 'https://platform.instagram.com/en_US/embeds.js' );
				$script_template->setAttribute( 'charset', 'utf-8' );

				// Replace the node with the empty template.
				$node->parentNode->replaceChild( $figure_template, $node );

				// Add the node and script templates to the iframe template.
				$iframe_template->appendChild( $node );
				$iframe_template->appendChild( $script_template );

				// Add the node template to the figure template.
				$figure_template->appendChild( $iframe_template );

				// Get a placeholder node for the figure template.
				$placeholder = wpna_content_parser_get_placeholder_node( $figure_template );

				// Replace the figure template with the new placeholder one.
				$figure_template->parentNode->replaceChild( $placeholder, $figure_template );
			}

			$i--;
		}

		return $dom_document;
	}
endif;
add_filter( 'wpna_facebook_article_content_transform', 'wpna_transform_embed_instagram', 5, 1 );
