# Custom Backgrounds

A plugin for allowing users to set a custom background on a per-post basis.  This plugin hooks into the WordPress `custom-background` theme feature and overwrites the values on single post views if the post has been given a custom background.

## Requirements

Your theme must support the [Custom Backgrounds](http://codex.wordpress.org/Custom_Backgrounds) feature for this plugin to work.

	add_theme_support( 'custom-background' );

## Notes

Please don't use this on a live server at the moment.  

Right now, the custom fields this plugin uses are not hidden via the `_` prefix.  This is for debugging purposes.  The custom field keys will be changed later.