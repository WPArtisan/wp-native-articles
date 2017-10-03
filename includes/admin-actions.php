<?php
/**
 * Admin Actions
 *
 * @package     wp-native-articles
 * @subpackage  Admin/Actions
 * @copyright   Copyright (c) 2017, WPArtisan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Processes all WPNA actions sent via POST and GET by looking for the 'wpna-action'
 * request and running do_action() to call the function.
 *
 * Credit: Easy Digital Downloads.
 *
 * @since 1.2.4
 * @return void
 */
function wpna_process_actions() {
	// @codingStandardsIgnoreStart
	if ( isset( $_POST['wpna-action'] ) ) {
		do_action( 'wpna_' . $_POST['wpna-action'], $_POST );
	}

	if ( isset( $_GET['wpna-action'] ) ) {
		do_action( 'wpna_' . $_GET['wpna-action'], $_GET );
	}
	// @codingStandardsIgnoreEnd
}
add_action( 'admin_init', 'wpna_process_actions' );
