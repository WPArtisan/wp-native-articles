jQuery(function() {

	jQuery( '#wpna-tabs' ).tabs();

	jQuery( '.wpna-override' ).change(function() {

		if ( jQuery( this ).is( ':checked' ) ) {
			jQuery( jQuery( this ).data('toggle') ).show();
		} else {
			jQuery( jQuery( this ).data('toggle') ).hide();
		}
	});

});
