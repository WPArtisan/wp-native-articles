<?php
/**
 * Template for the content of each post.
 *
 * This is used by both the API and the RSS feed. Expects $post to be an
 * instance of WPNA_Facebook_Post. This template can be overridden by creating
 * a template of the same name in your theme folder.
 *
 * @since 1.0.0
 * @package wp-native-articles
 */

?>
<!doctype html>
<?php if ( $post->is_rtl() ) : ?>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>" dir="rtl" prefix="op: http://media.facebook.com/op#">
<?php else : ?>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>" prefix="op: http://media.facebook.com/op#">
<?php endif; ?>
	<head>
		<meta charset="<?php echo esc_attr( get_option( 'blog_charset' ) ); ?>">
		<link rel="canonical" href="<?php echo esc_url( $post->get_permalink() ); ?>">
		<meta property="op:generator" content="wp-native-articles" />
		<meta property="op:generator:version" content="<?php echo esc_attr( WPNA_VERSION ); ?>" />
		<meta property="op:generator:transformer" content="wp-native-articles-parser"/>
		<meta property="op:generator:transformer:version" content="<?php echo esc_attr( WPNA_PARSER_VERSION ); ?>"/>
		<meta property="op:markup_version" content="v1.0"/>
		<meta property="fb:article_style" content="<?php echo esc_attr( $post->get_style() ); ?>">
		<?php if ( wpna_switch_to_boolean( wpna_get_post_option( $post->get_the_ID(), 'fbia_enable_ads' ) ) ) : ?>

			<?php if ( wpna_switch_to_boolean( wpna_get_post_option( $post->get_the_ID(), 'fbia_auto_ad_placement' ) ) ) : ?>
				<?php
				// Get the ad density.
				$density = wpna_get_post_option( $post->get_the_ID(), 'fbia_ad_density', 'default' ); ?>

			<meta property="fb:use_automatic_ad_placement" content="enable=true ad_density=<?php echo esc_attr( $density ); ?>">
			<?php endif; ?>

			<?php if ( $recirculation_ad = wpna_get_post_option( $post->get_the_ID(), 'fbia_recirculation_ad' ) ) : ?>
			<meta property="fb:op-recirculation-ads" content="placement_id=<?php echo esc_attr( $recirculation_ad ); ?>">
			<?php endif; ?>

		<?php endif; ?>

	</head>

	<body>
		<article>
			<header>

				<?php
				/**
				 * The main cover for the article.
				 * Can be an image or video and can have a caption.
				 * Optional.
				 *
				 * @link https://developers.facebook.com/docs/instant-articles/reference/cover
				 */
				?>
				<?php
				// Check if it should be shown for this article or not.
				$show_media = wpna_switch_to_boolean( wpna_get_post_option( $post->get_the_ID(), 'fbia_show_media', 'on' ) );
				?>
				<?php if ( $show_media ) : ?>
					<?php if ( $video_url = get_post_meta( $post->get_the_ID(), '_wpna_fbia_video_header', true ) ) : ?>
						<figure>
							<video>
								<?php
									// Manually pass all mime type to wp_check_filetype().
									// Incase the current user has upload mime type restrictions applied.
									$mime_types = wp_get_mime_types();
									$filetype   = wp_check_filetype( $video_url, $mime_types );
								?>
								<source src="<?php echo esc_url( $video_url ); ?>" type="<?php echo esc_attr( $filetype['type'] ); ?>" />
							</video>
						</figure>
					<?php elseif ( $image = $post->get_the_featured_image() ) : ?>
						<figure>
							<img src="<?php echo esc_url( $image['url'] ); ?>" />
							<?php if ( ! empty( $image['caption'] ) ) : ?>
								<figcaption><?php echo esc_html( $image['caption'] ); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endif; ?>
				<?php endif; ?>

				<?php
				/**
				 * The main title for the article. Has to be in <h1> tags.
				 * Required.
				 *
				 * @link https://developers.facebook.com/docs/instant-articles/reference/cover
				 */
				?>
				<h1><?php echo esc_html( $post->get_the_title() ); ?></h1>

				<?php
				/**
				 * The secondary title for the article. In <h2> tags.
				 * Optional.
				 *
				 * @link https://developers.facebook.com/docs/instant-articles/reference/cover
				 */
				?>
				<?php
				// Check if it should be shown for this article or not.
				$show_subtitle = wpna_switch_to_boolean( wpna_get_post_option( $post->get_the_ID(), 'fbia_show_subtitle', 'on' ) );
				?>
				<?php if ( $show_subtitle && $post->get_the_excerpt() ) : ?>
					<h2><?php echo wp_kses_post( $post->get_the_excerpt() ); ?></h2>
				<?php endif; ?>

				<?php
				/**
				 * The kicker for the article.
				 * Optional.
				 *
				 * @link https://developers.facebook.com/docs/instant-articles/reference/cover
				 */
				?>
				<?php
				// Check if it should be shown for this article or not.
				$show_kicker = wpna_switch_to_boolean( wpna_get_post_option( $post->get_the_ID(), 'fbia_show_kicker', 'on' ) );
				?>
				<?php if ( $show_kicker && $post->get_the_kicker() ) : ?>
					<h3 class="op-kicker"><?php echo esc_html( $post->get_the_kicker() ); ?></h3>
				<?php endif; ?>

				<?php // The date and time when your article was originally published. ?>
				<time class="op-published" datetime="<?php echo esc_attr( $post->get_publish_date_iso() ); ?>"><?php echo esc_html( $post->get_publish_date() ); ?></time>

				<?php // The date and time when your article was last updated. ?>
				<time class="op-modified" datetime="<?php echo esc_attr( $post->get_modified_date_iso() ); ?>"><?php echo esc_html( $post->get_modified_date() ); ?></time>

				<?php
				// Check if it should be shown for this article or not.
				$show_authors = wpna_switch_to_boolean( wpna_get_post_option( $post->get_the_ID(), 'fbia_show_authors', 'on' ) );
				?>
				<?php if ( $show_authors ) : ?>

					<?php
					// The authors of your article.
					$authors = $post->get_authors();
					if ( ! empty( $authors ) ) : ?>
						<?php foreach ( (array) $authors as $author ) : ?>
							<address>
								<a><?php echo esc_html( $author->display_name ); ?></a>
								<?php echo esc_html( get_the_author_meta( 'description', $author->ID ) ); ?>
							</address>
						<?php endforeach; ?>
					<?php endif; ?>
				<?php endif; ?>

				<?php
				// Ad code for the article.
				if ( wpna_switch_to_boolean( wpna_get_post_option( $post->get_the_ID(), 'fbia_enable_ads' ) ) ) : ?>
					<?php
					// @codingStandardsIgnoreLine.
					echo $post->get_ads();
					?>
				<?php endif; ?>

				<?php
				// Sponsored code for the article.
				if ( wpna_switch_to_boolean( wpna_get_post_option( $post->get_the_ID(), 'fbia_sponsored' ) ) ) : ?>
					<?php
					// Check if there's a custom sponsor.
					$custom_sponsor_url = get_post_meta( $post->get_the_ID(), '_wpna_fbia_custom_sponsor', true );

					// If it's not overriden try the post author.
					if ( ! $custom_sponsor_url ) {
						// The authors of your article.
						$authors = $post->get_authors();
						// Get the Facebook URL of the author is it exists.
						$custom_sponsor_url = get_the_author_meta( 'facebook', $authors[0]->ID );
					}
					?>

					<?php if ( ! empty( $custom_sponsor_url ) ) : ?>
						<?php
						// If it's not already a URL then make it one.
						if ( false === filter_var( $custom_sponsor_url, FILTER_VALIDATE_URL ) ) {
							$custom_sponsor_url = 'https://www.facebook.com/' . ltrim( $custom_sponsor_url, '/' );
						}
						?>

						<ul class="op-sponsors">
							<li><a href="<?php echo esc_url( $custom_sponsor_url ); ?>" rel="facebook"></a></li>
						</ul>
					<?php endif; ?>

				<?php endif; ?>

				<?php
					/**
					 * Use this action to output any further elements in the article header.
					 *
					 * @since 1.0.0
					 * @param WP_Post $post The current post.
					 */
					do_action( 'wpna_facebook_article_content_header', $post );
				?>

			</header>

			<?php
				// Article body.
				// @codingStandardsIgnoreLine.
				echo $post->get_the_content();
			?>

			<?php
				// Article analytics code.
				// @codingStandardsIgnoreLine.
				echo $post->get_analytics();
			?>

			<footer>

				<?php // First aside block is for article credits. ?>
				<?php if ( $post->get_credits() ) : ?>
					<aside>
						<?php echo esc_html( $post->get_credits() ); ?>
					</aside>
				<?php endif; ?>

				<?php // Copyright follows credits. ?>
				<?php if ( $post->get_copyright() ) : ?>
					<small><?php echo esc_html( $post->get_copyright() ); ?></small>
				<?php endif; ?>

				<?php
				// Check if any manual related articles exist for this post.
				$manual_related_articles = $post->get_manual_related_articles();
				if ( ! empty( $manual_related_articles ) ) : ?>

					<ul class="op-related-articles">
						<?php foreach ( $manual_related_articles as $related_article ) : ?>
							<?php if ( $related_article->sponsored ) : ?>
								<li data-sponsored="true">
							<?php else : ?>
								<li>
							<?php endif; ?>
								<a href="<?php echo esc_url( $related_article->url ); ?>"></a>
							</li>
						<?php endforeach; ?>
					</ul>

				<?php else :

					/**
					 * No manual related articles were found, generate them automatically.
					 * Can define up to 4 related articles at the bottom of an article.
					 */
					$related_articles_loop = $post->get_related_articles();
				?>

					<?php if ( $related_articles_loop->have_posts() ) : ?>
						<ul class="op-related-articles">
							<?php foreach ( $related_articles = $related_articles_loop->get_posts() as $related_article ) : ?>

								<?php
								$attrs = '';

								// Check if the related article is a sponsored one.
								if ( wpna_switch_to_boolean( wpna_get_post_option( $related_article->ID, 'fbia_sponsored' ) ) ) {
									$attrs = ' data-sponsored="true"';
								}

								/**
								 * Filter any attributes applied to the <li> element
								 * of the related articles. e.g. sponsored.
								 *
								 * @since 1.0.0
								 * @param $attrs List of attributes to add
								 * @param $related_article The current related articles
								 * @param $post The current post
								 */
								$attrs = apply_filters( 'wpna_facebook_article_related_articles_attributes', $attrs, $related_article, $post );
								?>

								<?php // @codingStandardsIgnoreStart ?>
								<li<?php echo $attrs; ?>><a href="<?php echo esc_url( get_permalink( $related_article ) ); ?>"></a></li>
								<?php // @codingStandardsIgnoreEnd ?>

							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

				<?php endif; ?>

				<?php
					/**
					 * Use this action to output any further elements in the article footer.
					 *
					 * @since 1.0.0
					 * @param WP_Post $post The current post.
					 */
					do_action( 'wpna_facebook_article_content_footer', $post );
				?>

			</footer>

		</article>
	</body>
</html>
