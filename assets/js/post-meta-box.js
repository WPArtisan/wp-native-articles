jQuery(function() {

	jQuery( '#wpna-tabs' ).tabs();

	jQuery( '.wpna-override' ).change(function() {

		if ( jQuery( this ).is( ':checked' ) ) {
			jQuery( jQuery( this ).data('toggle') ).show();
		} else {
			jQuery( jQuery( this ).data('toggle') ).hide();
		}
	});

	jQuery( document ).ready(function() {

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

		function wpnaCheckVideoHeaderType(  ) {
			var fileName, fileExtension;
			// Grab the extenson from the file URL.
			fileName = jQuery( '#fbia_video_header' ).val();

			if ( fileName.length <= 0 ) {
				jQuery( '.wpna-video-type-warning' ).hide();
				return;
			}

			fileName = fileName.split(/[?#]/)[0];
			fileExtension = fileName.substr( ( fileName.lastIndexOf('.') + 1 ) );

			// Valid Facebook Video extensions: https://www.facebook.com/help/218673814818907
			var validExtensions = ['3g2', '3gp', '3gpp', 'asf', 'avi', 'dat', 'divx', 'dv', 'f4v',
				'flv', 'gif', 'm2ts', 'm4v', 'mkv', 'mod', 'mov', 'mp4', 'mpe', 'mpeg', 'mpeg4',
				'mpg', 'mts', 'nsv', 'ogm', 'ogv', 'qt', 'tod', 'ts', 'vob', 'wmv'];

			if ( jQuery.inArray( fileExtension, validExtensions ) >= 0 ) {
				jQuery( '.wpna-video-type-warning' ).hide();
			} else {
				jQuery( '.wpna-video-type-warning' ).show();
			}
		}

		if ( jQuery( '#fbia_video_header' ).length ) {
			jQuery( '#fbia_video_header' ).keyup( function() {
				wpnaCheckVideoHeaderType();
			});

			wpnaCheckVideoHeaderType();
		}

		function checkCustomSponsorURL() {
			// Grab the extenson from the file URL.
			var sponsorURL = jQuery( '#fbia_custom_sponsor' ).val();

			if ( sponsorURL.length <= 0 ) {
				jQuery( '.wpna-custom-sponser-warning' ).hide();
				return;
			}

			if ( /^(https?:\/\/)?(www\.)?facebook.com\/[a-zA-Z0-9(\.\?)?]/i.test( sponsorURL ) ) {
				jQuery( '.wpna-custom-sponser-warning' ).hide();
			} else {
				jQuery( '.wpna-custom-sponser-warning' ).show();
			}

		}

		if ( jQuery( '#fbia_custom_sponsor' ).length ) {
			jQuery( '#fbia_custom_sponsor' ).keyup( function() {
				checkCustomSponsorURL();
			});

			checkCustomSponsorURL();
		}

	});

});
