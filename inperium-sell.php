<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://inperium.com/
 * @since             1.0.0
 * @package           Inperium
 *
 * @wordpress-plugin
 * Plugin Name:       Inperium Sell
 * Description:       Convert your website visitors into leads by capturing their contact details and adding them into your CRM with a form builder from Inperium Sell.
 * Version:           1.0.0
 * Author:            Inperium Corp.
 * Author URI:        https://inperium.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       inperium
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'INPERIUM_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 */
function activate_inperium() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-inperium-activator.php';
	Inperium_Activator::activate();
}

register_activation_hook( __FILE__, 'activate_inperium' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-inperium.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_inperium() {

	$plugin = new Inperium();
	$plugin->run();

}
run_inperium();
