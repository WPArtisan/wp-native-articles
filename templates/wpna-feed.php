<?php
/**
 * Template for the RSS feed.
 *
 * The main instant articles feed template. Uses the main WordPress loop to
 * output all the requred posts for the instant article feed. Everypost is then
 * passed through the transformer that correctly formats it for instant articles
 * This template can be overridden by creating a template of the same name
 * in your theme folder.
 *
 * @since 1.0.0
 * @package wp-native-articles
 */

?>
<?php
	// Has to be done like this otherwise WP plugin repository hooks parse it as PHP and fail it.
	echo sprintf( '<?xml version="1.0" encoding="%s"?>', esc_attr( get_option( 'blog_charset' ) ) );
?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
	<channel>
		<title><?php bloginfo_rss( 'name' ); ?></title>
		<link><?php bloginfo_rss( 'url' ); ?></link>
		<description><?php bloginfo_rss( 'description' ); ?></description>

		<?php
			/**
			 * Add any custom output to the articles feed channel header.
			 * Has access to all global variables.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wpna_facebook_channel_header' );
		?>

		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post(); ?>

				<?php
				// Check if this post should be converted.
				if ( ! wpna_should_convert_post_ia( get_the_ID() ) ) {
					continue;
				}
				?>

				<?php
					/**
					 * To correctly format the article according to IA standards
					 * we need to transform the post. The WPNA_Facebook_Post class
					 * does this.
					 *
					 * @var WPNA_Facebook_Post
					 */
					$post = new WPNA_Facebook_Post( get_the_ID() );

					// Load in the item template.
					include wpna_locate_template( 'wpna-feed-item' );
				?>

			<?php endwhile; ?>

			<?php
				/**
				 * `lastBuildDate` is the last time the feed was updated.
				 * For all intensive purposes this will be the same as the
				 * most recent post's modification date in the query.
				 *
				 * Although the inital query for this is ordered by modified
				 * date we can't trust it hasn't been altered.
				 */
				global $posts;

				// Pluck all dates from the posts.
				$post_dates = wp_list_pluck( $posts, 'post_modified' );

				// Order dates by desc.
				arsort( $post_dates );

				/**
				 * Filter the feed lastbuilddate
				 *
				 * The latest date is now the final element of the array. Pop it off
				 * Dates are already in the correct format (ISO8601) by this point.
				 * $posts is available globally.
				 *
				 * @since 1.0.0
				 * @param string The date of the most recent post
				 */
				$last_modified = apply_filters( 'wpna_facebook_lastbuilddate', array_pop( $post_dates ) );

			?>

			<?php if ( $last_modified ) : ?>
				<lastBuildDate><?php echo esc_html( $last_modified ); ?></lastBuildDate>
			<?php endif; ?>

		<?php endif; ?>

		<?php
			/**
			 * Add any custom output to the articles feed channel header.
			 * Has access to all global variables.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wpna_facebook_channel_footer' );
		?>

	</channel>
</rss>
