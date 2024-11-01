<?php
class SCD_Kallyas_Theme {

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
		if ( $current_theme->Name == 'Kallyas' || $current_theme->Template == 'kallyas' || $current_theme->Template == 'Kallyas' ) {
			$this->active = true;
			add_action( 'init', array( $this, 'init' ) );

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
			'id'       => 'kallyas_theme_settings',
			'type'     => 'checkbox',
			'title'    => __( 'Kallyas Theme Compatibility', 'webmaster-user-role' ),
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

	function init() {
		if ( ! Simple_Client_Dashboard::current_user_is_webmaster() ) {
			return;
		}

		$webmaster_user_role_config = Simple_Client_Dashboard::get_config();
		if ( ! is_array( $webmaster_user_role_config ) ) {
			return;
		}

		if ( empty( $webmaster_user_role_config['kallyas_theme_settings']['access_theme_options_panel'] ) ) {
			$zn_framework = Zn_Framework::instance();
			remove_action( 'admin_menu', array( $zn_framework->theme_options, 'zn_add_admin_pages' ) );
		} else {

		}
	}

	function optionsframework_add_admin() {
		$label = __( 'Theme Options', 'kallyas_dictionary' );

		$of_page = add_theme_page( THEMENAME, $label, 'webmaster', 'optionsframework', 'optionsframework_options_page' );

		// Add framework functionaily to the head individually
		add_action( "admin_print_scripts-$of_page", 'of_load_only' );
		add_action( "admin_print_styles-$of_page", 'of_style_only' );
	}
}
new SCD_Kallyas_Theme();
