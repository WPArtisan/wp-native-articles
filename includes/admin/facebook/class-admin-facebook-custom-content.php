<?php
/**
 * Facebook admin class for allowing seperate content override for posts.
 *
 * @since 1.2.5
 * @package wp-native-articles
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allows a post content to be overridden specifially for IA.
 *
 * Registers a new meta box tab on the posts page and hooks into the
 * the_content to override it.
 *
 * @since  1.2.5
 */
class WPNA_Admin_Facebook_Custom_Content extends WPNA_Admin_Base implements WPNA_Admin_Interface {

	/**
	 * Hooks registered in this class.
	 *
	 * This method is auto called from WPNA_Admin_Base.
	 *
	 * @since 1.2.5
	 *
	 * @access public
	 * @return void
	 */
	public function hooks() {
		add_action( 'save_post', array( $this, 'save_post_meta' ), 10, 3 );

		add_filter( 'wpna_post_meta_box_content_tabs',              array( $this, 'post_meta_box_styling_settings' ), 10, 1 );
		add_filter( 'wpna_facebook_article_pre_the_content_filter', array( $this, 'override_post_content' ), 10, 1 );

		// Sanitize the post meta.
		add_filter( 'wpna_sanitize_post_meta_fbia_video_header',                    'esc_url_raw', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_custom_content_enable',           'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_custom_content',                  'wpna_sanitize_unsafe_html', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_related_article_one',             'esc_url_raw', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_related_article_one_sponsored',   'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_related_article_two',             'esc_url_raw', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_related_article_two_sponsored',   'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_related_article_three',           'esc_url_raw', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_related_article_three_sponsored', 'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_related_article_four',            'esc_url_raw', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_related_article_four_sponsored',  'wpna_switchval', 10, 1 );
	}

	/**
	 * Register the custom content tab for use in the post meta box.
	 *
	 * Just a filter that enables modification of the $tabs array.
	 * Would be better switched to a function.
	 *
	 * @since 1.0.0
	 * @todo Refactor. Tabs class?
	 *
	 * @access public
	 * @param  array $tabs Existing tabs.
	 * @return array
	 */
	public function post_meta_box_styling_settings( $tabs ) {

		$tabs[] = array(
			'key'      => 'fbia_custom_content',
			'title'    => esc_html__( 'Content', 'wp-native-articles' ),
			'callback' => array( $this, 'post_meta_box_custom_content_cb' ),
		);

		return $tabs;
	}

