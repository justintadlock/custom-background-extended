<?php

class CB_Custom_Backgrounds_Admin {

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

		/* Only load on the edit post screen. */
		add_action( 'load-post.php',     array( $this, 'load_post' ) );
		add_action( 'load-post-new.php', array( $this, 'load_post' ) );
	}

	/**
	 * Add actions for the edit post screen.
	 */
	public function load_post() {

		/* If the current theme doesn't support custom backgrounds, bail. */
		if ( !current_theme_supports( 'custom-background' ) )
			return;

		/* Load scripts and styles. */
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/* Add meta boxes. */
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		/* Save metadata. */
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
	}

	/**
	 * Loads scripts/styles for the color picker and image uploader.
	 */
	public function enqueue_scripts( $hook_suffix ) {

		/* Make sure we're on the edit post screen before loading media. */
		if ( !in_array( $hook_suffix, array( 'post-new.php', 'post.php' ) ) )
			return;

		wp_register_script( 'cb-custom-backgrounds', CUSTOM_BACKGROUNDS_URI . 'js/custom-backgrounds.js', array( 'media-views' ), false, true );

		wp_localize_script(
			'cb-custom-backgrounds',
			'cb_custom_backgrounds',
			array(
				'title'  => __( 'Set Background Image', 'custom-backgrounds' ),
				'button' => __( 'Set background image', 'custom-backgrounds' )
			)
		);

		wp_enqueue_script( 'cb-custom-backgrounds' );
		wp_enqueue_script( 'wp-color-picker'       );
		wp_enqueue_style(  'wp-color-picker'       );
	}

	/**
	 * Add custom meta boxes.
	 */
	function add_meta_boxes( $post_type ) {

		if ( !post_type_supports( $post_type, 'custom-background' ) )
			return;

		add_meta_box(
			'cb-custom-backgrounds',
			__( 'Custom Background', 'custom-backgrounds' ),
			array( $this, 'do_meta_box' ),
			$post_type,
			'side',
			'core'
		);
	}

	/**
	 * Display the custom background meta box.
	 */
	function do_meta_box( $post ) {

		/* Get the background color. */
		$color = trim( get_post_meta( $post->ID, 'cb_custom_background_color', true ), '#' );

		/* Get the background image attachment ID. */
		$attachment_id = get_post_meta( $post->ID, 'cb_custom_background_image', true );

		/* If an attachment ID was found, get the image source. */
		if ( !empty( $attachment_id ) )
			$image = wp_get_attachment_image_src( absint( $attachment_id ), 'post-thumbnail' );

		/* Get the image URL. */
		$url = !empty( $image ) && isset( $image[0] ) ? $image[0] : '';

		/* Get the background image settings. */
		$repeat     = get_post_meta( $post->ID, 'cb_custom_background_repeat',     true );
		$position_x = get_post_meta( $post->ID, 'cb_custom_background_position_x', true );
		$attachment = get_post_meta( $post->ID, 'cb_custom_background_attachment', true );

		/* Get theme mods. */
		$mod_repeat     = get_theme_mod( 'background_repeat' );
		$mod_position_x = get_theme_mod( 'background_position_x' );
		$mod_attachment = get_theme_mod( 'background_attachment' );

		/* Set up an array of allowed values for the repeat option. */
		$repeat_options = array( 
			''          => sprintf( __( 'Default (%s)',      'custom-backgrounds' ), $mod_repeat ),
			'no-repeat' =>          __( 'No Repeat',         'custom-backgrounds' ), 
			'repeat'    =>          __( 'Repeat',            'custom-backgrounds' ),
			'repeat-x'  =>          __( 'Horizontal Repeat', 'custom-backgrounds' ),
			'repeat-y'  =>          __( 'Vertical Repeat',   'custom-backgrounds' ),
		); 

		/* Set up an array of allowed values for the position-x option. */
		$position_x_options = array( 
			''          => sprintf( __( 'Default (%s)', 'custom-backgrounds' ), $mod_position_x ),
			'left'   =>             __( 'Left',         'custom-backgrounds' ), 
			'right'  =>             __( 'Right',        'custom-backgrounds' ),
			'center' =>             __( 'Center',       'custom-backgrounds' ),
		); 

		/* Set up an array of allowed values for the attachment option. */
		$attachment_options = array( 
			''       => sprintf( __( 'Default (%s)', 'custom-backgrounds' ), $mod_attachment ),
			'scroll' =>          __( 'Scroll',       'custom-backgrounds' ), 
			'fixed'  =>          __( 'Fixed',        'custom-backgrounds' ),
		); ?>

		<!-- Begin hidden fields. -->
		<?php wp_nonce_field( plugin_basename( __FILE__ ), 'cb_meta_nonce' ); ?>
		<input type="hidden" name="cb-background-image" id="cb-background-image" value="<?php echo esc_attr( $attachment_id ); ?>" />
		<!-- End hidden fields. -->

		<!-- Begin background color. -->
		<p>
			<label for="cb-background-color"><?php _e( 'Color', 'custom-backgrounds' ); ?></label>
			<br />
			<input type="text" name="cb-background-color" id="cb-backround-color" class="cb-wp-color-picker" value="#<?php echo esc_attr( $color ); ?>" />
		</p>
		<!-- End background color. -->

		<!-- Begin background image. -->
		<p>
			<img class="cb-background-image-url" src="<?php echo esc_url( $url ); ?>" style="max-width: 100%; display: block;" />
			<a href="#" class="cb-add-media"><?php _e( 'Set background image', 'custom-backgrounds' ); ?></a> 
			<a href="#" class="cb-remove-media"><?php _e( 'Remove background image', 'custom-backgrounds' ); ?></a>
		</p>
		<!-- End background image. -->

		<!-- Begin background image options -->
		<div class="cb-background-image-options">

			<p>
				<label for="cb-background-repeat"><?php _e( 'Repeat', 'custom-backgrounds' ); ?></label>
				<select class="widefat" name="cb-background-repeat" id="cb-background-repeat">
				<?php foreach( $repeat_options as $option => $label ) { ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $repeat, $option ); ?> /><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
			</p>

			<p>
				<label for="cb-background-position-x"><?php _e( 'Horizontal Position', 'custom-backgrounds' ); ?></label>
				<select class="widefat" name="cb-background-position-x" id="cb-background-position-x">
				<?php foreach( $position_x_options as $option => $label ) { ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $position_x, $option ); ?> /><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
			</p>

			<p>
				<label for="cb-background-attachment"><?php _e( 'Attachment', 'custom-backgrounds' ); ?></label>
				<select class="widefat" name="cb-background-attachment" id="cb-background-attachment">
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
	 */
	function save_post( $post_id, $post ) {

		/* Verify the nonce. */
		if ( !isset( $_POST['cb_meta_nonce'] ) || !wp_verify_nonce( $_POST['cb_meta_nonce'], plugin_basename( __FILE__ ) ) )
			return;

		/* Get the post type object. */
		$post_type = get_post_type_object( $post->post_type );

		/* Check if the current user has permission to edit the post. */
		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
			return $post_id;

		/* Don't save if the post is only a revision. */
		if ( 'revision' == $post->post_type )
			return;

		$image_id = absint( $_POST['cb-background-image'] );

		if ( 0 >= $image_id ) {
			$repeat     = '';
			$position_x = '';
			$attachment = '';
		} else {
			$repeat     = strip_tags( $_POST['cb-background-repeat'] );
			$position_x = strip_tags( $_POST['cb-background-position-x'] );
			$attachment = strip_tags( $_POST['cb-background-attachment'] );
		}

		$meta = array(
			'cb_custom_background_color'      => trim( strip_tags( $_POST['cb-background-color'] ), '#' ),
			'cb_custom_background_image'      => $image_id,
			'cb_custom_background_repeat'     => $repeat,
			'cb_custom_background_position_x' => $position_x,
		//	'cb_custom_background_position_y' => strip_tags( $_POST['cb-background-position-y'] ),
			'cb_custom_background_attachment' => $attachment,
		);

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

CB_Custom_Backgrounds_Admin::get_instance();

?>