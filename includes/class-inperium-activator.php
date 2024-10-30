<?php
/**
 * Fired during plugin activation
 *
 * @link       https://inperium.com
 * @since      1.0.0
 *
 * @package    Inperium
 * @subpackage Inperium/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Inperium
 * @subpackage Inperium/includes
 * @author     Artyom Gerus <artyom@inperium.com>
 */
class Inperium_Activator {

	/**
	 * Creates a new table in database to store form settings.
	 *
	 * @since  1.0.0
	 */
	public static function activate() {

		$crud = new Inperium_CRUD();
		$crud->create_table();

	}

}