	/**
	 * Output HTML for the Styling post meta box tab.
	 *
	 * These values are set per article and override global defaults.
	 * Fields are currently hardcoded. The settings API won't work here.
	 * Fields have the same names as their global variables. This allows for
	 * checking if the global variables has been overridden at an article level
	 * or not.
	 *
	 * @since 1.1.0
	 * @todo Publish button
	 * @todo Swtich to hooks for fields
	 *
	 * @access public
	 * @param  WP_Post $post Global post object.
	 * @return void
	 */
	public function post_meta_box_custom_content_cb( $post ) {
		?>
		<div class="pure-form pure-form-aligned">

			<h3><?php esc_html_e( 'Video Header', 'wp-native-articles' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Use a video in the the article header instead of the feature image.', 'wp-native-articles' ); ?></p>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_video_header"><?php esc_html_e( 'Video URL', 'wp-native-articles' ); ?></label>
					<input type="url" name="_wpna_fbia_video_header" id="fbia_video_header" class="" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wpna_fbia_video_header', true ) ); ?>" />

					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_video_header' );
					?>
				</div>
			</fieldset>

			<h3><?php esc_html_e( 'Content Override', 'wp-native-articles' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Set custom content for this post to be used in the Instant Article. If enabled, this will be used instead of the content above. This is useful if you are getting lots of import errors in Facebook.', 'wp-native-articles' ); ?></p>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_custom_content_enable"><?php esc_html_e( 'Enable Content Override', 'wp-native-articles' ); ?></label>
					<input type="checkbox" name="_wpna_fbia_custom_content_enable" id="fbia_custom_content_enable" class="" value="on" <?php checked( 'on', get_post_meta( get_the_ID(), '_wpna_fbia_custom_content_enable', true ) ); ?>/>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_custom_content_enable' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_custom_content"><?php esc_html_e( 'Content', 'wp-native-articles' ); ?></label>
					<textarea name="_wpna_fbia_custom_content" id="fbia_custom_content" class="" value="" rows="6" cols="60"><?php echo esc_textarea( get_post_meta( get_the_ID(), '_wpna_fbia_custom_content', true ) ); ?></textarea>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_custom_content' );
					?>
				</div>
			</fieldset>

			<h3><?php esc_html_e( 'Related Articles', 'wp-native-articles' ); ?></h3>

			<p class="description">
				<?php esc_html_e( 'Manually specify the first four related articles for this post. Has to be a link to a post on the same site.', 'wp-native-articles' ); ?>
				<?php echo sprintf(
					wp_kses(
						__( 'See the <a target="_blank" href="%s">Official Documentation</a> for more information on related articles.', 'wp-native-articles' ),
						array( 'a' => array( 'href' => array(), 'target' => array() ) )
					),
					esc_url( 'https://developers.facebook.com/docs/instant-articles/reference/related-articles' )
				);?>
			</p>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_related_article_one"><?php esc_html_e( 'Related Article One', 'wp-native-articles' ); ?></label>
					<input type="url" name="_wpna_fbia_related_article_one" id="fbia_related_article_one" class="" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wpna_fbia_related_article_one', true ) ); ?>" />
					<label><?php esc_html_e( 'Sponsored', 'wp-native-articles' ); ?>
						<input type="checkbox" name="_wpna_fbia_related_article_one_sponsored" id="fbia_related_article_one_sponsored" class="" value="on" <?php checked( 'on', get_post_meta( get_the_ID(), '_wpna_fbia_related_article_one_sponsored', true ) ); ?> />
					</label>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_related_article_one' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_related_article_two"><?php esc_html_e( 'Related Article Two', 'wp-native-articles' ); ?></label>
					<input type="url" name="_wpna_fbia_related_article_two" id="fbia_related_article_two" class="" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wpna_fbia_related_article_two', true ) ); ?>" />
					<label><?php esc_html_e( 'Sponsored', 'wp-native-articles' ); ?>
						<input type="checkbox" name="_wpna_fbia_related_article_two_sponsored" id="fbia_related_article_two_sponsored" class="" value="on" <?php checked( 'on', get_post_meta( get_the_ID(), '_wpna_fbia_related_article_two_sponsored', true ) ); ?> />
					</label>

					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_related_article_two' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_related_article_three"><?php esc_html_e( 'Related Article Three', 'wp-native-articles' ); ?></label>
					<input type="url" name="_wpna_fbia_related_article_three" id="fbia_related_article_three" class="" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wpna_fbia_related_article_three', true ) ); ?>" />
					<label><?php esc_html_e( 'Sponsored', 'wp-native-articles' ); ?>
						<input type="checkbox" name="_wpna_fbia_related_article_three_sponsored" id="fbia_related_article_three_sponsored" class="" value="on" <?php checked( 'on', get_post_meta( get_the_ID(), '_wpna_fbia_related_article_three_sponsored', true ) ); ?> />
					</label>

					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_related_article_three' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_related_article_four"><?php esc_html_e( 'Related Article Four', 'wp-native-articles' ); ?></label>
					<input type="url" name="_wpna_fbia_related_article_four" id="fbia_related_article_four" class="" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wpna_fbia_related_article_four', true ) ); ?>" />
					<label><?php esc_html_e( 'Sponsored', 'wp-native-articles' ); ?>
						<input type="checkbox" name="_wpna_fbia_related_article_four_sponsored" id="fbia_related_article_four_sponsored" class="" value="on" <?php checked( 'on', get_post_meta( get_the_ID(), '_wpna_fbia_related_article_four_sponsored', true ) ); ?> />
					</label>

					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_related_article_four' );
					?>
				</div>
			</fieldset>

			<?php
			/**
			 * Add extra fields using this action. Or deregister this method
			 * altogether and register your own.
			 *
			 * @since 1.2.5
			 */
			do_action( 'wpna_post_meta_box_facebook_custom_content_footer' );
			?>

			<?php wp_nonce_field( 'wpna_save_post_meta-' . get_the_ID(), '_wpna_nonce' ); ?>
		</div>

		<?php
	}

	/**
	 * Save the new post meta field data.
	 *
	 * Creates a unique filter for each value that then uses hooks to provide
	 * sanitization. Values are then stored in the post meta.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  int  $post_id The post ID.
	 * @param  post $post    The post object.
	 * @param  bool $update  Whether this is an existing post being updated or not.
	 * @return void
	 */
	public function save_post_meta( $post_id, $post, $update ) {

		// Don't save if it's an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Don't save if it's a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Get the nonce.
		$nonce = filter_input( INPUT_POST, '_wpna_nonce', FILTER_SANITIZE_STRING );

		// Verify that the input is coming from the proper form.
		// Since an nonce will only include alpha-numeric characters, we use sanitize_key() to sanitize it.
		// Automatically strips out any quotes or slashes so unslash isn't needed.
		if ( ! $nonce || ! wp_verify_nonce( sanitize_key( $nonce ), 'wpna_save_post_meta-' . $post_id ) ) {
			return;
		}

		// Get the post type.
		$post_type = filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_STRING );

		// Get post types we want to add the box to.
		$allowed_post_types = wpna_allowed_post_types();

		// Check this is a valid post type.
		if ( ! in_array( $post_type, $allowed_post_types, true ) ) {
			return;
		}

		// Make sure the user has permissions to post.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Nothing fancy, let's just buid our own data array.
		$field_keys = array(
			'_wpna_fbia_video_header',
			'_wpna_fbia_custom_content_enable',
			'_wpna_fbia_custom_content',
			'_wpna_fbia_related_article_one',
			'_wpna_fbia_related_article_one_sponsored',
			'_wpna_fbia_related_article_two',
			'_wpna_fbia_related_article_two_sponsored',
			'_wpna_fbia_related_article_three',
			'_wpna_fbia_related_article_three_sponsored',
			'_wpna_fbia_related_article_four',
			'_wpna_fbia_related_article_four_sponsored',
		);

		/**
		 * Use this filter to add any custom fields to the data.
		 *
		 * @since 1.0.0
		 *
		 * @var array  $field_keys The keys to check.
		 * @var object $post       Whether this is an existing post being updated or not.
		 * @var bool   $update     Whether it's an update or new post.
		 */
		$field_keys = apply_filters( 'wpna_post_meta_box_facebook_custom_content_field_keys', $field_keys, $post, $update );

		// Return all the values from $_POST that have keys in field_keys.
		$values = array_intersect_key( wp_unslash( $_POST ), array_flip( $field_keys ) ); // Input var okay.

		// Sanitize using the same hook / filter method as the global options.
		// Each key has a unique filter that can be hooked into to validate.
		$sanitized_values = array();

		foreach ( $values as $key => $value ) {

			// If the value is empty then remove any post meta for this key.
			// This means it will inherit the global defaults.
			if ( empty( $value ) ) {
				continue;
			}

			// Workout the correct filtername from the $key.
			$filter_name = str_replace( '_wpna_', 'wpna_sanitize_post_meta_', $key );

			// Check if a filter exists.
			if ( has_filter( $filter_name ) ) {

				/**
				 * Use filters to allow sanitizing of individual options.
				 *
				 * All sanitization hooks should be registerd in the hooks() method.
				 * This is more for correct sanitization than setting default values.
				 * Use the hooks below for that.
				 *
				 * @since 1.0.0
				 *
				 * @param mixed  $value  The value to sanitize
				 * @param string $key    The options name
				 * @param array  $values All options
				 */
				$sanitized_values[ $key ] = apply_filters( $filter_name, $value, $key, $values );
			} else {
				// If no filter was found then throw an error.
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// @codingStandardsIgnoreLine
					trigger_error( esc_html( sprintf( 'Filter missing for `%s`', $filter_name ) ) );
				}
			}
		}

		// Only save the data that has actually been set.
		// Otherwise we create unnecessary meta rows.
		$sanitized_values = array_filter( $sanitized_values );

		/**
		 * Filter the values before they're saved.
		 *
		 * @since 1.1.4
		 * @var array Postmeta to save for this post.
		 */
		$sanitized_values = apply_filters( 'wpna_sanitize_post_meta_facebook_custom_content', $sanitized_values, $field_keys, $post );

		// Work out which values haven't been set so they can be removed.
		$remove_fields = array_diff( $field_keys, array_keys( $sanitized_values ) );

		// Remove these fields. They will inherit the global values.
		foreach ( $remove_fields as $meta_key ) {
			delete_post_meta( $post_id, $meta_key );
		}

		// Save the new meta.
		foreach ( $sanitized_values as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

	}

	/**
	 * Override the post content.
	 *
	 * In Instant Articles, outputs the custom content that's been set
	 * instead of the default post content if it's enabled.
	 *
	 * This is replaced before the content filters so they still run.
	 * Means you can use shortcodes etc.
	 *
	 * @param  string $content Original post content.
	 * @return string The content to use in the instant article.
	 */
	public function override_post_content( $content ) {
		// Check to see if custom post content is enabled.
		$enable = get_post_meta( get_the_ID(), '_wpna_fbia_custom_content_enable', true );

		// If it's enabled, override the post content with it.
		if ( wpna_switch_to_boolean( $enable ) ) {
			$content = get_post_meta( get_the_ID(), '_wpna_fbia_custom_content', true );
		}

		return $content;
	}

}
