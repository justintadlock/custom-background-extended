jQuery( document ).ready( function( $ ) {

	/* === Begin color picker JS. === */

	/* Add the WordPress color picker to our custom color input. */
	$( '.cb-wp-color-picker' ).wpColorPicker();

	/* Hide the "Color" label. */
	$( 'label[for="cb-background-color"]' ).hide();

	/* === End color picker JS. === */

	/* === Begin background image JS. === */

	/* If the background <img> source has a value, show it.  Otherwise, hide. */
	if ( $( '.cb-background-image-url' ).attr( 'src' ) ) {
		$( '.cb-background-image-url' ).show();
	} else {
		$( '.cb-background-image-url' ).hide();
	}

	/* If there's a value for the background image input. */
	if ( $( 'input#cb-background-image' ).val() ) {

		/* Hide the 'set background image' link. */
		$( '.cb-add-media' ).hide();

		/* Show the 'remove background image' link, the image, and extra options. */
		$( '.cb-remove-media, .cb-background-image-options, .cb-background-image-url' ).show();
	}

	/* Else, if there's not a value for the background image input. */
	else {

		/* Show the 'set background image' link. */
		$( '.cb-add-media' ).show();

		/* Hide the 'remove background image' link, the image, and extra options. */
		$( '.cb-remove-media, .cb-background-image-options, .cb-background-image-url' ).hide();
	}

	/* When the 'remove background image' link is clicked. */
	$( '.cb-remove-media' ).click(
		function( j ) {

			/* Prevent the default link behavior. */
			j.preventDefault();

			/* Set the background image input value to nothing. */
			$( '#cb-background-image' ).val( '' );

			/* Show the 'set background image' link. */
			$( '.cb-add-media' ).show();

			/* Hide the 'remove background image' link, the image, and extra options. */
			$( '.cb-remove-media, .cb-background-image-url, .cb-background-image-options' ).hide();
		}
	);

	/**
	 * The following code deals with the custom media modal frame for the background image.  It is a 
	 * modified version of Thomas Griffin's New Media Image Uploader example plugin.
	 *
	 * @link      https://github.com/thomasgriffin/New-Media-Image-Uploader
	 * @license   http://www.opensource.org/licenses/gpl-license.php
	 * @author    Thomas Griffin <thomas@thomasgriffinmedia.com>
	 * @copyright Copyright 2013 Thomas Griffin
	 */

	// Prepare the variable that holds our custom media manager.
	var cb_custom_backgrounds_frame;

	/* When the 'set background image' link is clicked. */
	$( '.cb-add-media' ).click( 

		function( j ) {

			/* Prevent the default link behavior. */
			j.preventDefault();

			// If the frame already exists, open it.
			if ( cb_custom_backgrounds_frame ) {
				cb_custom_backgrounds_frame.open();
				return;
			}

			// Create the media frame.
			cb_custom_backgrounds_frame = wp.media.frames.cb_custom_backgrounds_frame = wp.media( 
				{

					// We can pass in a custom class name to our frame.
					className: 'media-frame cb-custom-backgrounds-frame',

					// Frame type ('select' or 'post').
					frame: 'select',

					// Whether to allow multiple images
					multiple: false,

					// Custom frame title.
					title: cb_custom_backgrounds.title,

					// Media type allowed.
					library: {
						type: 'image'
					},

					// Custom "insert" button.
					button: {
						text:  cb_custom_backgrounds.button
					}
				}
			);

			// Do stuff with the data when an image has been selected.
			cb_custom_backgrounds_frame.on( 'select', 

				function() {

					// Construct a JSON representation of the model.
					var media_attachment = cb_custom_backgrounds_frame.state().get( 'selection' ).first().toJSON();

					// Send the attachment ID to our custom input field via jQuery.
					$( '#cb-background-image').val( media_attachment.id );
					$( '.cb-background-image-url' ).attr( 'src', media_attachment.url );
					$( '.cb-add-media' ).hide();

					$( '.cb-background-image-url, .cb-remove-media, .cb-background-image-options' ).show();
				}
			);

			// Open up the frame.
			cb_custom_backgrounds_frame.open();
		}
	);

	/* === End background image JS. === */

});