<?php
class SCD_Yoast extends SCD_Base_Module {

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
		add_action( 'plugins_loaded', array( $this, 'wpseo_admin_init' ), 16 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 999 );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 100 );
	}

	function is_active() {
		return ( function_exists( 'wpseo_init' ) );
	}

	function settings_section( $sections ) {
		$section = array(
			'title'    => __( 'Yoast SEO', 'webmaster-user-role' ),
			'dashicon' => 'dashicons dashicons-admin-tools',
			'id'       => 'yoast_metabox',
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_yoast_metabox_settings',
					'type'     => 'checkbox',
					'title'    => __( 'Yoast SEO Capabilities', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'yoast_post_metabox' => __( 'Edit SEO values on individual posts/pages', 'webmaster-user-role' ),
						'yoast_settings'     => __( 'Use Yoast Settings Menu', 'webmaster-user-role' ),
					),

					'default'  => array(
						'yoast_post_metabox' => '1',
						'yoast_settings'     => '0',
					),
				),
			),
		);

		$sections[] = $this->filter_section( $section );
		return $sections;
	}

	function wpseo_admin_init() {
		if ( empty( $GLOBALS['wpseo_metabox'] ) ) {
			return;
		}

		$webmaster_user_role_config = $this->plugin->settings->get();
		if ( is_array( $webmaster_user_role_config ) && isset( $webmaster_user_role_config['webmaster_yoast_metabox_settings']['yoast_post_metabox'] ) && empty( $webmaster_user_role_config['webmaster_yoast_metabox_settings']['yoast_post_metabox'] ) ) {
			remove_action( 'add_meta_boxes', array( $GLOBALS['wpseo_metabox'], 'add_meta_box' ) );
			remove_action( 'admin_enqueue_scripts', array( $GLOBALS['wpseo_metabox'], 'enqueue' ) );
			remove_action( 'wp_insert_post', array( $GLOBALS['wpseo_metabox'], 'save_postdata' ) );
			remove_action( 'edit_attachment', array( $GLOBALS['wpseo_metabox'], 'save_postdata' ) );
			remove_action( 'add_attachment', array( $GLOBALS['wpseo_metabox'], 'save_postdata' ) );
			remove_action( 'admin_init', array( $GLOBALS['wpseo_metabox'], 'setup_page_analysis' ) );
			remove_action( 'admin_init', array( $GLOBALS['wpseo_metabox'], 'translate_meta_boxes' ) );
		}
	}

	function admin_menu() {
		$webmaster_user_role_config = $this->plugin->settings->get();

		if ( is_array( $webmaster_user_role_config ) && empty( $webmaster_user_role_config['webmaster_yoast_metabox_settings']['yoast_settings'] ) ) {
			remove_menu_page( 'wpseo_dashboard' );
		}
	}

	function admin_bar_menu( $wp_admin_bar ) {
		$webmaster_user_role_config = $this->plugin->settings->get();

		if ( ! is_array( $webmaster_user_role_config ) ) {
			return;
		}

		if ( empty( $webmaster_user_role_config['webmaster_yoast_metabox_settings']['yoast_settings'] ) ) {
			$wp_admin_bar->remove_node( 'wpseo-settings' );
		}

		if ( empty( $webmaster_user_role_config['webmaster_yoast_metabox_settings']['yoast_post_metabox'] ) ) {
			$wp_admin_bar->remove_node( 'wpseo-analysis' );
		}

		if ( empty( $webmaster_user_role_config['webmaster_yoast_metabox_settings']['yoast_post_metabox'] )
			&& empty( $webmaster_user_role_config['webmaster_yoast_metabox_settings']['yoast_settings'] )
		) {
			$wp_admin_bar->remove_node( 'wpseo-menu' );
		}
	}
}
