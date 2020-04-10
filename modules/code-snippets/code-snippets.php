<?php namespace WSUWP\Plugin\Tools;


class Code_Snippets {

	protected static $post_type = 'code_snippet';
	protected static $snippet_types = array(
		'tracking-html' => 'Tracking/Retargetting (HTML only)',
		'tracking-js'   => 'Tracking/Retargetting (JS)',
		'custom-js'     => 'Custom JS',
		'meta'          => 'Meta Tag (HTML only)',
	);
	protected static $nonce = 'wsuwp_tools_code_snippet_nonce';
	protected static $nonce_action = 'wsuwp_tools_code_snippet_nonce_save_post';


	public static function get( $property ) {

		switch ( $property ) {

			case 'post_type':
				return self::$post_type;
			case 'snippet_types':
				return self::$snippet_types;
			case 'nonce':
				return self::$nonce;
			case 'nonce_action':
				return self::$nonce_action;
			default:
				return '';

		}
	}

	public function init() {

		add_action( 'init', __CLASS__ . '::init_active' );

	}


	public static function init_active() {

		if ( ! empty( Options::get_option( 'tools', 'code_snippets', 'is_active', false ) ) ) {

			self::register_post_type();

			add_action( 'edit_form_after_title', __CLASS__ . '::render_editor' );

			add_action( 'save_post_code_snippet', __CLASS__ . '::save', 10, 3 );

		}

	}


	public static function register_post_type() {

		$labels = array(
			'name'                  => 'Code Snippet',
			'singular_name'         => 'Code Snippet',
			'menu_name'             => 'Code Snippets',
			'name_admin_bar'        => 'Code Snippet',
			'add_new'               => 'Add New',
			'add_new_item'          => 'Add New Code Snippet',
			'new_item'              => 'New Code Snippet',
			'edit_item'             => 'Edit Code Snippet',
			'view_item'             => 'View Code Snippet',
			'all_items'             => 'All Code Snippets',
			'search_items'          => 'Search Code Snippet',
			'parent_item_colon'     => 'Parent code snippets:',
			'not_found'             => 'No code snippets found.',
			'not_found_in_trash'    => 'No code snippets found in Trash.',
			'featured_image'        => 'code snippet Cover Image',
			'set_featured_image'    => 'Set cover image',
			'remove_featured_image' => 'Remove cover image',
			'use_featured_image'    => 'Use as cover image',
			'archives'              => 'code snippet archives',
			'insert_into_item'      => 'Insert into Code Snippet',
			'uploaded_to_this_item' => 'Uploaded to this Code Snippet',
			'filter_items_list'     => 'Filter code snippets list',
			'items_list_navigation' => 'code snippets list navigation',
			'items_list'            => 'code snippets list',
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'code-snippet' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'author' ),
		);

		if ( is_super_admin() ) {

			$args['show_ui'] = true;

		}

		register_post_type( self::get( 'post_type' ), $args );

	}


	public static function render_editor( $post ) {

		if ( 'code_snippet' === $post->post_type ) {

			$type     = get_post_meta( $post->ID, 'wsuwp_code_snippet_type', true );
			$location = get_post_meta( $post->ID, 'wsuwp_code_snippet_location', true );
			$snippet  = get_post_meta( $post->ID, 'wsuwp_code_snippet', true );

			wp_nonce_field( self::get( 'nonce_action' ), self::get( 'nonce' ) );

			include __DIR__ . '/templates/editor.php';

		}

	}


	public static function save( $post_id, $post, $update ) {

		$save_args = array(
			'wsuwp_code_snippet_type'     => array(),
			'wsuwp_code_snippet_location' => array(),
			'wsuwp_code_snippet'          => array(
				'sanitize_callback' => __CLASS__ . '::sanitize_snippet',
			),
		);

		if ( $update && 'code_snippet' === $post->post_type ) {

			require_once Plugin::get_plugin_dir() . '/classes/class-save-post.php';

			$save_post = new Save_Post( $save_args, self::get( 'nonce_action' ), self::get( 'nonce' ) );

			$save_post->save_post( $post_id, $post, $update );

		}

	}


	public static function sanitize_snippet( $value ) {

		return $value;

	}

}

(new Code_Snippets)->init();
