<?php namespace WSUWP\Plugin\Tools;


class Customizer {

	protected static $panel_id = 'wsu_tools_panel';


	public static function get( $property ) {

		switch ( $property ) {
			case 'panel_id':
				return self::$panel_id;
			default:
				return '';
		}
	}


	public function __construct() {

		require_once Plugin::get_plugin_dir() . 'customizer/sections/customizer-section.php';

	}


	public static function init() {

		add_action( 'customize_register', __CLASS__ . '::add_customizer_options' );

	}



	public static function add_customizer_options( $wp_customize ) {

		require_once Plugin::get_plugin_dir() . 'customizer/controls/wsuwp-controls.php';

		$wp_customize->add_panel(
			'tools',
			array(
				'priority'       => 10,
				'capability'     => 'delete_users',
				'title'          => 'WSU Tools',
				'description'    => 'Add Tools & Features',
			)
		);

		$customizer_section_classes = array(
			'Code_Snippets'     => 'code-snippets',
		);

		$theme_key    = Options::get( 'theme_key' );
		$settings_key = Options::get( 'settings_key' );

		foreach ( $customizer_section_classes as $section_class_slug => $file_slug ) {

			if ( self::require_class( $file_slug ) ) {

				$class_name = __NAMESPACE__ . '\Customizer_Section_' . $section_class_slug;

				if ( class_exists( $class_name ) ) {

					$section = new $class_name( $wp_customize, 'tools', $theme_key, $settings_key );
					$section->add_section();
	
				} // End if
			} // End if
		}

	}

	public static function require_class( $file_slug ) {

		$class_file = Plugin::get_plugin_dir() . 'customizer/sections/customizer-section-' . $file_slug . '.php';

		if ( file_exists( $class_file ) ) {

			require_once $class_file;

			return true;

		} else {

			return false;

		}

	}

}

( new Customizer() )->init();
