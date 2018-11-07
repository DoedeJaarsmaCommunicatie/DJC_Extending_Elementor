<?php

/**
 * Fired during plugin activation
 *
 * @link       https://doedejaarsma.nl
 * @since      1.0.0
 *
 * @package    Djcee
 * @subpackage Djcee/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Djcee
 * @subpackage Djcee/includes
 * @author     Mitch Hijlkema <mitch@doedejaarsma.nl>
 */
class Djcee_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() 
	{
		if( defined( 'DJCEE_VERSION' ) ) :

			update_option( 'DJCEE_VERSION', DJCEE_VERSION );
		else:
			
			update_option( 'DJCEE_VERSION', '1.0.0');
		endif;
	}

}
