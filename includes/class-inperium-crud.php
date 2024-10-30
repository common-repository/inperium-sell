<?php
/**
 * Regular CRUD operations related to the Inperium Plugin.
 *
 * @link       https://inperium.com
 * @since      1.0.0
 *
 * @package    Inperium
 * @subpackage Inperium/includes
 */

/**
 * Regular CRUD operations related to the Inperium Plugin.
 *
 * @package    Inperium
 * @subpackage Inperium/includes
 * @author     Artyom Gerus <artyom@inperium.com>
 */
class Inperium_CRUD {

	/**
	 * Current version of Inperium Plugin database.
	 * Needed for database upgrade functionality.
	 *
	 * @since   1.0.0
	 * @var     string DB_VERSION  Current version of Inperium Plugin database.
	 */
	const DB_VERSION = '1.1';

	/**
	 * The name of the Inperium database table without prefix.
	 *
	 * @since   1.0.0
	 * @var     string TABLE_NAME_BASE  The name of the Inperium database table without prefix.
	 */
	const TABLE_NAME_BASE = 'inperium_forms';

	/**
	 * Global WPDB object which is needed to talk to the WordPress database.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     object $wpdb  Global WPDB object which is needed to talk to the WordPress database.
	 */
	protected $wpdb;

	/**
	 * The name of the Inperium database table, ready for use in requests.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     string $table_name  The name of the Inperium database table, ready for use in requests.
	 */
	protected $table_name;

	/**
	 * Initializes the class and sets its properties.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {

		global $wpdb;

		$this->wpdb       = $wpdb;
		$this->table_name = $wpdb->prefix . self::TABLE_NAME_BASE;

	}

	/**
	 * Gets existing form settings from database.
	 *
	 * Returns `null` if no settings are stored yet or ID is null.
	 *
	 * @since  1.0.0
	 * @param  int $id  ID of the requested form settings record.
	 * @return mixed  Form settings object or `null` if something is wrong.
	 */
	public function get_form_settings( $id ) {

		if ( isset( $id ) && is_numeric( $id ) ) {

			$cache_key = 'form_settings_' . $id;
			$form      = wp_cache_get( $cache_key );

			if ( false === $form ) {
				$wpdb = $this->wpdb;
				$form = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT * FROM $this->table_name WHERE id = %d", // phpcs:ignore
						$id
					)
				); // db call ok.

				if ( null !== $form && isset( $form->fields ) ) {
					$form->fields = json_decode( $form->fields, true );
				}

				wp_cache_set( $cache_key, $form );
			}

			return $form;
		}

		return null;
	}

	/**
	 * Gets all stored form settings and returns their IDs and names.
	 *
	 * Returns `null` if no settings are stored yet.
	 *
	 * @since  1.0.0
	 * @return  array  Array of objects containing IDs and names.
	 */
	public function get_all_forms() {

		$cache_key = 'form_settings_all';
		$forms     = wp_cache_get( $cache_key );

		if ( false === $forms ) {
			$forms = $this->wpdb->get_results(
				"SELECT id, name
				FROM $this->table_name" // phpcs:ignore
			);

			wp_cache_set( $cache_key, $forms );
		}

		return $forms;
	}

	/**
	 * Puts form settings into database.
	 *
	 * Updates existing row if ID is provided, otherwise creates a new one.
	 *
	 * @since  1.0.0
	 * @param  int    $id                Optional. ID of the updated form.
	 * @param  string $name              Name of the updated form.
	 * @param  array  $fields            Fields of the updated form.
	 * @param  string $comment           Comment of the updated form.
	 * @param  string $redirect_page_id  ID of the page to redirect to after successful submit.
	 * @return mixed  Returns the ID of created or updated row, or `false` on error.
	 */
	public function put_form_settings( $id, $name, $fields, $comment, $redirect_page_id ) {

		$wpdb   = $this->wpdb;
		$data   = array(
			'name'             => $name,
			'fields'           => wp_json_encode( $fields ),
			'comment'          => $comment,
			'redirect_page_id' => $redirect_page_id,
		);
		$format = array(
			'%s',
			'%s',
			'%s',
			'%s',
		);

		if ( ! isset( $id ) ) {
			$result = $wpdb->insert(
				$this->table_name,
				$data,
				$format
			); // db call ok.
			return false !== $result ? $wpdb->insert_id : false;
		} else {
			$result = $wpdb->update( // phpcs:ignore
				$this->table_name,
				$data,
				array( 'id' => $id ),
				$format,
				array( '%d' )
			); // db call ok.
			return false !== $result ? $id : false;
		}

	}

	/**
	 * Deletes existing form settings record from database.
	 *
	 * @since  1.0.0
	 * @param  int $id  ID of the form settings record which has to be deleted.
	 * @return mixed  Returns the number of rows updated, or `false` on error.
	 */
	public function delete_form_settings( $id ) {

		return $this->wpdb->delete(
			$this->table_name,
			array( 'id' => $id )
		);

	}

	/**
	 * Checks database version and updates if necessary.
	 *
	 * @since  1.0.0
	 */
	public function update_db_check() {

		$version = get_site_option( 'inperium_db_version' );

		if ( self::DB_VERSION !== $version ) {
			$this->create_table();
		}

		if ( '1.0' === $version ) {
			$this->wpdb->query(
				"ALTER TABLE $this->table_name DROP COLUMN entity_type" // phpcs:ignore
			); // db call ok, no cache ok.
		}

	}

	/**
	 * A utility function that is used to create a new table for Inperium Sell Plugin needs.
	 *
	 * @since  1.0.0
	 */
	public function create_table() {

		$charset_collate = $this->wpdb->get_charset_collate();

		$sql = "CREATE TABLE $this->table_name (
			id mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(50) NOT NULL,
			fields longtext NOT NULL,
			comment text NULL,
			redirect_page_id varchar(10) NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'inperium_db_version', self::DB_VERSION );

	}

}
