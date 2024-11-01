<?php
class SCD_SGCachePress extends SCD_Base_Module {

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
		add_action( 'admin_init', array( $this, 'maybe_block_sgcachepress_settings' ) );
	}

	function is_active() {
		return ( class_exists( '\SiteGround_Optimizer\Admin\Admin' ) );
	}

	function settings_section( $sections ) {
		$section = array(
			'title'    => __( 'SiteGround Caching', 'webmaster-user-role' ),
			'dashicon' => 'dashicons dashicons-admin-tools',
			'id'       => 'sgcachepress',
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_sgcachepress_metabox_settings',
					'type'     => 'checkbox',
					'title'    => __( 'SiteGround Caching Capabilities', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'sgcachepress_settings' => __( 'Use SiteGround SuperCacher Settings Menu', 'webmaster-user-role' ),
					),

					'default'  => array(
						'sgcachepress_settings' => '0',
					),
				),
			),
		);

		$sections[] = $this->filter_section( $section );
		return $sections;
	}

	public function admin_menu() {
		$webmaster_user_role_config = $this->plugin->settings->get();
		if ( isset( $webmaster_user_role_config['webmaster_sgcachepress_metabox_settings'] ) && empty( $webmaster_user_role_config['webmaster_sgcachepress_metabox_settings']['sgcachepress_settings'] ) ) {
			remove_menu_page( 'sg-cachepress' );
		}
	}

	public function maybe_block_sgcachepress_settings() {
		$webmaster_user_role_config = $this->plugin->settings->get();

		if ( isset( $webmaster_user_role_config['webmaster_sgcachepress_metabox_settings'] ) && empty( $webmaster_user_role_config['webmaster_sgcachepress_metabox_settings']['sgcachepress_settings'] ) ) {
			global $pagenow;

			if ( 'admin.php' == $pagenow && isset( $_GET['page'] ) && 'sg-cachepress' == $_GET['page'] ) {
				wp_redirect( admin_url() );
				exit;
			}
		}
	}
}
