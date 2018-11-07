<?php
/**
 * Created by PhpStorm.
 * User: mitch
 * Date: 2018-11-05
 * Time: 15:00
 */

final class DJC_related_loader extends \Elementor\Widget_Base
{
	/**
	 * The name for this \Elementor Widget
	 *
	 * @return string
	 */
	public function get_name()
	{
		return 'related-loader';
	}
	
	/**
	 * This is the title that is shown in the editor
	 *
	 * @return mixed
	 */
	public function get_title()
	{
		return __( 'Post loader', 'djcee');
	}
	
	/**
	 * The icon shown in the editor
	 *
	 * @uses fontawesome v4.7.0
	 *
	 * @return string
	 */
	public function get_icon()
	{
		return 'fa fa-database';
	}
	
	/**
	 * returns a location where this widget is visible.
	 *
	 * @return array
	 */
	public function get_categories()
	{
		return [ 'djcee' ];
	}
	
	/**
	 * Registers the controls
	 *
	 * @uses hmvt_content_section()
	 * @uses hmvt_style_section()
	 * @uses hmvt_pages_sections()
	 */
	protected function _register_controls()
	{
		$this->hmvt_content_section();
		
		$this->hmvt_style_section();
		
		$this->hmvt_pages_sections();
	}
	
	/**
	 * This renders the content. The content is dependant on a specific option, shows either a list or a card view.
	 *
	 * @uses _render_cards() for the card display
	 * @uses _render_classic() for the list display
	 */
	protected function render()
	{
		$settings = $this->get_settings_for_display();
		if( $settings['display_style'] === 'card')
			$this->_render_cards();
		if( $settings['display_style'] === 'list')
			$this->_render_classic();
	}
	
	/**
	 * Renders a list view
	 */
	protected function _render_classic()
	{
		$posts = $this->fetchRenderProjects(); ?>
		<div class="list_container d-flex flex-column">
			<?php while( $posts->have_posts() ): $posts->the_post(); ?>
				
				<span class="link_container">
                    <a class="d-flex " href="<?php print get_the_permalink($posts->post->ID) ?>">
                        <?php print get_the_title($posts->post->ID) ?> <i class="fas fa-chevron-right ml-auto"></i>
                    </a>
                </span>
			<?php
			endwhile;  wp_reset_postdata(); ?>
		</div>
		<?php
	}
	
	/**
	 * Renders the card view
	 */
	protected function _render_cards()
	{
		$settings = $this->get_settings_for_display();
		$this->render_helpers();
		$posts = $this->fetchRenderProjects();
		if( $posts ): ?>
			<div <?php print $this->get_render_attribute_string('container_row'); ?>>
				<?php while( $posts->have_posts() ): $posts->the_post();?>
                
                    <?php
					$button_add_class = '';
					if( $settings['read_more_button_background'] === 'taxonomy')
					{
						$terms = wp_get_post_terms( $posts->post->ID, $settings['fetch_related_taxonomy'] );
						
						$button_add_class = $terms? $terms[0]->slug : '';
					}
                    ?>
					<div <?php print $this->get_render_attribute_string('container_column') ?>>
						<div <?php print $this->get_render_attribute_string('project_card') ?>>
							<?php if( $settings['show_image'] === 'yes'): ?>
								<div <?php print $this->get_render_attribute_string('project_card_media') ?>>
									<img src="<?php print get_the_post_thumbnail_url() ?>" alt="<?php print get_the_title(); ?>" class="project_image">
									<?php $term = \wp_get_post_terms( $posts->post->ID, $settings['fetch_related_taxonomy'] ); ?>
									<div class="img_overlay_container">
										<img src="<?php if($term) print get_field('featured_image', 'category_' . $term[0]->term_id)['url']; ?>" class="img_overlay_thema_icon">
									</div>
								</div>
							<?php endif; ?>
							<div <?php print $this->get_render_attribute_string('project_card_content') ?>>
								<div class="project-card-title">
									<?php print get_the_title(); ?>
								</div>
								<?php if( $settings['show_excerpt'] === 'yes'): ?>
									<div class="project-card-content">
										<?php # print the_excerpt_max_charlength($settings['excerpt_length']); ?>
									</div>
								<?php endif; ?>
							</div>
							<div class="d-flex justify-content-end">
								<a href="<?php print get_the_permalink(); ?>" class="card-content-button <?php print "card-content-button-tax-{$button_add_class}"; ?>">
									<?php _e( (string) ( $settings[ 'read_more_button_text' ] ), 'hmvt'); ?> <i class="fas fa-chevron-right"></i>
								</a>
							</div>
						</div>
					</div>
				<?php endwhile; \wp_reset_postdata(); ?>
			</div>
			<?php if( $settings['use_paginate'] === "yes" ):?>
				<div class="row">
					<div class="pagination ml-auto mr-2">
						<?php print paginate_links( array(
							'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
							'total'        => $posts->max_num_pages,
							'current'      => max( 1, get_query_var( 'paged' ) ),
							'format'       => '?paged=%#%',
							'show_all'     => false,
							'type'         => 'plain',
							'end_size'     => 2,
							'mid_size'     => 1,
							'prev_next'    => true,
							'prev_text'    => sprintf( '<i></i> %1$s', $settings['prev_text'] ),
							'next_text'    => sprintf( '%1$s <i></i>', $settings['next_text'] ),
							'add_args'     => false,
							'add_fragment' => '',
						) ); ?>
					</div>
				</div>
			<?php endif; ?>
		<?php
		else:
			_e("No {$settings['load_style']} found", 'djcee');
		endif;
	}
	
