<?php
/**
 * Created by PhpStorm.
 * User: mitch
 * Date: 2018-11-06
 * Time: 11:21
 */

class DJC_sticky_control extends \Elementor\Base_Data_Control
{
	public function get_type() {
		return 'sticky_posts';
	}
	
	/**
	 * Get select2 control default settings.
	 *
	 * Retrieve the default settings of the select2 control. Used to return the
	 * default settings while initializing the select2 control.
	 *
	 * @since 3.0.0
	 * @access protected
	 *
	 * @return array Control default settings.
	 */
	protected function get_default_settings() {
		return [
			'options' => [],
			'multiple' => false,
			'select2options' => $this->fetchAllRelatedPosts(),
		];
	}
	
	public function content_template() {
		$control_uid = $this->get_control_uid();
		?>
		<div class="elementor-control-field">
			<label for="<?php echo esc_attr( $control_uid ); ?>" class="elementor-control-title">{{{ data.label }}}</label>
			<div class="elementor-control-input-wrapper">
				<# var multiple = ( data.multiple ) ? 'multiple' : ''; #>
				<select id="<?php echo $control_uid; ?>" class="elementor-select2" type="select2" {{ multiple }} data-setting="{{ data.name }}">
					<option></option>
					<# _.each( data.select2options.posts, function ( post ) {
						var value = data.controlValue
						if ( typeof value == 'string' ) {
							var selected = ( post.post_title === value ) ? 'selected' : ''
						} else if ( null !== value ) {
							var value = _.values( value )
							var selected = ( -1 !== value.indexOf( option_value ) ) ? 'selected' : ''
						} #>
					<option value="{{post.ID}}" {{ selected }}>{{post.post_title}}</option>
					<#
					} ) #>
				</select>
			</div>
		</div>
		<# if ( data.description ) { #>
			<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}
	
	protected function fetchAllRelatedPosts()
	{
		$id = \get_queried_object_id();
		
		$post_type = \get_post_type( $id );
		
		$args = [
			'post_type'         =>  $post_type,
			'post_status'       =>  'publish',
			'posts_per_page'    =>  -1,
			'order'             =>  'ASC',
			'orderby'           =>  'name',
			'post__not_in'      =>  [ \get_queried_object_id() ]
		];
		
		$q = new \WP_Query( $args );
		
		if( $q->have_posts() ) return $q;
		return false;
	}
	
}