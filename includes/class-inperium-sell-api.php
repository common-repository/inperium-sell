<?php
/**
 * Wrapper for Inperium Sell API.
 *
 * @link       https://inperium.com
 * @since      1.0.0
 *
 * @package    Inperium
 * @subpackage Inperium/includes
 */

/**
 * Wrapper for Inperium Sell API.
 *
 * @package    Inperium
 * @subpackage Inperium/includes
 * @author     Artyom Gerus <artyom@inperium.com>
 */
class Inperium_Sell_API {

	/**
	 * Base part of the API URL.
	 *
	 * @since   1.0.0
	 * @var     string API_BASE  Base part of the API URL.
	 */
	const API_BASE = 'https://api.inperium.com/v1/sell/';

	/**
	 * Content type header.
	 *
	 * @since   1.0.0
	 * @var     string CONTENT_TYPE  Content type header.
	 */
	const CONTENT_TYPE = 'application/json';

	/**
	 * Entity types in Inperium Sell which are allowed to be created via API.
	 *
	 * @since   1.0.0
	 * @var     array ALLOWED_ENTITIES  Entity types.
	 */
	const ALLOWED_ENTITIES = array( 'contacts', 'companies', 'activities' );

	/**
	 * API key to autheticate the request.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     string $api_key  API key to autheticate the request.
	 */
	protected $api_key;

	/**
	 * Initializes the class and sets its properties.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {

		$this->api_key = get_option( 'inperium_api_key' );

	}

	/**
	 * Makes the API request.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @param   string $url     The URL to request.
	 * @param   string $method  Request method (post, get).
	 * @param   array  $data    Request data.
	 * @return  mixed  Request result or `false` if something is wrong.
	 */
	protected function do_request( $url, $method, $data ) {

		if ( isset( $this->api_key ) && '' !== $this->api_key && false !== $this->api_key && ! empty( $method ) ) {

			$args = array();

			switch ( strtolower( $method ) ) {
				case 'post':
					$args = array(
						'headers' => array(
							'Content-Type' => self::CONTENT_TYPE,
							'X-API-KEY'    => $this->api_key,
						),
						'body'    => wp_json_encode( $data ),
					);
					return wp_remote_post( $url, $args );
				case 'get':
					$args = array(
						'headers' => array(
							'X-API-KEY' => $this->api_key,
						),
					);
					$url .= '?' . http_build_query( $data, null, '&' );
					return wp_remote_get( $url, $args );
				default:
					return false;
			};

		} else {
			return false;
		}

	}

	/**
	 * Creates a new entity in Inperium Sell.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @param   string $type  Entity type (`companies`, `contacts`, `activities`).
	 * @param   array  $data  Entity data.
	 * @return  mixed  An array which contains response `status` and json-decoded `body`, or `false` if something is wrong.
	 */
	protected function create_entity( $type, $data ) {

		if ( in_array( $type, self::ALLOWED_ENTITIES, true ) ) {
			$response = $this->do_request(
				self::API_BASE . $type,
				'POST',
				$data
			);
			if ( false !== $response && is_array( $response ) ) {
				return array(
					'status' => $response['response']['code'],
					'body'   => json_decode( wp_remote_retrieve_body( $response ) ),
				);
			} else {
				return false;
			}
		}

		return false;

	}

	/**
	 * Creates a new company in Inperium Sell.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   array $data  Company form data.
	 * @return  mixed  An array which contains response `status` and json-decoded `body`, or `false` if something is wrong.
	 */
	public function create_company( $data ) {

		return $this->create_entity( 'companies', $data );

	}

	/**
	 * Creates a new contact in Inperium Sell.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   array $data  Contact form data.
	 * @return  mixed  An array which contains response `status` and json-decoded `body`, or `false` if something is wrong.
	 */
	public function create_contact( $data ) {

		return $this->create_entity( 'contacts', $data );

	}

	/**
	 * Creates a new activity record.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   array $data  Data for activity record.
	 * @return  mixed  An array which contains response `status` and json-decoded `body`, or `false` if something is wrong.
	 */
	public function create_activity( $data ) {

		return $this->create_entity( 'activities', $data );

	}

	/**
	 * Gets available properties from Sell API for specific entity type.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   array   $entity_types  Array of entity types to get properties for.
	 * @param   boolean $drop_transient  Defines whether to drop an existing request transient (cache) or not. Default: `false`.
	 * @return  object  Available properties for provided entity type. Returns `false` if something is wrong.
	 */
	public function get_properties( $entity_types, $drop_transient = false ) {

		$transient_name = 'properties_query_result_' . strtoupper( implode( '_', $entity_types ) );

		if ( true === $drop_transient ) {
			delete_transient( $transient_name );
		}

		$data = get_transient( $transient_name );

		if ( false === $data ) {
			$response = $this->do_request(
				self::API_BASE . 'properties',
				'GET',
				array(
					'pageNumber' => 1,
					'pageSize'   => 10000,
					'sort'       => '+label',
					'objectType' => 'in::' . strtoupper( implode( ',', $entity_types ) ),
					'dataType'   => 'in::SINGLE_LINE,NUMBER,EMAILS,PHONE_NUMBERS',
				)
			);
			if ( false !== $response && ! is_wp_error( $response ) && is_array( $response ) ) {
				$json = json_decode( wp_remote_retrieve_body( $response ) );
				if ( isset( $json->data ) ) {
					set_transient( $transient_name, $json->data, 10 * MINUTE_IN_SECONDS );
					return $json->data;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return $data;
		}

	}

}
