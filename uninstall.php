<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Cleans up the options and drops the settings table.
 *
 * @link       https://inperium.com/
 * @since      1.0.0
 *
 * @package    Inperium
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'inperium_api_key' );
delete_option( 'inperium_db_version' );

global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}inperium_forms" ); // phpcs:ignore
