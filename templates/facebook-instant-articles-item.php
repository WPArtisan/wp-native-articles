<?php
/**
 * Template for the RSS feed item.
 *
 * Each item in the RSS feed needs to be formatted accordingly. The content
 * field contains the formatted WordPress post. Expects $post to be an instance
 * of WPNA_Facebook_Post. This template can be overridden by creating a
 * template of the same name in your theme folder.
 *
 * @since 0.0.1
 */
?>
<item>
	<title><?php echo esc_html( $post->get_the_title() ); ?></title>
	<link><?php echo esc_url( $post->get_permalink() ); ?></link>
	<content:encoded><![CDATA[
		<?php include wpna_locate_template( 'facebook-instant-articles-item-content' ); ?>
	]]></content:encoded>
	<guid isPermaLink="false"><?php echo esc_url( get_the_guid() ); ?></guid>
	<description><![CDATA[<?php echo esc_html( $post->get_the_excerpt() ); ?>]]></description>
	<pubDate><?php echo esc_html( $post->get_publish_date_iso() ); ?></pubDate>
	<modDate><?php echo esc_html( $post->get_modified_date_iso() ); ?></modDate>

	<?php if ( ! empty( $authors = $post->get_authors() ) ) : ?>
		<?php foreach ( (array) $authors as $author ) : ?>
			<author><?php echo esc_html( $author->display_name ); ?></author>
		<?php endforeach; ?>
	<?php endif; ?>

</item>
