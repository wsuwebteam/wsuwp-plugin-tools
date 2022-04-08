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
	protected static $snippets = array(
		'header' => array(),
		'body'   => array(),
		'footer' => array(),
	);


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

			if ( is_admin() ) {

				add_action( 'edit_form_after_title', __CLASS__ . '::render_editor' );

				add_action( 'save_post_code_snippet', __CLASS__ . '::save', 10, 3 );

				add_action( 'save_post', __CLASS__ . '::save_metabox', 10, 3 );

				add_action( 'add_meta_boxes', __CLASS__ . '::register_metabox' );

				add_action( 'admin_menu', __CLASS__ . '::register_page' );

			}

			add_action( 'wp', __CLASS__ . '::set_snippets' );

			add_action( 'wp_head', __CLASS__ . '::do_header_snippets', 99999 );

			add_action( 'wp_body_open', __CLASS__ . '::do_body_snippets', 99999 );

			add_action( 'wp_footer', __CLASS__ . '::do_footer_snippets', 99999 );

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
			'show_ui'            => true,
			'show_in_menu'       => 'wsu-tools',
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


	public static function register_page() {

		// TODO:Fix? $role = ( is_multisite() ) ? 'create_sites' : 'manage_options';

			/*add_submenu_page(
				'wsu-tools',
				'Code Snippets',
				'Code Snippets',
				'edit_posts',
				'edit.php?post_type=code_snippet'
			);*/

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


	public static function register_metabox() {

		$screens = array( 'page', 'post' );

		foreach ( $screens as $screen ) {

			add_meta_box(
				'wsuwp_tools_code_snippets',
				'Activate Code Snippets',
				__CLASS__ . '::the_metabox',
				$screen,
				'side'
			);
		}

	}


	public static function the_metabox( $post ) {

		$snippets  = get_post_meta( $post->ID, 'wsuwp_post_code_snippets', true );

		wp_nonce_field( self::get( 'nonce_action' ), self::get( 'nonce' ) );

		Form_Fields::multi_checkbox(
			'wsuwp_post_code_snippets',
			array(
				'choices'       => self::admin_get_code_snippets(),
				'current_value' => $snippets,
			)
		);

	}


	public static function save( $post_id, $post, $update ) {

		$save_settings = array(
			'wsuwp_code_snippet_type'     => array(),
			'wsuwp_code_snippet_location' => array(),
			'wsuwp_code_snippet'          => array(
				'sanitize_callback' => __CLASS__ . '::sanitize_snippet',
			),
		);

		$save_args = array(
			'nonce'        => self::get( 'nonce' ),
			'nonce_action' => self::get( 'nonce_action' ),
		);

		if ( $update && 'code_snippet' === $post->post_type ) {

			require_once Plugin::get_plugin_dir() . '/wsu-library/utilities/class-save-post.php';

			$save_post = new Save_Post( $save_settings, $save_args );

			$save_post->save_post( $post_id, $post, $update );

		}

	}

	public static function save_metabox( $post_id, $post, $update ) {

		$save_settings = array(
			'wsuwp_post_code_snippets' => array(),
		);

		$save_args = array(
			'nonce'        => self::get( 'nonce' ),
			'nonce_action' => self::get( 'nonce_action' ),
		);

		require_once Plugin::get_plugin_dir() . '/wsu-library/utilities/class-save-post.php';

		$save_post = new Save_Post( $save_settings, $save_args );

		$save_post->save_post( $post_id, $post, $update );

	}


	public static function sanitize_snippet( $value ) {

		return $value;

	}

	public static function admin_get_code_snippets( $as_array = true, $args = array() ) {

		$default_args = array(
			'post_type'      => 'code_snippet',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		$args = array_merge( $default_args, $args );

		$code_snippets = get_posts( $args );

		if ( is_array( $code_snippets ) ) {

			$snippet_array = array();

			foreach ( $code_snippets as $code_snippet ) {

				$snippet_array[ $code_snippet->ID ] = $code_snippet->post_title;

			}

			return $snippet_array;

		} else {

			return array();

		}

	}


	public static function do_footer_snippets() {

		if ( ! empty( self::$snippets['footer'] ) ) {

			foreach ( self::$snippets['footer'] as $snippet ) {

				echo $snippet;

			}

		}

	}

	public static function do_body_snippets() {

		if ( ! empty( self::$snippets['body'] ) ) {

			foreach ( self::$snippets['body'] as $snippet ) {

				echo $snippet;

			}
		}

	}


	public static function do_header_snippets() {

		if ( ! empty( self::$snippets['header'] ) ) {

			foreach ( self::$snippets['header'] as $snippet ) {

				echo $snippet;

			}
		}

	}


	public static function set_snippets() {

		$snippets = array();

		$site_snippets = Options::get_option( 'tools', 'code_snippets', 'site_active' );

		if ( ! empty( $site_snippets ) ) {

			$site_snippets = explode( ',', $site_snippets );

			$snippets = array_merge( $snippets, $site_snippets );

		}

		if ( is_singular() ) {

			$post_snippets = get_post_meta( get_the_ID(), 'wsuwp_post_code_snippets', true );

			if ( ! empty( $post_snippets ) ) {

				$post_snippets = explode( ',', $post_snippets );
	
				$snippets = array_merge( $snippets, $post_snippets );
	
			}
		}

		if ( ! empty( $snippets ) ) {

			foreach ( $snippets as $snippet_id ) {

				$post_meta = get_post_meta( $snippet_id );

				$location = ( ! empty( $post_meta['wsuwp_code_snippet_location'] ) && is_array( $post_meta['wsuwp_code_snippet_location'] ) ) ? reset( $post_meta['wsuwp_code_snippet_location'] ) : false;
				$snippet  = ( ! empty( $post_meta['wsuwp_code_snippet'] ) && is_array( $post_meta['wsuwp_code_snippet'] ) ) ? reset( $post_meta['wsuwp_code_snippet'] ) : '';

				if ( ! empty( $location ) && ! empty( $snippet ) && array_key_exists( $location, self::$snippets ) ) {

					self::$snippets[ $location ][] = $snippet;

				}
			}
		}
	}

}

(new Code_Snippets)->init();
