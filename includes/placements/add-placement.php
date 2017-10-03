<?php
/**
 * Template for the Add Placement Page.
 *
 * @package     wp-native-articles
 * @subpackage  Includes/Placements
 * @copyright   Copyright (c) 2017, WPArtisan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">

<h2><?php esc_html_e( 'Add New Placement', 'wp-native-articles' ); ?> - <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpna_placements' ) ); ?>" class="button-secondary"><?php esc_html_e( 'Go Back', 'wp-native-articles' ); ?></a></h2>

<?php wpna_premium_feature_notice(); ?>

<form id="wpna-add-placement" action="" method="POST">

	<?php do_action( 'wpna_add_palcement_form_top' ); ?>

	<table class="form-table">
		<tbody>

			<?php do_action( 'wpna_add_placement_form_before_name' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="wpna-placement-name"><?php esc_html_e( 'Name', 'wp-native-articles' ); ?></label>
				</th>
				<td>
					<input name="name" required="required" id="wpna-placement-name" class="regular-text" type="text" value="" disabled="true" />
					<p class="description"><?php esc_html_e( 'The name of this placement', 'wp-native-articles' ); ?></p>
				</td>
			</tr>

			<?php do_action( 'wpna_add_placement_form_before_status' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="wpna-placement-status"><?php esc_html_e( 'Status', 'wp-native-articles' ); ?></label>
				</th>
				<td>
					<select required="required" name="status" id="wpna-placement-status" disabled="true">
						<option value="active"><?php esc_html_e( 'Active', 'wp-native-articles' ); ?></option>
						<option value="inactive"><?php esc_html_e( 'Inactive', 'wp-native-articles' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Whether the placement is active or not', 'wp-native-articles' ); ?></p>
				</td>
			</tr>

			<?php do_action( 'wpna_add_placement_form_before_code_type' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="wpna-name"><?php esc_html_e( 'Code Type', 'wp-native-articles' ); ?></label>
				</th>
				<td>
					<div id="wpna-placement-content-type">
						<select name="content_type" class="wpna-placement-select-toggle">
							<option value="custom"><?php esc_html_e( 'Custom Content', 'wp-native-articles' ); ?></option>
							<option value="related_posts"><?php esc_html_e( 'Related Posts', 'wp-native-articles' ); ?></option>
							<!-- <option value=""></option> -->
						</select>

						<?php do_action( 'wpna_add_placement_form_before_content' ); ?>

						<div id="wpna-placement-custom" class="wpna-placement-content-form">
							<label for="wpna-placement-custom-content">
								<h4><?php esc_html( 'Custom Content', 'wp-native-articles' ); ?></h4>
							</label>
							<label>
								<textarea id="wpna-placement-custom-content" name="content" class="large-text code" rows="10" cols="50" ></textarea>
								<p class="description">
									<?php echo wp_kses(
										__( 'The code you wish to insert. This should be in <strong>valid</strong> Instant Article format.', 'wp-native-articles' ),
										array( 'strong' => array() )
									);?>
								</p>
							</label>
							<hr />
						</div>

					</div>

				</td>
			</tr>

			<?php do_action( 'wpna_add_placement_form_before_position' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="wpna-name"><?php esc_html( 'Position', 'wp-native-articles' ); ?></label>
				</th>
				<td>
					<div id="wpna-placement-position-top-wrap">
						<label>
							<input type="checkbox" name="position_top" id="wpna-placement-position-top" class="wpna-placement-position" value="true" />
							<?php esc_html_e( 'Top', 'wp-native-articles' ); ?>
						</label>
					</div>

					<div id="wpna-placement-position-bottom-wrap">
						<label>
							<input type="checkbox" name="position_bottom" id="wpna-placement-position-bottom" class="wpna-placement-position" value="true" />
							<?php esc_html_e( 'Bottom', 'wp-native-articles' ); ?>
						</label>
					</div>


					<div id="wpna-placement-position-paragraph-wrap">
						<label>
							<input type="checkbox" name="position_paragraph_toggle" id="wpna-placement-position-paragraph-toggle" class="wpna-placement-toggle" value="true" />
							<?php esc_html_e( 'After Paragraph', 'wp-native-articles' ); ?>
						</label>

						<div id="wpna-placement-position-paragraph-form" class="hidden">
							<h4><?php esc_html_e( 'After Paragraph', 'wp-native-articles' ); ?></h4>
							<label>
								<p><?php esc_html_e( 'Insert after this paragraph', 'wp-native-articles' ); ?></p>
								<input type="number" name="position_paragraph" id="wpna-placement-position-paragraph" class="wpna-placement-position" value="0" min="0" step="0" disabled="true"/>
							</label>
							<hr />
						</div>
					</div>

					<div id="wpna-placement-position-words-wrap">
						<label>
							<input type="checkbox" name="position_words_toggle" id="wpna-placement-position-words-toggle" class="wpna-placement-toggle" value="true" />
							<?php esc_html_e( 'After Words', 'wp-native-articles' ); ?>
						</label>

						<div id="wpna-placement-position-words-form" class="hidden">
							<h4><?php esc_html_e( 'After Words', 'wp-native-articles' ); ?></h4>
							<label>
								<p><?php esc_html_e( 'Will be rounded to the nearest paragraph.', 'wp-native-articles' ); ?></p>
								<input type="number" name="position_words" id="wpna-placement-position-words" class="wpna-placement-position" value="150" min="0" step="0" disabled="true"/>
							</label>
							<hr />
						</div>
					</div>

					<?php do_action( 'wpna_add_placement_form_position' ); ?>

					<p class="description"><?php esc_html_e( 'Where the code is positioned within the Instant Article.', 'wp-native-articles' ); ?></p>
				</td>
			</tr>

			<?php do_action( 'wpna_add_placement_form_before_filters' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="wpna-placement-filters"><?php esc_html_e( 'Add Filter', 'wp-native-articles' ); ?></label>
				</th>
				<td>
					<div id="wpna-placement-filter-category-wrap">

						<label>
							<input type="checkbox" name="filter_category" id="wpna-placement-filter-category-toggle" class="wpna-placement-toggle" value="true" />
							<?php esc_html_e( 'Add Category Filter', 'wp-native-articles' ); ?>
						</label>

						<div id="wpna-placement-filter-category-form" class="hidden">

							<?php $terms = get_categories( array( 'hide_empty' => false ) ); ?>

							<h4><?php esc_html_e( 'Categories Filter', 'wp-native-articles' ); ?></h4>

							<label>
								<p><?php esc_html_e( 'Include Categories', 'wp-native-articles' ); ?></p>
								<select name="category__in[]" multiple="multiple" class="select2" style="width:25em;" disabled="true">
									<option value=""></option>
									<?php foreach ( $terms as $term ) :?>
										<option value="<?php echo esc_attr( $term->term_id );?>">
											<?php echo esc_html( $term->name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</label>

							<p class="description">
								<?php echo wp_kses(
									__( 'Only insert this placement into posts that <strong>HAVE</strong> these categories.', 'wp-native-articles' ),
									array( 'strong' => array() )
								);?>
							</p>

							<br />
							<br />

							<label>
								<p><?php esc_html_e( 'Exclude Categories', 'wp-native-articles' ); ?></p>
								<select name="category__not_in[]" multiple="multiple" class="select2" style="width:25em;" disabled="true">
									<option value=""></option>
									<?php foreach ( $terms as $term ) :?>
										<option value="<?php echo esc_attr( $term->term_id );?>">
											<?php echo esc_html( $term->name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</label>

							<p class="description">
								<?php echo wp_kses(
									__( 'Only insert this placement into posts that <strong>DO NOT HAVE</strong> these categories.', 'wp-native-articles' ),
									array( 'strong' => array() )
								);?>
							</p>

							<hr />

						</div>

					</div> <!-- ./wpna-placement-filter-category-wrap -->

					<div class="wpna-placement-filter-tag-wrap">
						<label>
							<input type="checkbox" name="filter_tag" id="wpna-placement-filter-tag-toggle" class="wpna-placement-toggle" value="true" />
							<?php esc_html_e( 'Add Tag Filter', 'wp-native-articles' ); ?>
						</label>

						<div id="wpna-placement-filter-tag-form" class="hidden">

							<?php $terms = get_tags( array( 'hide_empty' => false ) ); ?>

							<h4><?php esc_html_e( 'Tag Filter', 'wp-native-articles' ); ?></h4>

							<label>
								<p><?php esc_html_e( 'Include Tags', 'wp-native-articles' ); ?></p>
								<select name="tag__in[]" multiple="multiple" class="select2" style="width:25em;" disabled="true">
									<option value=""></option>
									<?php foreach ( $terms as $term ) : ?>
										<option value="<?php echo esc_attr( $term->term_id );?>">
											<?php echo esc_html( $term->name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</label>

							<p class="description">
								<?php echo wp_kses(
									__( 'Only insert this placement into posts that <strong>HAVE</strong> these tags.', 'wp-native-articles' ),
									array( 'strong' => array() )
								);?>
							</p>

							<br />
							<br />

							<label>
								<p><?php esc_html_e( 'Exclude Tags', 'wp-native-articles' ); ?></p>
								<select name="tag__not_in[]" multiple="multiple" class="select2" style="width:25em;" disabled="true">
									<option value=""></option>
									<?php foreach ( $terms as $term ) : ?>
										<option value="<?php echo esc_attr( $term->term_id );?>">
											<?php echo esc_html( $term->name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</label>

							<p class="description">
								<?php echo wp_kses(
									__( 'Only insert this placement into posts that <strong>DO NOT HAVE</strong> these tags.', 'wp-native-articles' ),
									array( 'strong' => array() )
								);?>
							</p>

							<hr />

						</div>

					</div> <!-- ./wpna-placement-filter-tag-wrap -->

					<div class="wpna-placement-filter-author-wrap">
						<label>
							<input type="checkbox" name="filter_author" id="wpna-placement-filter-author-toggle" class="wpna-placement-toggle" value="true" />
							<?php esc_html_e( 'Add Author Filter', 'wp-native-articles' ); ?>
						</label>

						<div id="wpna-placement-filter-author-form" class="hidden">

							<?php $terms = get_users( array( 'orderby' => 'nicename', 'fields' => array( 'ID', 'display_name' ) ) ); ?>

							<h4><?php esc_html_e( 'Author Filter', 'wp-native-articles' ); ?></h4>

							<label>
								<p><?php esc_html_e( 'Include Authors', 'wp-native-articles' ); ?></p>
								<select name="author__in[]" multiple="multiple" class="select2" style="width:25em;" disabled="true">
									<option value=""></option>
									<?php foreach ( $terms as $term ) :?>
										<option value="<?php echo esc_attr( $term->ID );?>">
											<?php echo esc_html( $term->display_name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</label>

							<p class="description">
								<?php echo wp_kses(
									__( 'Only insert this placement into posts that <strong>HAVE</strong> these authors.', 'wp-native-articles' ),
									array( 'strong' => array() )
								);?>
							</p>

							<br />
							<br />

							<label>
								<p><?php esc_html_e( 'Exclude Authors', 'wp-native-articles' ); ?></p>
								<select name="author__not_in[]" multiple="multiple" class="select2" style="width:25em;" disabled="true">
									<option value=""></option>
									<?php foreach ( $terms as $term ) :?>
										<option value="<?php echo esc_attr( $term->ID );?>">
											<?php echo esc_html( $term->display_name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</label>

							<p class="description">
								<?php echo wp_kses(
									__( 'Only insert this placement into posts that <strong>DO NOT HAVE</strong> these authors.', 'wp-native-articles' ),
									array( 'strong' => array() )
								);?>
							</p>

							<hr />

						</div>

					</div> <!-- ./wpna-placement-filter-author-wrap -->

					<div class="wpna-placement-filter-author-wrap">
						<label>
							<input type="checkbox" name="filter_custom_enable" id="wpna-placement-filter-custom-toggle" class="wpna-placement-toggle" value="true" />
							<?php esc_html_e( 'Add Custom Filter', 'wp-native-articles' ); ?>
						</label>

						<div id="wpna-placement-filter-custom-form" class="hidden">

							<h4><?php esc_html_e( 'Custom Filter', 'wp-native-articles' ); ?></h4>

							<input type="text" name="filter_custom" class="regular-text" placeholder="e.g. post_type=movie&amp;tag_slug__in=action" value="" disabled="true"/>
							<p class="description"><?php esc_html_e( 'Use this field to create a filter from custom WP_Query parameters. It is very powerful but be careful.', 'wp-native-articles' ); ?></p>

							<hr />

						</div>

					</div> <!-- ./wpna-placement-filter-custom-wrap -->

					<?php do_action( 'wpna_add_placement_form_filters' ); ?>

					<p class="description"><?php esc_html_e( 'Add filters to your placement to restrict the posts that it applies to.', 'wp-native-articles' ); ?></p>

				</td>
			</tr>

			<?php do_action( 'wpna_add_placement_form_before_start_date' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="wpna-placement-start-date"><?php esc_html_e( 'Start Date', 'wp-native-articles' ); ?></label>
				</th>
				<td>
					<input type="date" id="wpna-placement-start-date" name="start_date" value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>" disabled="true"/>
					<p class="description"><?php printf( esc_html__( 'Enter the date when this placement becomes active in the format %s.', 'wp-native-articles' ), '<strong>dd/mm/yyyy</strong>' ); ?></p>
				</td>
			</tr>

			<?php do_action( 'wpna_add_placement_form_before_end_date' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="wpna-placement-end-date"><?php esc_html_e( 'End Date', 'wp-native-articles' ); ?></label>
				</th>
				<td>
					<input type="date" id="wpna-placement-end-date" name="end_date" value="" min="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>" disabled="true"/>
					<p class="description"><?php printf( esc_html__( 'Enter the date when this placement becomes inactive in the format %s. For no end, leave blank.', 'wp-native-articles' ), '<strong>dd/mm/yyyy</strong>' ); ?></p>
				</td>
			</tr>

		</tbody>
	</table>

	<?php do_action( 'wpna_add_placement_form_bottom' ); ?>

	<p class="submit">
		<input type="submit" name="submit" value="<?php esc_html_e( 'Add Placement', 'wp-native-articles' ); ?>" class="button-primary" disabled="true"/>
	</p>

</form>
</div>
