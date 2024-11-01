<?php
class SCD_Itsec extends SCD_Base_Module {

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
	}

	function is_active() {
		return ( class_exists( 'ITSEC_Core' ) );
	}

	function settings_section( $sections ) {
		$section = array(
			'title'    => __( 'iThemes Security', 'webmaster-user-role' ),
			'dashicon' => 'dashicons dashicons-lock',
			'id'       => 'itsec',
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_cap_itsec',
					'type'     => 'checkbox',
					'title'    => __( 'iThemes Security', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'itsec_manage' => __( 'Manage security settings', 'webmaster-user-role' ),
					),

					'default'  => array(
						'itsec_manage' => '0',
					),
				),
			),
		);

		$sections[] = $this->filter_section( $section );
		return $sections;
	}
}
