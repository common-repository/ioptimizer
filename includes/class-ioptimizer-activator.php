<?php

/**
 * Fired during plugin activation
 *
 * @link       https://i-optimizer.com/
 * @since      1.0.0
 *
 * @package    Ioptimizer
 * @subpackage Ioptimizer/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ioptimizer
 * @subpackage Ioptimizer/includes
 * @author     Angel Daniel Mainerici <mainerici.angel@gmail.com>
 */
class Ioptimizer_Activator {
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		update_option( 'ioptimizer_host', 'https://i-optimizer.com/' );
		update_option( 'ioptimizer_status', "on" );
		if ( ! get_option( 'ioptimizer_token' ) ) {
			add_option( 'ioptimizer_token', 'free' );
		}
		update_option( 'ioptimizer_lazy_load', '' );
		wp_remote_get( sprintf( '%sapi/token/%s', get_option( 'ioptimizer_host' ), get_option( 'ioptimizer_token' ) ), [
			'headers' => [
				'platform'  => 'wordpress',
				'host-name' => get_option( 'siteurl' ),
			]
		] );
	}
}
