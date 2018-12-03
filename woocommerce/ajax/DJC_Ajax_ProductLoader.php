<?php
/**
 * Created by PhpStorm.
 * User: mitch
 * Date: 2018-11-27
 * Time: 11:00
 */


/**
 *
 * Class DJC_Ajax_ProductLoader
 *
 * @author Mitch Hijlkema <mitch@hijlkema.xyz>
 * @since 1.0.4
 * @copyright Mitch Hijlkema <mitch@hijlkema.xyz>
 * @license GPL-3.0-or-later
 * @version 2.0.0
 */
class DJC_Ajax_ProductLoader {
	
	/**
	 * This holds the product meta keys used LIKE pa_*
	 *
	 * @var $meta_keys array|null
	 * @since 2.0.0
	 */
	private $meta_keys;
	
	/**
	 * This holds all the posts returned from wp_query
	 *
	 * @var $filtered_posts array|null
	 * @since 2.0.0
	 */
	private $filtered_posts;
	
	/**
	 * The request from the AJAX call
	 *
	 * @var $request array
	 * @since 2.0.0
	 */
	private $request;
	
	/**
	 * The response to return to the ajax request
	 *
	 * @var $response array
	 * @since 2.0.0
	 */
	private $response;
	
	/**
	 * The query args used in
	 *
	 * @var $query_args array
	 * @since 2.0.0
	 */
	private $query_args;
	
	/**
	 * The \WP_Query that fixes everything
	 *
	 * @var $the_query \WP_Query
	 * @since 2.0.0
	 */
	private $the_query;
	
	/**
	 * This variable holds the filters and their filtrate
	 *
	 * @var $filtered_data array
	 * @since 2.0.0
	 */
	private $filtered_data;
	
	/**
	 * The filters included in the page
	 *
	 * @var $filters array|null
	 */
	private $filters;
	
	/**
	 * DJC_Ajax_ProductLoader constructor.
	 */
	public function __construct()
	{
		add_action( 'wp_ajax_filtered_callback', [ $this, 'filtered_callback' ] );
		add_action( 'wp_ajax_nopriv_filtered_callback', [ $this, 'filtered_callback' ] );

	}
	
	/**
	 * Initialize the request and query args to use in later functions
	 *
	 * @return void
	 */
	private function init() {
		$this->query_args = [
			'post_type'         => [ 'product' ],
			'posts_per_page'    => -1,
			'orderby'           => 'name',
			'order'             => 'ASC',
			'meta_query'        =>  [
				'relation'      => 'OR',
			],
			'tax_query'         =>  [
				'relation'      => 'AND',
			],
		];
		
		$this->request = $_POST['data']['filter'];
		# 0 => array (size=2) 'filter' => string 'category' (length=8) 'filtrate' => string 'wit' (length=3)
		
		$this->filters = explode(',', $_POST['data']['helpers']['filters']);
//		print_r( $this->filters ); die;
	
	}
	
	/**
	 * Filters the request data to only get the filters from the request
	 *
	 * @uses $request
	 * @uses $filtered_data
	 * @return void
	 */
	private function filterRequest() {
		
		foreach( $this->request as $d )
		{
			$this->filtered_data [$d['filter']] [] = $d['filtrate'];
		}
		
//		print_r($this->filtered_data); die;
	}
	
	/**
	 * Adds a filter for only the WooCommerce Product Attributes
	 *
	 * @uses preg_grep_keys
	 * @uses $filtered_data
	 * @uses $meta_keys
	 * @uses $query_args
	 * @return void
	 */
	private function paFilter() {
		foreach( $this->preg_grep_keys( '/^pa_/', $this->filtered_data) as $meta_key => $values)
		{
			$this->meta_keys []= $meta_key;
			
			$this->query_args ['tax_query'] [] = [
				'taxonomy'  =>  $meta_key,
				'field'     =>  'slug',
				'terms'     =>  $values,
				'operator'  =>  'IN'
			];
		}
		
	}
	
