<?php
/**
 * Placements Contextual Help.
 *
 * @package     wp-native-articles
 * @subpackage  Includes/Placements
 * @copyright   Copyright (c) 2017, WPArtisan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the Contextual Help for the Placements Page.
 *
 * @return void
 */
function wpna_placements_contextual_help() {
	$screen = get_current_screen();

	// Default help tab.
	$screen->add_help_tab( array(
		'id'      => 'wpna-placement-general',
		'title'   => esc_html__( 'Default', 'wp-native-articles' ),
		'content' => 'Placements are an easy way of adding any content you like within your Instant Articles.',
	));

	// Adding Placements help tab.
	// @codingStandardsIgnoreStart
	$screen->add_help_tab( array(
		'id'      => 'wpna-placement-add',
		'title'   => esc_html__( 'Add Placement', 'wp-native-articles' ),
		'content' =>
			'<p>' . esc_html__( 'You can easily create any number of Placements using this page.', 'wp-native-articles' ) . '</p>',
	));

	// Help sidebar.
	$screen->set_help_sidebar(
		'<p><strong>' . sprintf( __( 'For more information:', 'wp-native-articles' ) . '</strong></p>' .
		'<p>' . sprintf( __( 'Visit the <a target="_blank" href="%s">documentation</a> on the WP Native Articles website.', 'wp-native-articles' ), esc_url( 'http://docs.wp-native-articles.com' ) ) ) . '</p>' .
		'<p>' . sprintf(
			__( 'Follow us on <a target="_blank" href="%1$s">Twitter</a> and <a target="_blank" href="%1$s">Facebook</a> to keep up with the latest updates. Visit our <a target="_blank" href="%1$s">website</a> for more information.', 'wp-native-articles' ),
			esc_url( 'https://twitter.com/native_wp' ),
			esc_url( 'https://www.facebook.com/wpnativearticles' ),
			esc_url( 'https://wp-native-articles.com/?utm_source=fplugin&utm_medium=contextualhelp-placements' )
		) . '</p>'
	);
	// @codingStandardsIgnoreEnd

	/**
	 * Use to add more help tabs.
	 *
	 * @since 1.3.0
	 * @var The current screen.
	 */
	do_action( 'wpna_placements_contextual_help', $screen );
}
