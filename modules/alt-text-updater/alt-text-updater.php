<?php namespace WSUWP\Plugin\Tools;


class Alt_Text_Updater {

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

		add_action( 'admin_menu', __CLASS__ . '::register_submenu_page' );


	}


	public static function init_active() {

		// if ( ! empty( Options::get_option( 'tools', 'Alt_Text_Updater', 'is_active', false ) ) ) {

			self::register_submenu_page();

		// }

	}


	public static function register_submenu_page() {

		// if ( is_super_admin() ) {

			add_submenu_page( 
				'wsu-tools',
				'Alt Text Updater',
				'Alt Text Updater',
				'manage_options', 
				'alt-text-updater', 
				__CLASS__ . '::page_content'
			);
		// }
	}

	public static function page_content() {
		
		include __DIR__ . '/templates/editor.php';

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
				'wsuwp_tools_Alt_Text_Updater',
				'Activate Code Snippets',
				__CLASS__ . '::the_metabox',
				$screen,
				'side'
			);
		}

	}


	public static function the_metabox( $post ) {

		$snippets  = get_post_meta( $post->ID, 'wsuwp_post_Alt_Text_Updater', true );

		wp_nonce_field( self::get( 'nonce_action' ), self::get( 'nonce' ) );

		Form_Fields::multi_checkbox(
			'wsuwp_post_Alt_Text_Updater',
			array(
				'choices'       => self::admin_get_Alt_Text_Updater(),
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
			'wsuwp_post_Alt_Text_Updater' => array(),
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

	public static function admin_get_Alt_Text_Updater( $as_array = true, $args = array() ) {

		$default_args = array(
			'post_type'      => 'code_snippet',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		$args = array_merge( $default_args, $args );

		$Alt_Text_Updater = get_posts( $args );

		if ( is_array( $Alt_Text_Updater ) ) {

			$snippet_array = array();

			foreach ( $Alt_Text_Updater as $code_snippet ) {

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


	public static function do_header_snippets() {

		if ( ! empty( self::$snippets['header'] ) ) {

			foreach ( self::$snippets['header'] as $snippet ) {

				echo $snippet;

			}
		}

	}


	public static function set_snippets() {

		$snippets = array();

		$site_snippets = Options::get_option( 'tools', 'Alt_Text_Updater', 'site_active' );

		if ( ! empty( $site_snippets ) ) {

			$site_snippets = explode( ',', $site_snippets );

			$snippets = array_merge( $snippets, $site_snippets );

		}

		if ( is_singular() ) {

			$post_snippets = get_post_meta( get_the_ID(), 'wsuwp_post_Alt_Text_Updater', true );

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

(new Alt_Text_Updater)->init();
