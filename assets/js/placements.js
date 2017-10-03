var wpnaPlacementToggle = function( el ) {
	var target = jQuery( el ).attr('id');

	target = target.replace( 'toggle', 'form' );

	if ( el.checked ) {
		jQuery( '#' + target ).removeClass('hidden');
	} else {
		jQuery( '#' + target ).addClass('hidden');
	}
};

jQuery( '.wpna-placement-toggle:checkbox' ).each(function( index, el ) {
	wpnaPlacementToggle( el );
});

jQuery( '.wpna-placement-toggle:checkbox' ).change( function( el ) {
	wpnaPlacementToggle( this );
});

var wpnaPlacementSelectToggle = function( el ) {
	var target = jQuery( el ).val();

	// Work out the ID.
	target = 'wpna-placement-' + target.replace( '_', '-' );

	// Hide any open forms.
	jQuery( '.wpna-placement-content-form' ).addClass('hidden')

	// Dispalay the chosen one.
	jQuery( '#' + target ).removeClass('hidden');
};

wpnaPlacementSelectToggle( jQuery( '.wpna-placement-select-toggle' ) );

jQuery( '.wpna-placement-select-toggle' ).change( function( el ) {
	wpnaPlacementSelectToggle( this );
});

jQuery( function() {
	jQuery( '.select2' ).select2({ dropdownAutoWidth: true });
} );
