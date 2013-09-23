<?php

class CB_Custom_Backgrounds_Filter {

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.1.0
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * The background color property.
	 *
	 * @since  0.1.0
	 * @access public
	 * @var    string
	 */
	public $color = '';

	/**
	 * The background image property.
	 *
	 * @since  0.1.0
	 * @access public
	 * @var    string
	 */
	public $image = '';

	/**
	 * The background repeat property.  Allowed: 'no-repeat', 'repeat', 'repeat-x', 'repeat-y'.
	 *
	 * @since  0.1.0
	 * @access public
	 * @var    string
	 */
	public $repeat = 'repeat';

	/**
	 * The horizontal value of the background position property.  Allowed: 'left', 'right', 'center'.
	 *
	 * @since  0.1.0
	 * @access public
	 * @var    string
	 */
	public $position_x = 'left';

	/**
	 * The background attachment property.  Allowed: 'scroll', 'fixed'.
	 *
	 * @since  0.1.0
	 * @access public
	 * @var    string
	 */
	public $attachment = 'scroll';

	/**
	 * Plugin setup.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
	}

	public function template_redirect() {

		/* If the current theme doesn't support custom backgrounds, bail. */
		if ( !current_theme_supports( 'custom-background' ) )
			return;

		add_filter( 'theme_mod_background_color', array( $this, 'background_color' ) );
		add_filter( 'theme_mod_background_image', array( $this, 'background_image' ) );

		//	add_filter( 'theme_mod_background_position_y', array( $this, 'background_position_y' ) );
			add_filter( 'theme_mod_background_repeat',     array( $this, 'background_repeat'     ) );
			add_filter( 'theme_mod_background_position_x', array( $this, 'background_position_x' ) );
			add_filter( 'theme_mod_background_attachment', array( $this, 'background_attachment' ) );
	}

	public function background_color( $color ) {

		if ( is_singular() ) {

			$new_color = get_post_meta( get_queried_object_id(), 'cb_custom_background_color', true );

			return !empty( $new_color ) ? preg_replace( '/[^0-9a-fA-F]/', '', $new_color ) : $color;
		}

		return $color;
	}

	public function background_image( $image ) {

		if ( is_singular() ) {

			$attachment_id = get_post_meta( get_queried_object_id(), 'cb_custom_background_image', true );

			if ( !empty( $attachment_id ) ) {
				// todo - need custom image size
				$new_image = wp_get_attachment_image_src( $attachment_id, 'full' );

				$this->image = $new_image[0];

				return !empty( $new_image ) ? esc_url( $new_image[0] ) : $image;
			}
		}

		return $image;
	}

	public function background_repeat( $repeat ) {

		if ( is_singular() ) {

			$new_repeat = get_post_meta( get_queried_object_id(), 'cb_custom_background_repeat', true );

			return !empty( $new_repeat ) ? $new_repeat : $repeat;
		}

		return $repeat;
	}

	public function background_position_x( $position_x ) {

		if ( is_singular() ) {

			$new_position_x = get_post_meta( get_queried_object_id(), 'cb_custom_background_position_x', true );

			return !empty( $new_position_x ) ? $new_position_x : $position_x;
		}

		return $position_x;
	}

	public function background_position_y( $position_y ) {

		if ( is_singular() ) {

			$new_position_y = get_post_meta( get_queried_object_id(), 'cb_custom_background_position_y', true );

			return !empty( $new_position_y ) ? $new_position_y : $position_y;
		}

		return $position_y;
	}

	public function background_attachment( $attachment ) {

		if ( is_singular() ) {

			$new_attachment = get_post_meta( get_queried_object_id(), 'cb_custom_background_attachment', true );

			return !empty( $new_attachment ) ? $new_attachment : $attachment;
		}

		return $attachment;
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



CB_Custom_Backgrounds_Filter::get_instance();

?>