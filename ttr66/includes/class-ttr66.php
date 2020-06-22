<?php
class TTR66 {
	protected $loader;
	protected $plugin_name;
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		if ( defined( 'TTR66_VERSION' ) ) {
			$this->version = TTR66_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'TTR66';

		$this->load_dependencies();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 * Create an instance of the loader which will be used to register the hooks with WordPress.
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ttr66-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ttr66-admin.php';
		$this->loader = new TTR66_Loader();
	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 */
	private function define_admin_hooks() {
		$plugin_admin = new TTR66_Admin( $this->get_plugin_name(), $this->get_version() );

		// Add the TTR66 dash widget and XML upload page/menu
		$this->loader->add_action( 'wp_dashboard_setup', $plugin_admin, 'add_dashboard_widget');
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_custom_menu_page' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of WordPress.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
