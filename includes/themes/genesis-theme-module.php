<?php
class SCD_Genesis_Theme {

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
		if ( $current_theme->Name == 'genesis' || $current_theme->Template == 'genesis' ) {
			$this->active = true;

			add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 1 );

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
			'id'       => 'genesis_theme_settings',
			'type'     => 'checkbox',
			'title'    => __( 'Genesis Theme Compatibility', 'webmaster-user-role' ),
			'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),
			'options'  => array(
				'access_theme_options_panel' => __( 'Access Genesis Theme Options panel', 'webmaster-user-role' ),
			),

			'default'  => array(
				'access_theme_options_panel' => '0',
			),
		);

		return $fields;
	}

	function after_setup_theme() {
		if ( ! Simple_Client_Dashboard::current_user_is_webmaster() ) {
			return;
		}

		$webmaster_user_role_config = Simple_Client_Dashboard::get_config();
		if ( ! is_array( $webmaster_user_role_config ) ) {
			return;
		}

		if ( ! empty( $webmaster_user_role_config['genesis_theme_settings']['access_theme_options_panel'] ) ) {
			return;
		}

		remove_action( 'genesis_admin_menu', 'genesis_add_admin_submenus' );
		remove_action( 'after_setup_theme', 'genesis_add_admin_menu' );
	}
}
new SCD_Genesis_Theme();
