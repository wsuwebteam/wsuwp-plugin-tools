<?php namespace WSUWP\Plugin\Tools;

class WSUWP_Customize_Control_Multi_Checkbox extends \WP_Customize_Control {

	public $type = 'checkbox-multiple';

	public function enqueue() {

		//wp_enqueue_script( 'wsuwp-customize-wsuwp-controls', Plugin::get_plugin_url() . 'customizer/controls/wsuwp-controls.js', array( 'jquery' ) );

	}

	public function render_content() {

		if ( empty( $this->choices ) ) {
			return; 
		}

		include __DIR__ . '/templates/multi-checkbox.php';

	}
	
}
