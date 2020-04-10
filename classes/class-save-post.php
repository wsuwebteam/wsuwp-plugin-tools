<?php namespace WSUWP\Plugin\Tools;

/**
 * Use this class as a base for saving post data. The save-post method should be called using
 * the save_post_{posttype} hook.
 *
 * @version: 0.0.1
 * @package https://github.com/washingtonstateuniversity/wsuwp-code-library/blob/master/post/class-save-post.php
 */

class Save_Post {


	/**
	 * Nonce action to use when validating.
	 * @var null|string $nonce_action
	 * @since 0.0.1
	 */
	protected $nonce_action;


	/**
	 * Nonce name to use when validating.
	 * @var null|string $nonce_name
	 * @since 0.0.1
	 */
	protected $nonce_name;


	/**
	 * Array of options to save.
	 * @var array $save_options_array. Options array supports the following settings.
	 *  option_key => save_options - see $save_options_defaults for options and defaults
	 * @since 0.0.1
	 */
	protected $save_options_array = array();


	/**
	 * Array of options to save.
	 * @var array $save_options_defaults Default save options
	 * @since 0.0.1
	 */
	protected $save_options_defaults = array(
		'sanitize_type'     => 'text',  // Type of content to sanitize (optional)
		'sanitize_callback' => false,  // Custom callback to use to sanitize the value (optional).
		'save_default'      => false,  // Save default value if input field is empty (optional).
		'default_value'     => '',     // Default value to save if save_default is true (optional).
	);


	/**
	 * Nonce name to use when validating.
	 * @var null|string $nonce_name
	 * @since 0.0.1
	 */
	protected $save_args = array(
		'only_update'        => true, // Only do save action on update post (not create post).
		'on_autosave'        => false, // Save on autosave - probably not a good idea
	);


	/**
	 * Set up the Save_Post class.
	 * @since 0.0.1
	 *
	 * @param false|array  $options        Save options with the structure of $save_options_array
	 * @param false|string $nonce_action   Action to verify when saving the post.
	 * @param false|string $nonce_name     Nonce name to verify.
	 */
	public function __construct( $options = false, $nonce_action = false, $nonce_name = false, $save_args = false ) {

		// Check if options are provided and call set_options method.
		if ( is_array( $options ) ) {

			$this->set_options( $options );

		} // End if

		// Check if nonce action is provided and call set_nonce_action method.
		if ( ! empty( $nonce_action ) ) {

			$this->set_nonce_action( $nonce_action );

		} // End if

		// Check if nonce name is provided and call set_nonce_name method.
		if ( ! empty( $nonce_name ) ) {

			$this->set_nonce_name( $nonce_name );

		} // End if

		// Check if save args are provided and call set_save_args method.
		if ( is_array( $save_args ) ) {

			$this->set_save_args( $save_args );

		} // End if

	} // End __construct


	/**
	 * Set the $save_options_array property value after setting defaults.
	 * @since 0.0.1
	 *
	 * @param array $options Multidimensional array or an array of keys to save.
	 */
	public function set_options( array $options ) {

		// Check if $options is an array otherwise return WP_Error
		if ( is_array( $options ) ) {

			// Loop through the options and set defaults
			foreach ( $options as $key => $option_array ) {

				/**
				 * If an array of keys ( key1, key2, key3 ) is passed to the to set_options it will assign the
				 * default options to each key. If keys and options are passed it will merge the provide options
				 * with the default options.
				 */
				// Check for multi-dim array and sets the key for it.
				$option_key = ( is_array( $option_array ) ) ? $key : $option_array;

				// Check for multi-dim array and get provided options (or not)
				$option_args = ( is_array( $option_array ) ) ? $option_array : array();

				// Merge provide options with default ones
				$option_args = array_merge( $this->save_options_defaults, $option_args );

				// Set the key in the save_options_array
				$this->save_options_array[ $option_key ] = $option_args;

			} // End foreach
		} else {

			return new \WP_Error( 'Invalid Array', 'The set_options requires an array to be passed to it', $options );

		} // End if

	} // End set_options


