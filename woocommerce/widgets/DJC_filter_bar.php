<?php
/**
 * Created by PhpStorm.
 * User: mitch
 * Date: 2018-11-26
 * Time: 13:07
 */

final class DJC_filter_bar extends \Elementor\Widget_Base {
	
	private $is_added_product_filter = false;
	
	private $product_count = 0;
	
	/**
	 * @var WP_Query
	 */
	private $the_query;
	
	public function get_name()
	{
		return 'filter_bar';
	}
	
	public function get_title()
	{
		return __( 'Filter bar', 'djcee');
	}
	
	public function get_icon()
	{
		return 'fa fa-filter';
	}
	
	public function get_categories()
	{
		return [ 'djcee_wc', 'djcee' ];
	}
	
	protected function includes()
    {
	    $this->add_script_depends( 'limiter_djcee_script' );
	    $this->add_script_depends( 'wp_filter_djcee_script' );
	    $this->add_script_depends( 'wp_sort_djcee_script' );
	    $this->add_style_depends( 'limiter_djcee_style' );
	    $this->add_style_depends( 'wp_filter_full_style_grid');
    }
    
	protected function _register_controls()
	{
	    $this->includes();
		$this->djc_filter_content_section();
		
		/*
		 * TODO: Add styling section
		 * FIXME: Filter counts need to update
		 *   For now this does not update fully.
		 * FIXME: Filter needs to remove empty
		 *   This works via hides now. Needs the extra ajax call.
		 * TODO: Make multiple ajax calls when filtering
		 *   One for filters available
		 *   One for products
		 * TODO: Make an ajax call for sorting the products
		 */
	}
	
	protected function render()
	{
		$this->includes();
		$settings = $this->get_settings_for_display();
		$filter_list = [];
		
		foreach( $settings['filter_list'] as $filter)
        {
            if( $filter['filter_type'] === 'product_att' )
            {
	            $attribute = wc_get_attribute( $filter['product_attribute'] );
	            $filter_list['attributes'] []= $attribute->slug;
            }
            
            if( $filter['filter_type'] === 'product_cat' )
            {
                $filter_list['categories'] []= null;
            }
        }
		
		if( $settings['display_style'] === 'list' )
		{
		    ?>
                <form class="js-filter-page-filter-form" id="filter_<?=get_the_ID()?>" data-filter-target="<?=$settings['form_target_class']?>" data-wordpress-filter="<?=$settings['wp_filter_product']?>">
                    <?php
                        foreach($settings['filter_list'] as $filter)
                        {
                            if( $filter['filter_type'] === 'product_cat' )
                                $this->_render_categories_template( $filter );
                            if( $filter['filter_type'] === 'product_att' )
                                $this->_render_attribute_template( $filter['product_attribute'], $filter);
                        }
                    ?>
                </form>
            <?php
        }
		
		if( $settings['display_style'] === 'full' )
        {
            $this->djc_fetch_all_products();
	        ?>
            <section class="djcee__filter__container__full">
                <aside class="djc__filter__full__aside">
                    <form class="js-filter-page-filter-form" id="filter_<?=get_the_ID()?>"
                          data-filter-target="<?=$settings['form_target_class']?? 'js__target__filter__container'?>"
                          data-wordpress-filter="<?=$settings['wp_filter_product']?>"
                          data-filter-attributes-list="<?=implode(',', $filter_list['attributes'])?>"
                          <?php if( isset( $filter_list['categories'] ) ) : ?>
                          data-filter-categories-list="<?=implode(',', $filter_list['categories'])?>"
                          <?php endif; ?>
                    >
		                <?php
		                foreach($settings['filter_list'] as $filter)
		                {
			                if( $filter['filter_type'] === 'product_cat' )
				                $this->_render_categories_template( $filter );
			                if( $filter['filter_type'] === 'product_att' )
				                $this->_render_attribute_template( $filter['product_attribute'], $filter);
		                }
		                ?>
                    </form>
                </aside>
                <main class="djc__filter__full__main">
                    <aside class="djc__filter__full__top_bar d-flex justify-content-between">
                        <p>
                        <span class="js__target__count" data-count="<?=$this->the_query->post_count?>" data-string="<?=__('producten gevonden', 'djcee')?>">
                            <?=$this->the_query->post_count?> <?=__('producten gevonden', 'djcee')?>
                        </span>
                        </p>
                        <form action="#">
                            <select name="orderby" id="orderby" class="js__filter__order_by">
                                <option value="title:asc">Name ascending</option>
                                <option value="title:desc">Name descending</option>
                            </select>
                        </form>
                    </aside>
                    <section class="js__target__filter__container">
	                    <?php $this->_render_product_template(); ?>
                    </section>
                </main>
            </section>
	        <?php
        }
	}
	
