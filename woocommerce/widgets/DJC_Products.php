<?php
/**
 * Created by PhpStorm.
 * User: mitch
 * Date: 2018-11-30
 * Time: 14:22
 */

class DJC_Products extends \Elementor\Widget_Base {

	public function get_name() {
		return 'djc_product_loader';
	}

	public function get_title() {
		return __( 'Products loader', 'vmw' );
	}

	public function get_icon() {
		return 'fa fa-martini';
	}
	
	public function get_categories() {
		return ['djcee_wc', 'djcee'];
	}
	
	protected function _register_controls() {
		/**
		 * TODO: Add settings for: [ columns, rows, filter_param, wp_filter ]
		 */
		
		$this->_register_display_settings();
	}
	
	private function _register_display_settings() {
		$this->start_controls_section( 'display-section',
			[
				'label'     =>  __( 'Display', 'djcee'),
				'tab'       =>  \Elementor\Controls_Manager::TAB_CONTENT
			]
		);
		
		$this->add_control( '', []);
		
		$this->end_controls_section();
	}
}