<?php
/**
 * Created by PhpStorm.
 * User: mitch
 * Date: 2018-11-30
 * Time: 14:22
 */

class DJC_Products extends \Elementor\Widget_Base {
	
	private $is_added_product_filter = false;
	
	public function get_name() {
		return 'djc_products_loader';
	}

	public function get_title() {
		return __( 'Products loader', 'vmw' );
	}

	public function get_icon() {
		return 'fa fa-glass';
	}
	
	public function get_categories() {
		return ['djcee_wc', 'djcee'];
	}
	
	protected function _register_controls() {
		$this->_register_display_settings();
	}
	
	protected function render()
	{
		$this->_render_product_template();
	}
	
	final private function _render_product_template()
	{
		$the_query = $this->fetch_all_products();
		
		if( $the_query->have_posts() ) :
			while( $the_query->have_posts() ) :
				
				$the_query->the_post();
				
				print \apply_filters('horizontal_product_card_filter', $the_query->post );
			
			endwhile;
		endif;
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
	
	protected function fetch_all_products()
	{
		if( $this->is_added_product_filter )
		{
			remove_action( 'pre_get_posts', [ wc()->query, 'product_query' ] );
		}
		
		return new \WP_Query( $this-> parse_query_args() );
	}
	
	protected function parse_query_args()
	{
		$query_args = [
			'post_type'             =>  'product',
			'post_status'           =>  'publish',
			'ignore_sticky_posts'   =>  true,
		];
		
		$query_args = $GLOBALS['wp_query']->query_vars;
		add_action( 'pre_get_posts', [ wc()->query, 'product_query' ] );
		$this->is_added_product_filter = true;
		
		$query_args['fields'] = 'ids';
		return $query_args;
	}
}