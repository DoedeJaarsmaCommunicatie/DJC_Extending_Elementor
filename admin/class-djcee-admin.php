<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://doedejaarsma.nl
 * @since      1.0.0
 *
 * @package    Djcee
 * @subpackage Djcee/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Djcee
 * @subpackage Djcee/admin
 * @author     Mitch Hijlkema <mitch@doedejaarsma.nl>
 */
class Djcee_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Djcee_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Djcee_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/djcee-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Djcee_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Djcee_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/djcee-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Registers the default and not so default files
	 *
	 * @return void
	 */
	public function includes()
	{
		$djcee_error = function ( $message, $subtitle = '', $title = '' ) {
			$title = $title ?: __('DJC Extending Elementor &rsaquo; Error', 'djcee');
			$footer = '<a href="https://doedejaarsma.nl/contact/">doedejaarsma/contact</a>';
			$message = "<h1>{$title}<br><small>{$subtitle}</small></h1><p>{$message}</p><p>{$footer}</p>";
			wp_die( $message, $title );
		};

		$includes = [
			'DJC_Elementor_Extensions'
		];
		
		array_map( function( $file ) use( $djcee_error ) {
			$file = plugin_dir_path( __FILE__ ) . "elementor/{$file}.php";
			
			require_once $file;
		}, $includes);
	}
}
