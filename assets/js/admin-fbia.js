//
// All the JS for the dashboard widget
//

var WPNA_ADMIN_FBIA = (function($) {

	/**
	 * Our construct function that is run when the
	 * class is first initialized
	 * @return function
	 */
	var initialize = function() {
		// Run on document ready
		$(function() {
			setupAuthToggle();
			setupAdsToggle();
		});
	};

	/**
	 * If basic auth is enabled on the RSS feed then toggle the
	 * username / password fields.
	 *
	 * @return function
	 */
	var setupAuthToggle = function setupAuthToggle() {

		// Check the element is visible.
		if ( ! $( '.wpna input#fbia_feed_authentication' ).length ) {
			return;
		}

		// Inline function for toggling the rows.
		var toggleAuthFields = function( el ) {
			// n.b. Only show / hide & fade are support on table rows.
			if ( el.checked ) {
				$( '.wpna input#fbia_feed_authentication_username' ).parents('tr').show();
				$( '.wpna input#fbia_feed_authentication_password' ).parents('tr').show();
			} else {
				$( '.wpna input#fbia_feed_authentication_username' ).parents('tr').hide();
				$( '.wpna input#fbia_feed_authentication_password' ).parents('tr').hide();
			}
		}

		// Fire as soon as the page is loaded.
		toggleAuthFields( $( '.wpna input#fbia_feed_authentication' )[ 0 ] );

		// Watch the checkbox for changes.
		jQuery( '.wpna input#fbia_feed_authentication' ).on( 'change', function() {
			toggleAuthFields( this );
		});
	}

	/**
	 * Toggle input fields depending on the type of ads shown.
	 *
	 * @return function
	 */
	var setupAdsToggle = function setupAdsToggle() {

		// Check the element is visible.
		if ( ! $( '.wpna select#fbia_ad_code_type' ).length ) {
			return;
		}

		// Inline function for toggling the rows.
		var toggleAdsFields = function( el ) {
			// n.b. Only show / hide & fade are support on table rows.
			if ( 'audience_network' == el.value ) {
				$( '.wpna input#fbia_ad_code_placement_id' ).parents('tr').show();
				$( '.wpna textarea#fbia_ad_code' ).parents('tr').hide();
			} else {
				$( '.wpna input#fbia_ad_code_placement_id' ).parents('tr').hide();
				$( '.wpna textarea#fbia_ad_code' ).parents('tr').show();
			}
		}

		// Fire as soon as the page is loaded.
		toggleAdsFields( $( '.wpna select#fbia_ad_code_type' )[ 0 ] );

		// Watch the checkbox for changes.
		jQuery( '.wpna select#fbia_ad_code_type' ).on( 'change', function() {
			toggleAdsFields( this );
		});
	};

	// Return the initialize function.
	return initialize();

})( jQuery );
