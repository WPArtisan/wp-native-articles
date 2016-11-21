<?php
/**
 * Template for the content of each post.
 *
 * This is used by both the API and the RSS feed. Expects $post to be an
 * instance of WPNA_Facebook_Post. This template can be overridden by creating
 * a template of the same name in your theme folder.
 *
 * @since 1.0.0
 */
?>
<!doctype html>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>" prefix="op: http://media.facebook.com/op#">
	<head>
		<meta charset="<?php echo esc_attr( get_option( 'blog_charset' ) ); ?>">
		<link rel="canonical" href="<?php echo esc_url( $post->get_permalink() ); ?>">
		<meta property="op:markup_version" content="v1.0">
		<meta property="fb:article_style" content="<?php echo esc_attr( $post->get_style() ); ?>">
		<?php if ( wpna_switch_to_boolean( wpna_get_post_option( $post->get_the_ID(), 'fbia_enable_ads' ) ) && wpna_switch_to_boolean( wpna_get_post_option( $post->get_the_ID(), 'fbia_auto_ad_placement' ) ) ) : ?>
			<meta property="fb:use_automatic_ad_placement" content="true">
		<?php endif; ?>
	</head>

	<body>
		<article>
			<header>

				<?php
				/**
				 * The main cover for the article.
				 * Can be an image or video and can have a caption
				 *
				 * @link https://developers.facebook.com/docs/instant-articles/reference/cover
				 */
				?>
				<?php if ( $image = $post->get_the_featured_image() ) : ?>
					<figure>
						<img src="<?php echo esc_url( $image['url'] ); ?>" />
						<?php if ( ! empty( $image['caption'] ) ) : ?>
							<figcaption><?php echo esc_html( $image['caption'] ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endif; ?>

				<?php
				/**
				 * The main title for the article. Has to be in <h1> tags
				 *
				 * @link https://developers.facebook.com/docs/instant-articles/reference/cover
				 */
				?>
				<h1><?php echo esc_html( $post->get_the_title() ); ?></h1>

				<?php
				/**
				 * The secondary title for the article. In <h2> tags
				 * Optional
				 *
				 * @link https://developers.facebook.com/docs/instant-articles/reference/cover
				 */
				?>
				<?php if ( $post->get_the_excerpt() ) : ?>
					<h2><?php echo esc_html( $post->get_the_excerpt() ); ?></h2>
				<?php endif; ?>

				<?php
				/**
				 * The kicker for the article
				 * Optional
				 *
				 * @link https://developers.facebook.com/docs/instant-articles/reference/cover
				 */
				?>
				<?php if ( $post->get_the_kicker() ) : ?>
					<h3 class="op-kicker"><?php echo esc_html( $post->get_the_kicker() ); ?></h3>
				<?php endif; ?>

				<?php // The date and time when your article was originally published ?>
				<time class="op-published" datetime="<?php echo esc_attr( $post->get_publish_date_iso() ); ?>"><?php echo esc_html( $post->get_publish_date() ); ?></time>

				<?php // The date and time when your article was last updated ?>
				<time class="op-modified" datetime="<?php echo esc_attr( $post->get_modified_date_iso() ); ?>"><?php echo esc_html( $post->get_modified_date() ); ?></time>

				<?php
				// The authors of your article
				if ( ! empty( $authors = $post->get_authors() ) ) : ?>
					<?php foreach ( (array) $authors as $author ) : ?>
						<address>
							<a><?php echo esc_html( $author->display_name ); ?></a>
							<?php echo esc_html( get_the_author_meta( 'description', $author->ID ) ); ?>
						</address>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php
				// Ad code for the article
				if ( wpna_switch_to_boolean( wpna_get_post_option( $post->get_the_ID(), 'fbia_enable_ads' ) ) ) : ?>
					<?php echo $post->get_ads(); ?>
				<?php endif; ?>

				<?php
				// Sponsored code for the article
				if ( wpna_switch_to_boolean( wpna_get_post_option( $post->get_the_ID(), 'fbia_sponsored' ) ) ) : ?>
					<?php if ( ! empty( $authors = $post->get_authors() ) ) : ?>
						<ul class="op-sponsors">
						<?php foreach ( (array) $authors as $author ) : ?>
							<?php if ( $fb_url = get_the_author_meta( 'facebook', $author->ID ) ) : ?>
								<?php
								// If it's not already a URL make it one
								if ( filter_var( $fb_url, FILTER_VALIDATE_URL ) === FALSE )
									$fb_url = 'https://www.facebook.com/' . ltrim( $fb_url, '/' );
								?>
								<li><a href="<?php echo esc_url( $fb_url ); ?>" rel="facebook"></a></li>
							<?php endif;?>
						<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				<?php endif; ?>

				<?php
					/**
					 * Use this action to output any further elements in the article header
					 *
					 * @since 1.0.0
					 * @param WP_Post $post The current post.
					 */
					do_action( 'wpna_facebook_article_content_header', $post );
				?>

			</header>

			<?php
				// Article body
				echo $post->get_the_content();
			?>

			<?php
				// Article analytics code
				echo $post->get_analytics();
			?>

			<footer>

				<?php // First aside block is for article credits ?>
				<?php if ( $post->get_credits() ) : ?>
					<aside>
						<?php echo esc_html( $post->get_credits() ); ?>
					</aside>
				<?php endif; ?>

				<?php // Copyright follows credits ?>
				<?php if ( $post->get_copyright() ) : ?>
					<small><?php echo esc_html( $post->get_copyright() ); ?></small>
				<?php endif; ?>

				<?php
					/**
					 * We can define up to 3 related articles at the bottom of an article
					 */
					$related_articles_loop = $post->get_related_articles();
				?>

				<?php if ( $related_articles_loop->have_posts() ) : ?>
					<ul class="op-related-articles">
						<?php foreach ( $related_articles = $related_articles_loop->get_posts() as $related_article ) : ?>

							<?php
							/**
							 * Filter any attributes applied to the <li> element
							 * of the related articles. e.g. sponsored
							 *
							 * @since 1.0.0
							 * @param $attrs List of attributes to add
							 * @param $related_article The current related articles
							 * @param $post The current post
							 */
							$attrs = apply_filters( 'wpna_facebook_article_related_articles_attributes', '', $related_article, $post );
							?>

							<li<?php echo esc_attr( $attrs ); ?>><a href="<?php echo esc_url( get_permalink( $related_article ) ); ?>"></a></li>

						<?php endforeach; ?>
					</ul>
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