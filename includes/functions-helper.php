<?php
/**
 * General helper functions for the plugin
 *
 * @author OzTheGreat
 * @since  1.0.0
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		// Grab all the options.
		$wpna_options = wpna_get_options();

		// Setup the default value.
		$value = $default;

		// Check if it exists in the global options array.
		if ( ! empty( $wpna_options[ $name ] ) ) {
			$value = $wpna_options[ $name ];
		}

		/**
		 * Filter all the option values before they're returned.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed  $value   The value being returned.
		 * @param string $name    The name of the option being retrieved.
		 * @param mixed  $default The default value to return.
		 */
		$option = apply_filters( 'wpna_get_option', $value, $name, $default );

		/**
		 * Filter a specific option value before it's returned.
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
		// get_option is cached so can call it as much as we like.
		$wpna_options = get_option( 'wpna_options' );

		/**
		 * Filter all the option values before they're returned.
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

		// Options stored in posts are prefixed with '_wpna_'.
		$post_option_name = '_wpna_' . $name;

		if ( $post_meta = get_post_meta( $post_id, $post_option_name, true ) ) {
			// Get the post specific option.
			$value = $post_meta;
		} else {
			// Check if it exists in the global options array.
			$value = wpna_get_option( $name, $default );
		}

		/**
		 * Filter all the option values before they're returned.
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
		 * Filter a specific option value before it's returned.
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

if ( ! function_exists( 'wpna_option_overridden_notice' ) ) :

	/**
	 * Checks to see if an option has been overridden.
	 *
	 * If an option value has been hooked into we display a warning saying
	 * it has potentially been overridden.
	 *
	 * @param string $option The option to check.
	 * @return void
	 */
	function wpna_option_overridden_notice( $option ) {
		wpna_hook_overridden_notice( 'wpna_get_option_' . $option );
	}
endif;

if ( ! function_exists( 'wpna_post_option_overridden_notice' ) ) :

	/**
	 * Checks to see if a post option has been overridden.
	 *
	 * If a post option value has been hooked into we display a warning saying
	 * it has potentially been overridden.
	 *
	 * @param string $option The post option to check.
	 * @return void
	 */
	function wpna_post_option_overridden_notice( $option ) {
		wpna_hook_overridden_notice( 'wpna_get_post_option_' . $option );
	}
endif;

if ( ! function_exists( 'wpna_hook_overridden_notice' ) ) :

	/**
	 * Checks to see if a particular hook has filters attached.
	 *
	 * If there are filters attached to the hook displays a warning message
	 * saying it's potentially been overridden.
	 *
	 * @param string $option_hook The option hook to check.
	 * @return void
	 */
	function wpna_hook_overridden_notice( $option_hook ) {
		global $wp_filter;

		if ( isset( $wp_filter[ $option_hook ] ) ) {

			$hooked_callbacks = array();

			foreach ( $wp_filter[ $option_hook ]->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback_id => $callback ) {
					$hooked_callbacks[] = wpna_get_filter_nice_name( $callback );
				}
			}

			/**
			 * Array for functions that might be changing the option value.
			 *
			 * @var array Hooked callbacks for this option.
			 * @var string The option currently being checked.
			 */
			$hooked_callbacks = apply_filters( 'wpna_overridden_notice_callbacks', $hooked_callbacks, $option_hook );

			if ( ! empty( $hooked_callbacks ) ) :
			?>
			<p>
				<span class="wpna-label wpna-label-warning"><?php esc_html_e( 'Warning', 'wp-native-articles' ); ?></span>
				<i><b><?php esc_html_e( 'Functions are hooking into this option and might be changing the output', 'wp-native-articles' ); ?></b></i>
			</p>
			<?php if ( current_user_can( 'manage_options' ) ) : ?>
				<p>
					<code><?php echo implode( '</code> <code>', array_map( 'esc_html', $hooked_callbacks ) ); ?></code>
				</p>
			<?php endif; ?>

			<?php
			endif;
		}
	}
endif;

