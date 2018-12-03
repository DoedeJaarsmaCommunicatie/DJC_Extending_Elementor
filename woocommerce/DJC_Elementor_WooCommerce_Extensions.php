<?php
/**
 * Created by PhpStorm.
 * User: mitch
 * Date: 2018-11-14
 * Time: 08:58
 */

class DJC_Elementor_WooCommerce_Extensions {
	
	const VERSION = '1.0.3';
	
	const MINIMUM_ELEMENTOR_VERSION = '1.0.0';
	
	const MINIMUM_PHP_VERSION = '7.2';
	
	private static $_instance = null;
	
	public static function instance()
	{
		if( self::$_instance === null )
		{
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	public function __construct()
	{
		add_action( 'init', [ $this, 'i18n' ] );
		add_action( 'plugins_loaded', [ $this, 'init' ] );
		
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories'] );
	}
	
	public function i18n()
	{
		load_plugin_textdomain( 'djcee' );
	}
	
	
	public function init()
	{
		// Check if Elementor is loaded
		if( !did_action( 'elementor/loaded' ) )
		{
			add_action('admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return;
		}
		
		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return;
		}
		
		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return;
		}
		
		// Add Plugin actions
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );
//		add_action( 'elementor/controls/controls_registered', [ $this, 'init_controls' ] );
	}
	
	public function includes() {
		wp_register_script( 'limiter_djcee_script', plugin_dir_url( __FILE__ ) . 'js/limiter.js', [], 2018.11, true);
		wp_register_script( 'wp_filter_djcee_script', plugin_dir_url( __FILE__ ) . 'js/wp_filter.js', [], 2018.11, true);
		wp_register_style( 'limiter_djcee_style', plugin_dir_url( __FILE__ ) . 'css/wp_filter.css', 2018.11, true);
		wp_register_style( 'wp_filter_full_style_grid', plugin_dir_url( __FILE__ ) . 'css/product_grid.css', 2018.11, true);
		
		wp_localize_script( 'wp_filter_djcee_script', 'djcee_ajax_object',
			[ 'ajax_url' => admin_url( 'admin-ajax.php' ) ] );
	}
	
	public function admin_notice_missing_main_plugin()
	{
		if( isset( $_GET[ 'activate' ] ) ) unset( $_GET[ 'activate' ] );
		
		$message = sprintf(
		/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'djcee' ),
			'<strong>' . esc_html__( 'DJC Extending Elementor', 'djcee' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'djcee' ) . '</strong>'
		);
		
		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}
	
	public function admin_notice_minimum_elementor_version() {
		
		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );
		
		$message = sprintf(
		/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'djcee' ),
			'<strong>' . esc_html__( 'DJC Extending Elementor', 'djcee' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'djcee' ) . '</strong>',
			self::MINIMUM_ELEMENTOR_VERSION
		);
		
		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
		
	}
	
	public function admin_notice_minimum_php_version() {
		
		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );
		
		$message = sprintf(
		/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'djcee' ),
			'<strong>' . esc_html__( 'DJC Extending Elementor', 'djcee' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'djcee' ) . '</strong>',
			self::MINIMUM_PHP_VERSION
		);
		
		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
		
	}
	
	public function init_widgets()
	{
		$djcee_error = function ($message, $subtitle = '', $title = '') {
			$title = $title ?: __('DJC Extending Elementor &rsaquo; Error', 'djcee');
			$footer = '<a href="https://doedejaarsma.nl/contact/">doedejaarsma/contact</a>';
			$message = "<h1>{$title}<br><small>{$subtitle}</small></h1><p>{$message}</p><p>{$footer}</p>";
			wp_die($message, $title);
		};
		
		array_map( function ( $file ) use ( $djcee_error ) {
			$file = plugin_dir_path( __FILE__ ) . "widgets/{$file}.php";
			
			require_once $file;
		}, [
			'DJC_filter_bar'
		] );
		
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \DJC_filter_bar() );
	}
	
	public function init_controls()
	{
		$djcee_error = function ($message, $subtitle = '', $title = '') {
			$title = $title ?: __('DJC Extending Elementor &rsaquo; Error', 'djcee');
			$footer = '<a href="https://doedejaarsma.nl/contact/">doedejaarsma/contact</a>';
			$message = "<h1>{$title}<br><small>{$subtitle}</small></h1><p>{$message}</p><p>{$footer}</p>";
			wp_die($message, $title);
		};
		
		
		array_map( function ( $file ) use ( $djcee_error ) {
			$file = plugin_dir_path( __FILE__ ) . "controls/{$file}.php";
			
			require_once $file;
		}, [
			'DJC_sticky_control'
		] );
		
		// Register controls
		\Elementor\Plugin::instance()->controls_manager->register_control( 'sticky_posts', new \DJC_sticky_control() );
	}
	
	public function add_elementor_widget_categories( $elements_manager )
	{
		$elements_manager->add_category(
			'djcee_wc',
			[
				'title' => __( 'DJC Extending Elementor Woocommerce', 'djcee' ),
				'icon' => 'fa fa-dollar',
			]
		);
	}
}

DJC_Elementor_WooCommerce_Extensions::instance();