	/**
	 * Set the $ounce_action property value.
	 * @since 0.0.1
	 *
	 * @param string $nonce_action Nonce action to use to verify.
	 */
	public function set_nonce_action( string $nonce_action ) {

		$this->nonce_action = $nonce_action;

	} // End set_nonce_action


	/**
	 * Set the $nonce_name property value.
	 * @since 0.0.1
	 *
	 * @param string $nonce_name Nonce action to use to verify.
	 */
	public function set_nonce_name( string $nonce_name ) {

		$this->nonce_name = $nonce_name;

	} // End set_nonce_name


	/**
	 * Set the $set_save_args property value.
	 * @since 0.0.1
	 *
	 * @param array $save_args Array of save options.
	 */
	public function set_save_args( array $save_args ) {

		$this->set_save_args = array_merge( $this->set_save_args, $save_args );

	} // End set_save_args


	/**
	 * Sanitize the value using the given method.
	 * @since 0.0.1
	 *
	 * @param variable $value              Value to sanitize.
	 * @param string   $type               Type of sanitation to use.
	 * @param variable $sanitize_callback  Custom function to sanitize value.
	 */
	public function sanitize_value( $value, $type = 'text', $sanitize_callback = false ) {

		// Check if it has a sanitize callback that works
		if ( ! empty( $sanitize_callback ) && is_callable( $sanitize_callback ) ) {

			// Sanitize the value
			$save_value = call_user_func( $sanitize_callback, $value );

			return $save_value;

		} else {

			// Check the sanitize_type and use the appropriate method. Return error if not found.
			switch ( $type ) {

				case 'text':
					return sanitize_text_field( $value );
					break;

				case 'html':
					return wp_kses_post( $value );
					break;

				default:
					$error = new \WP_Error(
						'Invalid Sanitize Type',
						'The type you are trying to sanitize does not exist',
						array(
							'type'  => $type,
							'value' => $value,
						)
					);
					return $error;
			} // End switch
		} // End if

	} // End sanitize_value


	/**
	 * Method to save post data
	 * @since 0.0.1
	 *
	 * @param int      $post_id  ID of the post being saved.
	 * @param WP_Post  $post     WP_Post object of the post being saved.
	 * @param bool     $update   Is this a post being updated or created.
	 */
	public function save_post( $post_id, $post, $update ) {

		if ( true === $this->check_can_save( $post_id, $post, $update ) ) {

			//OK, lets save something
			$save_options = $this->save_options_array;

			// Loop through all the save options
			foreach ( $save_options as $key => $option_args ) {

				// Check if the key has been sent
				if ( isset( $_REQUEST[ $key ] ) ) {

					$value = $_REQUEST[ $key ];

					$save_value = $this->sanitize_value(
						$value,
						$option_args['sanitize_type'],
						$option_args['sanitize_callback']
					);

					if ( ! is_wp_error( $save_value ) ) {

						// Save
						update_post_meta( $post_id, $key, $save_value );

					} // End if
				} // End if
			} // End foreach
		} // End if
	} // End save_post


	/**
	 * Check if the data can or should be saved.
	 * @since 0.0.1
	 *
	 * @param int      $post_id  ID of the post being saved.
	 * @param WP_Post  $post     WP_Post object of the post being saved.
	 * @param bool     $update   Is this a post being updated or created.
	 *
	 * @return bool True if save is OK.
	 */
	public function check_can_save( $post_id, $post, $update ) {

		// Check if save on update
		if ( $this->save_args['only_update'] && ! $update ) {

			return false;

		} // End if

		// Check if doing autosave
		if ( ! $this->save_args['on_autosave'] && defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {

			return false;

		} // End if

		// If this is a revision, abort
		if ( wp_is_post_revision( $post_id ) ) {

			return false;

		} // End if

		// Verify the nonce before proceeding.
		if ( ! isset( $_REQUEST[ $this->nonce_name ] ) || ! wp_verify_nonce( $_REQUEST[ $this->nonce_name ], $this->nonce_action ) ) {

			return false;

		} // End if

		// Check if the current user has permission to edit the post.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {

			return false;

		} // End if

		return true;

	} // End check_can_save

}