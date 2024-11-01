<?php
class SCD_BUSINESS_PROFILE extends SCD_Base_Module {

	public $plugin;

	function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Register hooks
	 */
	private function hooks() {
		if ( empty( $this->is_active() ) ) {
			return;
		}

		/**
		 * Add module section to schema
		 */
		add_filter( 'simple_client_dashboard/config/sections', array( $this, 'settings_section' ) );

		if ( $this->should_register_settings_access_hooks() ) {
			$this->register_settings_access_hooks();
		}
	}

	/**
	 * Register role related hooks
	 */
	private function register_settings_access_hooks() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 999 );
		add_action( 'admin_init', array( $this, 'maybe_block_for_webmaster' ) );
	}

	function is_active() {
		return ( class_exists( 'bpfwpInit' ) );
	}

	function settings_section( $sections ) {
		$section = array(
			'dashicon' => 'dashicons dashicons-admin-tools',
			'id'       => 'business_profile',
			'title'    => __( 'Business Profile', 'webmaster-user-role' ),
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_admin_menu_business_profile',
					'type'     => 'checkbox',
					'title'    => __( 'Business Profile', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'business_profile_menu' => __( 'Manage Business Profile', 'webmaster-user-role' ),
					),

					'default'  => array(
						'business_profile_menu' => '0',
					),
				),
			),
		);

		$sections[] = $this->filter_section( $section );
		return $sections;
	}

	function should_prevent_access() {
		if ( ! Simple_Client_Dashboard::current_user_is_webmaster() ) {
			return false;
		}

		$webmaster_user_role_config = $this->plugin->settings->get();

		if ( ! ( is_array( $webmaster_user_role_config ) && isset( $webmaster_user_role_config['webmaster_admin_menu_business_profile']['business_profile_menu'] ) && ! empty( $webmaster_user_role_config['webmaster_admin_menu_business_profile']['business_profile_menu'] ) ) ) {
			return true;
		}

		return false;
	}

	function admin_menu() {
		if ( $this->should_prevent_access() ) {
			remove_menu_page( 'bpfwp-business-profile' );
		}
	}

	function maybe_block_for_webmaster() {
		if ( empty( $this->should_prevent_access() ) ) {
			return;
		}

		global $pagenow;

		if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) ) {
			if ( strpos( $_GET['page'], 'bpfwp' ) !== false ) {
				wp_redirect( admin_url() );
				exit;
			}
		}
	}
}
