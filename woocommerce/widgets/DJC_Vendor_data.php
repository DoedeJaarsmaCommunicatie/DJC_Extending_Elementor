<?php
/**
 * Created by PhpStorm.
 * User: mitch
 * Date: 2018-12-04
 * Time: 14:42
 */

class DJC_Vendor_data extends \Elementor\Widget_base {
	
	public function get_name()
	{
		return 'vendor_data';
	}
	
	public function get_title()
	{
		return __('Vendor Data', 'djcee');
	}
	
	public function get_icon()
	{
		return 'fa fa-archive';
	}
	
	public function get_categories()
	{
		return [ 'djcee_wc', 'djcee' ];
	}
	
	protected function _register_controls() {}
	
	protected function render() {
		?>
		<aside>
			<section>
			<?php $this->the_vendor_logo(); ?>
				<h3>
					<?php $this->the_vendor_name(); ?>
				</h3>
				<p>
					<?php
						$this->the_vendor_profile();
					?>
				</p>
			</section>
		</aside>
	<?php
	}
	
	protected function fetchVendorMetaData()
	{
		if( $GLOBALS['wp_query']->query_vars['wcpv_product_vendors']) {
			$term = \get_term_by('slug', $GLOBALS['wp_query']->query_vars['wcpv_product_vendors'], 'wcpv_product_vendors');
			
			return WC_Product_Vendors_Utils::get_vendor_data_by_id( $term->term_id );
		}
		
		return false;
	}
	
	protected function the_vendor_name()
	{
		if( $this->fetchVendorMetaData() )
		{
			print $this->fetchVendorMetaData()['name'];
		}
	}
	
	protected function the_vendor_profile()
	{
		if( $this->fetchVendorMetaData() )
		{
			print $this->fetchVendorMetaData()['profile'];
		}
	}
	
	protected function the_vendor_logo()
	{
		if( $this->fetchVendorMetaData() )
		{
			print wp_get_attachment_image( $this->fetchVendorMetaData()['logo']);
		}
	}
	
}