	/**
	 * Adds a filter for only the product category
	 *
	 * @uses $filtered_data
	 * @uses $query_args
	 * @return void
	 */
	private function categoryFilter() {
		if( isset($this->filtered_data['category'] ) )
		{
			foreach ($this->filtered_data['category'] as $category)
			{
				$this->query_args ['tax_query'] []= [
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => $category,
				];
			}
		}
	}
	
	/**
	 * Adds a filter for only the price
	 *
	 * @uses $filtered_data
	 * @uses $query_args
	 * @return void
	 */
	private function priceFilter() {
		if( isset($this->filtered_data['price_tot'] ) )
		{
			foreach ($this->filtered_data['price_tot'] as $price)
			{
				$this->query_args ['meta_query'] []= [
					'key'       =>  '_price',
					'value'     =>  $price,
					'compare'   =>  '<=',
					'type'      => 'numeric',
				];
			}
		}
		
		if( isset($this->filtered_data['price_between'] ) )
		{
			foreach ($this->filtered_data['price_between'] as $price)
			{
				$this->query_args ['meta_query'] []= [
					'key'       =>  '_price',
					'value'     =>  explode( ',', $price),
					'compare'   =>  'BETWEEN',
					'type'      => 'numeric',
				];
			}
		}
		
		if( isset($this->filtered_data['price_van'] ) )
		{
			foreach ($this->filtered_data['price_van'] as $price)
			{
				$this->query_args ['meta_query'] []= [
					'key'       =>  '_price',
					'value'     =>  $price,
					'compare'   =>  '>=',
					'type'      =>  'numeric',
				];
			}
		}
	}
	
	/**
	 * Creates the \WP_Query variable $the_query
	 *
	 * @see \WP_Query
	 * @uses $the_query
	 * @return void
	 */
	private function query() {
//		print "<pre>";
//		print_r($this->args);
//		print "</pre>";
		
		$this->the_query = new \WP_Query( $this->query_args );

//		print "<pre>";
//		print_r($this->the_query->request);
//		print "</pre>";
	}
	
	/**
	 * Loops the posts
	 *
	 * @uses $the_query
	 * @uses $filtered_posts
	 * @uses $response
	 * @return void
	 */
	private function the_loop() {
		if( $this->the_query->have_posts() )
		{
			while( $this->the_query->have_posts() )
			{
				$this->the_query->the_post();
				
				$this->filtered_posts []= $this->the_query->post->ID;
				
				$this->response [ 'data' ] ['posts'] ['data'] []= \apply_filters('create_product_card', $this->the_query->post->ID, 4);
			}
			$this->response [ 'data' ] [ 'posts' ] ['count'] = $this->the_query->post_count;
			\wp_reset_postdata();
		}
	}
	
	/**
	 * @return void
	 */
	private function the_meta_loop() {
		foreach($this->filters as $filter) {
			$terms = \get_terms(
				[
					'taxonomy'  =>  $filter,
					'object_ids'=>  $this->filtered_posts,
				]
			);
			$this->response [ 'data' ] [ 'filters' ] []= $terms;
			
		}
		
		$this->response [ 'data' ] [ 'used' ] [ 'filters' ] = $this->filters;
		
		
	}
	
	/**
	 * Prints the response fit for the WP_Ajax
	 *
	 * @uses the_loop()
	 * @uses \wp_die() to signal the data is final for response
	 */
	protected function respond() {
		
		$this->the_loop();
		
		$this->the_meta_loop();
	
		wp_send_json( $this->response, 200);
	}
	
	/**
	 * This function is called when the ajax request is made
	 */
	public function filtered_callback()
	{
		\ob_clean();
		
		$this->init();
		
		$this->filterRequest();
		
		$this->categoryFilter();
		
		$this->priceFilter();
		
		$this->paFilter();

		$this->query();
		
		$this->respond();

	}
	
	
	/*
	 * Helpers
	 */
	/**
	 * Filters the array with a regex on the keys
	 *
	 * @param     $pattern
	 * @param     $input
	 * @param int $flags
	 *
	 * @return array
	 */
	private function preg_grep_keys($pattern, $input, $flags = 0) {
		return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
	}
}

new DJC_Ajax_ProductLoader();