	/**
	 * These help with the rendering making the render functions a tad more easy to read
	 */
	protected function render_helpers()
	{
		$settings = $this->get_settings_for_display();
		
		$this->add_render_attribute(
			'container_row',
			[
				'class' =>  [
					'row',
				]
			]
		);
		$column = (int) (12 / $settings['posts_per_column']);
		
		$this->add_render_attribute(
			'container_column',
			[
				'class' =>  [
					"col-sm-{$column}",
					'mb-2',
					'align-items-stretch',
					'd-flex',
				]
			]
		);
		
		$this->add_render_attribute(
			'project_card',
			[
				'class'   => [
					'card',
					'justify-content-between',
					'projecten_card',
				]
			]
		);
		
		
		$this->add_render_attribute(
			'project_card_media',
			[
				'class'   => [
					'card-media',
					'projecten_card_media',
				]
			]
		);
		
		$this->add_render_attribute(
			'project_card_content',
			[
				'class'   => [
					'card-content',
					'projecten_card_content',
				]
			]
		);
		
	}
	
	/**
	 *
	 */
	protected function hmvt_content_section()
	{
		
		$this->start_controls_section(
			'display-section',
			[
				'label'     =>  __( 'Display', 'djcee'),
				'tab'       =>  \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		
		$this->add_control(
			'display_style',
			[
				'label'     =>  __( 'Display Style', 'djcee' ),
				'type'       =>  \Elementor\Controls_manager::SELECT2,
				'options'   =>  [
					'card'  =>  __( 'Card', 'dcjee' ),
					'list'  =>  __( 'List', 'djcee' ),
				],
				'default'   =>  'card'
			]
		);
		
		if( \get_post_type( \get_queried_object_id() ) === "page" )
        {
            $typeArr = [];
            
	        foreach( \get_post_types([ 'public'   => true,
                                       '_builtin' => true ]) as $key => $types )
            {

                $typeArr [$key]=  __( $types, 'djcee' );
            }
            
	        foreach( \get_post_types([ 'public'  =>  true,
                                       '_builtin'=> false ]) as $key => $types )
            {

                $typeArr [$key]=  __( $types, 'djcee' );
            }
	        
	        $this->add_control(
		        'load_style',
		        [
			        'label'     =>  __( 'Load Style', 'djcee' ),
			        'type'       =>  \Elementor\Controls_Manager::SELECT2,
			        'options'   =>  $typeArr,
			        'default'   =>  \get_post_type( \get_queried_object_id() )
		        ]
	        );
        }
		
		$this->add_control(
            'read_more_button_text',
            [
	            'label'     =>  __( 'Read more text', 'djcee' ),
	            'type'      =>  \Elementor\Controls_Manager::TEXT,
				'default'   =>  __( 'Bekijk project', 'djcee'),
				'condition'	=>	[
					'display_style'	=>	'card'
				],
            ]
        );
		
		$this->add_control(
            'read_more_button_background',
            [
                'label'     =>  __( 'Read more button style', 'djcee'),
                'type'      =>  \Elementor\Controls_Manager::SELECT2,
                'options'   =>  [
                    'config'    =>  __( 'Settings', 'djcee' ),
                    'taxonomy'  =>  __( 'Category', 'djcee' )
				],
				'condition'	=>	[
					'display_style'	=>	'card'
				],
            ]
        );
		
		$this->end_controls_section();
		
		$this->start_controls_section(
			'filter-section',
			[
				'label'     =>  __( 'Filter', 'djcee'),
				'tab'       =>  \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		
		$taxonomies = get_object_taxonomies( get_post( get_queried_object_id() ) );

		if( $taxonomies )
		{
			$tax = [];

			foreach( $taxonomies as $taxonomy )
			{
				$tax [$taxonomy]= $taxonomy;
			}
			$this->add_control(
				'fetch_related',
				[
					'label' => __( 'Fetch relateds', 'djcee' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Yes', 'djcee' ),
					'label_off' => __( 'No', 'djcee' ),
					'default'   =>  'yes'
				]
			);
	
			$this->add_control(
				'fetch_related_taxonomy',
				[
					'label'     =>  __( 'Fetch on taxonomy:', 'djcee'),
					'type'      =>  \Elementor\Controls_Manager::SELECT2,
					'options'   =>  $tax,
					'condition'	=>	[
						'fetch_related'	=>	'yes'
					]
				]
			);
		}
		
		if ( get_post_type( \get_queried_object_id() ) !== "page"){
			$this->add_control(
				'sticky_1',
				[
					'label' => __( 'Sticky post', 'djcee' ),
					'type' => 'sticky_posts',
				]
			);
		}
		
		
		$this->add_control(
			'posts_amount',
			[
				'label' => __( 'Number of posts per page', 'djcee' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => -1,
				'max' => 16,
				'step' => 1,
				'default' => 4,
			]
		);
		
		
		$this->add_control(
			'posts_per_column',
			[
				'label' => __( 'Number of columns', 'djcee' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 12,
				'step' => 1,
				'default' => 4,
			]
		);
		
		$this->add_control(
			'use_paginate',
			[
				'label' => __( 'Paginate projects', 'djcee' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'djcee' ),
				'label_off' => __( 'No', 'djcee' ),
				'default'   =>  'no'
			]
		);
		
		$this->add_control(
			'show_excerpt',
			[
				'label' => __( 'Show excerpt in card', 'djcee' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'djcee' ),
				'label_off' => __( 'No', 'djcee' ),
				'default'   =>  'yes'
			]
		);
		
		
		$this->add_control(
			'excerpt_length',
			[
				'label' => __( 'Excerpt length', 'djcee' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 5,
				'max' => 400,
				'step' => 5,
				'default' => 10,
				'condition' => [
					'show_excerpt' => 'yes',
				],
			]
		);
		
		$this->add_control(
			'show_image',
			[
				'label' => __( 'Show image in card', 'djcee' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'djcee' ),
				'label_off' => __( 'No', 'djcee' ),
				'default'   =>  'yes'
			]
		);
		
		$this->end_controls_section();
	}
	
	/**
	 *
	 */
	protected function hmvt_pages_sections()
	{
		$this->start_controls_section(
			'paginate-controls-section',
			[
				'label'     =>  __( 'Paginate', 'djcee'),
				'tab'       =>  \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' =>  [
					'use_paginate'     =>  'yes'
				]
			]
		);
		
		$this->add_control(
			'prev_text',
			[
				'label' => __( 'Previous text', 'djcee' ),
				'type' => \Elementor\Controls_Manager::TEXT,
			]
		);
		
		$this->add_control(
			'next_text',
			[
				'label' => __( 'Next text', 'djcee' ),
				'type' => \Elementor\Controls_Manager::TEXT,
			]
		);
		
		$this->end_controls_section();
		
		$this->prev_button_style_section();
		$this->next_button_style_section();
		$this->page_button_style_section();
		
		
	}
	
	/**
	 *
	 */
	protected function prev_button_style_section()
	{
		$this->start_controls_section(
			'previous-button-style-section',
			[
				'label'     =>  __( 'Previous button', 'djcee'),
				'tab'       =>  \Elementor\Controls_Manager::TAB_STYLE,
				'condition' =>  [
					'use_paginate'     =>  'yes'
				]
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'prev_button_typography',
				'selector' => '{{WRAPPER}} .prev.page-numbers',
			]
		);
		
		$this->add_control(
			'prev_button_default_color',
			[
				'label'     =>  __( 'Button color', 'djcee'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .page-numbers.prev' => 'color: {{VALUE}}',
				],
			]
		);
		
		$this->add_control(
			'prev_button_background',
			[
				'label' => __( 'Button background', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::POPOVER_TOGGLE,
				'label_off' => __( 'Default', 'djcee' ),
				'label_on' => __( 'Custom', 'djcee' ),
				'return_value' => 'yes',
			]
		);
		
		$this->start_popover();
		
		$this->add_control(
			'prev_button_default_background',
			[
				'label'     =>  __( 'Default background', 'djcee'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .page-numbers.prev' => 'background: {{VALUE}}',
				],
			]
		);
		
		$this->add_control(
			'prev_button_hover_background',
			[
				'label'     =>  __( 'Hover background', 'djcee'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .page-numbers.prev:hover' => 'background: {{VALUE}}',
				],
			]
		);
		
		$this->end_popover();
		
		$this->add_control(
			'prev_button_padding',
			[
				'label'     =>  __('Button padding', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .page-numbers.prev'   =>  'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_control(
			'prev_button_margin',
			[
				'label'     =>  __('Button margin', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .page-numbers.prev'   =>  'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'prev_button_border',
				'selector' => '{{WRAPPER}} .prev.page-numbers',
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'prev_button_shadow',
				'selector' => '{{WRAPPER}} .prev.page-numbers',
			]
		);
		
		$this->end_controls_section();
	}
	
	/**
	 *
	 */
	protected function next_button_style_section()
	{
		$this->start_controls_section(
			'next-button-style-section',
			[
				'label'     =>  __( 'Next button', 'djcee'),
				'tab'       =>  \Elementor\Controls_Manager::TAB_STYLE,
				'condition' =>  [
					'use_paginate'     =>  'yes'
				]
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'next_button_typography',
				'selector' => '{{WRAPPER}} .next.page-numbers',
			]
		);
		
		$this->add_control(
			'next_button_default_color',
			[
				'label'     =>  __( 'Button color', 'djcee'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .page-numbers.next' => 'color: {{VALUE}}',
				],
			]
		);
		
		$this->add_control(
			'next_button_background',
			[
				'label' => __( 'Button background', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::POPOVER_TOGGLE,
				'label_off' => __( 'Default', 'djcee' ),
				'label_on' => __( 'Custom', 'djcee' ),
				'return_value' => 'yes',
			]
		);
		
		$this->start_popover();
		
		$this->add_control(
			'next_button_default_background',
			[
				'label'     =>  __( 'Default background', 'djcee'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .page-numbers.next' => 'background: {{VALUE}}',
				],
			]
		);
		
		$this->add_control(
			'next_button_hover_background',
			[
				'label'     =>  __( 'Hover background', 'djcee'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .page-numbers.next:hover' => 'background: {{VALUE}}',
				],
			]
		);
		
		$this->end_popover();
		
		$this->add_control(
			'next_button_padding',
			[
				'label'     =>  __('Button padding', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .page-numbers.next'   =>  'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_control(
			'next_button_margin',
			[
				'label'     =>  __('Button margin', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .page-numbers.next'   =>  'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'next_button_border',
				'selector' => '{{WRAPPER}} .next.page-numbers',
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'next_button_shadow',
				'selector' => '{{WRAPPER}} .next.page-numbers',
			]
		);
		
		$this->end_controls_section();
	}
	
	/**
	 *
	 */
	protected function page_button_style_section()
	{
		$this->start_controls_section(
			'numbered-button-style-section',
			[
				'label'     =>  __( 'Page buttons', 'djcee'),
				'tab'       =>  \Elementor\Controls_Manager::TAB_STYLE,
				'condition' =>  [
					'use_paginate'     =>  'yes'
				]
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'numbered_button_typography',
				'selector' => '{{WRAPPER}} .page-numbers',
			]
		);
		
		$this->add_control(
			'numbered_button_default_color',
			[
				'label'     =>  __( 'Button color', 'djcee'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .page-numbers' => 'color: {{VALUE}}',
				],
			]
		);
		
		$this->add_control(
			'numbered_button_background',
			[
				'label' => __( 'Button background', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::POPOVER_TOGGLE,
				'label_off' => __( 'Default', 'djcee' ),
				'label_on' => __( 'Custom', 'djcee' ),
				'return_value' => 'yes',
			]
		);
		
		$this->start_popover();
		
		$this->add_control(
			'numbered_button_default_background',
			[
				'label'     =>  __( 'Default background', 'djcee'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .page-numbers' => 'background: {{VALUE}}',
				],
			]
		);
		
		$this->add_control(
			'numbered_button_hover_background',
			[
				'label'     =>  __( 'Hover background', 'djcee'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .page-numbers:hover' => 'background: {{VALUE}}',
				],
			]
		);
		
		$this->add_control(
			'numbered_button_active_background',
			[
				'label'     =>  __( 'Current background', 'djcee'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .page-numbers.current' => 'background: {{VALUE}}',
				],
			]
		);
		
		$this->end_popover();
		
		$this->add_control(
			'numbered_button_padding',
			[
				'label'     =>  __('Button padding', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .page-numbers'   =>  'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_control(
			'numbered_button_margin',
			[
				'label'     =>  __('Button margin', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .page-numbers'   =>  'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'numbered_button_border',
				'selector' => '{{WRAPPER}} .page-numbers',
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'numbered_button_shadow',
				'selector' => '{{WRAPPER}} .page-numbers',
			]
		);
		
		$this->end_controls_section();
	}
	
	/**
	 *
	 */
	protected function hmvt_style_section()
	{
		$this->card_styling();
		$this->card_media_stying();
		$this->card_content_styling();
		$this->card_button_styling();
		
		$this->list_styling();
	}
	
	/**
	 *
	 */
	protected function card_button_styling()
	{
		$this->start_controls_section(
			'style-card-button-section',
			[
				'label'     =>  __('Buttons', 'djcee'),
				'tab'       =>  \Elementor\Controls_Manager::TAB_STYLE,
				'condition' =>  [
					'display_style'     =>  'card'
				]
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'card_button_background',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .card-content-button',
			]
		);
		
		$this->add_control(
			'card_button_radius',
			[
				'label'     =>  __('Card button border radius', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .card-content-button'   =>  'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'card_button_margin',
			[
				'label'     =>  __('Card button margin', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .card-content-button'   =>  'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'card_button_padding',
			[
				'label'     =>  __('Card button padding', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .card-content-button'   =>  'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'button_text_color',
			[
				'label' => __( 'Text color', 'djcee' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .card-content-button' => 'color: {{VALUE}}',
				],
			]
		);
		
		$this->add_control(
			'button_background_hover_color',
			[
				'label' => __( 'Background hover color', 'djcee' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .card-content-button:hover' => 'background: {{VALUE}}',
				],
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'card_content_button_typography',
				'selector' => '{{WRAPPER}} .card-content-button',
			]
		);
		
		$this->end_controls_section();
	}
	
	/**
	 *
	 */
	protected function card_content_styling()
	{
		$this->start_controls_section(
			'style-card-content-section',
			[
				'label'     =>  __('Card content', 'djcee'),
				'tab'       =>  \Elementor\Controls_Manager::TAB_STYLE,
				'condition' =>  [
					'display_style'     =>  'card'
				]
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'card_content_background',
				'selector' => '{{WRAPPER}} .projecten_card_content',
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'card_content_border',
				'selector' => '{{WRAPPER}} .projecten_card_content',
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_content_box_shadow',
				'selector' => '{{WRAPPER}} .projecten_card_content',
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'card_content_title_typography',
				'selector' => '{{WRAPPER}} .project-card-title',
			]
		);
		
		$this->add_control(
			'card_media_title_color',
			[
				'label' => __( 'Title color', 'djcee' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .project-card-title' => 'color: {{VALUE}}',
				],
			]
		);
		
		$this->add_control(
			'card_content_border_radius',
			[
				'label'     =>  __('Card content border radius', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .projecten_card_content'   =>  'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'card_content_margin',
			[
				'label'     =>  __('Card content margin', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .projecten_card_content'   =>  'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'card_content_padding',
			[
				'label'     =>  __('Card content padding', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .projecten_card_content'   =>  'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->end_controls_section();
	}
	
	/**
	 *
	 */
	protected function card_media_stying()
	{
		$this->start_controls_section(
			'style-card-media-section',
			[
				'label'     =>  __('Card Media', 'djcee'),
				'tab'       =>  \Elementor\Controls_Manager::TAB_STYLE,
				'condition' =>  [
					'display_style'     =>  'card',
                    'show_image'        =>  'yes'
				]
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'card_media_border',
				'selector' => '{{WRAPPER}} .projecten_card_media .project_image',
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_media_box_shadow',
				'selector' => '{{WRAPPER}} .projecten_card_media .project_image',
			]
		);
		
		$this->add_control(
			'card_media_border_radius',
			[
				'label'     =>  __('Card media border radius', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .projecten_card_media .project_image'   =>  'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'card_media_margin',
			[
				'label'     =>  __('Card media margin', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .projecten_card_media .project_image'   =>  'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'card_media_padding',
			[
				'label'     =>  __('Card media padding', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .projecten_card_media .project_image'   =>  'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'card_media_dimensions',
			[
				'label'     =>  __('Card media dimensions', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::IMAGE_DIMENSIONS,
				'selectors' =>  [
					'{{WRAPPER}} .projecten_card_media .project_image'   =>  'width: {{WIDTH}}px; height: {{HEIGHT}}px;',
				],
			]
		);
		
		$this->end_controls_section();
	}
	
	/**
	 *
	 */
	protected function card_styling()
	{
		$this->start_controls_section(
			'style-card-section',
			[
				'label'     =>  __('Card', 'djcee'),
				'tab'       =>  \Elementor\Controls_Manager::TAB_STYLE,
                'condition' =>  [
                    'display_style'     =>  'card'
                ]
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'card_background',
				'selector' => '{{WRAPPER}} .projecten_card',
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'card_border',
				'selector' => '{{WRAPPER}} .projecten_card',
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .projecten_card',
			]
		);
		
		$this->add_control(
			'card_border_radius',
			[
				'label'     =>  __('Card border radius', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .projecten_card'   =>  'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'card_margin',
			[
				'label'     =>  __('Card margin', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .projecten_card'   =>  'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'card_padding',
			[
				'label'     =>  __('Card padding', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .projecten_card'   =>  'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->end_controls_section();
	}
	
	/**
	 *
	 */
	protected function list_styling()
	{
		$this->start_controls_section(
			'style-list-section',
			[
				'label'     =>  __('List', 'djcee'),
				'tab'       =>  \Elementor\Controls_Manager::TAB_STYLE,
				'condition' =>  [
					'display_style'     =>  'list'
				]
			]
		);
		
		$this->add_control(
			'list_background',
			[
				'label'     =>  __( 'List background color', 'djcee'),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .list_container' => 'background-color: {{VALUE}}',
				],
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'list_border',
				'selector' => '{{WRAPPER}} .list_container',
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'list_box_shadow',
				'selector' => '{{WRAPPER}} .list_container',
			]
		);
		
		$this->add_control(
			'list_border_radius',
			[
				'label'     =>  __('List border radius', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .list_container'   =>  'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'list_margin',
			[
				'label'     =>  __('List margin', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .list_container'   =>  'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'list_padding',
			[
				'label'     =>  __('List padding', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .list_container'   =>  'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->end_controls_section();
		
		$this->start_controls_section(
			'style-link-section',
			[
				'label'     =>  __('Link', 'djcee'),
				'tab'       =>  \Elementor\Controls_Manager::TAB_STYLE,
				'condition' =>  [
					'display_style'     =>  'list'
				]
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'related_link_background',
				'selector' => '{{WRAPPER}} .link_container',
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'related_link_border',
				'selector' => '{{WRAPPER}} .link_container',
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'related_link_box_shadow',
				'selector' => '{{WRAPPER}} .link_container',
			]
		);
		
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'related_link',
				'selector' => '{{WRAPPER}} .link_container',
			]
		);
		
		$this->add_control(
			'related_links_color',
			[
				'label'     =>  __( 'Link color', 'djcee'),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .link_container a' => 'color: {{VALUE}}',
					'{{WRAPPER}} .link_container i' => 'color: {{VALUE}}',
				],
			]
		);
		
		$this->add_control(
			'related_link_border_radius',
			[
				'label'     =>  __('Link border radius', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .link_container'   =>  'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'related_link_margin',
			[
				'label'     =>  __('Link margin', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .link_container'   =>  'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'related_link_padding',
			[
				'label'     =>  __('Link padding', 'djcee'),
				'type'      =>  \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' =>  [
					'{{WRAPPER}} .link_container'   =>  'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->end_controls_section();
	}
	
	/**
	 * @return array
	 */
	protected function fetchAllProjects()
	{
		$settings = $this->get_settings_for_display();

		$args = [
			'post_type'         =>  \get_post_type( \get_queried_object_id() ),
			'post_status'       =>  'publish',
			'posts_per_page'    =>  -1,
			'order'             =>  'ASC',
			'orderby'           =>  'name',
			'post__not_in'      =>  [ \get_queried_object_id() ]
		];
		
		if(  count(wp_get_post_terms( \get_queried_object_id(), $settings['fetch_related_taxonomy'] )) > 0 )
		{
			$args += [
				'tax_query' => [
					'relation' => 'OR',
					[
						'taxonomy' => $settings['fetch_related_taxonomy'],
						'field'    => 'term_taxonomy_id',
						'terms'    => array_map( function ( $value ) {
							return $value->term_id;
						}, wp_get_post_terms( \get_queried_object_id(), $settings['fetch_related_taxonomy'] ) )
					]
				]
			];
		}
		
		$the_query = new \WP_Query( $args );
		$population = [];
		
		if ( $the_query->have_posts() )
		{
			while( $the_query->have_posts() )
			{
				$the_query->the_post();
				$population [$the_query->post->ID]= $the_query->post->post_title;
			}
			\wp_reset_postdata();
			return $population;
		}
		
		return $population;
	}
	
	/**
	 * @return bool|\WP_Query
	 */
	protected function fetchRenderProjects()
	{
		$settings = $this->get_settings_for_display();
		
		$args = [
			'post_type'     =>  $settings['load_style']?? \get_post_type( \get_queried_object_id() ),
			'post_status'   =>  'publish',
			'post__not_in'  =>  [ \get_queried_object_id() ],
			'paged'         =>  get_query_var( 'paged' )?? 1
		];
		
		if( $settings['posts_amount'] )
		{
			$args += [
				'posts_per_page' => (int) $settings[ 'posts_amount' ]
			];
		}
		
		if( isset($settings['sticky_1']) )
		{
			
			$args ['post__not_in']= [ \get_queried_object_id(), $settings['sticky_1'] ];
		}
		
		if( isset($settings['fetch_related']) && count( \wp_get_post_terms( \get_queried_object_id(), $settings['fetch_related_taxonomy'] )) > 0 )
		{
			$args += [
				'tax_query' => [
					'relation' => 'OR',
					[
						'taxonomy' => $settings['fetch_related_taxonomy'],
						'field'    => 'term_taxonomy_id',
						'terms'    => array_map( function ( $value ) {
							return $value->term_id;
						}, \wp_get_post_terms( \get_queried_object_id(), $settings['fetch_related_taxonomy'] ) )
					]
				]
			];
		}
		
		$q = new \WP_Query( $args );
		if( $q->have_posts() ) return $q;
		return false;
	}
}