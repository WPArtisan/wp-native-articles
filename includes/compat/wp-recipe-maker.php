<?php
/**
 * WP Recipe Maker compatibility.
 *
 * Injects a formatted recipe into a post via shortcodes.
 * Override the shortcode output and correctly format for IA.
 *
 * @link https://en-gb.wordpress.org/plugins/wp-recipe-maker/
 * @since 1.3.0.
 * @package wp-native-articles
 */

if ( ! function_exists( 'wpna_wp_recipe_maker_add_override_shortcodes' ) ) :

	/**
	 * Override the `quads` shortcode in IA.
	 *
	 * @param array  $override_tags Shortocde tags to override.
	 * @param string $content       Current post content.
	 * @return array $override_tags
	 */
	function wpna_wp_recipe_maker_add_override_shortcodes( $override_tags, $content ) {
		$override_tags['wprm-recipe'] = 'wpna_wp_recipe_maker_shortcode_override';
		return $override_tags;
	}
endif;
add_filter( 'wpna_facebook_article_setup_wrap_shortcodes_override_tags', 'wpna_wp_recipe_maker_add_override_shortcodes', 10, 2 );

if ( ! function_exists( 'wpna_wp_recipe_maker_shortcode_override' ) ) :

	/**
	 * Recipe maker embeds the finished HTML in the post content and turns it
	 * back to a shortcode for the Editor. This is super annoying as
	 * we want the shortcode. This turns it back into a shortcode.
	 *
	 * @param  string $content The post content.
	 * @return string The post content.
	 */
	function wpna_wp_recipe_maker_disable_fallback( $content ) {

		if ( class_exists( 'WPRM_Fallback_Recipe' ) ) {

			preg_match_all( WPRM_Fallback_Recipe::get_fallback_regex(), $content, $matches );
			foreach ( $matches[0] as $key => $match ) {
				$id = $matches[1][ $key ];
				preg_match_all( '/<!--WPRM Recipe ' . $id . '-->.?<!--(.+?)-->/ms', $match, $args );

				$shortcode_options = isset( $args[1][0] ) ? ' ' . $args[1][0] : '';

				$content = str_replace( $match, '[wprm-recipe id="' . $id . '"' . $shortcode_options . ']', $content );
			}
		}

		return $content;

	}
endif;
add_filter( 'wpna_facebook_article_pre_the_content_filter', 'wpna_wp_recipe_maker_disable_fallback', 8, 1 );

if ( ! function_exists( 'wpna_wp_recipe_maker_shortcode_override' ) ) :

	/**
	 * Grabs a recipe and correctly formats it.
	 *
	 * A lot of it is a direct copy from the plugin.
	 *
	 * @param  array $atts Shortocde tags to override.
	 * @return string Formatted recipe
	 */
	function wpna_wp_recipe_maker_shortcode_override( $atts ) {
		$atts = shortcode_atts( array(
			'id'       => 'random',
			'template' => '',
		), $atts, 'wprm-recipe' );

		$recipe_template = trim( $atts['template'] );

		// Get recipe.
		if ( 'random' === $atts['id'] ) {
			if ( function_exists( 'vip_get_random_posts' ) ) {
				$posts = vip_get_random_posts( 1, WPRM_POST_TYPE );
			} else {
				$posts = new WP_Query( array(
					'post_type'      => WPRM_POST_TYPE,
					'posts_per_page' => 1,
					// @codingStandardsIgnoreLine
					'orderby'        => 'rand',
				) );
				$posts = $query->posts;
			}

			$recipe_id = isset( $query->posts[0] ) ? $query->posts[0]->ID : 0;
		} elseif ( 'latest' === $atts['id'] ) {
			$posts = new WP_Query(array(
				'post_type'      => WPRM_POST_TYPE,
				'posts_per_page' => 1,
			));

			$recipe_id = isset( $query->posts[0] ) ? $query->posts[0]->ID : 0;
		} else {
			$recipe_id = intval( $atts['id'] );
		}

		$output = '';

		if ( class_exists( 'WPRM_Recipe_Manager' ) ) {
			$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

			if ( $recipe ) {
				$output = wpna_wp_recipe_maker_get_template( $recipe );
			}
		}

		return $output;

	}
