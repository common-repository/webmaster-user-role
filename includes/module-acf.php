<?php
class SCD_ACF extends SCD_Base_Module {

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
	}

	function is_active() {
		return ( function_exists( 'get_field' ) );
	}

	function settings_section( $sections ) {
		$section = array(
			'dashicon' => 'dashicons dashicons-admin-tools',
			'id'       => 'acf',
			'title'    => __( 'Advanced Custom Fields', 'webmaster-user-role' ),
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_admin_menu_acf',
					'type'     => 'checkbox',
					'title'    => __( 'Advanced Custom Fields', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'acf_menu' => __( 'Manage Custom Fields', 'webmaster-user-role' ),
					),

					'default'  => array(
						'acf_menu' => '0',
					),
				),
			),
		);

		$sections[] = $this->filter_section( $section );
		return $sections;
	}

	function admin_menu() {
		$webmaster_user_role_config = $this->plugin->settings->get();
		if ( ! ( is_array( $webmaster_user_role_config ) && isset( $webmaster_user_role_config['webmaster_admin_menu_acf']['acf_menu'] ) && ! empty( $webmaster_user_role_config['webmaster_admin_menu_acf']['acf_menu'] ) ) ) {
			remove_menu_page( 'edit.php?post_type=acf' );
			remove_menu_page( 'edit.php?post_type=acf-field-group' );
		}
	}
}
