<?php
/**
 * A form editor section for admin menu
 *
 * @link       https://inperium.com
 * @since      1.0.0
 *
 * @package    Inperium
 * @subpackage Inperium/admin/partials
 */

$api_key = get_option( 'inperium_api_key' );

if ( isset( $api_key ) && '' !== $api_key && false !== $api_key ) {

	$api  = new Inperium_Sell_API();
	$crud = new Inperium_CRUD();

	$form_id_get      = isset( $_GET['form'] ) ? sanitize_text_field( wp_unslash( $_GET['form'] ) ) : null;
	$plugin_page_slug = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

	$form_id               = $form_id_get;
	$form_name             = '';
	$form_fields           = array();
	$form_comment          = '';
	$form_redirect_page_id = '';

	if ( isset( $_POST['saveForm'], $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'inperium_form_settings' ) ) {
		$form_id_post = null;
		if ( ! empty( $_POST['formId'] ) ) {
			$form_id_post = sanitize_text_field( wp_unslash( $_POST['formId'] ) );
		}
		if ( ! empty( $_POST['formName'] ) ) {
			$form_name_post = sanitize_text_field( wp_unslash( $_POST['formName'] ) );
		}
		if ( ! empty( $_POST['fields'] ) ) {
			$form_fields_post = Inperium_Utilities::recursive_sanitize_text_field( wp_unslash( $_POST['fields'] ) ); // phpcs:ignore
		}
		if ( isset( $_POST['formComment'] ) ) {
			$form_comment_post = sanitize_text_field( wp_unslash( $_POST['formComment'] ) );
		}
		if ( isset( $_POST['redirectPage'] ) ) {
			$form_redirect_page_id = sanitize_text_field( wp_unslash( $_POST['redirectPage'] ) );
		}

		$save_form_result = $crud->put_form_settings( $form_id_post, $form_name_post, $form_fields_post, $form_comment_post, $form_redirect_page_id );

		if ( false !== $save_form_result ) {
			$form_id = strval( $save_form_result );
			?>
				<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Form settings saved.', 'inperium' ); ?></strong></p></div>
			<?php
		}
	}

	if ( isset( $_POST['deleteForm'], $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'inperium_form_settings' ) ) {
		if ( ! empty( $_POST['formId'] ) ) {
			$form_id_post = sanitize_text_field( wp_unslash( $_POST['formId'] ) );

			$delete_form_result = $crud->delete_form_settings( $form_id_post );

			if ( false !== $delete_form_result ) {
				?>
					<div class="notice notice-error is-dismissible"><p><strong><?php esc_html_e( 'Form deleted.', 'inperium' ); ?></strong></p></div>
				<?php
			}
		}
	}

	$all_forms = $crud->get_all_forms();

	$form = $crud->get_form_settings( $form_id );

	if ( null !== $form ) {
		$form_name             = $form->name;
		$form_fields           = $form->fields;
		$form_comment          = $form->comment;
		$form_redirect_page_id = $form->redirect_page_id;
	}

	$available_properties = $api->get_properties( array( 'CONTACTS', 'COMPANIES' ) );

	if ( false !== $available_properties ) {

		$available_properties = array_filter(
			$available_properties,
			function( $property ) {
				return true === $property->usage->detailPage;
			}
		);

		if ( ! empty( $form_fields ) ) {
			uasort(
				$available_properties,
				function( $prop_a, $prop_b ) use ( $form_fields ) {
					$field_name_a = $prop_a->name . '_' . strtolower( $prop_a->objectType ); // phpcs:ignore
					$field_name_b = $prop_b->name . '_' . strtolower( $prop_b->objectType ); // phpcs:ignore
					return isset( $form_fields[ $field_name_a ] ) && isset( $form_fields[ $field_name_b ] ) ?
									$form_fields[ $field_name_a ]['order'] - $form_fields[ $field_name_b ]['order'] :
									0;
				}
			);
		}

		?>
		<nav class="nav-tab-wrapper">
			<a href="?page=<?php echo esc_attr( $plugin_page_slug ); ?>" class="nav-tab <?php echo esc_attr( null === $form_id ? 'nav-tab-active' : '' ); ?>"><?php esc_html_e( 'New Form', 'inperium' ); ?></a>
			<?php
			foreach ( $all_forms as $form_settings ) {
				?>
					<a href="?page=<?php echo esc_attr( $plugin_page_slug ); ?>&form=<?php echo esc_attr( $form_settings->id ); ?>" class="nav-tab <?php echo esc_attr( $form_settings->id === $form_id ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html( $form_settings->name ); ?></a>
				<?php
			}
			?>
		</nav>

		<div class="tab-content">		
			<?php render_form_editor( $form_id, $form_name, $available_properties, $form_fields, $form_comment, $form_redirect_page_id, $plugin_page_slug ); ?>
		</div>

		<?php
	} else {
		?>
			<p class="inperium-error">
				<?php esc_html_e( 'Error: no connection to Inperium Sell API or API key is invalid!', 'inperium' ); ?>
			</p>
		<?php
	}
}

