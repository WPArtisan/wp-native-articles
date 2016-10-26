<?php

/**
 * General helper functions for the plugin
 *
 * @author OzTheGreat
 * @since  1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'wpna_get_option' ) ) :

	/**
	 * Retrieves a single plugin option.
	 *
	 * Gets a single option from the global array, runs it through
	 * filters then returns it. The second param can set a default value to be
	 * returned if the option doesn't exist
	 *
	 * @since 1.0.0
	 *
	 * @global $wpna_options   Global array holding the plugin options.
	 *
	 * @param  string $name    The name of the option to retrieve.
	 * @param  mixed  $default Optional. The default value to return.
	 *                         Default false.
	 * @return mixed The option or default value.
	 */
	function wpna_get_option( $name, $default = false ) {
		global $wpna_options;

		// Setup the default value
		$value = $default;

		// Check if it exists in the global options array
		if ( isset( $wpna_options[ $name ] ) )
			$value = $wpna_options[ $name ];

		/**
		 * Filter all the option values before they're returned
		 *
		 * @since 1.0.0
		 *
		 * @param mixed  $value   The value being returned.
		 * @param string $name    The name of the option being retrieved.
		 * @param mixed  $default The default value to return.
		 */
		$option = apply_filters( 'wpna_get_option', $value, $name, $default );

		/**
		 * Filter a specific option value before it's returned
		 *
		 * @since 1.0.0
		 *
		 * @param mixed  $value   The value being returned.
		 * @param string $name    The name of the option being retrieved.
		 * @param mixed  $default The default value to return.
		 */
		$option = apply_filters( 'wpna_get_option_' . $name, $value, $name, $default );

		return $option;
	}

endif;


if ( ! function_exists( 'wpna_get_options' ) ) :

	/**
	 * Retrieves all plugin options.
	 *
	 * Gets all options from the global array, runs them through
	 * filters then returns them.
	 *
	 * @since 1.0.0
	 *
	 * @global $wpna_options Global array holding the plugin options.
	 *
	 * @return array All of the plugin's options.
	 */
	function wpna_get_options() {
		global $wpna_options;

		if ( ! $wpna_options )
			$wpna_options = get_option( 'wpna_options' );

		/**
		 * Filter all the option values before they're returned
		 *
		 * @since 1.0.0
		 *
		 * @param array $wpna_options The options being returned.
		 */
		$wpna_options = apply_filters( 'wpna_get_options', $wpna_options );

		return $wpna_options;
	}

endif;

if ( ! function_exists( 'wpna_get_post_option' ) ) :

	/**
	 * Retrieves a post specific plugin option.
	 *
	 * Checks the post meta to see if the option is set.
	 * If it is not it will return to the global plugin value.
	 * Failing that it will return the default value passed.
	 *
	 * @since 1.0.0
	 *
	 * @param  int    $post_id The ID of the post to retrieve the option for.
	 * @param  string $name    The name of the option to retrieve.
	 * @param  mixed  $default Optional. The default value to return.
	 *                         Default false.
	 * @return mixed  The option or default value.
	 */
	function wpna_get_post_option( $post_id, $name, $default = false ) {

		if ( $post_meta = get_post_meta( $post_id, $name, true ) ) {
			// Get the post specific option
			$value = $post_meta;
		} else {
			// Check if it exists in the global options array
			$value = wpna_get_option( $name, $default );
		}

		/**
		 * Filter all the option values before they're returned
		 *
		 * @since 1.0.0
		 *
		 * @param mixed  $value   The value being returned.
		 * @param string $name    The name of the option being retrieved.
		 * @param mixed  $default The default value to return.
		 * @param int    $post_id The post ID.
		 */
		$option = apply_filters( 'wpna_get_post_option', $value, $name, $default, $post_id );

		/**
		 * Filter a specific option value before it's returned
		 *
		 * @since 1.0.0
		 *
		 * @param mixed  $value   The value being returned.
		 * @param string $name    The name of the option being retrieved.
		 * @param mixed  $default The default value to return.
		 * @param int    $post_id The post ID.
		 */
		$option = apply_filters( 'wpna_get_post_option_' . $name, $value, $name, $default, $post_id );

		return $option;
	}

endif;


