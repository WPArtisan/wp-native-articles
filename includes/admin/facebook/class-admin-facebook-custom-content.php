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
		add_filter( 'wpna_post_meta_box_content_tabs', array( $this, 'post_meta_box_styling_settings' ), 10, 1 );
		add_filter( 'wpna_post_meta_box_fields',       array( $this, 'post_meta_box_fields' ), 10, 1 );

		add_filter( 'wpna_facebook_article_pre_the_content_filter', array( $this, 'override_post_content' ), 10, 1 );

		// Sanitize the post meta.
		add_filter( 'wpna_sanitize_post_meta_fbia_video_header',                    'esc_url_raw', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_custom_content_enable',           'wpna_switchval', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_custom_content',                  'wpna_sanitize_unsafe_html', 10, 1 );
		add_filter( 'wpna_sanitize_post_meta_fbia_custom_sponsor',                  'sanitize_text_field', 10, 1 );
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
	 * Reigster fields that should be saved in the post meta.
	 *
	 * @since 1.3.5
	 * @access public
	 * @param  array $fields Fields that should be saved.
	 * @return array $fields
	 */
	public function post_meta_box_fields( $fields ) {
		$fields[] = 'fbia_video_header';
		$fields[] = 'fbia_custom_content_enable';
		$fields[] = 'fbia_custom_content';
		$fields[] = 'fbia_custom_sponsor';
		$fields[] = 'fbia_related_article_one';
		$fields[] = 'fbia_related_article_one_sponsored';
		$fields[] = 'fbia_related_article_two';
		$fields[] = 'fbia_related_article_two_sponsored';
		$fields[] = 'fbia_related_article_three';
		$fields[] = 'fbia_related_article_three_sponsored';
		$fields[] = 'fbia_related_article_four';
		$fields[] = 'fbia_related_article_four_sponsored';

		return $fields;
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
					<input type="url" name="wpna_options[fbia_video_header]" id="fbia_video_header" class="" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wpna_fbia_video_header', true ) ); ?>" />

					<p class="wpna-video-type-warning" style="display: none;">
						<span class="wpna-label wpna-label-warning"><?php esc_html_e( 'Warning', 'wp-native-articles' ); ?></span>
						<i><b><?php esc_html_e( 'Video headers have to link directly to a video file and cannot be embeds (e.g. YouTube).', 'wp-native-articles' ); ?></b></i>
					</p>

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
					<input type="checkbox" name="wpna_options[fbia_custom_content_enable]" id="fbia_custom_content_enable" class="" value="on" <?php checked( 'on', get_post_meta( get_the_ID(), '_wpna_fbia_custom_content_enable', true ) ); ?>/>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_custom_content_enable' );
					?>
				</div>
			</fieldset>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_custom_content"><?php esc_html_e( 'Content', 'wp-native-articles' ); ?></label>
					<textarea name="wpna_options[fbia_custom_content]" id="fbia_custom_content" class="" value="" rows="6" cols="60"><?php echo esc_textarea( get_post_meta( get_the_ID(), '_wpna_fbia_custom_content', true ) ); ?></textarea>
					<?php
					// Show a notice if the option has been overridden.
					wpna_post_option_overridden_notice( 'fbia_custom_content' );
					?>
				</div>
			</fieldset>

			<h3><?php esc_html_e( 'Sponsor', 'wp-native-articles' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Set a custom article sponsor. Should be a link to a Facebook Page.', 'wp-native-articles' ); ?></p>
			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_custom_sponsor"><?php esc_html_e( 'Facebook Page URL', 'wp-native-articles' ); ?></label>
					<input type="text" name="wpna_options[fbia_custom_sponsor]" id="fbia_custom_sponsor" class="" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wpna_fbia_custom_sponsor', true ) ); ?>" />

					<p class="wpna-custom-sponser-warning" style="display: none;">
						<span class="wpna-label wpna-label-warning"><?php esc_html_e( 'Warning', 'wp-native-articles' ); ?></span>
						<i><b><?php esc_html_e( 'Sponsor URL should be a direct link to this Facebook page.', 'wp-native-articles' ); ?></b></i>
					</p>

				</div>
			</fieldset>

			<h3><?php esc_html_e( 'Related Articles', 'wp-native-articles' ); ?></h3>

			<p class="description">
				<?php esc_html_e( 'Manually specify the first four related articles for this post. Has to be a link to a post on the same site.', 'wp-native-articles' ); ?>
				<?php echo sprintf(
					wp_kses(
						// translators: Placeholder is the URL to the document page.
						__( 'See the <a target="_blank" href="%s">Official Documentation</a> for more information on related articles.', 'wp-native-articles' ),
						array(
							'a' => array(
								'href'   => array(),
								'target' => array(),
							),
						)
					),
					esc_url( 'https://developers.facebook.com/docs/instant-articles/reference/related-articles' )
				);?>
			</p>

			<fieldset>
				<div class="pure-control-group">
					<label for="fbia_related_article_one"><?php esc_html_e( 'Related Article One', 'wp-native-articles' ); ?></label>
					<input type="url" name="wpna_options[fbia_related_article_one]" id="fbia_related_article_one" class="" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wpna_fbia_related_article_one', true ) ); ?>" />
					<label><?php esc_html_e( 'Sponsored', 'wp-native-articles' ); ?>
						<input type="checkbox" name="wpna_options[fbia_related_article_one_sponsored]" id="fbia_related_article_one_sponsored" class="" value="on" <?php checked( 'on', get_post_meta( get_the_ID(), '_wpna_fbia_related_article_one_sponsored', true ) ); ?> />
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
					<input type="url" name="wpna_options[fbia_related_article_two]" id="fbia_related_article_two" class="" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wpna_fbia_related_article_two', true ) ); ?>" />
					<label><?php esc_html_e( 'Sponsored', 'wp-native-articles' ); ?>
						<input type="checkbox" name="wpna_options[fbia_related_article_two_sponsored]" id="fbia_related_article_two_sponsored" class="" value="on" <?php checked( 'on', get_post_meta( get_the_ID(), '_wpna_fbia_related_article_two_sponsored', true ) ); ?> />
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
					<input type="url" name="wpna_options[fbia_related_article_three]" id="fbia_related_article_three" class="" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wpna_fbia_related_article_three', true ) ); ?>" />
					<label><?php esc_html_e( 'Sponsored', 'wp-native-articles' ); ?>
						<input type="checkbox" name="wpna_options[fbia_related_article_three_sponsored]" id="fbia_related_article_three_sponsored" class="" value="on" <?php checked( 'on', get_post_meta( get_the_ID(), '_wpna_fbia_related_article_three_sponsored', true ) ); ?> />
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
					<input type="url" name="wpna_options[fbia_related_article_four]" id="fbia_related_article_four" class="" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wpna_fbia_related_article_four', true ) ); ?>" />
					<label><?php esc_html_e( 'Sponsored', 'wp-native-articles' ); ?>
						<input type="checkbox" name="wpna_options[fbia_related_article_four_sponsored]" id="fbia_related_article_four_sponsored" class="" value="on" <?php checked( 'on', get_post_meta( get_the_ID(), '_wpna_fbia_related_article_four_sponsored', true ) ); ?> />
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

		</div>

		<?php
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
