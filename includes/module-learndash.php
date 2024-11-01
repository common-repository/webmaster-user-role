<?php
class SCD_LearnDash extends SCD_Base_Module {

	public $plugin;
	private $menu_slug = 'learndash-lms';

	/**
	 * SCD_LearnDash constructor.
	 *
	 * @param $plugin
	 */
	function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	} //end constructor

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
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 99999 );
		add_action( 'admin_init', array( $this, 'maybe_block_learndash_lms_settings' ) );
	}

	function is_active() {
		return ( class_exists( 'SFWD_LMS' ) );
	}

	function settings_section( $sections ) {
		if ( ! $this->is_active() ) {
			return $sections;
		}

		$section = array(
			'title'    => __( 'LearnDash LMS', 'webmaster-user-role' ),
			'dashicon' => 'dashicons dashicons-welcome-learn-more',
			'id'       => 'learndash',
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_caps_learndash_caps',
					'type'     => 'checkbox',
					'title'    => __( 'LearnDash Capabilities', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'edit_courses'           => __( 'Manage Courses (also includes Lessons, Topics, Quizzes, and Certificates)', 'webmaster-user-role' ),
						'edit_assignments'       => __( 'Manage Assignments', 'webmaster-user-role' ),
						'edit_groups'            => __( 'Manage Groups', 'webmaster-user-role' ),
						'group_leader'           => __( 'Group Administration (list users, export progress & results)', 'webmaster-user-role' ),
						'learndash_lms_settings' => __( 'Manage Settings', 'webmaster-user-role' ),
					),

					'default'  => array(
						'edit_courses'           => '1',
						'edit_assignments'       => '1',
						'edit_groups'            => '1',
						'group_leader'           => '1',
						'learndash_lms_settings' => '1',
					),
				),
			),
		);

		$sections[] = $this->filter_section( $section );
		return $sections;
	} //end settings_section function

	function admin_menu() {
		$webmaster_user_role_config = $this->plugin->settings->get();

		if ( ! isset( $webmaster_user_role_config['webmaster_caps_learndash_caps'] ) ) {
			return;
		}

		/**
		 * If all caps are turned on return
		 */
		if ( $this->are_all_caps_turned_on( $webmaster_user_role_config['webmaster_caps_learndash_caps'] ) ) {
			return;
		}

		/**
		 * If all caps are turned off remove learndash menu
		 */
		if ( $this->are_all_caps_turned_off( $webmaster_user_role_config['webmaster_caps_learndash_caps'] ) ) {
			remove_menu_page( 'learndash-lms' );
			return;
		}

		global $menu;

		foreach ( $menu as $key => $item ) {
			$capabilities = array( 'edit_courses', 'edit_assignments', 'edit_groups', 'group_leader' );
			foreach ( $capabilities as $capability ) {
				if ( $item[1] == $capability && isset( $webmaster_user_role_config['webmaster_caps_learndash_caps'][ $capability ] ) && empty( $webmaster_user_role_config['webmaster_caps_learndash_caps'][ $capability ] ) ) {
					unset( $menu[ $key ] );
				}
			}
		}

		/**
		 * Remove settings submenu
		 */
		if ( isset( $webmaster_user_role_config['webmaster_caps_learndash_caps']['learndash_lms_settings'] ) && empty( $webmaster_user_role_config['webmaster_caps_learndash_caps']['learndash_lms_settings'] ) ) {

			global $submenu;

			if ( isset( $submenu[ $this->menu_slug ] ) ) {
				foreach ( $submenu[ $this->menu_slug ] as $i => $item ) {
					if ( strpos( $item[2], 'learndash_lms_settings' ) !== false ) {
						unset( $submenu[ $this->menu_slug ][ $i ] );
					}
				}
			}
		}
	}

	function maybe_block_learndash_lms_settings() {
		$webmaster_user_role_config = $this->plugin->settings->get();

		if ( isset( $webmaster_user_role_config['webmaster_caps_learndash_caps']['learndash_lms_settings'] ) && empty( $webmaster_user_role_config['webmaster_caps_learndash_caps']['learndash_lms_settings'] ) ) {

			global $pagenow;

			if ( 'admin.php' == $pagenow && isset( $_GET['page'] ) && 'learndash_lms_settings' == $_GET['page'] ) {
				wp_redirect( admin_url() );
				exit;
			}
		}
	}
}
