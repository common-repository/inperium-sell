<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://inperium.com
 * @since      1.0.0
 *
 * @package    Inperium
 * @subpackage Inperium/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, hooks for enqueueing
 * the admin-specific stylesheet and JavaScript.
 *
 * @package    Inperium
 * @subpackage Inperium/admin
 * @author     Artyom Gerus <artyom@inperium.com>
 */
class Inperium_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string    $inperium    The ID of this plugin.
	 */
	private $inperium;

	/**
	 * The version of this plugin.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initializes the class and sets its properties.
	 *
	 * @since  1.0.0
	 * @param  string $inperium    The name of this plugin.
	 * @param  string $version     The version of this plugin.
	 */
	public function __construct( $inperium, $version ) {

		$this->inperium = $inperium;
		$this->version  = $version;

	}

	/**
	 * Registers the stylesheets for the admin area.
	 *
	 * @since  1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( 'jquery-ui-styles', plugin_dir_url( __FILE__ ) . 'css/vendor/jquery-ui.css', array(), $this->version, 'all' );

		wp_enqueue_style( $this->inperium, plugin_dir_url( __FILE__ ) . 'css/inperium-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Registers the JavaScript for the admin area.
	 *
	 * @since  1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-touch-punch' );

		wp_enqueue_script( $this->inperium, plugin_dir_url( __FILE__ ) . 'js/inperium-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Adds the admin menu.
	 *
	 * @since  1.0.0
	 */
	public function inperium_admin_menu() {

		add_menu_page(
			__( 'Inperium Sell', 'inperium' ),
			__( 'Inperium', 'inperium' ),
			'manage_options',
			'inperium-admin',
			array( $this, 'inperium_admin_display' ),
			plugin_dir_url( __FILE__ ) . 'images/logo.svg',
			58
		);

	}

	/**
	 * Defines Inperium Sell plugin admin page.
	 *
	 * @since  1.0.0
	 */
	public function inperium_admin_display() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html_e( 'You do not have sufficient permissions to access this page.', 'inperium' ) );
		}

		require_once dirname( __FILE__ ) . '/partials/inperium-admin-display.php';

	}

	/**
	 * Adds the admin settings.
	 *
	 * @since  1.0.0
	 */
	public function inperium_admin_settings() {

		register_setting( 'inperium-admin', 'inperium_api_key' );

		add_settings_section(
			'inperium_settings_section',
			__( 'Settings', 'inperium' ),
			array( $this, 'inperium_settings_section_callback' ),
			'inperium-admin'
		);

		add_settings_field(
			'inperium_settings_api_key',
			__( 'API Key', 'inperium' ),
			array( $this, 'inperium_settings_api_key_callback' ),
			'inperium-admin',
			'inperium_settings_section',
			array(
				'label_for' => 'inperium_settings_api_key',
			)
		);

	}

	/**
	 * Defines settings section description.
	 *
	 * @since  1.0.0
	 */
	public function inperium_settings_section_callback() {

		?>
			<p>
				<?php esc_html_e( 'To start building forms, provide an API key for Inperium Sell. Create forms out of company and contact properties, and then add them to your pages and posts with a shortcode.', 'inperium' ); ?>
			</p>
		<?php

	}

	/**
	 * Defines API key settings field.
	 *
	 * @since  1.0.0
	 * @param  array $args  Extra arguments used when outputting the field.
	 */
	public function inperium_settings_api_key_callback( $args ) {

		$api_key = get_option( 'inperium_api_key' );
		?>
			<input
				type="text"
				id="<?php echo esc_attr( $args['label_for'] ); ?>"
				name="inperium_api_key"
				class="regular-text code"
				value="<?php echo isset( $api_key ) ? esc_attr( $api_key ) : ''; ?>"
			>
		<?php

	}

}