endif;

if ( ! function_exists( 'wpna_wp_recipe_maker_get_template' ) ) :

	/**
	 * Grabs a template for a recipe.
	 *
	 * @param  object $recipe The recipe for format.
	 * @return string The formatted recipie.
	 */
	function wpna_wp_recipe_maker_get_template( $recipe ) {
		ob_start();
		?>
			<h1><?php echo esc_html( $recipe->name() ); ?></h1>

			<p><?php echo esc_html( strip_tags( $recipe->summary() ) ); ?></p>

			<?php
			// Get and display all the taxonomies.
			$taxonomies = WPRM_Taxonomies::get_taxonomies(); ?>
			<p>
			<?php foreach ( $taxonomies as $taxonomy => $options ) :
				$key   = substr( $taxonomy, 5 );
				$terms = $recipe->tags( $key );

				if ( count( $terms ) > 0 ) : ?>
					<strong><?php echo esc_html( WPRM_Template_Helper::label( $key . '_tags', $options['singular_name'] ) ); ?></strong>&nbsp;&nbsp;&nbsp;
					<?php foreach ( $terms as $index => $term ) {
						if ( 0 !== $index ) {
							echo ', ';
						}
						echo esc_html( $term->name );
}; ?>
				<br />
			<?php endif; // Count.
			endforeach; // Taxonomies. ?>
			</p>

			<p>
				<?php if ( $recipe->prep_time() ) : ?>
					<strong><?php echo esc_html( WPRM_Template_Helper::label( 'prep_time' ) ); ?></strong>&nbsp;&nbsp;&nbsp;<?php echo esc_html( strip_tags( $recipe->prep_time_formatted() ) ); ?>
				<?php endif; // Prep time. ?>
				<?php if ( $recipe->cook_time() ) : ?>
					<br />
					<strong><?php echo esc_html( WPRM_Template_Helper::label( 'cook_time' ) ); ?></strong>&nbsp;&nbsp;&nbsp;<?php echo esc_html( strip_tags( $recipe->cook_time_formatted() ) ); ?>
				<?php endif; // Cook time. ?>
				<?php if ( $recipe->total_time() ) : ?>
					<br />
					<strong><?php echo esc_html( WPRM_Template_Helper::label( 'total_time' ) ); ?></strong>&nbsp;&nbsp;&nbsp;<?php echo esc_html( strip_tags( $recipe->total_time_formatted() ) ); ?>
				<?php endif; // Total time. ?>
			</p>

			<p>
				<?php if ( $recipe->servings() ) : ?>
					<strong><?php echo esc_html( WPRM_Template_Helper::label( 'servings' ) ); ?></strong>&nbsp;&nbsp;&nbsp;<?php echo esc_html( strip_tags( $recipe->servings() ) ); ?> <?php echo esc_html( $recipe->servings_unit() ); ?>
				<?php endif; // servings. ?>
				<?php if ( $recipe->calories() ) : ?>
					<br />
					<strong><?php echo esc_html( WPRM_Template_Helper::label( 'calories' ) ); ?></strong>&nbsp;&nbsp;&nbsp;<?php echo esc_html( $recipe->calories() ); ?> <?php esc_html_e( 'kcal', 'wp-native-articles' ); ?>
				<?php endif; // calories. ?>
				<?php if ( $recipe->author() ) : ?>
					<br />
					<strong><?php echo esc_html( WPRM_Template_Helper::label( 'author' ) ); ?></strong>&nbsp;&nbsp;&nbsp;<?php echo esc_html( $recipe->author() ); ?>
				<?php endif; // author. ?>
			</p>


			<?php
			$ingredients = $recipe->ingredients();
			if ( count( $ingredients ) > 0 ) : ?>
				<h1><?php echo esc_html( WPRM_Template_Helper::label( 'ingredients' ) ); ?></h1>
				<?php foreach ( $ingredients as $ingredient_group ) : ?>
					<?php if ( $ingredient_group['name'] ) : ?>
					<h2><?php echo esc_html( $ingredient_group['name'] ); ?></h2>
					<?php endif; // Ingredient group name. ?>
					<ul>
						<?php foreach ( $ingredient_group['ingredients'] as $ingredient ) : ?>
						<li>
							<?php if ( $ingredient['amount'] ) : ?>
							<span><?php echo esc_html( $ingredient['amount'] ); ?></span>
							<?php endif; // Ingredient amount. ?>
							<?php if ( $ingredient['unit'] ) : ?>
							<span><?php echo esc_html( $ingredient['unit'] ); ?></span>
							<?php endif; // Ingredient unit. ?>
							<span><?php echo wp_kses_post( WPRM_Template_Helper::ingredient_name( $ingredient, true ) ); ?></span>
							<?php if ( $ingredient['notes'] ) : ?>
							<i><?php echo esc_html( $ingredient['notes'] ); ?></i>
							<?php endif; // Ingredient notes. ?>
						</li>
						<?php endforeach; // Ingredients. ?>
					</ul>
				<?php endforeach; // Ingredient groups. ?>
			<?php endif; // Ingredients. ?>


			<?php
			$instructions = $recipe->instructions();
			if ( count( $instructions ) > 0 ) : ?>
				<h1><?php echo esc_html( WPRM_Template_Helper::label( 'instructions' ) ); ?></h1>
				<?php foreach ( $instructions as $instruction_group ) : ?>

					<?php if ( $instruction_group['name'] ) : ?>
						<h2><?php echo esc_html( $instruction_group['name'] ); ?></h2>
					<?php endif; // Instruction group name. ?>

					<?php
						// We're making a sudo list as IA can't have
						// images in list elements.
						$i = 1;
					?>
					<?php foreach ( $instruction_group['instructions'] as $instruction ) : ?>
						<?php if ( $instruction['text'] ) : ?>
							<p><?php echo esc_html( $i++ ); ?>.&nbsp;&nbsp;&nbsp;<?php echo esc_html( strip_tags( $instruction['text'] ) ); ?></p>
						<?php endif; // Instruction text. ?>

						<?php if ( $instruction['image'] ) : ?>
							<figure>
								<?php
								// @codingStandardsIgnoreLine
								echo WPRM_Template_Helper::instruction_image( $instruction, 'full' );
								?>
							</figure>
						<?php endif; // Instruction image. ?>

					<?php endforeach; // Instructions. ?>

				<?php endforeach; // Instruction groups. ?>
			<?php endif; // Instructions. ?>

			<?php if ( $recipe->notes() ) : ?>
				<h2><?php echo esc_html( WPRM_Template_Helper::label( 'notes' ) ); ?></h2>
				<p><?php echo wp_kses_post( $recipe->notes() ); ?></p>
			<?php endif; // Notes. ?>

			<?php if ( WPRM_Settings::get( 'show_nutrition_label' ) ) : ?>

				<?php
				/**
				 * Template for the Nutrition Label.
				 *
				 * @link   http://bootstrapped.ventures
				 * @since  1.0.0
				 *
				 * @package WP_Recipe_Maker_Premium
				 * @subpackage WP_Recipe_Maker_Premium/templates/public
				 */

				$nutrition = $recipe->nutrition();

				$has_nutritional_information = false;
				$main_info                   = false;
				$sub_info                    = false;

				foreach ( WPRMP_Nutrition_Label::$nutrition_units as $field => $unit ) {
					if ( isset( $nutrition[ $field ] ) && false !== $nutrition[ $field ] ) {
						$$field = $nutrition[ $field ];

						if ( isset( WPRMP_Nutrition_Label::$daily_values[ $field ] ) ) {
							$perc_field  = $field . '_perc';
							$$perc_field = round( floatval( $$field ) / WPRMP_Nutrition_Label::$daily_values[ $field ] * 100 );
						}

						// Flags to know what to output.
						$has_nutritional_information = true;
						if ( in_array( $field, array( 'fat', 'cholesterol', 'sodium', 'potassium', 'carbohydrate', 'protein' ), true ) ) {
							$main_info = true;
						} elseif ( in_array( $field, array( 'vitamin_a', 'vitamin_c', 'calcium', 'iron' ), true ) ) {
							$sub_info = true;
						}
					}
				}

				if ( $has_nutritional_information ) :

					// Calculate calories if not set.
					$fat_calories = isset( $fat ) ? round( floatval( $fat ) * 9 ) : 0;

					if ( ! isset( $calories ) ) {
						$proteins = isset( $protein ) ? $protein : 0;
						$carbs    = isset( $carbohydrates ) ? $carbohydrates : 0;
						$calories = ( ( $proteins + $carbs ) * 4 ) + $fat_calories;
					}
				?>

					<h2><?php esc_html_e( 'Nutrition Facts', 'wp-native-articles' ); ?></h2>
					<p>
						<?php esc_html_e( 'Amount Per Serving', 'wp-native-articles' ); ?>
						<?php
						if ( isset( $serving_size ) ) {
							$unit = isset( $nutrition['serving_unit'] ) && $nutrition['serving_unit'] ? $nutrition['serving_unit'] : 'g';
							echo ' (' . esc_html( $serving_size ) . ' ' . esc_html( $unit ) . ')';
						}
						?>
					</p>
					<p>
						<strong><?php esc_html_e( 'Calories', 'wp-native-articles' ); ?></strong> <?php echo esc_html( $calories ); ?>kcal
					</p>
					<?php if ( $fat_calories ) : ?>
						<p>
							-&nbsp;<?php esc_html_e( 'Calories from Fat', 'wp-native-articles' ); ?> <?php echo esc_html( $fat_calories ); ?>kcal
						</p>
					<?php endif; // Fat calories. ?>
					<?php if ( $main_info ) : ?>
						<?php if ( isset( $fat ) ) : ?>
							<p>
								<strong><?php esc_html_e( 'Total Fat', 'wp-native-articles' ); ?></strong> <?php echo esc_html( $fat ); ?>g <i>(<?php echo esc_html( $fat_perc ); ?>%)</i>
							</p>
							<?php if ( isset( $saturated_fat ) ) : ?>
							<p>
								-&nbsp;<?php esc_html_e( 'Saturated Fat', 'wp-native-articles' ); ?> <?php echo esc_html( $saturated_fat ); ?>g <i>(<?php echo esc_html( $saturated_fat_perc ); ?>%)</i>
							</p>
							<?php endif; // Saturated Fat. ?>
							<?php if ( isset( $trans_fat ) ) : ?>
							<p>
								-&nbsp;<?php esc_html_e( 'Trans Fat', 'wp-native-articles' ); ?> <?php echo esc_html( $trans_fat ); ?>g
							</p>
							<?php endif; // Trans Fat. ?>
							<?php if ( isset( $polyunsaturated_fat ) ) : ?>
							<p>
								-&nbsp;<?php esc_html_e( 'Polyunsaturated Fat', 'wp-native-articles' ); ?> <?php echo esc_html( $polyunsaturated_fat ); ?>g
							</p>
							<?php endif; // Polyunsaturated Fat. ?>
							<?php if ( isset( $monounsaturated_fat ) ) : ?>
							<p>
								-&nbsp;<?php esc_html_e( 'Monounsaturated Fat', 'wp-native-articles' ); ?> <?php echo esc_html( $monounsaturated_fat ); ?>g
							</p>
							<?php endif; // Monounsaturated Fat. ?>
						<?php endif; // Fat. ?>
						<?php if ( isset( $cholesterol ) ) : ?>
						<p>
							<strong><?php esc_html_e( 'Cholesterol', 'wp-native-articles' ); ?></strong> <?php echo esc_html( $cholesterol ); ?>mg <i>(<?php echo esc_html( $cholesterol_perc ); ?>%)</i>
						</p>
						<?php endif; // Cholesterol. ?>
						<?php if ( isset( $sodium ) ) : ?>
						<p>
							<strong><?php esc_html_e( 'Sodium', 'wp-native-articles' ); ?></strong> <?php echo esc_html( $sodium ); ?>mg <i>(<?php echo esc_html( $sodium_perc ); ?>%)</i>
						</p>
						<?php endif; // Sodium. ?>
						<?php if ( isset( $potassium ) ) : ?>
						<p>
							<strong><?php esc_html_e( 'Potassium', 'wp-native-articles' ); ?></strong> <?php echo esc_html( $potassium ); ?>mg <i>(<?php echo esc_html( $potassium_perc ); ?>%)</i>
						</p>
						<?php endif; // Potassium. ?>
						<?php if ( isset( $carbohydrates ) ) : ?>
						<p>
							<strong><?php esc_html_e( 'Total Carbohydrates', 'wp-native-articles' ); ?></strong> <?php echo esc_html( $carbohydrates ); ?>g <i>(<?php echo esc_html( $carbohydrates_perc ); ?>%)</i>
						</p>
						<?php endif; // Carbohydrates. ?>
						<?php if ( isset( $fiber ) ) : ?>
						<p>
							<strong><?php esc_html_e( 'Dietary Fiber', 'wp-native-articles' ); ?></strong> <?php echo esc_html( $fiber ); ?>g <i>(<?php echo esc_html( $fiber_perc ); ?>%)</i>
						</p>
						<?php endif; // Fiber. ?>
						<?php if ( isset( $sugar ) ) : ?>
						<p>
							<strong><?php esc_html_e( 'Sugars', 'wp-native-articles' ); ?></strong> <?php echo esc_html( $sugar ); ?>g
						</p>
						<?php endif; // Sugar. ?>
						<?php if ( isset( $protein ) ) : ?>
						<p>
							<strong><?php esc_html_e( 'Protein', 'wp-native-articles' ); ?></strong> <?php echo esc_html( $protein ); ?>g <i>(<?php echo esc_html( $protein_perc ); ?>%)</i>
						</p>
						<?php endif; // Protein. ?>
					<?php endif; // Main info. ?>
					<?php if ( $sub_info ) : ?>
						<?php if ( isset( $vitamin_a ) ) : ?>
						<p>
							<strong><?php esc_html_e( 'Vitamin A', 'wp-native-articles' ); ?></strong> <?php echo esc_html( $vitamin_a ); ?>%
						</p>
						<?php endif; // Vitamin A. ?>
						<?php if ( isset( $vitamin_c ) ) : ?>
						<p>
							<strong><?php esc_html_e( 'Vitamin C', 'wp-native-articles' ); ?></strong> <?php echo esc_html( $vitamin_c ); ?>%
						</p>
						<?php endif; // Vitamin C. ?>
						<?php if ( isset( $calcium ) ) : ?>
						<p>
							<strong><?php esc_html_e( 'Calcium', 'wp-native-articles' ); ?></strong> <?php echo esc_html( $calcium ); ?>%
						</p>
						<?php endif; // Calcium. ?>
						<?php if ( isset( $iron ) ) : ?>
						<p>
							<strong><?php esc_html_e( 'Iron', 'wp-native-articles' ); ?></strong> <?php echo esc_html( $iron ); ?>%
						</p>
						<?php endif; // Iron. ?>
					<?php endif; // Sub info. ?>

					<p><i>* <?php esc_html_e( 'Percent Daily Values are based on a 2000 calorie diet.', 'wp-native-articles' ); ?></i></p>

				<?php endif; // Has nutritional information. ?>

			<?php endif; ?>

		<?php
		$output = ob_get_clean();

		return $output;
	}
endif;
