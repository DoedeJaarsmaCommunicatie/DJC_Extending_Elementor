<?php
/**
 * Created by PhpStorm.
 * User: mitch
 * Date: 2018-11-27
 * Time: 13:14
 */

class DJC_WooCommerce_Filters {
	
	public function __construct() {
//		add_filter('create_product_card', [ $this, 'product_card_filter' ], 10, 2);
//		add_filter('create_product_form', [ $this, 'product_form_filter'], 10, 3 );
	}
	
	public function product_card_filter($product_id, $size = 3)
	{
		$post = \get_post($product_id);
		$_product = \wc_get_product( $product_id );
		
		$html = null;
		$html .= sprintf('<div class="%s" id="sale_card_container_%d">', 'col-sm-' . $size, $post->ID);
		$html .= sprintf('<div class="%s" id="sale_card_%d">', 'card sale_card', $post->ID);
		$html .= \get_the_post_thumbnail( $post->post_parent ?: $post->ID, 'medium', [ 'class' => 'align-self-center']);
		$html .= sprintf('<div class="%s" id="sale_card_header_%d">', 'card-header bg-white', $post->ID);
		$html .= sprintf('<h4 class="%s">%s</h4>', 'font-bold', \get_the_title($post));
		$html .= sprintf('<span class="%s" id="sale_price_%d">%s: &euro; %d</span>', 'font-bold text-primary', $post->ID, 'Nu slechts', $_product->get_price());
		$html .= '</div>'; # Close .card-header
		$html .= sprintf('<div class="%s" id="form_container_%d">%s</div>', 'd-flex', $post->ID, \apply_filters('create_product_form', $post->ID, $_product, $post->post_parent));
		
		$html .= '</div>'; # Close .card
		$html .= '</div>'; # Close .col-sm-3
		
		return $html;
	}
	
	public function product_form_filter($productID, \WC_Product $product = null, $parent_id = null) {
		$permalink = \get_permalink( $productID );
		$html = NULL;
		
		$loggedin = is_user_logged_in();
		
		$html .= sprintf('<form class="%s" id="shopping_cart_%d">', 'shopping_cart_form w-25 d-flex', $productID);
		$html .= sprintf('<input class="%s" type="%s" min="%d" name="%s" id="%s" value="%d">', 'frontpage_input', 'tel', 1, 'quantity', 'quantity', 1);
		$html .= sprintf('<input name="%s" class="%s" value="%d" type="%s">', 'variation_id', 'variation_id', $productID, 'hidden');
		$html .= sprintf('<input name="%s" class="%s" value="%d" type="%s">', 'product_id', 'product_id', $parent_id, 'hidden');
		foreach( $product->get_attributes() as $key => $val)
		{
			if( \is_object( $val) )
			{
				continue;
			}
			
			$html .= sprintf('<input name="attribute_%s" data-attribute="%s" type="hidden">',
				$key,
				$val);
		}
		$html .= sprintf('<button type="%s" class="%s" id="add_to_cart_button_%d">%s</button>', 'submit', 'btn btn-light h-100', $productID, '<i class="text-secondary fas fa-shopping-basket"></i>');
		$html .= '</form>'; # end .shopping_cart_form
		if( $loggedin )
		{
			$html .= sprintf('<form class="%s" data-action="%s" data-variationid="%d" data-userid="%d">', 'd-flex w-25 align-items-center justify-content-center', 'favourite', $productID, \get_current_user_id());
			$html .= \apply_filters('is_favourite', $productID, \get_current_user_id(), 'heart', 'text-secondary');
			$html .= '</form>'; # end .favourite_form
		}
		$html .= sprintf( '<a class="%s" id="meer_info_link_%d" href="%s">%s</a>', 'bg-secondary text-white butt d-flex w-50 ' . (!$loggedin? 'm-auto' : ''), $productID, $permalink, 'Meer info');
		
		return $html;
	}
}

new DJC_WooCommerce_Filters();