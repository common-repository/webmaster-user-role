<?php
/*
 * Module for Custom Post Type UI
 */

class SCD_Wordfence extends SCD_Base_Module {

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

		if ( $this->should_register_settings_access_hooks() ) {
			$this->register_settings_access_hooks();
		}
	}

	/**
	 * Register role related hooks
	 */
	private function register_settings_access_hooks() {
		add_action( 'wp_user_dashboard_setup', array( $this, 'manage_wordfence_widget' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'manage_wordfence_widget' ) );
	}

	function is_active() {
		return ( class_exists( 'wordfence' ) );
	}

	function settings_section( $sections ) {
		$section = array(
			'title'    => __( 'Wordfence', 'webmaster-user-role' ),
			'dashicon' => 'dashicons dashicons-admin-generic',
			'id'       => 'wordfence',
			'active'   => true,
			'fields'   => array(
				array(
					'id'          => 'webmaster_caps_wordfence_caps',
					'type'        => 'checkbox',
					'title'       => __( 'Wordfence Capabilities', 'webmaster-user-role' ),
					'subtitle'    => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),
					'description' => 'Only the administrator can manage Wordfence due to how the plugin is built, but you can control whether the Webmaster (Admin) user can see the dashboard widget',

					'options'     => array(
						'view_wf_widget' => __( 'View Wordfence Dashboard Widget', 'webmaster-user-role' ),
					),

					'default'     => array(
						'view_wf_widget' => '0',
					),
				),
			),
		);

		$sections[] = $this->filter_section( $section );
		return $sections;
	}//end settings_section()

	function manage_wordfence_widget() {
		$webmaster_user_role_config = $this->plugin->settings->get();

		if ( empty( $webmaster_user_role_config['webmaster_caps_wordfence_caps']['view_wf_widget'] ) ) {
			remove_meta_box( 'wordfence_activity_report_widget', 'dashboard', 'normal' );
		}
	}
} //end class SCD_Wordfence