/**
 * Renders a form editor with parameters.
 *
 * @since  1.0.0
 * @param  string $form_id  Form ID.
 * @param  string $form_name  Form name.
 * @param  array  $available_properties  Array of available properties from Inperium Sell.
 * @param  array  $form_fields  Existing form fields settings.
 * @param  string $form_comment  An internal comment that will be added as a note to created entities.
 * @param  string $form_redirect_page_id  Page ID to redirect user to after successful submit.
 * @param  string $plugin_page_slug  A slug of the plugin page which used as a target for `Delete` button.
 */
function render_form_editor( $form_id, $form_name, $available_properties, $form_fields, $form_comment, $form_redirect_page_id, $plugin_page_slug ) {
	?>
		<form method="post" action="">
			<?php wp_nonce_field( 'inperium_form_settings' ); ?>
			<?php if ( isset( $form_id ) ) { ?>
				<input
					type="hidden"
					name="formId"
					value="<?php echo esc_attr( isset( $form_id ) ? $form_id : '' ); ?>"
				>
			<?php } ?>
			<table class="form-table" role="presentation">
				<tbody>
					<?php if ( isset( $form_id ) ) { ?>
						<tr>
							<th scope='row'>
								<label><?php esc_html_e( 'Form shortcode', 'inperium' ); ?></label>
							</th>
							<td>
								<b>[inperium-form id='<?php echo esc_html( $form_id ); ?>']</b>
							</td>
						</tr>
					<?php } ?>
					<tr>
						<th scope='row'>
							<label for="inperium_form_name"><?php esc_html_e( 'Form name', 'inperium' ); ?></label>
						</th>
						<td>
							<input
								type="text"
								id="inperium_form_name"
								name="formName"
								class="regular-text"
								required
								value="<?php echo esc_attr( $form_name ); ?>"
							>
						</td>
					</tr>
					<tr>
						<th scope='row'><?php esc_html_e( 'Fields to display', 'inperium' ); ?></th>
						<td>
							<fieldset>
								<span><?php esc_html_e( 'Show properties for:', 'inperium' ); ?></span>
								<input type="checkbox" id="inperium_show_contacts" checked>
								<label for="inperium_show_contacts"><?php esc_html_e( 'Contacts', 'inperium' ); ?></label>
								<input type="checkbox" id="inperium_show_companies" checked>
								<label for="inperium_show_companies"><?php esc_html_e( 'Companies', 'inperium' ); ?></label>
								<div class="inperium-fields-list">
									<ul id="inperium-properties-sortable">
										<?php
										$index = 0;
										foreach ( $available_properties as $property ) {
											$field_name     = $property->name . '_' . strtolower( $property->objectType ); // phpcs:ignore
											$checked_value  = isset( $form_fields[ $field_name ]['checked'] ) && 'true' === $form_fields[ $field_name ]['checked'] ? 'checked' : '';
											$label_value    = isset( $form_fields[ $field_name ]['label'] ) ? $form_fields[ $field_name ]['label'] : $property->label;
											$required_value = isset( $form_fields[ $field_name ]['required'] ) && 'true' === $form_fields[ $field_name ]['required'] ? 'checked' : '';
											$order_value    = isset( $form_fields[ $field_name ]['order'] ) ? $form_fields[ $field_name ]['order'] : $index;
											?>
												<li class="inperium-fields-row ui-state-default inperium-entity-<?php echo esc_attr( strtolower( $property->objectType ) ); // phpcs:ignore ?>">
													<div class="inperium-fields-row-half">
														<label class="inperium-fields-row-label" for='checked_<?php echo esc_attr( $property->id ); ?>'>
															<input
																type='checkbox'
																name='fields[<?php echo esc_attr( $field_name ); ?>][checked]'
																value='true'
																id='checked_<?php echo esc_attr( $property->id ); ?>'
																<?php echo esc_attr( $checked_value ); ?>
															>
															<b><?php echo esc_html( '[' . ucfirst( strtolower( $property->objectType ) ) . '] ' . $property->label ); // phpcs:ignore ?></b>
														</label>
													</div>
													<div class="inperium-fields-row-half">
														<label class="inperium-fields-row-label" for='label_<?php echo esc_attr( $property->id ); ?>'>
															<?php esc_html_e( 'Label:', 'inperium' ); ?>
															<input
																type="text"
																name='fields[<?php echo esc_attr( $field_name ); ?>][label]'
																id='label_<?php echo esc_attr( $property->id ); ?>'
																class="regular-text"
																value="<?php echo esc_attr( $label_value ); ?>"
															>
														</label>
														<label class="inperium-fields-row-label" for='required_<?php echo esc_attr( $property->id ); ?>'>
															<?php esc_html_e( 'Required?', 'inperium' ); ?>
															<input
																type='checkbox'
																name='fields[<?php echo esc_attr( $field_name ); ?>][required]'
																value='true'
																id='required_<?php echo esc_attr( $property->id ); ?>'
																<?php echo esc_attr( $required_value ); ?>
															>
														</label>
														<input
															type="hidden"
															name='fields[<?php echo esc_attr( $field_name ); ?>][order]'
															class="inperium-property-order"
															value="<?php echo esc_attr( $order_value ); ?>"
														>
														<input
															type="hidden"
															name='fields[<?php echo esc_attr( $field_name ); ?>][data_type]'
															value="<?php echo esc_attr( $property->dataType ); // phpcs:ignore ?>"
														>
														<input
															type="hidden"
															name='fields[<?php echo esc_attr( $field_name ); ?>][property_name]'
															value="<?php echo esc_attr( $property->name ); ?>"
														>
														<input
															type="hidden"
															name='fields[<?php echo esc_attr( $field_name ); ?>][entity_type]'
															value="<?php echo esc_attr( $property->objectType ); // phpcs:ignore ?>"
														>
													</div>
												</li>
											<?php
											++$index;
										}
										?>
									</ul>
								</div>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope='row'>
							<label for="inperium_form_comment"><?php esc_html_e( 'Form comment', 'inperium' ); ?></label>
						</th>
						<td>
							<textarea name="formComment" id="inperium_form_comment" rows="5" class="regular-text"><?php echo esc_html( $form_comment ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="redirectPage"><?php esc_html_e( 'Redirect page', 'inperium' ); ?></label>
						</th>
						<td>
							<?php
								wp_dropdown_pages(
									array(
										'name'             => 'redirectPage',
										'show_option_none' => esc_html__( 'No redirect', 'inperium' ),
										'selected'         => esc_attr( empty( $form_redirect_page_id ) ? '' : $form_redirect_page_id ),
									)
								);
							?>
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit">
				<input name="saveForm" type="submit" class="button-primary"	value="<?php esc_attr_e( 'Save Form Settings', 'inperium' ); ?>"
				>
				<?php if ( isset( $form_id ) ) { ?>
					<input name="deleteForm" type="submit" class="button-secondary" formnovalidate formaction="?page=<?php echo esc_attr( $plugin_page_slug ); ?>"
						value="<?php esc_attr_e( 'Delete Form', 'inperium' ); ?>"
					>
				<?php } ?>
			</p>    
		</form>
	<?php
}
