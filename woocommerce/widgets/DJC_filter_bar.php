<?php
/**
 * Created by PhpStorm.
 * User: mitch
 * Date: 2018-11-26
 * Time: 13:07
 */

final class DJC_filter_bar extends \Elementor\Widget_Base {
	
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
	    $this->add_style_depends( 'limiter_djcee_style' );
    }
    
	protected function _register_controls()
	{
	    $this->includes();
		$this->djc_filter_content_section();
		
		/*
		 * TODO: Add styling section
		 * FIXME: Filter counts need to update
		 * FIXME: Filter needs to remove empty
		 */
	}
	
	protected function render()
	{
		$settings = $this->get_settings_for_display();
		
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
		<section>
			<h2><?= __( $filter_data['filter_title'], 'djcee' ) ?></h2>
			<ul>
				<?php foreach($categories as $category): ?>
				<li>
					<input type="checkbox" data-filtrate="<?= $category->slug ?>" data-filter="product_cat" id="category-<?= $category->slug ?>" />
					<label for="category-<?= $category->slug ?>">
						<?= $category->name ?>
					</label>
				</li>
				<?php endforeach; ?>
			</ul>
        
        </section>
		<?php
	}
	
	private function _render_attribute_template($id, $filter)
	{
		$settings = $this->get_settings_for_display();
		
		$attribute = wc_get_attribute( $id );
		if( $attribute !== null) :
		$terms = get_terms( $attribute->slug );
		WP_DEBUG? var_dump( $terms ) : null;
		?>
		<section  class="js-target__section__filter" data-filter-name="<?=$attribute->slug?>">
			<h2><?= __( $filter['filter_title'], 'djcee') ?></h2>
			<ul data-limit="<?= $filter['limit']['size'] ?>" data-toggle-target="<?=$attribute->slug?>">
				<?php foreach($terms as $term): ?>
					<li>
						<input type="checkbox" data-filtrate="<?= $term->slug ?>" data-filter="<?= $attribute->slug ?>" id="term-<?= $term->slug ?>" />
						<label for="term-<?= $term->slug ?>">
						<!-- <span class="term_count term_count_<?=$attribute->slug?>" id="term_count_<?=$term->slug?>" data-filter-slug="<?=$attribute->slug?>" data-filtrate-slug="<?=$term->slug?>"><?= $term->count ?></span> -->
							<?= $term->name ?>
						</label>
					</li>
				<?php endforeach; ?>
            <button class="js-toggle__button__hidden" data-toggle-arrow="<?=$attribute->slug?>"><?= $settings['delimiter'] ?></button>
			</ul>
        
        </section>
        <?php
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
					'list'      =>  __( 'List', 'djcee')
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
		
		$this->add_control('form_target_class',
            [
                'label'         =>  __( 'Target', 'djcee' ),
                'type'          =>  \Elementor\Controls_Manager::TEXT,
                'default'       =>  'js__target__filter__container',
            ]
        );
		
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
}