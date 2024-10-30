<?php
/**
 * Provides an admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://inperium.com
 * @since      1.0.0
 *
 * @package    Inperium
 * @subpackage Inperium/admin/partials
 */

if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore
	add_settings_error( 'inperium_messages', 'inperium_message', __( 'Settings Saved', 'inperium' ), 'updated' );
}

settings_errors( 'inperium_messages' );

?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form action="options.php" method="post">
		<?php
			settings_fields( 'inperium-admin' );
			do_settings_sections( 'inperium-admin' );
			submit_button( __( 'Save API Settings', 'inperium' ) );
		?>
	</form>

	<?php require_once dirname( __FILE__ ) . '/inperium-admin-form-editor.php'; ?>

</div>
