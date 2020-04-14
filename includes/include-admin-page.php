<?php namespace WSUWP\Plugin\Tools;


class Admin_Page {

	public function init() {

		if ( is_admin() ) {

			add_action( 'admin_menu', __CLASS__ . '::register_page' );

		}

	}


	public static function register_page() {

		add_menu_page(
			'WSU Tools',
			'WSU Tools',
			'manage_options',
			'wsu-tools',
			__CLASS__ . '::the_page',
			'dashicons-admin-tools',
			20
		);

	}


	public static function the_page() {

		echo 'Go Cougs';

	}

}

(new Admin_Page)->init();
