<?php
class SCD_Tools extends SCD_Base_Module {

	public $plugin;
	private $section;

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
		// add_filter( 'td_webmaster_capabilities', array( $this, 'capabilities' ) );

		if ( $this->should_register_settings_access_hooks() ) {
			$this->register_settings_access_hooks();
		}
	}

	/**
	 * Register role related hooks
	 */
	private function register_settings_access_hooks() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 1000 );
	}

	function is_active() {
		return true; // WP Core functionality, plugins is always present
	}

	function settings_section( $sections ) {
		if ( ! $this->is_active() ) {
			return $sections;
		}

		$this->section = array(
			'dashicon' => 'dashicons dashicons-admin-tools',
			'id'       => 'tools',
			'title'    => __( 'Tools & Settings', 'webmaster-user-role' ),
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_admin_menu_tools_settings',
					'type'     => 'checkbox',
					'title'    => __( 'Visible in Menu', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can view', 'webmaster-user-role' ),

					'options'  => array(
						'tools.php'           => __( 'Tools Menu', 'webmaster-user-role' ),
						'options-general.php' => __( 'Settings Menu', 'webmaster-user-role' ),
					),

					'default'  => array(
						'tools.php'           => '0',
						'options-general.php' => '0',
					),
				),

			),
		);

		if ( is_multisite() ) {

		}

		$sections[] = $this->section;

		return $sections;
	}



	function capabilities( $capabilities ) {
		$webmaster_user_role_config = $this->plugin->settings->get();

		return $capabilities;
	}

	function admin_menu() {
		$webmaster_user_role_config = $this->plugin->settings->get();
		if ( is_array( $webmaster_user_role_config ) && empty( $webmaster_user_role_config['webmaster_admin_menu_tools_settings']['tools.php'] ) ) {
			remove_menu_page( 'tools.php' );
		}
		if ( is_array( $webmaster_user_role_config ) && empty( $webmaster_user_role_config['webmaster_admin_menu_tools_settings']['options-general.php'] ) ) {
			remove_menu_page( 'options-general.php' );
		}
	}
}
