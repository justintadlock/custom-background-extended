<?php
/**
 * Plugin Name: Custom Backgrounds
 * Plugin URI: http://themehybrid.com/plugins/custom-backgrounds
 * Description: Allows users to create custom backgrounds for individual posts, which are displayed on the single post page.  It works alongside any plugin that supports the WordPress <code>custom-background</code> feature.
 * Version: 0.1.0-beta-1
 * Author: Justin Tadlock
 * Author URI: http://justintadlock.com
 *
 * This plugin was created so that users could create custom backgrounds for individual posts.  It ties
 * into the Wordpress 'custom-background' theme feature.  Therefore, it will only work with themes that 
 * add support for 'custom-background' via 'functions.php.
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU 
 * General Public License as published by the Free Software Foundation; either version 2 of the License, 
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write 
 * to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * @package   CustomBackgrounds
 * @version   0.1.0
 * @since     0.1.0
 * @author    Justin Tadlock <justin@justintadlock.com>
 * @copyright Copyright (c) 2013, Justin Tadlock
 * @link      http://themehybrid.com/plugins/custom-backgrounds
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

final class CB_Custom_Backgrounds {

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.1.0
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Plugin setup.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function __construct() {

		/* Set the constants needed by the plugin. */
		add_action( 'plugins_loaded', array( $this, 'constants' ), 1 );

		/* Internationalize the text strings used. */
		add_action( 'plugins_loaded', array( $this, 'i18n' ), 2 );

		/* Load the functions files. */
		add_action( 'plugins_loaded', array( $this, 'includes' ), 3 );

		/* Load the admin files. */
		add_action( 'plugins_loaded', array( $this, 'admin' ), 4 );

		/* Add post type support. */
		add_action( 'init', array( $this, 'post_type_support' ) );
	}

	/**
	 * Defines constants used by the plugin.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function constants() {

		/* Set constant path to the plugin directory. */
		define( 'CUSTOM_BACKGROUNDS_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

		/* Set the constant path to the plugin directory URI. */
		define( 'CUSTOM_BACKGROUNDS_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
	}

	/**
	 * Loads the initial files needed by the plugin.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function includes() {

		if ( !is_admin() )
			require_once( CUSTOM_BACKGROUNDS_DIR . 'inc/class-custom-backgrounds-filter.php' );
	}

	/**
	 * Loads the translation files.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function i18n() {

		/* Load the translation of the plugin. */
	//	load_plugin_textdomain( 'custom-backgrounds', false, 'custom-backgrounds/languages' );
	}

	/**
	 * Loads the admin functions and files.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function admin() {

		if ( is_admin() )
			require_once( CUSTOM_BACKGROUNDS_DIR . 'admin/class-custom-backgrounds-admin.php' );
	}

	/**
	 * Adds post type support for the 'custom-background' feature.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function post_type_support() {
		add_post_type_support( 'post', 'custom-background' );
		add_post_type_support( 'page', 'custom-background' );
	}

	/**
	 * Returns the instance.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		if ( !self::$instance )
			self::$instance = new self;

		return self::$instance;
	}
}

CB_Custom_Backgrounds::get_instance();

?>