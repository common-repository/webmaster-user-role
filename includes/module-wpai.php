<?php
class SCD_WPAI extends SCD_Base_Module {

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
		return ( class_exists( 'PMXI_Plugin' ) );
	}

	function settings_section( $sections ) {
		$section = array(
			'title'    => __( 'WP All Import', 'webmaster-user-role' ),
			'dashicon' => 'dashicons dashicons-admin-tools',
			'id'       => 'wpai',
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_wpai_metabox_settings',
					'type'     => 'checkbox',
					'title'    => __( 'WP All Import Capabilities', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'wpai_settings' => __( 'Use WP All Import Settings Menu', 'webmaster-user-role' ),
					),

					'default'  => array(
						'wpai_settings' => '0',
					),
				),
			),
		);

		$sections[] = $this->filter_section( $section );
		return $sections;
	}

	function admin_menu() {
		$webmaster_user_role_config = $this->plugin->settings->get();

		if ( is_array( $webmaster_user_role_config ) && empty( $webmaster_user_role_config['webmaster_wpai_metabox_settings']['wpai_settings'] ) ) {
			remove_menu_page( 'pmxi-admin-home' );
		}
	}
}
