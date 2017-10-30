<?php
/**
 * Admin Actions
 *
 * @package     wp-native-articles
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2017, WPArtisan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Admin Messages.
 *
 * A better way of handlign admin messages globally.
 *
 * @since 1.2.4
 * @return void
 */
function wpna_admin_messages() {

	// No message set, bail early.
	if ( empty( $_GET['wpna-message'] ) ) { // Input var okay.
		return;
	}

	switch ( $_GET['wpna-message'] ) { // Input var okay.

		// Placements.
		case 'placement_validation_fail':
			add_settings_error( 'wpna-notices', 'wpna-placement-validation-fail', esc_html__( 'Placement could not be added as one or more fields were empty or failed validation.', 'wp-native-articles' ), 'error' );
			break;
		case 'placement_added_error':
			add_settings_error( 'wpna-notices', 'wpna-placement-added-error', esc_html__( 'There was a problem adding this placement. Please try again.', 'wp-native-articles' ), 'error' );
			break;
		case 'placement_added_success':
			add_settings_error( 'wpna-notices', 'wpna-placement-added-success', esc_html__( 'Placement successfully added.', 'wp-native-articles' ), 'updated' );
			break;
		case 'placement_update_success':
			add_settings_error( 'wpna-notices', 'wpna-placement-updated-success', esc_html__( 'Placement(s) successfully updated.', 'wp-native-articles' ), 'updated' );
			break;
		case 'placement_delete_success':
			add_settings_error( 'wpna-notices', 'wpna-placement-delete-success', esc_html__( 'Placement(s) successfully deleted.', 'wp-native-articles' ), 'updated' );
			break;
	}

	// Do the notices.
	settings_errors( 'wpna-notices' );

}
add_action( 'admin_notices', 'wpna_admin_messages' );
