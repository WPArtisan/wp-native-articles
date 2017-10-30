jQuery(function() {

	jQuery( '#wpna-tabs' ).tabs();

	jQuery( '.wpna-override' ).change(function() {

		if ( jQuery( this ).is( ':checked' ) ) {
			jQuery( jQuery( this ).data('toggle') ).show();
		} else {
			jQuery( jQuery( this ).data('toggle') ).hide();
		}
	});

	jQuery( document ).ready(function(){

		function wpnaToggleAdCodeTemplates() {

			jQuery( '#wp-native-articles .wpna-ad-code-template' ).each(function( index, el ) {
				jQuery( el ).addClass( 'hidden' );
				jQuery( el, ' :input' ).prop( 'disabled', true );
			});

			// Hide any open ads.
			jQuery( '#wp-native-articles .wpna-ad-code-template' ).addClass( 'hidden' );

			var target = '#wpna-ad-code-template-' + jQuery( '#fbia-ad-code-type' ).val();

			jQuery( target ).removeProp( 'disabled' );
			jQuery( target ).removeClass( 'hidden' );
		}

		wpnaToggleAdCodeTemplates();

		jQuery( '#fbia-ad-code-type' ).change( function() {
			wpnaToggleAdCodeTemplates();
		} );

	});

});
