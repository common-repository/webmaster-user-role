<?php
class SCD_Cf7 extends SCD_Base_Module {

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

		if ( $this->should_filter_capabilities() ) {
			add_filter( 'td_webmaster_capabilities', array( $this, 'capabilities' ) );
		}

		if ( $this->should_register_settings_access_hooks() ) {
			$this->register_settings_access_hooks();
		}
	}

	/**
	 * Register role related hooks
	 */
	private function register_settings_access_hooks() {
		add_action( 'plugins_loaded', array( $this, 'remove_meta_caps_filter_for_webmaster' ) );
	}

	function is_active() {
		return ( defined( 'WPCF7_VERSION' ) || function_exists( 'wpcf7' ) );
	}

	function settings_section( $sections ) {
		$section = array(
			'dashicon' => 'dashicons dashicons-list-view',
			'id'       => 'cf7',
			'title'    => __( 'Contact Form 7', 'webmaster-user-role' ),
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_cf7',
					'type'     => 'checkbox',
					'title'    => __( 'Contact Form 7', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'scd_cf7_read_contact_form'   => __( 'Read Forms', 'webmaster-user-role' ),
						'scd_cf7_manage_contact_form' => __( 'Create & Edit Forms', 'webmaster-user-role' ),
						'scd_cf7_delete_contact_form' => __( 'Delete Forms', 'webmaster-user-role' ),
						'scd_cf7_manage_integration'  => __( 'Manage Integration', 'webmaster-user-role' ),
					),

					'default'  => array(
						'scd_cf7_read_contact_form'   => '1',
						'scd_cf7_manage_contact_form' => '0',
						'scd_cf7_delete_contact_form' => '0',
						'scd_cf7_manage_integration'  => '0',
					),
				),
			),
		);

		$sections[] = $this->filter_section( $section );
		return $sections;
	}

	function remove_meta_caps_filter_for_webmaster() {
		remove_filter( 'map_meta_cap', 'wpcf7_map_meta_cap', 10, 4 );
	}

	function capabilities( $capabilities ) {
		$webmaster_user_role_config = $this->plugin->settings->get();
		if ( ! is_array( $webmaster_user_role_config ) || empty( $webmaster_user_role_config['webmaster_cf7'] ) ) {
			return $capabilities;
		}

		/* Add Cf7 Capabilities */
		$cf7_caps = $this->get_cf7_capability_map();
		foreach ( $cf7_caps as $cf7_cap_key => $cf7_cap_value ) {
			$capabilities[ $cf7_cap_key ] = (int) $webmaster_user_role_config['webmaster_cf7'][ $cf7_cap_value ];
		}

		return $capabilities;
	}

	/**
	 * The keys are for cf7 official caps slugs
	 * The values are for SCD internal settings tracking
	 *
	 * @return void
	 */
	private function get_cf7_capability_map() {
		return array(
			'wpcf7_edit_contact_form'    => 'scd_cf7_manage_contact_form',
			'wpcf7_edit_contact_forms'   => 'scd_cf7_manage_contact_form',
			'wpcf7_read_contact_form'    => 'scd_cf7_read_contact_form',
			'wpcf7_read_contact_forms'   => 'scd_cf7_read_contact_form',
			'wpcf7_delete_contact_form'  => 'scd_cf7_delete_contact_form',
			'wpcf7_delete_contact_forms' => 'scd_cf7_delete_contact_form',
			'wpcf7_manage_integration'   => 'scd_cf7_manage_integration',
		);
	}
}
