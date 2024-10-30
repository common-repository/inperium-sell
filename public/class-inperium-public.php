<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://inperium.com
 * @since      1.0.0
 *
 * @package    Inperium
 * @subpackage Inperium/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, hooks for enqueueing
 * the public-facing stylesheet and JavaScript and all internal methods.
 *
 * @package    Inperium
 * @subpackage Inperium/public
 * @author     Artyom Gerus <artyom@inperium.com>
 */
class Inperium_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string $inperium  The ID of this plugin.
	 */
	private $inperium;

	/**
	 * The version of this plugin.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string $version  The current version of this plugin.
	 */
	private $version;

	/**
	 * Instance of the Inperium_CRUD object for accessing form settings table.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     Inperium_CRUD $crud  Instance of the Inperium_CRUD object.
	 */
	protected $crud;

	/**
	 * Initializes the class and sets its properties.
	 *
	 * @since  1.0.0
	 * @param  string $inperium  The name of the plugin.
	 * @param  string $version  The version of this plugin.
	 */
	public function __construct( $inperium, $version ) {

		$this->inperium = $inperium;
		$this->version  = $version;

		$this->crud = new Inperium_CRUD();

	}

	/**
	 * Registers the stylesheets for the public-facing side of the site.
	 *
	 * @since  1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->inperium, plugin_dir_url( __FILE__ ) . 'css/inperium-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Registers the JavaScript for the public-facing side of the site.
	 *
	 * @since  1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->inperium, plugin_dir_url( __FILE__ ) . 'js/inperium-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script(
			$this->inperium,
			'inperium_ajax',
			array(
				'ajax_url'           => admin_url( 'admin-ajax.php' ),
				'nonce'              => wp_create_nonce( 'inperium_form_submit' ),
				'submit_text'        => __( 'Submit', 'inperium' ),
				'submitted_text'     => __( 'Submitted!', 'inperium' ),
				'submitting_text'    => __( 'Submitting...', 'inperium' ),
				'error_text'         => __( 'Error', 'inperium' ),
				'unknown_error_text' => __( 'Unknown error', 'inperium' ),
			)
		);
		wp_enqueue_script( 'jquery-validate', plugin_dir_url( __FILE__ ) . 'js/vendor/jquery.validate.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'jquery-form' );

	}

	/**
	 * Defines all necessary input attributes for every specific data type.
	 *
	 * Returns default `type="text"` if type is empty or no specific attributes are set for this type.
	 *
	 * @since  1.0.0
	 * @param  string $data_type  Data type.
	 * @return string  Input attributes.
	 */
	public function get_input_attributes_for_data_type( $data_type ) {

		if ( ! empty( $data_type ) ) {
			switch ( $data_type ) {
				case 'SINGLE_LINE':
					return 'type="text"';
				case 'NUMBER':
					return 'type="text" inputmode="numeric" pattern="[0-9]*" data-rule-number';
				case 'EMAILS':
					return 'type="email" autocomplete="email"';
				case 'PHONE_NUMBERS':
					return 'type="tel" autocomplete="tel"';
			};
		};

		return 'type="text"';

	}

	/**
	 * Renders a form for specified ID as a string containing HTML code.
	 *
	 * Returns empty string if ID is invalid or form doesn't exist.
	 *
	 * @since  1.0.0
	 * @param  number $id  Form ID.
	 * @return string  HTML output.
	 */
	public function render_form_by_id( $id ) {

		$html = '';

		if ( is_numeric( $id ) ) {

			$form = $this->crud->get_form_settings( $id );

			if ( isset( $form ) ) {

				uasort(
					$form->fields,
					function( $a, $b ) {
						return $a['order'] - $b['order'];
					}
				);

				$html .= '<div class="inperium-form-wrapper">';
				$html .= ' <form action="" class="inperium-form">';
				$html .= '  <input type="hidden" name="formId" value="' . esc_attr( $id ) . '">';

				foreach ( (array) $form->fields as $field_name => $field_settings ) {
					if ( isset( $field_settings['checked'] ) && 'true' === $field_settings['checked'] ) {
						$html .= '  <div class="inperium-form-group">';
						$html .= '   <div class="inperium-form-label-wrapper">';
						$html .= '    <label for="' . esc_attr( $field_name ) . '">' . esc_html( $field_settings['label'] ) . '</label>';
						$html .= '   </div>';
						$html .= '   <div class="inperium-form-input-wrapper">';
						$html .= '    <input '
													. $this->get_input_attributes_for_data_type( $field_settings['data_type'] )
													. ' name="fields[' . esc_attr( $field_name ) . '][value]" id="' . esc_attr( $field_name ) . '" '
													. ( isset( $field_settings['required'] ) && 'true' === $field_settings['required'] ? 'required' : '' )
													. '>';
						$html .= '    <input '
													. 'type="hidden"'
													. 'name="fields[' . esc_attr( $field_name ) . '][property_name]"'
													. 'value="' . esc_attr( $field_settings['property_name'] ) . '"'
													. '>';
						$html .= '    <input '
													. 'type="hidden"'
													. 'name="fields[' . esc_attr( $field_name ) . '][entity_type]"'
													. 'value="' . esc_attr( $field_settings['entity_type'] ) . '"'
													. '>';
						$html .= '   </div>';
						$html .= '  </div>';
					}
				}

				$html .= '  <div class="error" style="display: none;"></div>';
				$html .= '  <button type="submit">' . esc_html__( 'Submit', 'inperium' ) . '</button>';
				$html .= ' </form>';
				$html .= '</div>';
			}
		}

		return $html;

	}

	/**
	 * The [inperium-form] shortcode.
	 *
	 * Accepts an ID and will display a form.
	 *
	 * @since  1.0.0
	 * @param  array  $atts  Shortcode attributes. Empty by default.
	 * @param  string $content  Shortcode content. Null by default.
	 * @param  string $tag  Shortcode tag (name). Empty by default.
	 * @return string  Shortcode output.
	 */
	public function inperium_form_shortcode( $atts = array(), $content = null, $tag = '' ) {

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		$inperium_atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			$tag
		);

		$output  = '';
		$form_id = esc_html( $inperium_atts['id'] );

		$api_key = get_option( 'inperium_api_key' );

		if ( isset( $api_key ) && '' !== $api_key && false !== $api_key ) {
			$output .= $this->render_form_by_id( $form_id );
		}

		if ( ! is_null( $content ) ) {
				$output .= do_shortcode( apply_filters( 'the_content', $content ) );
		}

		return $output;

	}

	/**
	 * Initializes all Inperium Sell Plugin shortcodes.
	 *
	 * @since  1.0.0
	 */
	public function inperium_shortcodes_init() {

		add_shortcode( 'inperium-form', array( $this, 'inperium_form_shortcode' ) );

	}

	/**
	 * Checks if a request was successful or not by provided response.
	 *
	 * Interrupts execution and sends json error on request failure.
	 *
	 * @since  1.0.0
	 * @param  array $response  Response to check.
	 */
	public function check_request( $response ) {

		if ( false === $response || ! is_array( $response ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Request failed due to a network error.', 'inperium' ),
				),
				503
			);
		}

		$status = $response['status'];

		if ( $status >= 400 && $status <= 599 ) {
			wp_send_json_error(
				array(
					'message' => __( 'Server responded with an error.', 'inperium' ),
				),
				$status
			);
		}

	}

	/**
	 * Handles all submits from Inperium forms.
	 *
	 * @since  1.0.0
	 */
	public function inperium_form_handler() {

		check_ajax_referer( 'inperium_form_submit' );

		if ( ! empty( $_POST['formId'] ) && is_numeric( $_POST['formId'] ) ) {

			$form_id = sanitize_text_field( wp_unslash( $_POST['formId'] ) );

			$form_settings = $this->crud->get_form_settings( $form_id );

			$form_comment          = $form_settings->comment;
			$form_redirect_page_id = $form_settings->redirect_page_id;

			if ( ! empty( $_POST['fields'] && is_array( $_POST['fields'] ) ) ) {

				$form_fields = array();

				foreach ( $_POST['fields'] as $key => $field ) { // phpcs:ignore
					$value = null;
					if ( 'emails' === $field['property_name'] ) {
						$value = array(
							array(
								'value'     => sanitize_email( wp_unslash( $field['value'] ) ),
								'type'      => 'PERSONAL',
								'isPrimary' => true,
							),
						);
					} elseif ( 'phoneNumbers' === $field['property_name'] ) {
						$value = array(
							array(
								'value'     => sanitize_text_field( wp_unslash( $field['value'] ) ),
								'type'      => 'PERSONAL',
								'isPrimary' => true,
							),
						);
					} else {
						$value = sanitize_text_field( wp_unslash( $field['value'] ) );
					}
					$form_fields[ strtolower( $field['entity_type'] ) ][ $field['property_name'] ] = $field['value'];
				}

				$api = new Inperium_Sell_API();

				$company_id = null;
				$contact_id = null;
				$body       = array( 'message' => __( 'Data has been successfully submitted!', 'inperium' ) );

				if ( ! empty( $form_fields['companies'] ) && ! empty( array_filter( $form_fields['companies'] ) ) ) {
					$response_companies = $api->create_company( $form_fields['companies'] );
					$this->check_request( $response_companies );
					$company_id = $response_companies['body']->id;
				}

				if ( ! empty( $form_fields['contacts'] ) && ! empty( array_filter( $form_fields['contacts'] ) ) ) {
					if ( null !== $company_id ) {
						$form_fields['contacts']['company'] = array( 'id' => $company_id );
					}
					$response_contacts = $api->create_contact( $form_fields['contacts'] );
					$this->check_request( $response_contacts );
					$contact_id = $response_contacts['body']->id;
				}

				if ( ( null !== $company_id || null !== $contact_id ) && ! empty( $form_comment ) ) {
					$response_activities = $api->create_activity(
						array(
							'body'                => $form_comment,
							'type'                => 'note',
							'associatedCompanies' => null !== $company_id ? array( $company_id ) : array(),
							'associatedContacts'  => null !== $contact_id ? array( $contact_id ) : array(),
						)
					);
					$this->check_request( $response_activities );
				};

				if ( ! empty( $form_redirect_page_id ) ) {
					$body['redirect_page'] = get_permalink( $form_redirect_page_id );
				}

				wp_send_json_success( $body, 201 );

			} else {

				wp_send_json_error(
					array(
						'message' => __( 'Form data is empty.', 'inperium' ),
					),
					400
				);

			}
		} else {

			wp_send_json_error(
				array(
					'message' => __( 'Unknown formId.', 'inperium' ),
				),
				400
			);

		}

		wp_die();

	}

}
