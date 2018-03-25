<?php
/**
 * HTML form for eidting a transformer.
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

// Need both of these.
global $shortcode_tags, $wp_filter;

// Copy the shortcode tags and sort them.
$shortcodes = $shortcode_tags;
ksort( $shortcodes );

// Setup the content filters.
$content_filters = array();
if ( isset( $wp_filter['the_content'] ) ) {
	foreach ( $wp_filter['the_content'] as $action => $functions ) {
		foreach ( $functions as $function_name => $callback ) {
			if ( ! in_array( $function_name, array( 'wptexturize', 'convert_smilies', 'wpautop', 'shortcode_unautop', 'do_shortcode' ), true ) ) {
				$name                                = wpna_get_filter_nice_name( $callback );
				$content_filters[ $action ][ $name ] = $callback;
			}
		}
	}
}

// Get the Facebook SDK rules.
$facebook_transformers = wpna_facebook_sdk_rules();

// Get the transformer to edit.
// @codingStandardsIgnoreLine
if ( ! empty( $_GET['transformer'] ) ) {
	$transformer_id = absint( $_GET['transformer'] );
} else {
	wp_die( esc_html__( 'Something went wrong.', 'wp-native-articles' ) );
}

// Load the transformer.
$transformer = wpna_get_transformer( $transformer_id );
?>
<div class="wrap">

	<h2><?php esc_html_e( 'Edit Transformer', 'wp-native-articles' ); ?> - <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_transformers' ) ); ?>" class="button-secondary"><?php esc_html_e( 'Go Back', 'wp-native-articles' ); ?></a></h2>

	<form id="wpna-edit-transformer" action="" method="POST">

		<?php do_action( 'wpna_edit_transformer_form_top' ); ?>

		<table class="form-table">
			<tbody>

				<?php do_action( 'wpna_edit_transformer_form_before_name' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="wpna-transformer-name"><?php esc_html_e( 'Name', 'wp-native-articles' ); ?></label>
					</th>
					<td>
						<input type="text" id="wpna-transformer-name" name="name" value="<?php echo esc_attr( $transformer->name ); ?>" class="regular-text" />
						<p class="description"><?php esc_html_e( 'The name of this transformer.', 'wp-native-articles' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'wpna_edit_transformer_form_before_status' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="wpna-transformer-status"><?php esc_html_e( 'Status', 'wp-native-articles' ); ?></label>
					</th>
					<td>
						<select id-"wpna-transformer-status" name="status">
							<option value="active"<?php selected( $transformer->status, 'active' ); ?>><?php esc_html_e( 'Active', 'wp-native-articles' ); ?></option>
							<option value="inactive"<?php selected( $transformer->status, 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'wp-native-articles' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Whether the transformer is active or not.', 'wp-native-articles' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'wpna_edit_transformer_form_before_type' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="wpna-transformer-type"><?php esc_html_e( 'Type', 'wp-native-articles' ); ?></label>
					</th>
					<td>
						<select id="wpna-transformer-type" class="js-trigger-type js-trigger-tip" name="type">
							<option value="post_content"<?php selected( $transformer->type, 'post_content' ); ?>><?php esc_html_e( 'Post Content', 'wp-native-articles' ); ?></option>
							<option value="shortcode"<?php selected( $transformer->type, 'shortcode' ); ?>><?php esc_html_e( 'Shortcode', 'wp-native-articles' ); ?></option>
							<option value="content_filter"<?php selected( $transformer->type, 'content_filter' ); ?>><?php esc_html_e( 'the_content() filter', 'wp-native-articles' ); ?></option>
							<option value="custom"<?php selected( $transformer->type, 'custom' ); ?>><?php esc_html_e( 'Custom Selector', 'wp-native-articles' ); ?></option>
						</select>

						<p class="description"><?php esc_html_e( 'The part of the post content to apply the transformer to.', 'wp-native-articles' ); ?></p>

						<?php if ( version_compare( PHP_VERSION, '5.4', '<' ) ) : ?>
							<p class="js-target-type js-custom hidden">
								<span class="wpna-label wpna-label-warning"><?php esc_html_e( 'Warning', 'wp-native-articles' ); ?></span>
								<i><b><?php esc_html_e( 'This feature requires PHP 5.4 or greater to function. Please upgrade PHP before using it.', 'wp-native-articles' ); ?></b></i>
							</p>
						<?php endif; ?>

						<div class="js-tip-wpna-transformer-type hidden">
							<hr />
							<p class="description">
								<?php esc_html_e( 'Post Content', 'wp-native-articles' ); ?> - <?php esc_html_e( 'The entire post content. For use with the "Pattern Matcher."', 'wp-native-articles' ); ?>
								<br />
								<?php esc_html_e( 'Shortcode', 'wp-native-articles' ); ?> - <?php esc_html_e( 'Transform an individual shortcode.', 'wp-native-articles' ); ?>
								<br />
								<?php esc_html_e( 'the_content() filter', 'wp-native-articles' ); ?> - <?php esc_html_e( 'Remove an individual filter from the post content.', 'wp-native-articles' ); ?>
								<br />
								<?php esc_html_e( 'Custom Selector', 'wp-native-articles' ); ?> - <?php esc_html_e( 'Use CSS or XPath selectors to target a particular element in the post.', 'wp-native-articles' ); ?>
							</p>
							<hr />
						</div>

					</td>
				</tr>

				<?php do_action( 'wpna_edit_transformer_form_before_selector' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="wpna-transformer-selector"><?php esc_html_e( 'Selector', 'wp-native-articles' ); ?></label>
					</th>
					<td>

						<div class="js-target-type js-post_content <?php echo 'post_content' !== $transformer->type ? 'hidden' : ''; ?>">
							<i>~</i>
						</div>

						<div class="js-target-type js-shortcode <?php echo 'shortcode' !== $transformer->type ? 'hidden' : ''; ?>">
							<select name="selector_shortcode">
								<?php foreach ( $shortcodes as $tag => $callback ) : ?>
									<option value="<?php echo esc_html( $tag ); ?>"<?php selected( $transformer->selector, $tag ); ?>>[<?php echo esc_html( $tag ); ?>]</option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'The shortcode to apply the transformation to.', 'wp-native-articles' ); ?></p>
						</div>

						<div class="js-target-type js-content_filter <?php echo 'content_filter' !== $transformer->type ? 'hidden' : ''; ?>">
							<select name="selector_content_filter">
								<?php foreach ( $content_filters as $action => $functions ) : ?>
									<optgroup label="<?php echo esc_attr( $action ); ?>">
										<?php foreach ( $functions as $function_name => $callback ) : ?>
											<option value="<?php echo esc_html( $function_name ); ?>"<?php selected( $transformer->selector, $function_name ); ?>><?php echo esc_html( $function_name ); ?></option>
										<?php endforeach; ?>
									</optgroup>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'The the_content() filter to apply the transformation to.', 'wp-native-articles' ); ?></p>
						</div>

						<div class="js-target-type js-custom <?php echo 'custom' !== $transformer->type ? 'hidden' : ''; ?>">
							<input type="text" class="regular-text" name="selector_custom" placeholder="e.g. span.bold" value="<?php echo esc_attr( $transformer->selector ); ?>" />
							<p class="description"><?php esc_html_e( 'Use CSS or XPath selectors to target a particular element.', 'wp-native-articles' ); ?></p>
						</div>

					</td>
				</tr>

				<?php do_action( 'wpna_edit_transformer_form_before_rule' ); ?>

				<tr>
					<th scope="row" valign="top">
						<label for="wpna-transformer-rule"><?php esc_html_e( 'Rule', 'wp-native-articles' ); ?></label>
					</th>
					<td>
						<select id="wpna-transformer-rule" class="js-rule js-trigger-rule" name="rule">
							<optgroup label="<?php esc_html_e( 'Custom Rules', 'wp-native-articles' ); ?>">
								<option value="bypass_parser"<?php selected( $transformer->rule, 'bypass_parser' ); ?>><?php esc_html_e( 'Bypass Parser', 'wp-native-articles' ); ?></option>
								<option value="pattern_matcher"<?php selected( $transformer->rule, 'pattern_matcher' ); ?>><?php esc_html_e( 'Pattern Matcher', 'wp-native-articles' ); ?></option>
								<option value="remove"<?php selected( $transformer->rule, 'remove' ); ?>><?php esc_html_e( 'Remove', 'wp-native-articles' ); ?></option>
							</optgroup>
							<?php if ( $facebook_transformers ) : ?>
								<optgroup label="<?php esc_html_e( 'Facebook Rules', 'wp-native-articles' ); ?>" class="js-trigger-rule-facebook_transformers">
									<?php foreach ( $facebook_transformers as $facebook_transformer => $options ) : ?>
										<option value="<?php echo esc_attr( $facebook_transformer ); ?>"<?php selected( $transformer->rule, $facebook_transformer ); ?>><?php echo esc_html( $facebook_transformer ); ?></option>
									<?php endforeach; ?>
								</optgroup>
							<?php endif; ?>
						</select>

						<div class="js-target-rule js-remove <?php echo 'remove' !== $transformer->rule ? 'hidden' : ''; ?>">
							<p class="description"><?php esc_html_e( 'Completely remove this content from the Instant Articles.', 'wp-native-articles' ); ?></p>
						</div>

						<div class="js-target-rule js-bypass_parser <?php echo 'bypass_parser' !== $transformer->rule ? 'hidden' : ''; ?>">
							<p class="description"><?php esc_html_e( 'Do not transform this content for Instant Articles. Insert into the article exactly as it is.', 'wp-native-articles' ); ?></p>
						</div>

						<div class="js-target-rule js-facebook_transformers <?php echo in_array( $transformer->rule, array( 'pattern_matcher', 'remove', 'bypass_parser' ), true ) ? 'hidden' : ''; ?>">
							<p class="description"><?php esc_html_e( 'Rule from the official Facebook Instant Article Transformer.', 'wp-native-articles' ); ?></p>
						</div>

					</td>
				</tr>

				<tr class="js-target-rule js-pattern_matcher <?php echo 'pattern_matcher' !== $transformer->rule ? 'hidden' : ''; ?>">
					<th scope="row" valign="top">
						<label for="wpna-transformer-search-for"><?php esc_html_e( 'Search for', 'wp-native-articles' ); ?></label>
					</th>
					<td>
						<input type="text" id="wpna-transformer-search-for" class="regular-text" name="search_for" placeholder='e.g. <img data-src="%s" src="" />' value="<?php echo esc_attr( $transformer->get_meta( 'search_for' ) ); ?>" />
						<p class="description">
							<?php echo wp_kses(
								// translators: Placeholder is literal.
								__( 'Match wildcard strings with <b>%s</b>.', 'wp-native-articles' ),
								array(
									'b' => array(),
								)
							);
							?>
						</p>
					</td>
				</tr>

				<tr class="js-target-rule js-pattern_matcher <?php echo 'pattern_matcher' !== $transformer->rule ? 'hidden' : ''; ?>">
					<th scope="row" valign="top">
						<label for="wpna-transformer-replace-for"><?php esc_html_e( 'Replace with', 'wp-native-articles' ); ?></label>
					</th>
					<td>
						<input type="text" id="wpna-transformer-replace-for" class="regular-text" name="replace_with" placeholder='e.g. <img src="${1}" />' value="<?php echo esc_attr( $transformer->get_meta( 'replace_with' ) ); ?>" />
						<p class="description"><?php esc_html_e( 'Each wildcard matched pattern is numerically named in order. E.g. ${1}, ${2} etc', 'wp-native-articles' ); ?></p>

						<div>
							<hr />
							<p class="description">
								<?php esc_html_e( 'The Pattern Matcher is powerful way to transform parts of your content.', 'wp-native-articles' ); ?>
								<br />
								<?php echo wp_kses(
									// translators: Placeholder is literal.
									__( 'You can use wildcard placeholders <b>%s</b> to search for, and capture, troublesome portions of your content.', 'wp-native-articles' ),
									array(
										'b' => array(),
									)
								);
								?>
								<br />
								<?php esc_html_e( 'Each placeholder is then captured and numbered in order allowing you to create a replacement template.', 'wp-native-articles' ); ?>
							</p>
							<br />
							<p class="description"><b><?php esc_html_e( 'Example', 'wp-native-articles' ); ?></b></p>
							<p class="description">
								<?php echo sprintf(
									wp_kses(
										// translators: Placeholder is example code.
										__( 'Match the following pattern within your content: <code>%s</code>', 'wp-native-articles' ),
										array(
											'code' => array(),
										)
									),
									esc_html( '<span class=”bold”>%s</span>' )
								); ?>
							</p>
							<p class="description">
								<?php echo sprintf(
									wp_kses(
										// translators: Placeholder is example code.
										__( 'Replace all occurances of the matched pattern with the following: <code>%s</code>', 'wp-native-articles' ),
										array(
											'code' => array(),
										)
									),
									esc_html( '<b>${1}</b>' )
								); ?>
							</p>
							<br />
							<p class="description">
								<?php echo sprintf(
									wp_kses(
										// translators: Placeholder is the URL to the transformers article.
										__( 'You can read more about the Pattern Matcher and how to use it <a target="_blank" href="%s">here</a>.', 'wp-native-articles' ),
										array(
											'a' => array(
												'href'   => array(),
												'target' => array(),
											),
										)
									),
									esc_url( 'https://wp-native-articles.com/features/transformers/#pattern_matcher' )
								); ?>
							</p>
							<hr />
						</div>

					</td>
				</tr>

				<tr class="js-target-rule js-facebook_transformers <?php echo in_array( $transformer->rule, array( 'pattern_matcher', 'remove', 'bypass_parser' ), true ) ? 'hidden' : ''; ?>">
					<th scope="row" valign="top">
						<label for="wpna-transformer-properties"><?php echo esc_html_e( 'Properties', 'wp-native-articles' ); ?></label>
					</th>
					<td>
						<textarea class="js-trigger-properties" rows="8" cols="50" name="properties" placeholder='{
	"anchor.href" : {
		"type" : "string",
		"selector" : "span.custom-href",
		"attribute": "data-link"
	}
}
'><?php echo esc_textarea( $transformer->get_meta( 'properties' ) ); ?></textarea>
						<p class="js-json-warning js-json-invalid hidden" style="color:red;"><strong><?php esc_html_e( 'Not valid JSON', 'wp-native-articles' ); ?></strong></p>
						<p class="js-json-warning js-json-valid hidden" style="color:#46b450;"><strong><?php esc_html_e( 'Valid JSON', 'wp-native-articles' ); ?></strong></p>
						<p class="description"><?php esc_html_e( 'Set custom properties for the transformer rule here. Must be a valid a JSON object.', 'wp-native-articles' ); ?></p>
						<p class="description">
							<?php echo sprintf(
								wp_kses(
									// translators: Placeholder is the URL to the properties page.
									__( 'A full list of available properties for each transformer rule can be found <a target="_blank" href="%s">here</a>.', 'wp-native-articles' ),
									array(
										'a' => array(
											'href'   => array(),
											'target' => array(),
										),
									)
								),
								esc_url( 'https://developers.facebook.com/docs/instant-articles/sdk/transformer-rules#rule-classes' )
							); ?>
						</p>
					</td>
				</tr>

			</tbody>
		</table>

		<?php do_action( 'wpna_edit_transformer_form_bottom' ); ?>

		<p class="submit">
			<input type="hidden" name="transformer_id" value="<?php echo absint( $transformer->ID ); ?>" />
			<input type="hidden" name="wpna-action" value="edit_transformer" />
			<input type="hidden" name="wpna-redirect" value="<?php echo esc_url( admin_url( 'admin.php?page=wpna_transformers' ) ); ?>" />
			<input type="hidden" name="wpna-transformer-nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpna_transformer_nonce' ) ); ?>" />
			<input type="submit" name="submit" value="<?php esc_html_e( 'Update Transformer', 'wp-native-articles' ); ?>" class="button-primary" />
		</p>

	</form>
</div>
