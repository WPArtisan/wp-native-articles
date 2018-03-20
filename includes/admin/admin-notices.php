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
 * Admin Notices.
 *
 * A better way of handling admin messages globally.
 *
 * @since 1.2.4
 * @return void
 */
function wpna_admin_notices() {

	// No message set, bail early.
	// @codingStandardsIgnoreLine
	if ( empty( $_GET['wpna-message'] ) ) { // Input var okay.
		return;
	}

	// @codingStandardsIgnoreLine
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

		// Transformer.
		case 'transformer_validation_fail':
			add_settings_error( 'wpna-notices', 'wpna-transformer-validation-fail', esc_html__( 'Transformer could not be added as one or more fields were empty or failed validation.', 'wp-native-articles' ), 'error' );
			break;
		case 'transformer_added_error':
			add_settings_error( 'wpna-notices', 'wpna-transformer-added-error', esc_html__( 'There was a problem adding this transformer. Please try again.', 'wp-native-articles' ), 'error' );
			break;
		case 'transformer_added_success':
			add_settings_error( 'wpna-notices', 'wpna-transformer-added-success', esc_html__( 'Transformer successfully added.', 'wp-native-articles' ), 'updated' );
			break;
		case 'transformer_update_success':
			add_settings_error( 'wpna-notices', 'wpna-transformer-updated-success', esc_html__( 'Transformer(s) successfully updated.', 'wp-native-articles' ), 'updated' );
			break;
		case 'transformer_delete_success':
			add_settings_error( 'wpna-notices', 'wpna-transformer-delete-success', esc_html__( 'Transformer(s) successfully deleted.', 'wp-native-articles' ), 'updated' );
			break;

		// Post Syncer.
		case 'post_sync_complete':
			add_settings_error( 'wpna-notices', 'wpna-post-sync-complete', esc_html__( 'Post Sync successfully completed.', 'wp-native-articles' ), 'updated' );
			break;

		// Facebook API.
		case 'facebook_api_login_success':
			add_settings_error( 'wpna-notices', 'wpna-facebook-api-login-success', esc_html__( 'Facebook successfully connected.', 'wp-native-articles' ), 'updated' );
			break;
		case 'facebook_api_login_error':
			add_settings_error( 'wpna-notices', 'wpna-facebook-api-login-success', esc_html__( 'There was a problem connecting to Facebook.', 'wp-native-articles' ), 'error' );
			break;
		case 'facebook_api_logout_success':
			add_settings_error( 'wpna-notices', 'wpna-facebook-api-logout-success', esc_html__( 'Facebook successfully disconnected.', 'wp-native-articles' ), 'updated' );
			break;

		// Licencing.
		case 'license_save_success':
			add_settings_error( 'wpna-notices', 'license_save_success', esc_html__( 'License key successfully updated.', 'wp-native-articles' ), 'updated' );
			break;
		case 'license_save_error':
			add_settings_error( 'wpna-notices', 'license_save_error', esc_html__( 'There was a problem updating your licence. Please try again.', 'wp-native-articles' ), 'error' );
			break;
		case 'license_activate_success':
			add_settings_error( 'wpna-notices', 'license_activate_success', esc_html__( 'License successfully activated. You are now receiving updates.', 'wp-native-articles' ), 'updated' );
			break;
		case 'license_activate_error':
			if ( $message = filter_input( INPUT_GET, 'message', FILTER_SANITIZE_STRING ) ) {
				// Make sure it's correctly escaped.
				$message = esc_html( rawurldecode( $message ) );
			} else {
				$message = esc_html__( 'Error: Your license could not be activated. Please try again.', 'wp-native-articles' );
			}

			add_settings_error( 'wpna-notices', 'license_activate_error', $message, 'error' );
			break;
		case 'license_deactivate_success':
			add_settings_error( 'wpna-notices', 'license_deactivate_success', esc_html__( 'License successfully deactivated.', 'wp-native-articles' ), 'updated' );
			break;

		// Multisite notices.
		case 'multisite_options_update_success':
			add_settings_error( 'wpna-notices', 'multisite_options_update_success', esc_html__( 'Multisite settings updated successfully.', 'wp-native-articles' ), 'updated' );
			break;

		case 'multisite_options_update_error':
			add_settings_error( 'wpna-notices', 'multisite_options_update_error', esc_html__( 'Error updating multisite settings.', 'wp-native-articles' ), 'error' );
			break;

		case 'multisite_reset_success':
			add_settings_error( 'wpna-notices', 'wpna_multisite_reset_success', esc_html__( 'Blog settings successfully reset.', 'wp-native-articles' ), 'updated' );
			break;

		case 'multisite_reset_error_missing_id':
			add_settings_error( 'wpna-notices', 'wpna_multisite_reset_error_missing_id', esc_html__( 'Error: Please provide a blog ID to reset.', 'wp-native-articles' ), 'error' );
			break;

		default:
			/**
			 * Use this action to scan for any custom notices.
			 *
			 * @since 1.0.0
			 * @param string The notice key.
			 */
			// @codingStandardsIgnoreLine
			do_action_deprecated( 'wpna_multisite_notices', array( wp_unslash( $_GET['wpna-message'] ) ), '1.4.0', 'wpna_notices' );

			/**
			 * Use this action to add any custom notices.
			 *
			 * @since 1.4.0
			 */
			do_action( 'wpna_notices' );

			break;
	}

	// Check for any errors set in the transient.
	// These are generally errors that aren't set by the plugin.
	// e.g. ones returned from Facebook.
	if ( $notices = get_transient( 'wpna_notices' ) ) {

		foreach ( $notices as $notice ) {
			add_settings_error( 'wpna-notices', $notice['code'], esc_html( $notice['message'] ), $notice['type'] );
		}

		// Clear the transient.
		delete_transient( 'wpna_notices' );
	}

	// Do the notices.
	settings_errors( 'wpna-notices' );
}
add_action( 'admin_notices', 'wpna_admin_notices', 10, 0 );
add_action( 'network_admin_notices', 'wpna_admin_notices', 10, 0 );

