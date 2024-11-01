<?php
class SCD_TABLE_PRESS extends SCD_Base_Module {

	public $parent;

	function __construct( $parent ) {
		$this->parent = $parent;
		$this->hooks();
	}

	public function hooks() {
		if ( empty( $this->is_active() ) ) {
			return;
		}
		add_filter( 'simple_client_dashboard/config/sections', array( $this, 'settings_section' ) );
	}

	public function is_active() {
		return ( class_exists( 'TablePress' ) );
	}

	function settings_section( $sections ) {
		$section = array(
			'dashicon' => 'dashicons dashicons-list-view',
			'id'       => 'tablepress',
			'title'    => __( 'TablePress', 'webmaster-user-role' ),
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_caps_tablepress',
					'type'     => 'checkbox',
					'title'    => __( 'TablePress Capabilities', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'tablepress_list_tables'           => __( 'List Tables', 'webmaster-user-role' ),
						'tablepress_add_tables'            => __( 'Add New Tables', 'webmaster-user-role' ),
						'tablepress_edit_tables'           => __( 'Edit Existing Tables', 'webmaster-user-role' ),
						'tablepress_import_tables'         => __( 'Import Tables', 'webmaster-user-role' ),
						'tablepress_export_tables'         => __( 'Export Tables', 'webmaster-user-role' ),
						'tablepress_access_about_screen'   => __( 'Access TablePress About Screen', 'webmaster-user-role' ),
						'tablepress_access_options_screen' => __( 'Access TablePress Options Screen', 'webmaster-user-role' ),
					),

					'default'  => array(
						'tablepress_list_tables'           => '1',
						'tablepress_add_tables'            => '1',
						'tablepress_edit_tables'           => '1',
						'tablepress_import_tables'         => '1',
						'tablepress_export_tables'         => '1',
						'tablepress_access_about_screen'   => '1',
						'tablepress_access_options_screen' => '0',
					),
				),

			),
		);

		$sections[] = $this->filter_section( $section );
		return $sections;
	}
}