	protected function _content_template()
	{
		parent::_content_template();
	}
	
	final private function _render_categories_template($filter_data)
	{
		$args = array(
			'taxonomy'     => $filter_data['filter_type'],
			'orderby'      => 'name',
			'show_count'   => 1,
			'hide_empty'   => 1
		);
		
		$categories = get_categories( $args );
		
		?>
		<section class="js-target__section__filter" data-filter-name="product_cat">
			<h2><?= __( $filter_data['filter_title'], 'djcee' ) ?></h2>
			<ul>
				<?php foreach($categories as $category): ?>
				<li>
					<input type="checkbox" data-filtrate="<?= $category->slug ?>" data-filter-facet="product_cat:<?=$category->slug?>" data-filter="product_categorie[]" id="category-<?= $category->slug ?>"
                    <?php if( $category->slug === get_query_var( 'product_cat' ) ) {
                        print 'checked';
                    }
                    if( in_array( $category->slug, get_query_var( 'product_categorie' ) ) )
                    {
                      print 'checked';
                    } ?>
                    />
					<label for="category-<?= $category->slug ?>">
						<?= $category->name ?>
					</label>
				</li>
				<?php endforeach; ?>
			</ul>
        
        </section>
		<?php
	}
	
	final private function _render_attribute_template($id, $filter)
	{
		$settings = $this->get_settings_for_display();
		
		$attribute = wc_get_attribute( $id );
		if( $attribute !== null) :
		$terms = get_terms( $attribute->slug );
		?>
		<section  class="js-target__section__filter" data-filter-name="<?=$attribute->slug?>">
			<h2><?= __( $filter['filter_title'], 'djcee') ?></h2>
			<ul data-limit="<?= $filter['limit']['size'] ?>" data-toggle-target="<?=$attribute->slug?>">
				<?php foreach($terms as $term): ?>
					<li>
						<input type="checkbox" data-filtrate="<?= $term->slug ?>" data-filter-facet="<?=$attribute->slug?>:<?=$term->slug?>" data-filter="<?= $attribute->slug ?>[]" id="term-<?= $term->slug ?>"
                               <?php if( in_array( $term->slug, get_query_var( $attribute->slug ), true ) ) {
                                   print 'checked';
                               } ?>
                        />
						<label for="term-<?= $term->slug ?>">
							<?=$term->name?>
                        </label>
					</li>
				<?php endforeach; ?>
            <button class="js-toggle__button__hidden" data-toggle-arrow="<?=$attribute->slug?>"><?= $settings['delimiter'] ?></button>
			</ul>
        
        </section>
        <?php
        endif;
	}
	
	final private function _product_order_options()
    {
        return [
                'name:asc'  =>  __('Name Ascending', 'djcee'),
                'name:desc'  =>  __('Name Descending', 'djcee'),
        ];
    }
	
	final private function _render_product_template()
    {
        $settings = $this->get_settings_for_display();
	
	    if( $this->the_query->have_posts() ) :
            while( $this->the_query->have_posts() ) :
                
                $this->the_query->the_post();
                
	            print \apply_filters($settings['wp_filter_product'], $this->the_query->post, 4);

            endwhile;
        endif;
    }
	
	private function djc_filter_content_section()
	{
		$this->start_controls_section( 'display-section',
			[
				'label'     =>  __( 'Display', 'djcee'),
				'tab'       =>  \Elementor\Controls_Manager::TAB_CONTENT
			]
		);
		
		$this->djc_filter_content_display_controls();
		
		$this->end_controls_section();
		
		/*
		 * This should only be a really complicated repeater
		 */
		$this->start_controls_section( 'filter-section',
			[
				'label'     =>  __( 'Filter', 'djcee'),
				'tab'       =>  \Elementor\Controls_Manager::TAB_CONTENT
			]
		);
		
		$this->djc_filter_content_filter_controls();
		
		$this->end_controls_section();
	}
	