if ( ! function_exists( 'wpna_get_filter_nice_name' ) ) :

	/**
	 * Takes a callback function for a hook and constructs a unique name for it.
	 * This can then be used later on to remove the same hook.
	 *
	 * @param  mixed $callback The callback function.
	 * @return string
	 */
	function wpna_get_filter_nice_name( $callback ) {

		$name = '';

		if ( is_array( $callback['function'] ) ) {
			// Filters added from inside classes using $this.
			if ( is_object( $callback['function'][0] ) ) {
				// Get the classname and method name.
				$name = get_class( $callback['function'][0] ) . '::' . $callback['function'][1];
			} else {
				// Filters added from outside classes using names.
				$name = $callback['function'][0] . '::' . $callback['function'][1];
			}
		} elseif ( is_object( $callback['function'] ) ) {
			// Filters added via closures.
			if ( $callback['function'] instanceof Closure ) {
				if ( class_exists( 'ReflectionFunction' ) ) {
					$func = new ReflectionFunction( $callback['function'] );
					$name = 'Closure::' . basename( $func->getFileName() ) . '::' . $func->getStartLine();
				}
			}
		} else {
			// Filters added via functions.
			$name = $callback['function'];
		}

		return $name;
	}
endif;

if ( ! function_exists( 'boolval' ) ) :

	/**
	 * Converts a value to a boolean.
	 *
	 * PHP <= 5.5 didn't have boolval function, this patches it in.
	 *
	 * @since 1.0.6
	 *
	 * @param  mixed $value The value to turn to bool.
	 * @return boolean
	 */
	function boolval( $value ) {
		return (bool) $value;
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

		// Check if there's an extension or not.
		$name .= '.php' !== substr( $name, -4 ) ? '.php' : '';

		/**
		 * If override all WPNA templates shpuld be inside a directory in any
		 * themes. This allows you to filter the name of that diectory.
		 *
		 * @since 1.1.0
		 * @var string The name of the directory to check in.
		 */
		$template_override_directory = apply_filters( 'wpna_template_override_directory', 'wp-native-articles' );

		/**
		 * The old way of overriding templates was to put them at the top level
		 * inside a theme, they should really have been inside a folder.
		 *
		 * We now check both. No point thowing an error as it would get lost.
		 */
		$files_to_check = array( $name, "$template_override_directory/$name" );

		// locate_template() returns the path to file.
		// If either the child theme or the parent theme have overridden the template.
		if ( $overridden_template = locate_template( $files_to_check ) ) {
			return $overridden_template;
		}

		// If neither the child nor parent theme have overridden the template,
		// we load the template from the 'templates' sub-directory of the directory this file is in.
		$template_path = WPNA_BASE_PATH . '/templates/' . $name;

		/**
		 * Alter the path for a template file.
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

		// Strip off any resizing params.
		$url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $url );

		// Let's check the cache.
		if ( $attachment_id = wp_cache_get( md5( $url ), 'wpna' ) ) {
			return $attachment_id;
		}

		// This will be quickest as it's cached.
		if ( ! $attachment_id && function_exists( 'wpcom_vip_attachment_url_to_postid' ) ) {
			$attachment_id = wpcom_vip_attachment_url_to_postid( $url );
		}

		// Only came in in 4.0 so let's be a bit careful.
		if ( ! $attachment_id && function_exists( 'attachment_url_to_postid' ) ) {
			// @codingStandardsIgnoreLine. This is a 3x fallback function, not expected to be used.
			$attachment_id = attachment_url_to_postid( $url );
		}

		// If we stil haven't found it let's run ths custom query. It is a tad slow.
		if ( ! $attachment_id ) {

			$dir = wp_upload_dir();

			if ( strpos( $url, $dir['baseurl'] . '/' ) ) {
				return null;
			}

			$file = basename( $url );

			$query_args = array(
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'post_type'              => 'attachment',
				'post_status'            => 'inherit',
				'fields'                 => 'ids',
				// 4x fallback function.
				// @codingStandardsIgnoreLine.
				'meta_query'             => array(
					array(
						'value'   => $file,
						'compare' => 'LIKE',
						'key'     => '_wp_attachment_metadata',
					),
				),
			);

			$query = new WP_Query( $query_args );

			if ( $query->have_posts() ) {

				foreach ( $query->posts as $post_id ) {

					$meta = wp_get_attachment_metadata( $post_id );

					if ( empty( $meta['sizes'] ) ) {
						continue;
					}

					$original_file       = basename( $meta['file'] );
					$cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );

					if ( $original_file === $file || ( is_array( $cropped_image_files ) && in_array( $file, $cropped_image_files, true ) ) ) {
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
		 * @param string   $url           The URL we were trying to find the ID for.
		 */
		$attachment_id = apply_filters( 'wpna_get_attachment_id_from_src', $attachment_id, $url );

		// Cache the result.
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
	 * @return void
	 */
	function wpna_load_textdomain() {
		load_plugin_textdomain( 'wp-native-articles', false, plugin_basename( WPNA_BASE_PATH ) . '/languages/' );
	}
endif;

if ( ! function_exists( 'wpna_replace_date_placeholders' ) ) :

	/**
	 * Replaces placeholders in a string with date variables.
	 *
	 * Can use any PHP date format placeholders as long as it's preceeded by %.
	 * A double placeholder works as an escape e.g. %%Y parses literally.
	 *
	 * @since 1.1.6
	 *
	 * @param string $string The string to parse the date varaibles in.
	 * @return The parsed string
	 */
	function wpna_replace_date_placeholders( $string ) {
		// Grab all the placeholders.
		preg_match_all( '/(?<!%)%([A-Za-z])|(?<=%%)([A-Za-z])/', $string, $matches );

		// Cycle through them and setup the date params.
		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $match ) {
				if ( ! empty( $match ) ) {
					$string = str_replace( "%{$match}", date( $match ), $string );
				}
			}
		}

		// Cycle through the double escaped characters and replace them.
		if ( ! empty( $matches[2] ) ) {
			foreach ( $matches[2] as $match ) {
				$string = str_replace( "%%{$match}", "%{$match}", $string );
			}
		}

		return $string;
	}
