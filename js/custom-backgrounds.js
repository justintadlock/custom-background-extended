jQuery( document ).ready( function( $ ) {

	/* === Begin color picker JS. === */

	/* Add the WordPress color picker to our custom color input. */
	$( '.cbe-wp-color-picker' ).wpColorPicker();

	/* Hide the "Color" label. */
	$( 'label[for="cbe-background-color"]' ).hide();

	/* === End color picker JS. === */

	/* === Begin background image JS. === */

	/* If the background <img> source has a value, show it.  Otherwise, hide. */
	if ( $( '.cbe-background-image-url' ).attr( 'src' ) ) {
		$( '.cbe-background-image-url' ).show();
	} else {
		$( '.cbe-background-image-url' ).hide();
	}

	/* If there's a value for the background image input. */
	if ( $( 'input#cbe-background-image' ).val() ) {

		/* Hide the 'set background image' link. */
		$( '.cbe-add-media-text' ).hide();

		/* Show the 'remove background image' link, the image, and extra options. */
		$( '.cbe-remove-media, .cbe-background-image-options, .cbe-background-image-url' ).show();
	}

	/* Else, if there's not a value for the background image input. */
	else {

		/* Show the 'set background image' link. */
		$( '.cbe-add-media-text' ).show();

		/* Hide the 'remove background image' link, the image, and extra options. */
		$( '.cbe-remove-media, .cbe-background-image-options, .cbe-background-image-url' ).hide();
	}

	/* When the 'remove background image' link is clicked. */
	$( '.cbe-remove-media' ).click(
		function( j ) {

			/* Prevent the default link behavior. */
			j.preventDefault();

			/* Set the background image input value to nothing. */
			$( '#cbe-background-image' ).val( '' );

			/* Show the 'set background image' link. */
			$( '.cbe-add-media-text' ).show();

			/* Hide the 'remove background image' link, the image, and extra options. */
			$( '.cbe-remove-media, .cbe-background-image-url, .cbe-background-image-options' ).hide();
		}
	);

	/**
	 * The following code deals with the custom media modal frame.  It is a modified version 
	 * of Thomas Griffin's New Media Image Uploader example plugin.
	 *
	 * @link      https://github.com/thomasgriffin/New-Media-Image-Uploader
	 * @license   http://www.opensource.org/licenses/gpl-license.php
	 * @author    Thomas Griffin <thomas@thomasgriffinmedia.com>
	 * @copyright Copyright 2013 Thomas Griffin
	 */

	// Prepare the variable that holds our custom media manager.
	var cbe_custom_backgrounds_frame;

	/* When the 'set background image' link is clicked. */
	$( '.cbe-add-media' ).click( 

		function( j ) {

			/* Prevent the default link behavior. */
			j.preventDefault();

			// If the frame already exists, open it.
			if ( cbe_custom_backgrounds_frame ) {
				cbe_custom_backgrounds_frame.open();
				return;
			}

			// Create the media frame.
			cbe_custom_backgrounds_frame = wp.media.frames.cbe_custom_backgrounds_frame = wp.media( 
				{

					// We can pass in a custom class name to our frame.
					className: 'media-frame cbe-custom-background-extended-frame',

					// Frame type ('select' or 'post').
					frame: 'select',

					// Whether to allow multiple images
					multiple: false,

					// Custom frame title.
					title: cbe_custom_backgrounds.title,

					// Media type allowed.
					library: {
						type: 'image'
					},

					// Custom "insert" button.
					button: {
						text:  cbe_custom_backgrounds.button
					}
				}
			);

			// Do stuff with the data when an image has been selected.
			cbe_custom_backgrounds_frame.on( 'select', 

				function() {

					// Construct a JSON representation of the model.
					var media_attachment = cbe_custom_backgrounds_frame.state().get( 'selection' ).first().toJSON();

					// Send the attachment ID to our custom input field via jQuery.
					$( '#cbe-background-image').val( media_attachment.id );
					$( '.cbe-background-image-url' ).attr( 'src', media_attachment.url );
					$( '.cbe-add-media-text' ).hide();

					$( '.cbe-background-image-url, .cbe-remove-media, .cbe-background-image-options' ).show();
				}
			);

			// Open up the frame.
			cbe_custom_backgrounds_frame.open();
		}
	);

	/* === End background image JS. === */

});