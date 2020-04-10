<?php namespace WSUWP\Plugin\Tools;


class Plugin {

	public function init() {

		require_once __DIR__ . '/include-options.php';
		require_once __DIR__ . '/include-customizer.php';

		$this->init_modules();

	}

	protected static function init_modules() {

		require_once self::get_module_dir() . '/code-snippets/code-snippets.php';

	}


	public static function get_plugin_dir() {

		return plugin_dir_path( dirname( __FILE__ ) );

	}


	public static function get_module_dir() {

		return plugin_dir_path( dirname( __FILE__ ) ) . '/modules/';

	}

}

(new Plugin)->init();