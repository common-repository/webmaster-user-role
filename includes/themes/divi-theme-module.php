<?php
class SCD_Divi_Theme {

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
		if ( $current_theme->Name == 'Divi' || $current_theme->Template == 'Divi' ) {
			$this->active = true;

			add_action( 'admin_menu', array( $this, 'admin_menu' ), 100 );
			// add_action( 'admin_menu', array( $this, 'override_switch_themes' ), 9 );
			// add_action( 'admin_menu', array( $this, 'restore_switch_themes' ), 11 );

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
			'id'          => 'divi_theme_settings',
			'type'        => 'checkbox',
			'title'       => __( 'Divi Theme Compatibility', 'webmaster-user-role' ),
			'subtitle'    => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),
			'description' => __(
				'<h3>IMPORTANT: If you want to enable access to Divi Theme Options, you MUST also enable the user\'s ability to "Switch Themes"</h3>
				<p>This is a limitation based on how the Divi theme is coded. If this changes in a future Divi update, we will improve this :)</p>'
			),

			'options'     => array(
				'access_theme_options_panel' => __( 'Access Divi Theme Options panel', 'webmaster-user-role' ),
			),

			'default'     => array(
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

		global $themename, $shortname, $options;
		$menu_slug = 'core_functions.php';

		if ( empty( $webmaster_user_role_config['divi_theme_settings']['access_theme_options_panel'] ) ) {
			echo '<pre>' . print_r( 'remove', true ) . '</pre>';
			remove_submenu_page( 'themes.php', $menu_slug );
		} else {
			// $capability = 'webmaster';
			// $core_page = add_theme_page( $themename . ' ' . esc_html__( 'Options', $themename ), $themename . ' ' . esc_html__( 'Theme Options', $themename ), $capability, $menu_slug, 'et_build_epanel' );
		}
	}

	function override_switch_themes() {
		if ( ! Simple_Client_Dashboard::current_user_is_webmaster() ) {
			return;
		}

		$webmaster_user_role_config = Simple_Client_Dashboard::get_config();
		if ( ! is_array( $webmaster_user_role_config ) ) {
			return;
		}
		add_filter( 'user_has_cap', array( $this, 'filter_switch_themes_cap' ), 10, 5 );
	}

	function restore_switch_themes() {

		if ( ! Simple_Client_Dashboard::current_user_is_webmaster() ) {
			return;
		}

		$webmaster_user_role_config = Simple_Client_Dashboard::get_config();
		if ( ! is_array( $webmaster_user_role_config ) ) {
			return;
		}

		remove_filter( 'user_has_cap', array( $this, 'filter_switch_themes_cap' ), 10, 5 );
	}

	function filter_switch_themes_cap( $allcaps, $caps, $args, $user ) {
		$webmaster_user_role_config = Simple_Client_Dashboard::get_config();
		if ( empty( $webmaster_user_role_config['divi_theme_settings']['access_theme_options_panel'] ) ) {
			$allcaps['switch_themes'] = 0;
		} else {
			$allcaps['switch_themes'] = 1;
		}

		return $allcaps;
	}
}
new SCD_Divi_Theme();
