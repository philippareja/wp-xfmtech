<?php

/**
 * Plugin Name:       Themify Updater
 * Plugin URI:        https://themify.me/docs/themify-updater-documentation
 * Description:       This plugin allows you to auto update all Themify themes and plugins with a license key.
 * Version:           1.0.6
 * Author:            Themify
 * Author URI:        https://themify.me
 * Text Domain:       themify-updater
 * Domain Path:       /languages
 */
 
 // If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
/**
 * The code that runs during plugin activation.
 */
function activate_themify_updater() {
    delete_transient("themify_updater_cache");
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_themify_updater() {
    delete_transient("themify_updater_cache");
}

register_activation_hook(__FILE__, 'activate_themify_updater');
register_deactivation_hook(__FILE__, 'deactivate_themify_updater');
//add_filter( 'plugin_row_meta', 'themify_updater_plugin_row_meta', 10, 2 );
function themify_updater_plugin_row_meta( $links, $file ) {
	if ( plugin_basename( __FILE__ ) === $file ) {
		$row_meta = array(
		  'changelogs'    => '<a href="' . esc_url( 'https://themify.me/changelogs/' ) . basename( dirname( $file ) ) .'.txt" target="_blank" aria-label="' . esc_attr__( 'Plugin Changelogs', 'ptb' ) . '">' . esc_html__( 'View Changelogs', 'ptb' ) . '</a>'
		);

		return array_merge( $links, $row_meta );
	}
	return (array) $links;
}

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/init.php';

if( !function_exists('get_plugin_data') || !function_exists('is_plugin_active_for_network') ){
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

$themify_updater_data = get_plugin_data( __FILE__ );

define('THEMIFY_UPDATER_DIR_PATH', dirname( __FILE__ ) );
define('THEMIFY_UPDATER_VERSION', $themify_updater_data['Version'] );
define('THEMIFY_UPDATER_DIR_URL', plugin_dir_url(__FILE__));
define('THEMIFY_UPDATER_NETWORK_ENABLED', is_plugin_active_for_network(basename(dirname(__FILE__)).'/'.basename(__FILE__)));

unset($themify_updater_data);

$themify_updater = Themify_Updater::get_instance();