	/*
	 * Content controls section
	 */
	final private function djc_filter_content_display_controls()
	{
		$this->add_control('display_style',
			[
				'label'     =>  __( 'Display Style', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::SELECT2,
				'options'   =>  [
					'list'      =>  __( 'List', 'djcee'),
                    'full'      =>  __( 'Full', 'djcee'),
				],
				'default'   =>  'list'
			]
		);
		
		$this->add_control('delimiter',
            [
                'label'         =>  __( 'Delimiter', 'djcee' ),
                'type'          =>  \Elementor\Controls_Manager::TEXT,
                'placeholder'   =>  __( 'More', 'djcee'),
                'default'       =>  __( 'More', 'djcee'),
            ]
        );
		
//		$this->add_control('form_target_class',
//            [
//                'label'         =>  __( 'Target', 'djcee' ),
//                'type'          =>  \Elementor\Controls_Manager::TEXT,
//                'default'       =>  'js__target__filter__container',
//                'condition'     =>  [
//                        'display_style' =>  'list'
//                ]
//            ]
//        );
		
		$this->add_control('wp_filter_product',
            [
                'label'         =>  __( 'Wordpress Filter', 'djcee'),
                'block'         =>  true,
                'type'          =>  \Elementor\Controls_Manager::TEXT,
                'default'       =>  'create_product_card'
            ]
        );
	}
	
	final private function djc_filter_content_filter_controls()
	{
		$repeater = new \Elementor\Repeater();
		
		$repeater->add_control( 'filter_title',
			[
				'label' => __( 'Title', 'djcee' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Filter Title' , 'djcee' ),
				'label_block' => true,
			]
		);
		
		$repeater->add_control( 'filter_type',
			[
				'label' => __( 'Filter type', 'djcee'),
				'type'  =>  \Elementor\Controls_Manager::SELECT2,
				'options'   =>  [
					'product_cat'       =>  __( 'Categories', 'djcee' ),
//					'regular_price'     =>  __( 'Price', 'djcee' ),
					'product_att'       =>  __( 'Attributes', 'djcee'),
				],
			]
		);
		
		$repeater->add_control( 'product_attribute',
			[
				'label'     =>  __( 'Product attribute', 'djcee' ),
				'type'      =>  \Elementor\Controls_Manager::SELECT2,
				'options'   =>  $this->djc_all_product_attributes(),
				'condition' =>  [
					'filter_type'   =>  'product_att'
				]
			]
		);
		
		$repeater->add_control( 'limit',
			[
				'label'     =>  __( 'Limit', 'djcee' ),
				'type'      =>  \Elementor\Controls_Manager::SLIDER,
				'range'     =>  [
                    'min'   =>  4,
                    'max'   =>  20,
                    'step'  =>  1,
                ],
				'default'   => [
				        'size'      =>   9,
                ],
				'condition' =>  [
					'filter_type'   =>  [
						'product_att',
                        'product_cat'
                    ],
				]
			]
		);
		
		$this->add_control(
			'filter_list',
			[
				'label' => __( 'Filters', 'djcee' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'title_field' => '{{{ filter_title }}}',
			]
		);
	}
	
	private function djc_all_product_attributes()
	{
		$arr = [];
		
		foreach(wc_get_attribute_taxonomies() as $attribute)
		{
			$arr [$attribute->attribute_id]= $attribute->attribute_name;
		}
		return $arr;
	}
	
	/**
	 * @return WP_Query
	 */
	protected function djc_fetch_all_products()
    {
        
        if( $this->is_added_product_filter )
        {
            remove_action( 'pre_get_posts', [ wc()->query, 'product_query' ] );
        }
        $this->the_query = new WP_Query( $this->parse_query_args() );
        return $this->the_query;
    }
    
    protected function parse_query_args()
    {
        $meta_args = $this->djc_all_product_attributes();
        
        $query_args = [
            'post_type'             =>  'product',
            'post_status'           =>  'publish',
            'ignore_sticky_posts'   =>  true,
            'tax_query'             => [
                    'relation'          => 'OR',
            ],
            'meta_query'            =>  [
            
            ]
        ];
        
        if( !is_page( wc_get_page_id( 'shop' ) ) )
        {
            $query_args = $GLOBALS['wp_query']->query_vars;
	        add_action( 'pre_get_posts', [ wc()->query, 'product_query' ] );
	        $this->is_added_product_filter = true;
        }
        
        if( get_query_var( 'product_cat' ) )
        {
	        $query_args['tax_query'] []= [
		        'taxonomy'  => 'product_cat',
		        'field'     => 'slug',
		        'terms'     => get_query_var('product_cat')
	        ];
        }
        
        if( get_query_var( 'product_categorie' ) )
        {
	        $query_args['tax_query'] []= [
		        'taxonomy'  => 'product_cat',
		        'field'     => 'slug',
		        'terms'     => get_query_var('product_categorie')
	        ];
        }
        
        foreach( $meta_args as $pa_val => $pa_name)
        {
            if( get_query_var( "pa_$pa_name" ) )
            {
                $query_args['tax_query'] []= [
                    'taxonomy'  => "pa_$pa_name",
                    'field'     => 'slug',
                    'terms'     => get_query_var( "pa_$pa_name" ),
                ];
            }
        }
	
	    $query_args['fields'] = 'ids';
        $query_args['posts_per_page'] = 999;
        return $query_args;
        
    }
}