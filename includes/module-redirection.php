<?php
class SCD_Redirection extends SCD_Base_Module {

	public $plugin;
	public $caps;

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
		add_filter( 'redirection_role', array( $this, 'redirection_role' ) );
	}

	function is_active() {
		return class_exists( 'Redirection_Admin' );
	}

	function settings_section( $sections ) {
		$section = array(
			'title'    => __( 'Redirection', 'webmaster-user-role' ),
			'dashicon' => 'dashicons dashicons-list-view',
			'id'       => 'redirection',
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'redirection_settings',
					'type'     => 'checkbox',
					'title'    => __( 'Manage Redirection', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster users can', 'webmaster-user-role' ),

					'options'  => array(
						'manage_redirections' => 'Mange Redirection',
					),

					'default'  => array(
						'manage_redirections' => '0',
					),
				),
			),
		);

		$sections[] = $this->filter_section( $section );
		return $sections;
	}

	function redirection_role( $redirection_role ) {
		$webmaster_user_role_config = $this->plugin->settings->get();

		if ( ! is_array( $webmaster_user_role_config ) ) {
			return $redirection_role;
		}

		if ( ! empty( $webmaster_user_role_config['redirection_settings']['manage_redirections'] ) ) {
			return 'webmaster';
		}

		return $redirection_role;
	}
}
