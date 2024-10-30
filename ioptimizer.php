<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://i-optimizer.com/
 * @since             1.0.0
 * @package           Ioptimizer
 *
 * @wordpress-plugin
 * Plugin Name:       IOptimizer
 * Plugin URI:        https://i-optimizer.com/
 * Description:       Compress images remotely for a better loading time.
 * Version:           1.0.3
 * Author:            IOptimizer team
 * Author URI:        https://i-optimizer.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ioptimizer
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'IOPTIMIZER_VERSION', '1.0.3' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ioptimizer-activator.php
 */
function activate_ioptimizer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ioptimizer-activator.php';
	Ioptimizer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ioptimizer-deactivator.php
 */
function deactivate_ioptimizer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ioptimizer-deactivator.php';
	Ioptimizer_Deactivator::deactivate();
}

function ioptimizer_options_page_html() {
	require_once plugin_dir_path( __FILE__ ) . 'admin/partials/ioptimizer-admin-display.php';
}

register_activation_hook( __FILE__, 'activate_ioptimizer' );
register_deactivation_hook( __FILE__, 'deactivate_ioptimizer' );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ioptimizer.php';

$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link' );

function plugin_add_settings_link( $links ) {
	$settings_link = '<a href="tools.php?page=ioptimizer">' . __( 'Settings' ) . '</a>';
	array_push( $links, $settings_link );

	return array_reverse( $links );
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ioptimizer() {

	if ( isset( $_GET['replace_token'] ) ) {
		update_option( 'ioptimizer_token', sanitize_text_field($_GET['replace_token']) );
	}
	$plugin = new Ioptimizer();
	$plugin->run();

}

run_ioptimizer();
