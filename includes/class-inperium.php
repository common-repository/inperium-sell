<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://inperium.com
 * @since      1.0.0
 *
 * @package    Inperium
 * @subpackage Inperium/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Inperium
 * @subpackage Inperium/includes
 * @author     Artyom Gerus <artyom@inperium.com>
 */
class Inperium {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     Inperium_Loader $loader  Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     string $inperium  The string used to uniquely identify this plugin.
	 */
	protected $inperium;

	/**
	 * The current version of the plugin.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     string $version  The current version of the plugin.
	 */
	protected $version;

	/**
	 * Defines the core functionality of the plugin.
	 *
	 * Sets the plugin name and the plugin version that can be used throughout the plugin.
	 * Loads the dependencies, defines the locale, and sets the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {

		if ( defined( 'INPERIUM_VERSION' ) ) {
			$this->version = INPERIUM_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->inperium = 'inperium';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->set_update_db_check();

	}

	/**
	 * Loads the required dependencies for this plugin.
	 *
	 * Includes the following files that make up the plugin:
	 *
	 * - Inperium_Loader. Orchestrates the hooks of the plugin
	 * - Inperium_i18n. Defines internationalization functionality.
	 * - Inperium_Admin. Defines all hooks for the admin area.
	 * - Inperium_Public. Defines all hooks for the public side of the site.
	 * - Inperium_CRUD. Defines CRUD functionality.
	 * - Inperium_Sell_API. Defines Sell API functionality.
	 * - Inperium_Utilities. Defines some utility functions and helpers.
	 *
	 * Creates an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-inperium-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-inperium-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-inperium-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-inperium-public.php';

		/**
		 * The class responsible for all regular CRUD operations.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-inperium-crud.php';

		/**
		 * The class responsible for Sell API request.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-inperium-sell-api.php';

		/**
		 * The class which contains static utility functions and helpers.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-inperium-utilities.php';

		$this->loader = new Inperium_Loader();

	}

	/**
	 * Defines the locale for this plugin for internationalization.
	 *
	 * Uses the Inperium_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since   1.0.0
	 * @access  private
	 */
	private function set_locale() {

		$plugin_i18n = new Inperium_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Registers all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since   1.0.0
	 * @access  private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Inperium_Admin( $this->get_inperium(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'inperium_admin_settings' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'inperium_admin_menu' );

	}

	/**
	 * Registers all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since   1.0.0
	 * @access  private
	 */
	private function define_public_hooks() {

		$plugin_public = new Inperium_Public( $this->get_inperium(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'init', $plugin_public, 'inperium_shortcodes_init' );

		if ( wp_doing_ajax() ) {
			$this->loader->add_action( 'wp_ajax_inperium_form_submit', $plugin_public, 'inperium_form_handler' );
			$this->loader->add_action( 'wp_ajax_nopriv_inperium_form_submit', $plugin_public, 'inperium_form_handler' );
		}

	}

	/**
	 * Registers hook for checking database version and updating schema when needed.
	 *
	 * @since   1.0.0
	 * @access  private
	 */
	private function set_update_db_check() {

		$plugin_crud = new Inperium_CRUD();

		$this->loader->add_action( 'plugins_loaded', $plugin_crud, 'update_db_check' );

	}

	/**
	 * Runs the loader to execute all of the hooks with WordPress.
	 *
	 * @since  1.0.0
	 */
	public function run() {

		$this->loader->run();

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since   1.0.0
	 * @return  string  The name of the plugin.
	 */
	public function get_inperium() {

		return $this->inperium;

	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since   1.0.0
	 * @return  Inperium_Loader  Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {

		return $this->loader;

	}

	/**
	 * Retrieves the version number of the plugin.
	 *
	 * @since   1.0.0
	 * @return  string  The version number of the plugin.
	 */
	public function get_version() {

		return $this->version;

	}

}
