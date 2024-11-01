<?php
class SCD_Themes extends SCD_Base_Module {

	public $plugin;
	private $section;

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
		return true; // WP Core functionality, plugins is always present
	}

	function settings_section( $sections ) {
		if ( ! $this->is_active() ) {
			return $sections;
		}

		$this->section = array(
			'dashicon' => 'dashicons dashicons-admin-appearance',
			'id'       => 'themes',
			'title'    => __( 'Themes', 'webmaster-user-role' ),
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_caps_themes',
					'type'     => 'checkbox',
					'title'    => __( 'Theme Capabilities', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'edit_theme_options' => __( 'Manage Theme, Widgets, Menus, and Customizer', 'webmaster-user-role' ),
						'install_themes'     => __( 'Install Themes', 'webmaster-user-role' ),
						'update_themes'      => __( 'Update Themes', 'webmaster-user-role' ),
						'switch_themes'      => __( 'Switch Active Theme', 'webmaster-user-role' ),
						'edit_themes'        => __( 'Edit Themes', 'webmaster-user-role' ),
						'delete_themes'      => __( 'Delete Themes', 'webmaster-user-role' ),
					),

					'default'  => array(
						'edit_theme_options' => '1',
						'install_themes'     => '0',
						'update_themes'      => '0',
						'switch_themes'      => '0',
						'edit_themes'        => '0',
						'delete_themes'      => '0',
					),
				),
			),
		);

		$supported_theme = apply_filters( 'webmaster_supported_theme', false );
		if ( $supported_theme ) {
			$supported_theme_setting_fields = apply_filters( 'webmaster_supported_theme_setting_fields', array() );
			$this->section['fields']        = array_merge( $this->section['fields'], $supported_theme_setting_fields );
		} else {
			$this->add_unsupported_theme_settings_fields();
		}

		if ( is_multisite() ) {
			$this->section['fields']['0']['options'] = array(
				'switch_themes' => 'Switch Active Theme',
			);

			$this->section['fields']['0']['desc'] = '
			<p><strong>' . __( 'Notes for Multisite:', 'webmaster-user-role' ) . '</strong></p>
			<p>' . __( 'WordPress core code only allows designated "Super Admins" to manage plugins for the entire network', 'webmaster-user-role' ) . '</p>
			<p' . __( 'Blog/Site admins can only activate/deactivate plugins installed by the network administrator', 'webmaster-user-role' ) . '</p>';
		}

		$sections[] = $this->section;

		return $sections;
	}

	function add_unsupported_theme_settings_fields() {
		$this->section['fields'][] = array(
			'id'       => 'unsupported_theme_settings',
			'type'     => 'checkbox',
			'title'    => __( '3rd Party Theme Compatibility', 'webmaster-user-role' ),
			'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

			'options'  => array(
				'access_theme_options_panel' => __( 'Access Theme Options panel (see notes)', 'webmaster-user-role' ),
			),
			'desc'     =>
			'<p><strong>' . __( 'Note: Your theme is not specifically supported, but it can be!', 'webmaster-user-role' ) . '</strong></p>
				 <p>' . __( 'Many themes create custom panels for Theme Options. Unfortunately each theme is different and requires a custom integration.', 'webmaster-user-role' ) . '</p>
				 <p>' . __( 'If you\'d like to see your theme supported, please <strong>email us at <a href="mailto:team@nsquared.io">team@nsquared.io</a></strong> and we\'ll be happy to add support for it!', 'webmaster-user-role' ) . '</p>
				 <p><strong>' . __( 'On unsupported themes, this checkbox will try to control access to the Theme Options panel, but in most cases you will need to submit a support request to add support for your theme.', 'webmaster-user-role' ) . '</strong></p>',

			'default'  => array(
				'access_theme_options_panel' => '0',
			),
		);
	}

	function capabilities( $capabilities ) {
		$webmaster_user_role_config = $this->plugin->settings->get();

		// if ( is_multisite() ) {
		// $capabilities['manage_network_themes'] = 0;// (int)$webmaster_user_role_config['webmaster_caps_plugins']['activate_plugins'];
		// }

		return $capabilities;
	}
}