endif;

if ( ! function_exists( 'wpna_get_fbia_post' ) ) :

	/**
	 * A generic function for grabbing the Instant Article version of a post.
	 *
	 * @since 1.3.0
	 * @param  int|object $wp_post A post object or ID for grabbing the post.
	 * @return string The formatted Instant Article
	 */
	function wpna_get_fbia_post( $wp_post = null ) {

		// Set the transforming IA flag.
		wpna_transforming_ia( true );

		// Make sure we have the post.
		$wp_post = get_post( $wp_post );

		// When running in CRON the global post isn't set so set it.
		// @codingStandardsIgnoreLine
		$GLOBALS['post'] = $wp_post;

		// Make sure all the global WP values are populated.
		setup_postdata( $wp_post );

		// Variable name is important here. It's accessed in the template.
		$post = new WPNA_Facebook_Post( $wp_post->ID );

		/**
		 * Fired before an Instant Article post template is loaded.
		 *
		 * @var $post The post being converted
		 */
		do_action( 'wpna_pre_get_fbia_post', $post );

		// Generate the article and grab the HTML.
		ob_start();

		include wpna_locate_template( 'wpna-article' );

		$html_source = ob_get_clean();

		/**
		 * Fired after an Instant Article post template is loaded.
		 *
		 * @var $post The post being converted
		 * @var $html_source The post source
		 */
		do_action( 'wpna_post_get_fbia_post', $post, $html_source );

		// Reset just incase.
		wp_reset_postdata();

		// Reset the transforming IA flag.
		wpna_transforming_ia( false );

		return $html_source;
	}
endif;

if ( ! function_exists( 'wpna_transforming_ia' ) ) :

	/**
	 * Used to workout if an article is currently being converted or not.
	 *
	 * @since 1.3.3
	 * @param mixed $status Set the current covnersation status.
	 * @return bool Whether an article is currently being converted.
	 */
	function wpna_transforming_ia( $status = null ) {
		static $wpna_transforming_ia;

		if ( isset( $status ) ) {
			$wpna_transforming_ia = $status;
		}

		return $wpna_transforming_ia;
	}
endif;

if ( ! function_exists( 'wpna_should_convert_post_ia' ) ) :

	/**
	 * Whether a post should be converted to IA or not.
	 * All methods (RSS, API etc) use this function.
	 *
	 * @param WP_Post|int $wp_post Post object or Post ID.
	 * @return bool Whether the post should be converted or not.
	 */
	function wpna_should_convert_post_ia( $wp_post ) {
		// Make sure we've got a WP_Post object.
		$wp_post = get_post( $wp_post );

		// Return true by default.
		$return = true;

		// Check if post is an autosave.
		if ( wp_is_post_autosave( $wp_post ) ) {
			$return = false;
		}

		// If post is a revision, return.
		if ( wp_is_post_revision( $wp_post ) ) {
			$return = false;
		}

		// Check the post type.
		if ( ! in_array( get_post_type( $wp_post ), wpna_allowed_post_types(), true ) ) {
			$return = false;
		}

		// Only sync published posts.
		if ( 'publish' !== get_post_status( $wp_post ) ) {
			$return = false;
		}

		// Don't publish posts with password protection.
		if ( post_password_required( $wp_post ) ) {
			$return = false;
		}

		/**
		 * Whether this article should be converted to IA.
		 * Applies to ALL methods (RSS FEED, API, Mass Covnerter etc)
		 *
		 * @var bool
		 * @var WP_Post
		 */
		$return = apply_filters( 'wpna_should_convert_post_ia', $return, $wp_post );

		return $return;
	}
