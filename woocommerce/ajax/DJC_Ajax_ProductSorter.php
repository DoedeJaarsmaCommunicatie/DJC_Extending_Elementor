<?php
/**
 * Created by PhpStorm.
 * User: mitch
 * Date: 2018-12-12
 * Time: 08:58
 */

class DJC_Ajax_ProductSorter {
	
	/**
	 * This is the order_by query string. accepted values are rand, name.
	 *
	 * @var  string $orderby
	 */
	private $orderby;
	
	/**
	 * The order string. accepted values are ASC, DESC.
	 *
	 * @var  string order
	 */
	private $order;
	
	/**
	 * An array of integers.
	 *
	 * @var  array $product_ids
	 */
	private $product_ids;
	
	/**
	 * @var  array $query_args
	 */
	private $query_args;
	/**
	 * The sent request.
	 *
	 * @var  array|null
	 */
	private $request;
	
	private $response;
	
	public function __construct()
	{
		add_action( 'wp_ajax_the_sorting_hat_callback', [ $this, 'the_sorting_hat_callback' ] );
		add_action( 'wp_ajax_nopriv_the_sorting_hat_callback', [ $this, 'the_sorting_hat_callback' ] );
	}
	
	private function init(): void
	{
		$this->setRequest( $_POST['data'] );
		$this->setOrderby( $this->getRequest() ['order_by']);
		$this->setOrder( $this->getRequest() ['order']);
		$this->setProductIds( $this->getRequest() ['post_ids'] );
	}
	
	private function set_query_args(): void
	{
		$this->setQueryArgs( [
			'post_type'         =>  [ 'product' ],
			'posts_per_page'    =>  -1,
			'orderby'           =>  $this->getOrderby(),
			'order'             =>  $this->getOrder(),
			'post__in'          =>  $this->getProductIds(),
		] );
	}
	
	private function queryProducts() {
		$the_query = new \WP_Query( $this->getQueryArgs() );
		
		if( $the_query->have_posts() ) {
			while( $the_query->have_posts() ) {
				$the_query->the_post();
				
				$this->response [ 'data' ] ['posts'] ['data'] []= \apply_filters('create_product_card', $the_query->post->ID, 4);
			}
			\wp_reset_postdata();
		}
	}
	
	public function the_sorting_hat_callback(): void
	{
		\ob_clean();
		
		$this->init();
		
		$this->set_query_args();
		
		$this->queryProducts();
		
		wp_send_json( $this->response );
	}
	
	/**
	 * @return string
	 */
	public function getOrderby() : string {
		return $this->orderby;
	}
	
	/**
	 * @param string $orderby
	 */
	public function setOrderby( string $orderby ) : void {
		$this->orderby = $orderby;
	}
	
	/**
	 * @return string
	 */
	public function getOrder() : string {
		return $this->order;
	}
	
	/**
	 * @param string $order
	 */
	public function setOrder( string $order ) : void {
		$this->order = $order;
	}
	
	/**
	 * @return array
	 */
	public function getQueryArgs() : array {
		return $this->query_args;
	}
	
	/**
	 * @param array $query_args
	 */
	public function setQueryArgs( array $query_args ) : void {
		$this->query_args = $query_args;
	}
	
	/**
	 * @return array
	 */
	public function getProductIds() : array {
		return $this->product_ids;
	}
	
	/**
	 * @param array $product_ids
	 */
	public function setProductIds( array $product_ids ) : void {
		$this->product_ids = $product_ids;
	}
	
	/**
	 * @return array|null
	 */
	public function getRequest() : ?array {
		return $this->request;
	}
	
	/**
	 * @param array|null $request
	 */
	public function setRequest( ?array $request ) : void {
		$this->request = $request;
	}
	
}

new DJC_Ajax_ProductSorter();