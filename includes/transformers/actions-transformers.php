<?php
/**
 * Applies transformers to the post content when it's being converted.
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
 * Add in the Transformers post type.
 *
 * @return void
 */
function wpna_setup_transformers_post_type() {

	/** Transformers Post Type */
	$transformer_labels = array(
		'name'               => esc_html_x( 'Transformers', 'post type general name', 'wp-native-articles' ),
		'singular_name'      => esc_html_x( 'Transformer', 'post type singular name', 'wp-native-articles' ),
		'add_new'            => esc_html__( 'Add New', 'wp-native-articles' ),
		'add_new_item'       => esc_html__( 'Add New Transformer', 'wp-native-articles' ),
		'edit_item'          => esc_html__( 'Edit Transformer', 'wp-native-articles' ),
		'new_item'           => esc_html__( 'New Transformer', 'wp-native-articles' ),
		'all_items'          => esc_html__( 'All Transformers', 'wp-native-articles' ),
		'view_item'          => esc_html__( 'View Transformer', 'wp-native-articles' ),
		'search_items'       => esc_html__( 'Search Transformers', 'wp-native-articles' ),
		'not_found'          => esc_html__( 'No Transformers found', 'wp-native-articles' ),
		'not_found_in_trash' => esc_html__( 'No Transformers found in Trash', 'wp-native-articles' ),
		'parent_item_colon'  => '',
		'menu_name'          => esc_html__( 'Transformers', 'wp-native-articles' ),
	);

	$transformer_args = array(
		'labels'          => apply_filters( 'wpna_transformer_labels', $transformer_labels ),
		'public'          => false,
		'query_var'       => false,
		'rewrite'         => false,
		'show_ui'         => false,
		'capability_type' => 'manage_options',
		'map_meta_cap'    => true,
		'supports'        => array( 'title' ),
		'can_export'      => true,
	);
	register_post_type( 'wpna_transformer', $transformer_args );
}
add_action( 'init', 'wpna_setup_transformers_post_type', 1 );

/**
 * Add in the Transformers custom post status.
 *
 * @return void
 */
function wpna_setup_transformers_post_type_statuses() {
	// Tansformer Code Statuses.
	register_post_status( 'active', array(
		'label'                     => esc_html_x( 'Active', 'Active transformer code status', 'wp-native-articles' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		// translators: Placeholder is the number of matching posts.
		'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'wp-native-articles' ),
	)  );
	register_post_status( 'inactive', array(
		'label'                     => esc_html_x( 'Inactive', 'Inactive transformer code status', 'wp-native-articles' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		// translators: Placeholder is the number of matching posts.
		'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'wp-native-articles' ),
	)  );
}
add_action( 'init', 'wpna_setup_transformers_post_type_statuses', 2 );

/**
 * Run any pattern mathcing transformers over the entire post content.
 *
 * @param  string $content The post content.
 * @return string The post content.
 */
function wpna_apply_pattern_matchers_transformers( $content ) {

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
		return $content;
	}

	$patterns     = array();
	$replacements = array();

	// Apply any pattern_matcher transformers set to the entire post content.
	foreach ( $transformers as $transformer ) {
		if ( 'post_content' !== $transformer->type ) {
			continue;
		}

		if ( 'pattern_matcher' !== $transformer->rule ) {
			continue;
		}

		// Pattern to search for.
		$search_for = $transformer->get_meta( 'search_for' );

		// preg_quote escapes the dot, question mark and equals sign in the URL (by
		// default) as well as all the forward slashes (because we pass '/' as the
		// $delimiter argument).
		$patterns[] = '/' . str_replace( '%s', '(.*)', preg_quote( $search_for, '/' ) ) . '/';

		// Pattern to replace it with.
		$replacements[] = $transformer->get_meta( 'replace_with' );
	}

	// If we have $patterns & $replacements, and they're the same length, run the regex.
	if ( ! empty( $patterns ) && ! empty( $replacements ) && count( $patterns ) === count( $replacements ) ) {
		$content = preg_replace( $patterns, $replacements, $content );
	}

	return $content;
}
add_filter( 'wpna_facebook_article_after_the_content_filter', 'wpna_apply_pattern_matchers_transformers', 10, 1 );


/**
 * Apply all content hook transformers.
 *
 * Cycles through all the transformer rules looking for the_content ones
 * and removes them if found.
 *
 * Content hooks can only be removed.
 *
 * @return void
 */
function wpna_apply_content_filters_transformers() {

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
		return;
	}

	global $wp_filter;

	if ( ! isset( $wp_filter['the_content'] ) ) {
		return;
	}

	foreach ( $wp_filter['the_content'] as $action => $functions ) {
		foreach ( $functions as $function_name => $callback ) {
			if ( ! in_array( $function_name, array( 'wptexturize', 'convert_smilies', 'wpautop', 'shortcode_unautop', 'do_shortcode' ), true ) ) {
				// Apply any shortcode transforemrs set to remove.
				foreach ( $transformers as $transformer ) {
					if ( 'content_filter' === $transformer->type && 'remove' === $transformer->rule ) {
						if ( wpna_get_filter_nice_name( $callback ) === $transformer->selector ) {
							remove_filter( 'the_content', $function_name, $action );
						}
					}
				}
			}
		}
	}

}
add_action( 'wpna_facebook_article_pre_the_content_transform', 'wpna_apply_content_filters_transformers', 10 );