endif;

if ( ! function_exists( 'wpna_content_parser_get_placeholder' ) ) :

	/**
	 * A generic function for storing a content string and returning a unique id.
	 *
	 * This is used extensively in the content-parser to remove content strings
	 * we don't want to be parsed any further.
	 *
	 * @since 1.3.2
	 * @param  string $content The content to placeholder.
	 * @return string Generate a unique (enough) key for this placeholder
	 */
	function wpna_content_parser_get_placeholder( $content ) {
		global $_shortcode_content;

		if ( ! $_shortcode_content || ! is_array( $_shortcode_content ) ) {
			$_shortcode_content = array();
		}

		// Generate a unique (enough) key for this re.
		$shortcode_key = mt_rand();

		// Store the shortcode content in the global array so it can be replaced later.
		$_shortcode_content[ $shortcode_key ] = $content;

		// Return the unique key for this palceholder.
		return $shortcode_key;
	}
endif;

if ( ! function_exists( 'wpna_content_parser_get_placeholder_node' ) ) :

	/**
	 * A generic function for creating a placeholder for a DOMDocument node.
	 *
	 * This is used extensively in the content-parser to remove content strings
	 * we don't want to be parsed any further.
	 *
	 * @since 1.3.2
	 * @param  string $node The node to create the content placeholder for.
	 * @return string A DOMDocument node <pre> tag containg a placeholder key.
	 */
	function wpna_content_parser_get_placeholder_node( $node ) {
		// Save the node content.
		$output = $node->ownerDocument->saveXML( $node );

		// Remove CDATA tags.
		$output = str_replace( array( '<![CDATA[', ']]>' ), '', $output );

		// Get a unique palceholder marker for this content.
		$placeholder = wpna_content_parser_get_placeholder( $output );

		// Create a new <pre> element. wpautop doesn't wrap <pre> tags.
		$fragment = $node->ownerDocument->createElement( 'pre', $placeholder );

		return $fragment;
	}
endif;

if ( ! function_exists( 'wpna_premium_feature_notice' ) ) :

	/**
	 * Outputs an HTML notice prompting an upgrade to the premium version.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	function wpna_premium_feature_notice() {
		?>
		<hr />
		<h4>
		<?php echo wp_kses(
			__( '<span class="wpna-premium-feature">Premium Feature</span> This a premium only feature, visit <a href="https://wp-native-articles.com?utm_source=fplugin&utm_medium=upgrade_premium_notice" target="_blank">https://wp-native-articles.com</a> to upgrade and enable it.', 'wp-native-articles' ),
			array(
				'span' => array(
					'class' => true,
				),
				'a'    => array(
					'href'   => true,
					'target' => true,
				),
			)
		); ?>
		</h4>
		<hr />
		<?php
	}
endif;

if ( ! function_exists( 'wpna_parse_url' ) ) :

	/**
	 * WP function wp_prase_url() wasn't added until WP 4.4.
	 * The $component parameter wasn't added until 4.7.
	 *
	 * @param  string $url The URL to parse.
	 * @return array
	 */
	function wpna_parse_url( $url ) {
		// Preferable as it has extreme compatibility.
		if ( function_exists( 'wp_parse_url' ) ) {

			// Explode the URL into parts.
			$url_parts = wp_parse_url( $url );
		} else {
			// @codingStandardsIgnoreLine
			$url_parts = @parse_url( $url );
		}

		return $url_parts;
	}
endif;

if ( ! function_exists( 'wp_json_encode' ) ) :

	/**
	 * WP function wp_json_encode() wasn't added until WP 4.1.
	 * It's not a full port, we don't care much about the other two params.
	 *
	 * @param  mixed $data The data to encode.
	 * @param  int   $options Encode options.
	 * @param  int   $depth The depth to encode to.
	 * @return array
	 */
	function wp_json_encode( $data, $options = 0, $depth = 512 ) {
		// @codingStandardsIgnoreLine
		return json_encode( $data );
	}
endif;
