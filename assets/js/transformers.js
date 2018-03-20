var wpnaTransformerTypeToggle = function( el ) {
	var type = jQuery( el ).val();
	var target = '.js-' + type;

	// Show the correct selector dropdown.
	jQuery( '.js-target-type:not(' + target + ')' ).hide();
	jQuery( target ).show();

	// Make sure all the "rules" are enabled.
	jQuery( '.js-rule option').prop( 'disabled', false );

	// Only show the pattern_macher rule if the type is post_content.
	if ( 'post_content' === type ) {
		jQuery( '.js-rule' ).val( 'pattern_matcher' );
		jQuery( '.js-rule option[value!="pattern_matcher"]').prop( 'disabled', true );
	} else if ( 'shortcode' === type ) {
		console.log(jQuery( '.js-rule' ).val() );
		if ( [ 'pattern_matcher', 'bypass_parser', 'remove' ].indexOf( jQuery( '.js-rule' ).val() ) <= -1 ) {
			jQuery( '.js-rule' ).val( 'remove' );
		}
		jQuery( '.js-rule option:not([value=pattern_matcher], [value=remove], [value=bypass_parser])' ).prop( 'disabled', true );
	} else if ( 'content_filter' === type ) {
		jQuery( '.js-rule' ).val( 'remove' );
		jQuery( '.js-rule option[value!="remove"]').prop( 'disabled', true );
	} else if ( 'custom' === type ) {
		if ( 'bypass_parser' === jQuery( '.js-rule' ).val() && 'bypass_parser' === jQuery( '.js-rule' ).val() ) {
			jQuery( '.js-rule' ).val( 'remove' );
		}
		jQuery( '.js-rule option[value=pattern_matcher], .js-rule option[value=bypass_parser]' ).prop( 'disabled', true );
	}

	jQuery( '.js-rule' ).trigger('change');
};

wpnaTransformerTypeToggle( jQuery( '.js-trigger-type' ) );

jQuery( '.js-trigger-type' ).change( function( el ) {
	wpnaTransformerTypeToggle( this );
});

// Show the Facebook SDK Rules description if one is selected.
// Show any subfields for the Facebook rule if they exist.
var wpnaTransformerRuleToggle = function( el ) {
	var type = jQuery( el ).val();
	var target = '.js-' + type;

	// Show the correct selector dropdown.
	jQuery( '.js-target-rule:not(' + target + ')' ).hide();
	jQuery( target ).show();

	if ( jQuery( ':selected', el ).parent( 'optgroup' ).hasClass( 'js-trigger-rule-facebook_transformers' ) ) {
		jQuery( '.js-facebook_transformers' ).show();
	}

};

wpnaTransformerRuleToggle( jQuery( '.js-trigger-rule' ) );

jQuery( '.js-trigger-rule' ).on( 'change', function( el ) {
	wpnaTransformerRuleToggle( this );
});

// Show any tips.
// todo: Move to global js.
jQuery(document).ready(function() {

	jQuery( '.js-trigger-tip' ).on( 'focus', function() {
		var tipSelector = '.js-tip-' + jQuery( this ).attr( 'id' );
		jQuery( tipSelector ).slideDown();
	});

	jQuery( '.js-trigger-tip' ).on( 'blur', function() {
		var tipSelector = '.js-tip-' + jQuery( this ).attr( 'id' );
		jQuery( tipSelector ).slideUp();
	});

	// Show the JSON validation warnings.
	jQuery( '.js-trigger-properties' ).keyup( function() {
		jQuery( '.js-json-warning' ).hide();

		if ( ! wpnaIsValidJson( jQuery( this ).val() ) ) {
			jQuery( '.js-json-invalid' ).show();
		} else {
			jQuery( '.js-json-valid' ).show();
		}
	});
});


/**
 * Check if a string is valid JSON or not.
 * @link https://stackoverflow.com/a/20392392/1367899
 *
 * @param  {[type]} jsonString [description]
 * @return {[type]}            [description]
 */
function wpnaIsValidJson (jsonString){
	try {
		var o = JSON.parse( jsonString );

		// Handle non-exception-throwing cases:
		// Neither JSON.parse(false) or JSON.parse(1234) throw errors, hence the type-checking,
		// but... JSON.parse(null) returns null, and typeof null === "object",
		// so we must check for that, too. Thankfully, null is falsey, so this suffices:
		if (o && typeof o === "object") {
			return o;
		}
	}
	catch (e) { }

	return false;
};
