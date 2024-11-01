<?php
class SCD_Ninja_Forms extends SCD_Base_Module {

	public $plugin;

	function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	function hooks() {
		if ( ! $this->is_active() ) {
			return;
		}

		add_filter( 'simple_client_dashboard/config/sections', array( $this, 'settings_section' ) );

		if ( $this->should_register_settings_access_hooks() ) {
			$this->register_settings_access_hooks();
		}
	}

	function register_settings_access_hooks() {
		add_filter( 'ninja_forms_admin_import_export_capabilities', array( $this, 'nf_import_export_filter' ) );
		add_filter( 'ninja_forms_admin_settings_capabilities', array( $this, 'nf_settings_filter' ) );
		add_filter( 'ninja_forms_admin_extend_capabilities', array( $this, 'nf_extensions_filter' ) );
		add_filter( 'ninja_forms_admin_status_capabilities', array( $this, 'nf_status_filter' ) );
		add_filter( 'ninja_forms_admin_submissions_capabilities', array( $this, 'nf_submissions_filter' ) );
		add_filter( 'ninja_forms_admin_all_forms_capabilities', array( $this, 'nf_edit_forms_filter' ) );
		add_filter( 'ninja_forms_admin_add_new_capabilities', array( $this, 'nf_edit_forms_filter' ) );
		add_action( 'admin_menu', array( $this, 'manage_forms_submenus' ) );
	}

	function is_active() {
		return class_exists( 'Ninja_Forms' );
	}

	function settings_section( $sections ) {
		$section = array(
			'title'    => __( 'Ninja Forms', 'webmaster-user-role' ),
			'dashicon' => 'dashicons dashicons-feedback',
			'id'       => 'ninjaforms',
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_caps_ninjaforms',
					'type'     => 'checkbox',
					'title'    => __( 'Ninja Forms Capabilities', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'nf_edit_forms'        => __( 'View & Edit Forms', 'webmaster-user-role' ),
						'nf_view_submissions'  => __( 'View Form Submissions', 'webmaster-user-role' ),
						'nf_import_export'     => __( 'Import and Export Forms', 'webmaster-user-role' ),
						'nf_manage_settings'   => __( 'Manage Ninja Forms Settings', 'webmaster-user-role' ),
						'nf_manage_extensions' => __( 'Manage Extensions', 'webmaster-user-role' ),
						'nf_view_status'       => __( 'View System Status', 'webmaster-user-role' ),
					),

					'default'  => array(
						'nf_edit_forms'        => '1',
						'nf_view_submissions'  => '1',
						'nf_import_export'     => '0',
						'nf_manage_settings'   => '0',
						'nf_manage_extensions' => '0',
						'nf_view_status'       => '0',
					),
				),
			),
		);

		$sections[] = $this->filter_section( $section );
		return $sections;
	}

	/* Remove Import/Export Capabilities */
	function nf_import_export_filter( $nfCaps ) {
		return $this->filter_capability( 'nf_import_export', $nfCaps );
	}

	/* Remove Ninja Forms Settings Management Capability */
	function nf_settings_filter( $nfCaps ) {
		return $this->filter_capability( 'nf_manage_settings', $nfCaps );
	}

	/* Remove Capability to add Extensions */
	function nf_extensions_filter( $nfCaps ) {
		return $this->filter_capability( 'nf_manage_extensions', $nfCaps );
	}

	/* Remove Capability to view system status */
	function nf_status_filter( $nfCaps ) {
		return $this->filter_capability( 'nf_view_status', $nfCaps );
	}

	/* Remove Capability to view submissions */
	function nf_submissions_filter( $nfCaps ) {
		return $this->filter_capability( 'nf_view_submissions', $nfCaps );
	}

	/* Remove capabilties associated with editing forms */
	function nf_edit_forms_filter( $nfCaps ) {
		return $this->filter_capability( 'nf_edit_forms', $nfCaps );
	}

	private function filter_capability( $capability_key, $nfCaps ) {
		$webmaster_user_role_config = $this->plugin->settings->get();

		if ( ! is_array( $webmaster_user_role_config ) ) {
			return $nfCaps;
		}

		if ( empty( $webmaster_user_role_config['webmaster_caps_ninjaforms'][ $capability_key ] ) ) {
			$nfCaps = 'update_core';
		}

		return $nfCaps;
	}

	function manage_forms_submenus() {
		$webmaster_user_role_config = $this->plugin->settings->get();

		if ( ! is_array( $webmaster_user_role_config ) || empty( $webmaster_user_role_config['webmaster_caps_ninjaforms'] ) ) {
			return;
		}

		if ( empty( $webmaster_user_role_config['webmaster_caps_ninjaforms']['nf_edit_forms'] ) ) {
			remove_submenu_page( 'ninja-forms', 'ninja-forms' );
		}

		/* if all capabilities are turned off, remove ninja forms from admin menu */
		if ( $this->are_all_caps_turned_off( $webmaster_user_role_config['webmaster_caps_ninjaforms'] ) ) {
			remove_menu_page( 'ninja-forms' );
		}
	}
}//end class