/**
 * Override shortcode functions to apply transformer functions.
 *
 * Cycles through all the transformer rules looking for shortcode ones
 * then overrides that shortcodes callback function with the relevant one.
 *
 * @param  array $override_tags Shortcodes and their callback functions.
 * @return string
 */
function wpna_apply_shortcodes_transformers( $override_tags ) {

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
		return $override_tags;
	}

	// Apply any shortcode transformers set to remove or bypass.
	foreach ( $transformers as $transformer ) {
		if ( 'shortcode' !== $transformer->type ) {
			continue;
		}

		// If it's removing it, return an empty string.
		if ( 'remove' === $transformer->rule ) {
			$override_tags[ $transformer->selector ] = '__return_empty_string';
		}

		// If it's being bypassed then placeholder it in.
		if ( 'bypass_parser' === $transformer->rule ) {
			$override_tags[ $transformer->selector ] = 'wpna_transformer_shortcode_bypass_parser';
		}

		// If it's being pattern matched.
		if ( 'pattern_matcher' === $transformer->rule ) {
			$override_tags[ $transformer->selector ] = 'wpna_transformer_shortcode_pattern_matcher';
		}
	}

	return $override_tags;
}
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', 'wpna_apply_shortcodes_transformers', 10 );

/**
 * Stops a shortcode from going through the content parser.
 *
 * This is very similar to the WPNA_Facebook_Content_Parser::default_shortcode_callback()
 * method but without the auto wrapping stuff.
 *
 * @access public
 * @param  array  $attr    Shortcode attributes.
 * @param  string $content The content of the post.
 * @param  string $tag     Tag used for the shortcode.
 * @return string
 */
function wpna_transformer_shortcode_bypass_parser( $attr, $content = null, $tag ) {
	global $_shortcode_tags;

	// Get the original shortcode output.
	$content = call_user_func( $_shortcode_tags[ $tag ], $attr, $content, $tag );

	// Return a placeholder so it doesn't get parsed.
	return '<pre>' . wpna_content_parser_get_placeholder( $content ) . '</pre>';
}

/**
 * Runs the Pattern Matcher over a shortcode.
 *
 * @access public
 * @param  array  $attr    Shortcode attributes.
 * @param  string $content The content of the post.
 * @param  string $tag     Tag used for the shortcode.
 * @return string
 */
function wpna_transformer_shortcode_pattern_matcher( $attr, $content = null, $tag ) {
	global $_shortcode_tags;

	// Get the original shortcode output.
	$content = call_user_func( $_shortcode_tags[ $tag ], $attr, $content, $tag );

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
		return $content;
	}

	// Apply any shortcode transformers set to remove or bypass.
	foreach ( $transformers as $transformer ) {
		if ( 'shortcode' !== $transformer->type ) {
			continue;
		}

		// If it's being pattern matched.
		if ( 'pattern_matcher' !== $transformer->rule ) {
			continue;
		}

		// Make sure it's the correct shortcode.
		if ( $transformer->selector !== $tag ) {
			continue;
		}

		// Pattern to search for.
		$search_for = $transformer->get_meta( 'search_for' );

		// preg_quote escapes the dot, question mark and equals sign in the URL (by
		// default) as well as all the forward slashes (because we pass '/' as the
		// $delimiter argument).
		$pattern = '/' . str_replace( '%s', '(.*)', preg_quote( $search_for, '/' ) ) . '/';

		// Pattern to replace it with.
		$replacement = $transformer->get_meta( 'replace_with' );

		// Do the replacement.
		$content = preg_replace( $pattern, $replacement, $content );
	}

	// Return a placeholder so it doesn't get parsed.
	return '<pre>' . wpna_content_parser_get_placeholder( $content ) . '</pre>';
}
