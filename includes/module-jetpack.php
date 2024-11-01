<?php
class SCD_Jetpack extends SCD_Base_Module {

	public $plugin;

	function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	function hooks() {
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
		add_action( 'admin_init', array( $this, 'maybe_block_jetpack_for_webmaster' ) );
	}

	function is_active() {
		return ( class_exists( 'Jetpack' ) );
	}

	function settings_section( $sections ) {
		$section = array(
			'title'    => __( 'Jetpack', 'webmaster-user-role' ),
			'dashicon' => 'dashicons dashicons-list-view',
			'id'       => 'jetpack',
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_jetpack_caps',
					'type'     => 'checkbox',
					'title'    => __( 'Jetpack', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'manage_settings' => __( 'Manage Settings', 'webmaster-user-role' ),
					),

					'default'  => array(
						'manage_settings' => '0',
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

		if ( ! ( is_array( $webmaster_user_role_config ) && isset( $webmaster_user_role_config['webmaster_jetpack_caps']['manage_settings'] ) && ! empty( $webmaster_user_role_config['webmaster_jetpack_caps']['manage_settings'] ) ) ) {
			return true;
		}

		return false;
	}

	function admin_menu() {
		if ( $this->should_prevent_access() ) {
			remove_menu_page( 'jetpack' );
		}
	}

	function maybe_block_jetpack_for_webmaster() {
		if ( $this->should_prevent_access() ) {

			global $pagenow;

			if ( 'admin.php' == $pagenow && isset( $_GET['page'] ) && 'jetpack' == $_GET['page'] ) {
				wp_redirect( admin_url() );
				exit;
			}
		}
	}
}
