<?php
/**
 * Created by PhpStorm.
 * User: mitch
 * Date: 2018-12-12
 * Time: 08:28
 */

/**
 * This class generates the filters used.
 *
 * This filter returns the available options, these should be placed in the list as list items.
 * Class DJC_Ajax_FilterLoader
 */
class DJC_Ajax_FilterLoader {
	
	private $meta_keys;
	
	private $product_ids;
	
	private $query_args;
	
	private $request;
	
	public function __construct()
	{
	}
	
	private function init(): void
	{
		$this->request = $_POST['data'];
	}
	
	public function the_filter_callback(): void
	{
		\ob_clean();
		
		$this->init();
		
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
	private function preg_grep_keys($pattern, $input, $flags = 0): array
{
		return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
	}
}

new DJC_Ajax_FilterLoader();