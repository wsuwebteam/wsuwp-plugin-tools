<?php namespace WSUWP\Plugin\Tools;


class Options {

	protected static $theme_key     = 'wsuwp_theme_options';
	protected static $settings_key  = 'wsuwp_site_options';
	protected static $options       = array(
		'tools' => array(
			'code_snippets' => array(
				'is_active' => false,
				'site_active' => '',
				'wsu_active' => '',
			),
		),
	);


	public static function get( $property ) {

		switch ( $property ) {

			case 'settings_key':
				return self::$settings_key;
			case 'theme_key':
				return self::$theme_key;
			case 'options':
				return self::$options;
			default:
				return '';
		}
	}


	public function init() {

		add_action( 'init', __CLASS__ . '::set_options', 9 );

		if ( is_customize_preview() ) {

			add_action( 'wp_head', __CLASS__ . '::set_options', 1 );

		}

	}


	public static function set_options() {

		$theme_options  = get_theme_mod( self::get( 'theme_key' ), array() );

		$site_options   = get_option( self::get( 'settings_key' ) );

		$site_options   = ( is_array( $site_options ) ) ? $site_options : array();
		$site_options   = apply_filters( 'wsu_wds_options', $site_options, 'site_options' );
		$theme_options  = apply_filters( 'wsu_wds_options', $theme_options, 'theme_options' );

		self::merge_option_sets( self::get( 'options' ), $site_options, $theme_options );

	}


	protected static function merge_option_sets( $default_options, $site_options, $theme_options ) {

		foreach ( $default_options as $group => $objects ) {

			foreach ( $objects as $object => $properties ) {

				foreach ( $properties as $property => $value ) {

					if ( ! empty( $theme_options[ $group ][ $object ] ) && array_key_exists( $property, $theme_options[ $group ][ $object ] ) ) {

						$new_value = $theme_options[ $group ][ $object ][ $property ];
						self::set_option( $group, $object, $property, $new_value );

					} elseif ( ! empty( $site_options[ $group ][ $object ] ) && array_key_exists( $property, $site_options[ $group ][ $object ] ) ) {


						$new_value = $site_options[ $group ][ $object ][ $property ];
						self::set_option( $group, $object, $property, $new_value );

					}
				}
			}
		}
	}

	protected static function set_option( $group, $object, $property, $value ) {

		if ( empty( self::$options[ $group ] ) ) {

			self::$options[ $group ] = array(
				$object => array(
					$property => $value,
				),
			);

		} elseif ( empty( self::$options[ $group ][ $object ] ) ) {

			self::$options[ $group ][ $object ] = array(
				$property => $value,
			);
		} else {
			self::$options[ $group ][ $object ][ $property ] = $value;
		}

	}


	public static function get_option( $group, $object, $property, $default = '' ) {

		$options = self::get( 'options' );

		if ( ! empty( $options[ $group ] ) && ! empty( $options[ $group ][ $object ] ) && isset( $options[ $group ][ $object ][ $property ] ) ) {

			$property_value = $options[ $group ][ $object ][ $property ];

			return $property_value;

		} else {

			return $default;

		}

	}

	public static function get_options( $group, $object, $default = array() ) {

		$options = self::get( 'options' );

		if ( ! empty( $options[ $group ] ) && ! empty( $options[ $group ][ $object ] ) ) {

			return $options[ $group ][ $object ];

		} else {

			return $default;

		}

	}

}

( new Options )->init();