if ( ! function_exists( 'wpna_locate_template' ) ) :

	/**
	 * Locates a plugin template and returns the path to it
	 *
	 * Takes a template name and first searches for it in themes to see if
	 * it's been overridden or not. If it can't find it defaults to the one
	 * located in the plugin.
	 *
	 * @since 1.0.0
	 * @todo Pass params through?
	 *
	 * @param  string $name Name of the template to locate.
	 * @return string The full path to the template file.
	 */
	function wpna_locate_template( $name ) {

		// Check if there's an extension or not
		$name .= '.php' !== substr( $name, -4 ) ? '.php' : '' ;

		// locate_template() returns the path to file
		// if either the child theme or the parent theme have overridden the template
		if ( $overridden_template = locate_template( $name ) )
			return $overridden_template;

		// If neither the child nor parent theme have overridden the template,
		// we load the template from the 'templates' sub-directory of the directory this file is in
		$template_path = WPNA_BASE_PATH . '/templates/' . $name;

		/**
		 * Alter the path for a template file
		 *
		 * @since 1.0.0
		 *
		 * @param string $template_path The path to the template.
		 * @param string $name          The name of the template to locate.
		 */
		$template_path = apply_filters( 'wpna_template_path', $template_path, $name );

		return $template_path;
	}

endif;


if ( ! function_exists( 'wpna_get_attachment_id_from_src' ) ) :

	/**
	 * Gets an attachment ID from given a URL.
	 *
	 * Takes a given URL and uses several different methods to try and find
	 * the attachment URL corresponding to it. Results are cached.
	 *
	 * @since 1.0.0
	 *
	 * @link http://wpscholar.com/blog/get-attachment-id-from-wp-image-url/
	 *
	 * @param string $url The URL to find the attachment for.
	 * @return int Attachment ID on success, null on failure.
	 */
	function wpna_get_attachment_id_from_src( $url ) {

		$attachment_id = null;

		// Strip off any resizing params
		$url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $url );

		// Let's check the cache
		if ( $attachment_id = wp_cache_get( md5( $url ), 'wpna' ) )
			return $attachment_id;

		// This will be quickest as it's cached
		if ( ! $attachment_id && function_exists( 'wpcom_vip_attachment_url_to_postid' ) )
			$attachment_id = wpcom_vip_attachment_url_to_postid( $url );

		// Only came in in 4.0 so let's be a bit careful
		if ( ! $attachment_id && function_exists( 'attachment_url_to_postid' ) ) {
			$attachment_id = attachment_url_to_postid( $url );
		}

		// If we stil haven't found it let's run ths custom query. It is a tad slow.
		if ( ! $attachment_id ) {

			$dir = wp_upload_dir();

			if ( strpos( $url, $dir['baseurl'] . '/' ) )
				return null;

			$file = basename( $url );

			$query_args = array(
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'post_type'              => 'attachment',
				'post_status'            => 'inherit',
				'fields'                 => 'ids',
				'meta_query'             => array(
					array(
						'value'   => $file,
						'compare' => 'LIKE',
						'key'     => '_wp_attachment_metadata',
					),
				)
			);

			$query = new WP_Query( $query_args );

			if ( $query->have_posts() ) {

				foreach ( $query->posts as $post_id ) {

					$meta = wp_get_attachment_metadata( $post_id );

					$original_file       = basename( $meta['file'] );
					$cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );

					if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
						$attachment_id = $post_id;
						break;
					}

				}

			}

		}

		/**
		 * Filter the attachment ID found from the URL
		 *
		 * @since 1.0.0
		 *
		 * @param int|null $attachment_id The ID if it's found or null if not.
		 * @param string   $url               The URL we were trying to find the ID for
		 */
		$attachment_id = apply_filters( 'wpna_get_attachment_id_from_src', $attachment_id, $url );

		// Cache the result
		wp_cache_set( md5( $url ), $attachment_id, 'wpna' );

		return $attachment_id;
	}

endif;

if ( ! function_exists( 'wpna_load_textdomain' ) ) :

	/**
	 * Load plugin textdomain.
	 *
	 * Checks in the languages folder by default.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	function wpna_load_textdomain() {
		load_plugin_textdomain( 'wp-native-articles', false, WPNA_BASE_PATH . '/languages' );
	}
endif;
