<?php
class SCD_Transport_Theme {

	private $active;

	function __construct() {
		add_filter( 'webmaster_supported_theme', array( $this, 'is_supported_theme' ) );
		add_filter( 'webmaster_supported_theme_setting_fields', array( $this, 'setting_fields' ) );
	}

	function is_supported_theme( $supported ) {
		if ( $supported ) {
			return $supported;
		}

		return $this->is_active();
	}

	function is_active() {
		if ( $this->active ) {
			return true;
		}

		$current_theme = wp_get_theme();
		if ( strtolower( $current_theme->Name ) == 'transport' || strtolower( $current_theme->Template ) == 'transport' ) {
			$this->active = true;
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 100 );
			add_action( 'wp_before_admin_bar_render', array( $this, 'wp_before_admin_bar_render' ) );

			return true;
		}

		return false;
	}

	function setting_fields( $fields = array() ) {
		if ( ! $this->is_active() ) {
			return $fields;
		}

		$fields   = array();
		$fields[] = array(
			'id'       => 'transport_theme_settings',
			'type'     => 'checkbox',
			'title'    => __( 'Transport Theme Compatibility', 'webmaster-user-role' ),
			'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

			'options'  => array(
				'access_theme_options_panel' => __( 'Access Theme Options panel', 'webmaster-user-role' ),
			),

			'default'  => array(
				'access_theme_options_panel' => '0',
			),
		);

		return $fields;
	}

	function admin_menu() {
		if ( ! Simple_Client_Dashboard::current_user_is_webmaster() ) {
			return;
		}

		$webmaster_user_role_config = Simple_Client_Dashboard::get_config();
		if ( ! is_array( $webmaster_user_role_config ) ) {
			return;
		}

		if ( ! empty( $webmaster_user_role_config['transport_theme_settings']['access_theme_options_panel'] ) ) {
			add_theme_page( 'Theme Options', 'Theme Options', 'read', 'theme_options', 'theme_options_do_page' );
		}
	}

	function wp_before_admin_bar_render() {
		if ( ! Simple_Client_Dashboard::current_user_is_webmaster() ) {
			return;
		}

		$webmaster_user_role_config = Simple_Client_Dashboard::get_config();
		if ( ! is_array( $webmaster_user_role_config ) ) {
			return;
		}

		if ( empty( $webmaster_user_role_config['transport_theme_settings']['access_theme_options_panel'] ) ) {
			global $wp_admin_bar;
			$wp_admin_bar->remove_menu( 'avia' );
		}
	}
}
new SCD_Transport_Theme();