/**
 * Add in the action for hooking into the Admin notices on our plugin pages.
 * Any page that uses the Settings API should be hooked in here.
 *
 * @since 1.4.0
 * @return void
 */
function wpna_admin_notices_general_hook() {
	add_action( 'admin_notices', 'wpna_admin_notices_general', 10, 0 );
}
add_action( 'load-native-articles_page_wpna_facebook', 'wpna_admin_notices_general_hook', 10, 0 );
add_action( 'load-toplevel_page_wpna_general', 'wpna_admin_notices_general_hook', 10, 0 );

/**
 * Display the 'general' settings errors.
 * This is the group used by the settings API.
 *
 * @since 1.4.0
 * @return void
 */
function wpna_admin_notices_general() {
	settings_errors( 'general' );
}

/**
 * Dismiss any notices that may be shown if the dismiss link is clicked..
 *
 * @since 1.3.5
 * @return void
 */
function wpna_dismiss_notices() {

	// No message set, bail early.
	if ( empty( $_GET['wpna-notice'] ) ) { // Input var okay.
		return;
	}

	// Check the nonce.
	// @codingStandardsIgnoreLine
	if ( ! isset( $_GET['wpna-dismiss-notice-nonce'] ) || ! wp_verify_nonce( $_GET['wpna-dismiss-notice-nonce'], 'wpna-dismiss-notice' ) ) {
		wp_die( 'Invalid nonce - WPNA Dismiss Notice Action' );
	}

	switch ( $_GET['wpna-notice'] ) { // Input var okay.
		case 'expired_license':
		case 'invalid_license':
			// Hide the notice for two weeks.
			set_transient( 'wpna_license_notice', true, 2 * WEEK_IN_SECONDS );

			break;

		// They've already rated the app, kill all rating prompts.
		case 'rating_permanent':
			delete_site_option( 'wpna_rating_prompts' );
			break;

		// They don't want to be bugged anymore at the moment.
		// Remove the current interval prompt.
		case 'rating_temporary':
			$prompts = (array) get_site_option( 'wpna_rating_prompts' );

			// 1 or fewer intervals and just remove the whole option.
			if ( count( $prompts ) <= 1 ) {
				delete_site_option( 'wpna_rating_prompts' );

			} else {
				// Sort the array and remove the lowest interval.
				sort( $prompts, SORT_NUMERIC );
				array_shift( $prompts );
				update_site_option( 'wpna_rating_prompts', $prompts );
			}

			break;

		default:
			/**
			 * Use this action to dismiss any custom notices.
			 *
			 * @since 1.4.0
			 */
			do_action( 'wpna_dismiss_notices_default' );

			break;

	}

	wp_safe_redirect( remove_query_arg( array( 'wpna-action', 'wpna-notice', 'wpna-dismiss-notice-nonce' ) ) );
	exit;

}
add_action( 'wpna_dismiss_notices', 'wpna_dismiss_notices', 10, 0 );

/**
 * Outputs the HTML for the rating notice admin prompt.
 *
 * We bug admins at certain intervals to rate the plugin.
 *
 * @since 1.4.0
 * @return void
 */
function wpna_rating_notices() {

	// We only want admins.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Get the plugin activation time & rating prompts intervals.
	$activation_time = get_site_option( 'wpna_activation_time' );
	$prompts         = (array) get_site_option( 'wpna_rating_prompts' );

	// Sort the prompts to ensure they're in order.
	sort( $prompts, SORT_NUMERIC );

	foreach ( $prompts as $prompt ) {
		// Check if any of the prompts are greater than the activation time.
		if ( strtotime( $activation_time ) < strtotime( "-{$prompt} days" ) ) {

			// Show the rating prompt.
			$message  = '<div class="notice notice-info">';
			$message .= '<p>' . esc_html__( "Hey, we noticed you've been using WP Native Articles for a little while now – that’s brilliant! Could you please do me a BIG favor and give it a 5-star rating on WordPress? It really helps us spread the word and boosts our motivation.", 'wp-native-articles' ) . '</p>';
			$message .= '<p>- Edward</p>';
			$message .= '<p><a href="https://wordpress.org/support/plugin/wp-native-articles/reviews/" target="_blank">' . esc_html__( 'Sure, you deserve it', 'wp-native-articles' ) . '</a></p>';
			$message .= '<p><a href="' . wp_nonce_url(
				add_query_arg(
					array(
						'wpna-action' => 'dismiss_notices',
						'wpna-notice' => 'rating_permanent',
					)
				),
				'wpna-dismiss-notice',
				'wpna-dismiss-notice-nonce'
			) . '">' . esc_html__( 'I already have', 'wp-native-articles' ) . '</a></p>';
			$message .= '<p><a href="' . wp_nonce_url(
				add_query_arg(
					array(
						'wpna-action' => 'dismiss_notices',
						'wpna-notice' => 'rating_temporary',
					)
				),
				'wpna-dismiss-notice',
				'wpna-dismiss-notice-nonce'
			) . '">' . esc_html__( 'Nope, not right now', 'wp-native-articles' ) . '</a><p>';
			$message .= '</div>';

			// @codingStandardsIgnoreLine
			echo $message;

			// Break out of the foreach loop.
			break;
		}
	}

}
add_action( 'admin_notices', 'wpna_rating_notices', 10, 0 );
