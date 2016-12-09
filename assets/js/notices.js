jQuery(function() {

	jQuery( '.wpna-notice' ).on( 'click', '.wpna-dismiss', function( event, el ) {

		event.preventDefault();

		jQuery( this ).closest('.is-dismissible').find( '.notice-dismiss' ).trigger( 'click' );

		wp.ajax.send( 'wpna-dismiss-notice', {

			// Send a nonce with the request
			data : {
				'_ajax_nonce' : wpnaNotices.nonce,
				'notice' : jQuery(this).data('notice')
			}
		});

	});

});
