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
	 * @param  array $attr Shortocde tags to override.
	 * @return string Formatted recipe
	 */
	function wpna_wp_recipe_maker_shortcode_override( $attr ) {
		$atts = shortcode_atts( array(
			'id'       => 'random',
			'template' => '',
		), $atts, 'wprm_recipe' );

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
			}

			$recipe_id = isset( $posts[0] ) ? $posts[0]->ID : 0;
		} elseif ( 'latest' === $atts['id'] ) {
			$posts = new WP_Query(array(
				'post_type'      => WPRM_POST_TYPE,
				'posts_per_page' => 1,
			));

			$recipe_id = isset( $posts[0] ) ? $posts[0]->ID : 0;
		} else {
			$recipe_id = intval( $atts['id'] );
		}

		$output = '';

		$recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

		if ( $recipe ) {
			$output = wpna_wp_recipe_maker_get_template( $recipe );
		}

		return $output;

	}
endif;

if ( ! function_exists( 'wpna_wp_recipe_maker_get_template' ) ) :

	/**
	 * Grabs a template for a recipe.
	 *
	 * @param  object $recipe The recipe for format.
	 * @return void
	 */
	function wpna_wp_recipe_maker_get_template( $recipe ) {
		?>
			<h1><?php echo esc_html( $recipe->name() ); ?></h1>

			<p><?php echo esc_html( strip_tags( $recipe->summary() ) ); ?></p>

			<?php
			// All these options are inside an iFrame so we can use SVG icons.
			?>
			<figure class="op-interactive">
				<iframe class="column-width" width="360">
					<?php
					// Get the default styles.
					// Add in a few of our own so it looks good in IA.
					// @codingStandardsIgnoreLine
					echo WPRM_Template_Manager::get_template_styles( $recipe, 'screen' );
					?>
					<style>
					* {
						font-family: Helvetica, sans-serif;
						font-size: 1rem;
					}
					.wprm-recipe-details-container {
						margin-bottom: 15px;
					}
					.wprm-recipe-details-container div {
						margin-bottom: 5px;
					}
					.wprm-recipe-details-icon svg {
						vertical-align: middle;
						width: 16px;
						height: 16px;
					}
					.wprm-recipe-details-name {
						display: inline-block;
						font-weight: 700;
						min-width: 130px;
					}
					</style>

					<div class="wprm-recipe-details-container wprm-recipe-tags-container">
						<?php
						$taxonomies = WPRM_Taxonomies::get_taxonomies();

						foreach ( $taxonomies as $taxonomy => $options ) :
							$key = substr( $taxonomy, 5 );
							$terms = $recipe->tags( $key );

							if ( count( $terms ) > 0 ) : ?>
							<div class="wprm-recipe-<?php echo esc_attr( $key ); ?>-container">
								<span class="wprm-recipe-details-icon"><?php include( WPRM_DIR . 'assets/icons/tag.svg' ); ?></span> <span class="wprm-recipe-details-name wprm-recipe-<?php echo esc_attr( $key ); ?>-name"><?php echo esc_html( WPRM_Template_Helper::label( $key . '_tags', $options['singular_name'] ) ); ?></span>
								<span class="wprm-recipe-<?php echo esc_attr( $key ); ?>"<?php echo esc_html( WPRM_Template_Helper::tags_meta( $key ) ); ?>>
									<?php foreach ( $terms as $index => $term ) {
										if ( 0 !== $index ) {
											echo ', ';
										}
										echo esc_html( $term->name );
}
									?>
								</span>
							</div>
						<?php endif; // Count.
						endforeach; // Taxonomies. ?>
					</div>

					<div class="wprm-recipe-details-container wprm-recipe-times-container">
						<?php if ( $recipe->prep_time() ) : ?>
						<div class="wprm-recipe-prep-time-container">
							<span class="wprm-recipe-details-icon"><?php include( WPRM_DIR . 'assets/icons/knife.svg' ); ?></span> <span class="wprm-recipe-details-name wprm-recipe-prep-time-name"><?php echo esc_html( WPRM_Template_Helper::label( 'prep_time' ) ); ?></span> <?php echo esc_html( $recipe->prep_time_formatted() ); ?>
						</div>
						<?php endif; // Prep time. ?>
						<?php if ( $recipe->cook_time() ) : ?>
						<div class="wprm-recipe-cook-time-container">
							<span class="wprm-recipe-details-icon"><?php include( WPRM_DIR . 'assets/icons/pan.svg' ); ?></span> <span class="wprm-recipe-details-name wprm-recipe-cook-time-name"><?php echo esc_html( WPRM_Template_Helper::label( 'cook_time' ) ); ?></span> <?php echo esc_html( $recipe->cook_time_formatted() ); ?>
						</div>
						<?php endif; // Cook time. ?>
						<?php if ( $recipe->total_time() ) : ?>
						<div class="wprm-recipe-total-time-container">
							<span class="wprm-recipe-details-icon"><?php include( WPRM_DIR . 'assets/icons/clock.svg' ); ?></span> <span class="wprm-recipe-details-name wprm-recipe-total-time-name"><?php echo esc_html( WPRM_Template_Helper::label( 'total_time' ) ); ?></span> <?php echo esc_html( $recipe->total_time_formatted() ); ?>
						</div>
						<?php endif; // Total time. ?>
					</div>

					<div class="wprm-recipe-details-container">
						<?php if ( $recipe->servings() ) : ?>
						<div class="wprm-recipe-servings-container">
							<span class="wprm-recipe-details-icon"><?php include( WPRM_DIR . 'assets/icons/cutlery.svg' ); ?></span>
							<span class="wprm-recipe-details-name wprm-recipe-servings-name"><?php echo esc_html( WPRM_Template_Helper::label( 'servings' ) ); ?></span>
							<span itemprop="recipeYield">
								<span class="wprm-recipe-details wprm-recipe-servings"><?php echo esc_html( $recipe->servings() ); ?></span>
								<span class="wprm-recipe-details-unit wprm-recipe-servings-unit"><?php echo esc_html( $recipe->servings_unit() ); ?></span>
							</span>
						</div>
						<?php endif; // Servings. ?>
						<?php if ( $recipe->calories() ) : ?>
						<div class="wprm-recipe-calories-container" itemprop="nutrition" itemscope itemtype="http://schema.org/NutritionInformation">
							<span class="wprm-recipe-details-icon"><?php include( WPRM_DIR . 'assets/icons/battery.svg' ); ?></span>
							<span class="wprm-recipe-details-name wprm-recipe-calories-name"><?php echo esc_html( WPRM_Template_Helper::label( 'calories' ) ); ?></span>
							<span itemprop="calories">
								<span class="wprm-recipe-details wprm-recipe-calories"><?php echo esc_html( $recipe->calories() ); ?></span>
								<span class="wprm-recipe-details-unit wprm-recipe-calories-unit"><?php esc_html_e( 'kcal', 'wp-recipe-maker' ); ?></span>
							</span>
						</div>
						<?php endif; // Calories. ?>
						<?php if ( $recipe->author() ) : ?>
						<div class="wprm-recipe-author-container">
							<span class="wprm-recipe-details-icon"><?php include( WPRM_DIR . 'assets/icons/chef-hat.svg' ); ?></span>
							<span class="wprm-recipe-details-name wprm-recipe-author-name"><?php echo esc_html( WPRM_Template_Helper::label( 'author' ) ); ?></span>
							<span class="wprm-recipe-details wprm-recipe-author"><?php echo esc_html( $recipe->author() ); ?></span>
						</div>
						<?php endif; // Author. ?>
					</div>

				</iframe>
			</figure>

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
							<span><?php echo esc_html( WPRM_Template_Helper::ingredient_name( $ingredient, true ) ); ?></span>
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
				<figure class="op-interactive">
					<iframe class="column-width">
						<?php // @codingStandardsIgnoreLine ?>
						<link rel="stylesheet" type="text/css" href="<?php echo esc_url( WPRMP_URL . 'assets/css/public/public.min.css' ); ?>"/>
						<style>
						.wprm-nutrition-label, html body .wprm-recipe-container .wprm-nutrition-label {
							max-width: 100%;
							width: 350px;
							padding: 5px;
							margin: 0 auto;
						}
						</style>
						<?php echo do_shortcode( WPRM_Template_Helper::nutrition_label( $recipe->id() ) ); ?>
					</iframe>
				</figure>
			<?php endif; ?>

		<?php
	}
endif;
