<?php namespace WSUWP\Plugin\Tools;

class Customizer_Section_Code_Snippets extends Customizer_Section {

	protected $section_title  = 'Code Snippets';
	protected $key_base       = 'code_snippets';
	protected $settings_group = 'tools';


	public function add_section() {

		$code_snippet_posts = get_posts(
			array(
				'post_type'      => 'code_snippet',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		$code_snippets = array();

		foreach ( $code_snippet_posts as $code_snippet ) {
			$code_snippets[ $code_snippet->ID ] = $code_snippet->post_title;
		}

		$this->wp_customize->add_section(
			$this->get( 'section_id' ),
			array(
				'title'      => $this->get( 'section_title' ),
				'priority'   => 30,
				'panel'      => $this->get( 'settings_group' ),
			)
		);

		$this->wp_customize->add_setting(
			$this->get_setting_key( 'is_active', true ),
			array(
				'default'     => false,
				'transport'   => 'refresh',
				'type'        => 'option',
			)
		);

		$this->wp_customize->add_setting(
			$this->get_setting_key( 'site_active', true ),
			array(
				'default'     => '',
				'transport'   => 'refresh',
				'type'        => 'option',
			)
		);

		$this->wp_customize->add_setting(
			$this->get_setting_key( 'global_active', true ),
			array(
				'default'     => '',
				'transport'   => 'refresh',
				'type'        => 'option',
			)
		);

		$this->wp_customize->add_control(
			$this->get_control_key( 'is_active' ),
			array(
				'settings' => $this->get_setting_key( 'is_active',  true ),
				'label'    => 'Enable Site Scripts',
				'section'  => $this->get( 'section_id' ),
				'type'     => 'checkbox',
			)
		);


		$this->wp_customize->add_control(
			new WSUWP_Customize_Control_Multi_Checkbox(
				$this->wp_customize,
				$this->get_control_key( 'site_active' ),
				array(
					'section' => $this->get( 'section_id' ),
					'label'   => 'Site-Wide Active Snippets',
					'settings' => $this->get_setting_key( 'site_active',  true ),
					'type'    => 'checkbox-multiple',
					'choices' => $code_snippets,
				)
			)
		);

		/*$this->wp_customize->add_control(
			new WSUWP_Customize_Control_Multi_Checkbox(
				$this->wp_customize,
				$this->get_control_key( 'wsu_active_scripts' ),
				array(
					'section' => $this->get( 'section_id' ),
					'label'   => 'Site-Wide Active Snippets',
					'settings' => $this->get_setting_key( 'active_scripts',  true ),
					'type'    => 'checkbox-multiple',
					'choices' => array(
						'500' => 'Google Analytics',
					),
				)
			)
		);*/

	}

}

