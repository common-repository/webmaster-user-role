<?php
class SCD_SIMPLE_CSS extends SCD_Base_Module {

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
		return ( defined( 'SCCSS_FILE' ) );
	}

	function settings_section( $sections ) {
		$section = array(
			'title'    => __( 'Simple Custom CSS', 'webmaster-user-role' ),
			'dashicon' => 'dashicons dashicons-admin-tools',
			'id'       => 'simple_css',
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_admin_menu_simple_css',
					'type'     => 'checkbox',
					'title'    => __( 'Simple Custom CSS', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'simple_css_menu' => __( 'Add CSS', 'webmaster-user-role' ),
					),

					'default'  => array(
						'simple_css_menu' => '0',
					),
				),
			),
		);

		$sections[] = $this->filter_section( $section );
		return $sections;
	}

	function admin_menu() {
		$webmaster_user_role_config = $this->plugin->settings->get();

		if ( ( is_array( $webmaster_user_role_config ) && isset( $webmaster_user_role_config['webmaster_admin_menu_simple_css']['simple_css_menu'] ) && ! $webmaster_user_role_config['webmaster_admin_menu_simple_css']['simple_css_menu'] ) ) {
			remove_submenu_page( 'themes.php', 'simple-custom-css.php' );
		}
	}
}
