<?php
/**
 * Simple Client Dashboard Wp Admin.
 *
 * @since   1.7.2
 * @package Simple_Client_Dashboard
 */

/**
 * Simple Client Dashboard Wp Admin.
 *
 * @since 1.7.2
 */
class SCD_Wp_Admin {

	protected $script_handle_whitelist = array();
	protected $style_handle_whitelist  = array();

	/**
	 * Parent plugin class.
	 *
	 * @since 1.7.2
	 *
	 * @var Simple_Client_Dashboard
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since 1.7.2
	 *
	 * @param Simple_Client_Dashboard $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since 1.7.2
	 */
	public function hooks() {

		add_action( 'admin_init', array( $this, 'store_enqueued_styles_scripts' ), 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'disable_third_party_styles_scripts' ), 9999999 );
		add_action( 'admin_body_class', array( $this, 'body_class' ) );

		add_action( 'admin_print_scripts', array( $this, 'remove_admin_notices' ) );

		add_action( 'admin_menu', array( $this, 'register_submenu_page' ) );
	}

	public function render_admin_page() {
		if ( $this->should_restrict_access_to_admin_page() ) {
			wp_die( __( 'Please ask your administrator to link your account to an active team member.', 'webmaster-user-role' ), __( 'Permission Denied', 'webmaster-user-role' ) );
		}

		wp_enqueue_style( 'scd-admin-vendor', $this->plugin->url( 'admin-app/dist/static/css/chunk-vendors.css' ), array(), Simple_Client_Dashboard::version );
		wp_enqueue_style( 'scd-admin-style', $this->plugin->url( 'admin-app/dist/static/css/app.css' ), array( 'scd-admin-vendor' ), Simple_Client_Dashboard::version );
		wp_enqueue_style( 'scd-admin-style-custom', $this->plugin->templates->locate_template_url( 'admin-app/custom.css' ), array(), Simple_Client_Dashboard::version );

		wp_enqueue_script( 'scd-admin-vendor', $this->plugin->url( 'admin-app/dist/static/js/chunk-vendors.js' ), array(), Simple_Client_Dashboard::version, true );
		wp_register_script( 'scd-admin-app', $this->plugin->url( 'admin-app/dist/static/js/app.js' ), array( 'scd-admin-vendor' ), Simple_Client_Dashboard::version, true );

		$settings        = $this->plugin->settings->get();
		$sections_schema = $this->plugin->settings->get_sections_schema_with_values();

		wp_localize_script( 'scd-admin-app', 'scd', $this->plugin->bootstrap->get_api_vars() );
		wp_localize_script( 'scd-admin-app', 'scd_sections_schema', $sections_schema );
		wp_localize_script( 'scd-admin-app', 'scd_settings', $settings );

		wp_enqueue_script( 'scd-admin-app' );

		echo '
		<div id="scd-admin-app"></div>
		';
	}

	public function remove_admin_notices() {
		if ( ! $this->is_admin_page() ) {
			return;
		}
		global $wp_filter;
		if ( is_user_admin() ) {
			if ( isset( $wp_filter['user_admin_notices'] ) ) {
				unset( $wp_filter['user_admin_notices'] );
			}
		} elseif ( isset( $wp_filter['admin_notices'] ) ) {
			unset( $wp_filter['admin_notices'] );
		}
		if ( isset( $wp_filter['all_admin_notices'] ) ) {
			unset( $wp_filter['all_admin_notices'] );
		}
	}

	public function is_admin_page() {
		if ( empty( $_GET['page'] ) || strpos( $_GET['page'], 'webmaster-user-role' ) === false ) {
			return false;
		}

		return true;
	}

	public function store_enqueued_styles_scripts() {
		if ( ! $this->is_admin_page() ) {
			return;
		}

		global $wp_scripts;
		$this->script_handle_whitelist = $wp_scripts->queue;
	}

	public function disable_third_party_styles_scripts() {
		if ( ! $this->is_admin_page() ) {
			return;
		}

		if ( $this->should_restrict_access_to_admin_page() ) {
			return;
		}

		$custom_whitelist = array();

		global $wp_scripts;
		foreach ( $wp_scripts->queue as $key => $handle ) {
			if ( strpos( $handle, 'scd-' ) === 0 ) {
				continue;
			}

			if ( in_array( $handle, $this->script_handle_whitelist ) || in_array( $handle, $custom_whitelist ) ) {
				continue;
			}

			wp_dequeue_script( $handle );
		}

		global $wp_styles;
		foreach ( $wp_styles->queue as $key => $handle ) {
			if ( strpos( $handle, 'scd-' ) === 0 ) {
				continue;
			}

			if ( in_array( $handle, $this->style_handle_whitelist ) || in_array( $handle, $custom_whitelist ) ) {
				continue;
			}

			wp_dequeue_style( $handle );
		}
	}

	function register_submenu_page() {
		add_submenu_page(
			'options-general.php',
			'Simple Client Dashboard',
			'Simple Client Dashboard',
			'administrator',
			'simple-client-dashboard',
			array( $this, 'render_admin_page' )
		);
	}

	public function should_restrict_access_to_admin_page() {
		return false;
	}

	// public function get_translations() {
	// include $this->plugin->dir( 'languages/admin-app-translations.php' );
	// return $translations;
	// }

	public function body_class( $classes ) {
		if ( ! $this->is_admin_page() ) {
			return $classes;
		}

		$classes = "$classes scd-admin-app "; // adding a trailing space for conflicts with poorly coded plugins

		return $classes;
	}
}
