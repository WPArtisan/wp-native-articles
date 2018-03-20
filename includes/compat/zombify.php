<?php
/**
 * Compatibility for the Zombify Page builder.
 *
 * @since 1.4.2
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_zombify_add_post_type_check' ) ) :

	/**
	 * Only some post types should be converted to IA.
	 *
	 * @param bool $should_convert Whether the post should be converted to IA or not.
	 * @return bool
	 */
	function wpna_zombify_add_post_type_check( $should_convert ) {
		if ( 'shortcode' === get_post_meta( get_the_ID(), 'zombify_postsave_type', true ) ) {
			// Grab the Zombify post type.
			$post_type = get_post_meta( get_the_ID(), 'zombify_data_type', true );
			// If it's one of these Zombify post types then don't convert it.
			if ( in_array( $post_type, array( 'trivia', 'countdown', 'personality', 'openlist', 'rankedlist', 'poll' ), true ) ) {
				$should_convert = false;
			}
		}

		return $should_convert;
	}
endif;
add_filter( 'wpna_should_convert_post_ia', 'wpna_zombify_add_post_type_check', 10, 1 );


if ( ! function_exists( 'wpna_zombify_shortcodes_override' ) ) :

	/**
	 * Override the `zombify_post` shortcode in IA.
	 *
	 * @param array  $override_tags Shortocde tags to override.
	 * @param string $content       Current post content.
	 * @return array $override_tags
	 */
	function wpna_zombify_shortcodes_override( $override_tags, $content ) {
		// Disables WP-Quads ads for Instant articles by returning an
		// empty string for the shortcode.
		$override_tags['zombify_post'] = 'wpna_zombify_post_shortcode_override';
		return $override_tags;
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', 'wpna_zombify_shortcodes_override', 10, 2 );

if ( ! function_exists( 'wpna_zombify_post_shortcode_override' ) ) :

	/**
	 * Do the output for the overriden shortcode.
	 *
	 * @param  array $atts Shortcode attributes.
	 * @return string
	 */
	function wpna_zombify_post_shortcode_override( $atts ) {

		if ( ! $postsavetype = get_post_meta( get_the_ID(), 'zombify_postsave_type', true ) ) {
			$postsavetype = 'shortcode';
		}

		if ( 'shortcode' === $postsavetype ) {

			if ( $post_type = get_post_meta( get_the_ID(), 'zombify_data_type', true ) ) {

				$zf_shortcode_args = shortcode_atts( array(
					'zf_page'           => '1',
					'zf_items_per_page' => '0',
				), $atts, 'zombify_post' );

				$zf_openlist_errors = array();

				zombify_frontend_save_post( $zf_openlist_errors );

				$data = zf_decode_data( get_post_meta( get_the_ID(), 'zombify_data', true ) );

				$function_callback = 'wpna_zombify_post_type_' . $post_type;

				if ( function_exists( $function_callback ) ) {
					$output = $function_callback( $data, $zf_shortcode_args );
				} else {
					$template_file = zombify()->locate_template( zombify()->quiz_view_dir( strtolower( $post_type ) . '.php' ) );

					ob_start();
					include $template_file;
					$output = ob_get_contents();
					ob_end_clean();

					$output = strip_tags( $output, '<figure><figcaption><cite><a><i><span><ol><li>' );
				}

				return do_shortcode( $output );
			} else {
				return '';
			}
		}

	}
endif;

if ( ! function_exists( 'wpna_zombify_post_type_story' ) ) :

	/**
	 * Add Zombify story post data in.
	 *
	 * @param  array $data Zombify post data to parse.
	 * @param  array $zf_shortcode_args Zombify shortcode arguments.
	 * @return string The extra post content
	 */
	function wpna_zombify_post_type_story( $data, $zf_shortcode_args ) {
		$output = '';
		$index  = 1;

		$main_data = $data;

		if ( isset( $main_data['story'] ) ) {
			foreach ( $main_data['story'] as $story ) {

				foreach ( $story as $st_index => $st_val ) {

					$story_data = $st_val[0];

					$function_callback = 'wpna_zombify_post_type_story_' . $st_index;

					if ( function_exists( $function_callback ) ) {
						$output .= $function_callback( $story_data );
					} else {
						$index = 1;

						$main_data = $data;

						ob_start();
						include zombify()->locate_template( zombify()->quiz_view_dir( 'story/' . $st_index . '.php' ) );
						$output = ob_get_contents();
						ob_end_clean();

						$output = strip_tags( $output, '<figure><figcaption><cite><a><i><span><ol><li>' );

					}
				}
			}
		}

		return $output;
	}
endif;

if ( ! function_exists( 'wpna_zombify_post_type_story_text' ) ) :

	/**
	 * Add Zombify story post data in.
	 *
	 * @param  array $story_data Zombify post data to parse.
	 * @param  array $zf_shortcode_args Zombify shortcode arguments.
	 * @return string The extra post content
	 */
	function wpna_zombify_post_type_story_text( $story_data, $zf_shortcode_args ) {
		$output  = '<h2>' . $story_data['text_title'] . '</h2>';
		$output .= $story_data['text_description'];

		return $output;
	}
endif;

if ( ! function_exists( 'wpna_zombify_post_type_story_story_list' ) ) :

	/**
	 * Add Zombify story list data in.
	 *
	 * @param  array $story_data Zombify post data to parse.
	 * @param  array $zf_shortcode_args Zombify shortcode arguments.
	 * @return string The extra post content
	 */
	function wpna_zombify_post_type_story_story_list( $story_data, $zf_shortcode_args ) {
		$data  = $story_data;
		$index = 1;

		$output = '';

		if ( ! isset( $data['list'] ) ) {
			return $output;
		}

		$items_per_page = zf_get_items_per_page( $zf_shortcode_args );
		$current_page   = zf_get_current_page( $zf_shortcode_args );
		$index          = ( $current_page - 1 ) * $items_per_page + 1;
		$data['list']   = array_slice( $data['list'], ( $current_page - 1 ) * $items_per_page, $items_per_page );

		foreach ( $data['list'] as $list ) {

			$output .= '<h2>#' . $index . ' ' . $list['title'] . '</h2>' . PHP_EOL;
			$values  = zf_array_values( $list['image'] );

			if ( 'image' === $list['mediatype'] && isset( $values[0]['attachment_id'] ) ) {
				$output .= '<figure>';

				ob_start();
				zombify_img_tag( $values[0]['attachment_id'], 'full' );
				$output .= ob_get_contents();
				ob_end_clean();

			} elseif ( 'embed' === $list['mediatype'] && ! empty( $list['embed_url'] ) ) {

				ob_start();
				Zombify_BaseQuiz::renderEmbed( $list, true );
				$output .= ob_get_contents();
				ob_end_clean();
			}

			if ( ! empty( $list['description'] ) ) {
				$output .= $list['description'];
			}
			$index++;
		}

		return $output;
	}
endif;

if ( ! function_exists( 'wpna_zombify_post_type_list' ) ) :

	/**
	 * Add Zombify post type list data in.
	 *
	 * @param  array $data Zombify post data to parse.
	 * @param  array $zf_shortcode_args Zombify shortcode arguments.
	 * @return string The extra post content
	 */
	function wpna_zombify_post_type_list( $data, $zf_shortcode_args ) {
		return wpna_zombify_post_type_story_story_list( $data, $zf_shortcode_args );
	}
endif;
