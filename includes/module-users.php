<?php
class SCD_Users extends SCD_Base_Module {

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
		add_filter( 'td_webmaster_capabilities', array( $this, 'capabilities' ) );

		if ( $this->should_register_settings_access_hooks() ) {
			$this->register_settings_access_hooks();
		}
	}

	/**
	 * Register role related hooks
	 */
	private function register_settings_access_hooks() {
		add_filter( 'editable_roles', array( $this, 'remove_adminstrator_from_editable_roles' ) );
	}

	function is_active() {
		return true; // WP Core functionality, users is always present
	}

	function settings_section( $sections ) {
		if ( ! $this->is_active() ) {
			return $sections;
		}

		$this->section = array(
			'dashicon' => 'dashicons dashicons-admin-users',
			'id'       => 'users',
			'title'    => __( 'Users', 'webmaster-user-role' ),
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_caps_users',
					'type'     => 'checkbox',
					'title'    => __( 'User Capabilities', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'list_users'   => __( 'List Users', 'webmaster-user-role' ),
						'create_users' => __( 'Create Users', 'webmaster-user-role' ),
						'edit_users'   => __( 'Edit Users', 'webmaster-user-role' ),
						'delete_users' => __( 'Delete Users', 'webmaster-user-role' ),
					),

					'default'  => array(
						'list_users'    => '0',
						'create_users'  => '0',
						'delete_users'  => '0',
						'promote_users' => '0',
						'edit_users'    => '0',
						'remove_users'  => '0',
					),
				),

			),
		);

		if ( is_multisite() ) {
			$this->section['fields']['0']['options'] = array(
				'list_users'    => __( 'View list of users on their site', 'webmaster-user-role' ),
				'promote_users' => __( 'Add existing users (must be existing users on the network)', 'webmaster-user-role' ),
				'remove_users'  => __( 'Remove users from their site', 'webmaster-user-role' ),
			);

			$this->section['fields']['0']['desc'] = '
				<p><strong>' . __( 'Notes for Multisite:', 'webmaster-user-role' ) . '</strong></p>
				<p>' . __( 'WordPress core code only allows designated "Super Admins" to Create new Users, Edit Users, and Delete Users from the Network.', 'webmaster-user-role' ) . '</p>
				<p>' . __( 'Blog/Site administrators are only able to add or remove existing users for their site.', 'webmaster-user-role' ) . '</p>
				<p>' . __( 'Due to these core restrictions, the Webmaster role won\'t be able to create brand new users for the network. This is actually not possible for a full Administrator-level user either, unless you add them as a Super Admin with the ability to administer the entire Network.', 'webmaster-user-role' ) . '</p>
				<p><a href="http://codex.wordpress.org/Create_A_Network" target="_blank">' . __( 'Learn More about WordPress Multisite', 'webmaster-user-role' ) . '</a></p>';
		}

		$sections[] = $this->section;

		return $sections;
	}

	function remove_adminstrator_from_editable_roles( $roles ) {
		if ( isset( $roles['administrator'] ) ) {
			unset( $roles['administrator'] );
		}
		return $roles;
	}

	function capabilities( $capabilities ) {
		$webmaster_user_role_config = $this->plugin->settings->get();

		if ( is_multisite() ) {
			if ( is_array( $webmaster_user_role_config ) && (int) $webmaster_user_role_config['webmaster_caps_users']['promote_users'] ) {
				$capabilities['add_users'] = 1;
			} else {
				$capabilities['add_users'] = 0;
			}
		}

		return $capabilities;
	}
}
