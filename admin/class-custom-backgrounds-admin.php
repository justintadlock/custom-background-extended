<?php
/**
 * The admin class for the plugin.  This sets up a "Custom Background" meta box on the edit post screen in the 
 * admin.  It loads the WordPress color picker, media views, and a custom JS file for allowing the user to 
 * select options that will overwrite the custom background on the front end for the singular view of the post.
 *
 * @package   CustomBackgroundExtended
 * @since     0.1.0
 * @author    Justin Tadlock <justin@justintadlock.com>
 * @copyright Copyright (c) 2013, Justin Tadlock
 * @link      http://themehybrid.com/plugins/custom-background-extended
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

final class CBE_Custom_Backgrounds_Admin {

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.1.0
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Whether the theme has a custom backround callback for 'wp_head' output.
	 *
	 * @since  0.1.0
	 * @access public
	 * @var    bool
	 */
	public $theme_has_callback = false;

	/**
	 * Plugin setup.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function __construct() {

		/* Only load on the edit post screen. */
		add_action( 'load-post.php',     array( $this, 'load_post' ) );
		add_action( 'load-post-new.php', array( $this, 'load_post' ) );
	}

	/**
	 * Add actions for the edit post screen.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function load_post() {
		$screen = get_current_screen();

		/* If the current theme doesn't support custom backgrounds, bail. */
		if ( !current_theme_supports( 'custom-background' ) || !post_type_supports( $screen->post_type, 'custom-background' ) )
			return;

		/* Get the 'wp_head' callback. */
		$wp_head_callback = get_theme_support( 'custom-background', 'wp-head-callback' );

		/* Checks if the theme has set up a custom callback. */
		$this->theme_has_callback = empty( $wp_head_callback ) || '_custom_background_cb' === $wp_head_callback ? false : true;

		/* Load scripts and styles. */
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/* Add meta boxes. */
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		/* Save metadata. */
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
	}

	/**
	 * Loads scripts/styles for the color picker and image uploader.
	 *
	 * @since  0.1.0
	 * @access public
	 * @param  string  $hook_suffix  The current admin screen.
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {

		/* Make sure we're on the edit post screen before loading media. */
		if ( !in_array( $hook_suffix, array( 'post-new.php', 'post.php' ) ) )
			return;

		wp_register_script( 'cbe-custom-background-extended', CUSTOM_BACKGROUND_EXT_URI . 'js/custom-backgrounds.js', array( 'wp-color-picker', 'media-views' ), false, true );

		wp_localize_script(
			'cbe-custom-background-extended',
			'cbe_custom_backgrounds',
			array(
				'title'  => __( 'Set Background Image', 'custom-background-extended' ),
				'button' => __( 'Set background image', 'custom-background-extended' )
			)
		);

		wp_enqueue_script( 'cbe-custom-background-extended' );
		wp_enqueue_style(  'wp-color-picker'       );
	}

	/**
	 * Add custom meta boxes.
	 *
	 * @since  0.1.0
	 * @access public
	 * @param  string  $post_type
	 * @return void
	 */
	function add_meta_boxes( $post_type ) {

		add_meta_box(
			'cbe-custom-background-extended',
			__( 'Custom Background', 'custom-background-extended' ),
			array( $this, 'do_meta_box' ),
			$post_type,
			'side',
			'core'
		);
	}

	/**
	 * Display the custom background meta box.
	 *
	 * @since  0.1.0
	 * @access public
	 * @param  object  $post
	 * @return void
	 */
	function do_meta_box( $post ) {

		/* Get the background color. */
		$color = trim( get_post_meta( $post->ID, '_custom_background_color', true ), '#' );

		/* Get the background image attachment ID. */
		$attachment_id = get_post_meta( $post->ID, '_custom_background_image_id', true );

		/* If an attachment ID was found, get the image source. */
		if ( !empty( $attachment_id ) )
			$image = wp_get_attachment_image_src( absint( $attachment_id ), 'post-thumbnail' );

		/* Get the image URL. */
		$url = !empty( $image ) && isset( $image[0] ) ? $image[0] : '';

		/* Get the background image settings. */
		$repeat     = get_post_meta( $post->ID, '_custom_background_repeat',     true );
		$position_x = get_post_meta( $post->ID, '_custom_background_position_x', true );
		$position_y = get_post_meta( $post->ID, '_custom_background_position_y', true );
		$attachment = get_post_meta( $post->ID, '_custom_background_attachment', true );

		/* Get theme mods. */
		$mod_repeat     = get_theme_mod( 'background_repeat',     'repeat' );
		$mod_position_x = get_theme_mod( 'background_position_x', 'left'   );
		$mod_position_y = get_theme_mod( 'background_position_y', 'top'    );
		$mod_attachment = get_theme_mod( 'background_attachment', 'scroll' );

		/**
		 * Make sure values are set for the image options.  This should always be set so that we can
		 * be sure that the user's background image overwrites the default/WP custom background settings.
		 * With one theme, this doesn't matter, but we need to make sure that the background stays 
		 * consistent between different themes and different WP custom background settings.  The data 
		 * will only be stored if the user selects a background image.
		 */
		$repeat     = !empty( $repeat )     ? $repeat     : $mod_repeat;
		$position_x = !empty( $position_x ) ? $position_x : $mod_position_x;
		$position_y = !empty( $position_y ) ? $position_y : $mod_position_y;
		$attachment = !empty( $attachment ) ? $attachment : $mod_attachment;

		/* Set up an array of allowed values for the repeat option. */
		$repeat_options = array( 
			'no-repeat' => __( 'No Repeat',           'custom-background-extended' ), 
			'repeat'    => __( 'Repeat',              'custom-background-extended' ),
			'repeat-x'  => __( 'Repeat Horizontally', 'custom-background-extended' ),
			'repeat-y'  => __( 'Repeat Vertically',   'custom-background-extended' ),
		);

		/* Set up an array of allowed values for the position-x option. */
		$position_x_options = array( 
			'left'   => __( 'Left',   'custom-background-extended' ), 
			'right'  => __( 'Right',  'custom-background-extended' ),
			'center' => __( 'Center', 'custom-background-extended' ),
		);

		/* Set up an array of allowed values for the position-x option. */
		$position_y_options = array( 
			'top'    => __( 'Top',    'custom-background-extended' ), 
			'bottom' => __( 'Bottom', 'custom-background-extended' ),
			'center' => __( 'Center', 'custom-background-extended' ),
		);

		/* Set up an array of allowed values for the attachment option. */
		$attachment_options = array( 
			'scroll' => __( 'Scroll', 'custom-background-extended' ), 
			'fixed'  => __( 'Fixed',  'custom-background-extended' ),
		); ?>

		<!-- Begin hidden fields. -->
		<?php wp_nonce_field( plugin_basename( __FILE__ ), 'cbe_meta_nonce' ); ?>
		<input type="hidden" name="cbe-background-image" id="cbe-background-image" value="<?php echo esc_attr( $attachment_id ); ?>" />
		<!-- End hidden fields. -->

		<!-- Begin background color. -->
		<p>
			<label for="cbe-background-color"><?php _e( 'Color', 'custom-background-extended' ); ?></label>
			<input type="text" name="cbe-background-color" id="cbe-backround-color" class="cbe-wp-color-picker" value="#<?php echo esc_attr( $color ); ?>" />
		</p>
		<!-- End background color. -->

		<!-- Begin background image. -->
		<p>
			<img class="cbe-background-image-url" src="<?php echo esc_url( $url ); ?>" style="max-width: 100%; display: block;" />
			<a href="#" class="cbe-add-media"><?php _e( 'Set background image', 'custom-background-extended' ); ?></a> 
			<a href="#" class="cbe-remove-media"><?php _e( 'Remove background image', 'custom-background-extended' ); ?></a>
		</p>
		<!-- End background image. -->

		<!-- Begin background image options -->
		<div class="cbe-background-image-options">

			<p>
				<label for="cbe-background-repeat"><?php _e( 'Repeat', 'custom-background-extended' ); ?></label>
				<select class="widefat" name="cbe-background-repeat" id="cbe-background-repeat">
				<?php foreach( $repeat_options as $option => $label ) { ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $repeat, $option ); ?> /><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
			</p>

			<p>
				<label for="cbe-background-position-x"><?php _e( 'Horizontal Position', 'custom-background-extended' ); ?></label>
				<select class="widefat" name="cbe-background-position-x" id="cbe-background-position-x">
				<?php foreach( $position_x_options as $option => $label ) { ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $position_x, $option ); ?> /><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
			</p>

			<?php if ( !$this->theme_has_callback ) { ?>
			<p>
				<label for="cbe-background-position-y"><?php _e( 'Vertical Position', 'custom-background-extended' ); ?></label>
				<select class="widefat" name="cbe-background-position-y" id="cbe-background-position-y">
				<?php foreach( $position_y_options as $option => $label ) { ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $position_y, $option ); ?> /><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
			</p>
			<?php } ?>

			<p>
				<label for="cbe-background-attachment"><?php _e( 'Attachment', 'custom-background-extended' ); ?></label>
				<select class="widefat" name="cbe-background-attachment" id="cbe-background-attachment">
				<?php foreach( $attachment_options as $option => $label ) { ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $attachment, $option ); ?> /><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
			</p>

		</div>
		<!-- End background image options. -->

	<?php }

	/**
	 * Saves the data from the custom backgrounds meta box.
	 *
	 * @since  0.1.0
	 * @access public
	 * @param  int    $post_id
	 * @param  object $post
	 * @return void
	 */
	function save_post( $post_id, $post ) {

		/* Verify the nonce. */
		if ( !isset( $_POST['cbe_meta_nonce'] ) || !wp_verify_nonce( $_POST['cbe_meta_nonce'], plugin_basename( __FILE__ ) ) )
			return;

		/* Get the post type object. */
		$post_type = get_post_type_object( $post->post_type );

		/* Check if the current user has permission to edit the post. */
		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
			return $post_id;

		/* Don't save if the post is only a revision. */
		if ( 'revision' == $post->post_type )
			return;

		/* Sanitize color. */
		$color = preg_replace( '/[^0-9a-fA-F]/', '', $_POST['cbe-background-color'] );

		/* Make sure the background image attachment ID is an absolute integer. */
		$image_id = absint( $_POST['cbe-background-image'] );

		/* If there's not an image ID, set background image options to an empty string. */
		if ( 0 >= $image_id ) {

			$repeat = $position_x = $position_y = $attachment = '';

		/* If there is an image ID, validate the background image options. */
		} else {

			/* White-listed values. */
			$allowed_repeat     = array( 'no-repeat', 'repeat', 'repeat-x', 'repeat-y' );
			$allowed_position_x = array( 'left', 'right', 'center' );
			$allowed_position_y = array( 'top', 'bottom', 'center' );
			$allowed_attachment = array( 'scroll', 'fixed' );

			/* Make sure the values have been white-listed. Otherwise, set an empty string. */
			$repeat     = in_array( $_POST['cbe-background-repeat'],     $allowed_repeat )     ? $_POST['cbe-background-repeat']     : '';
			$position_x = in_array( $_POST['cbe-background-position-x'], $allowed_position_x ) ? $_POST['cbe-background-position-x'] : '';
			$position_y = in_array( $_POST['cbe-background-position-y'], $allowed_position_y ) ? $_POST['cbe-background-position-y'] : '';
			$attachment = in_array( $_POST['cbe-background-attachment'], $allowed_attachment ) ? $_POST['cbe-background-attachment'] : '';
		}

		/* Set up an array of meta keys and values. */
		$meta = array(
			'_custom_background_color'      => $color,
			'_custom_background_image_id'   => $image_id,
			'_custom_background_repeat'     => $repeat,
			'_custom_background_position_x' => $position_x,
			'_custom_background_position_y' => $position_y,
			'_custom_background_attachment' => $attachment,
		);

		/* Loop through the meta array and add, update, or delete the post metadata. */
		foreach ( $meta as $meta_key => $new_meta_value ) {

			/* Get the meta value of the custom field key. */
			$meta_value = get_post_meta( $post_id, $meta_key, true );

			/* If a new meta value was added and there was no previous value, add it. */
			if ( $new_meta_value && '' == $meta_value )
				add_post_meta( $post_id, $meta_key, $new_meta_value, true );

			/* If the new meta value does not match the old value, update it. */
			elseif ( $new_meta_value && $new_meta_value != $meta_value )
				update_post_meta( $post_id, $meta_key, $new_meta_value );

			/* If there is no new meta value but an old value exists, delete it. */
			elseif ( '' == $new_meta_value && $meta_value )
				delete_post_meta( $post_id, $meta_key, $meta_value );
		}

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

CBE_Custom_Backgrounds_Admin::get_instance();

?>