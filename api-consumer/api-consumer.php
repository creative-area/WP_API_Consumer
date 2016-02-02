<?php
/**
 * WordPress API Consumer Plugin
 *
 * This plugin allows you to fetch data from a restfull API and
 * to display those data via a shortcode.
 *
 * @since    0.0.1
 * @package  API_Consumer
 *
 * @wordpress-plugin
 * Plugin Name:      API Consumer
 * Plugin URI:       http://www.creative-area.net
 * Description:      This plugin allows you to fetch data from a restfull API and to display those data via a shortcode.
 * Version:          0.0.1
 * Author:           CREATIVE AREA
 * Author URI:       http://www.creative-area.net
 * License:          GPL-2.0+
 * License URI:      http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:      api-consumer
 * Domain Path:      /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-api-consumer-activator.php
 */
function activate_api_consumer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-api-consumer-activator.php';
	API_Consumer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-api-consumer-deactivator.php
 */
function deactivate_api_consumer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-api-consumer-deactivator.php';
	API_Consumer_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_api_consumer' );
register_deactivation_hook( __FILE__, 'deactivate_api_consumer' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-api-consumer.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since		0.0.1
 */
function run_api_consumer() {
	$plugin = new API_Consumer();
	$plugin->run();
}
run_api_consumer();
