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
			setupAdsCheckDifferent();
			setupApiDraftToggle();
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
	}

	/**
	 * Check the regular Ad Placement & the Recirculation Ad are different.
	 * Show a warning if not.
	 *
	 * @return function
	 */
	var setupAdsCheckDifferent = function setupAdsCheckDifferent() {

		// Check the element is visible.
		if ( ! $( '.wpna input#fbia_recirculation_ad' ).length ) {
			return;
		}

		// Type of ad.
		var adType = $( '.wpna select#fbia_ad_code_type' );

		// Regular ad ID.
		var normalAd = $( '.wpna input#fbia_ad_code_placement_id' );

		// Recirculation Ad ID.
		var recirculationAd = $( '.wpna input#fbia_recirculation_ad' );

		var doAdswWarning = function() {

			// If they're the same show the warning.
			if ( 'audience_network' === adType.val() && normalAd.val().length > 0 && normalAd.val() == recirculationAd.val() ) {
				$( '.wpna .recirculation-ad-warning' ).show();
			} else {
				$( ' .wpna .recirculation-ad-warning' ).hide();
			}
		}

		adType.on( 'change', function() {
			doAdswWarning();
		});

		// Using add() should be slightly quicker.
		normalAd.add( recirculationAd ).keyup( function() {
			doAdswWarning();
		} );

		// Fire it initally.
		doAdswWarning();

	}

	/**
	 * If basic auth is enabled on the RSS feed then toggle the
	 * username / password fields.
	 *
	 * @return function
	 */
	var setupApiDraftToggle = function setupApiDraftToggle() {

		// Check the element is visible.
		if ( ! $( '.wpna select#fbia-environment' ).length ) {
			return;
		}

		// Inline function for toggling the rows.
		var toggleDraft = function( el ) {

			// n.b. Only show / hide & fade are support on table rows.
			if ( 'production' === el.value ) {
				$( '.wpna input#fbia-import-as-drafts' ).parents('tr').show();
			} else {
				$( '.wpna input#fbia-import-as-drafts' ).parents('tr').hide();
			}
		}

		// Fire as soon as the page is loaded.
		toggleDraft( $( '.wpna select#fbia-environment' )[ 0 ] );

		// Watch the checkbox for changes.
		jQuery( '.wpna select#fbia-environment' ).on( 'change', function() {
			toggleDraft( this );
		});
	};

	// Return the initialize function.
	return initialize();

})( jQuery );
