<?php
class SCD_Gravity_Forms extends SCD_Base_Module {

	public $plugin;
	public $caps;
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

		if ( $this->should_filter_capabilities() ) {
			add_filter( 'td_webmaster_capabilities', array( $this, 'capabilities' ) );
		}

		if ( $this->should_register_settings_access_hooks() ) {
			$this->register_settings_access_hooks();
		}
	}

	/**
	 * Register role related hooks
	 */
	private function register_settings_access_hooks() {
		add_action( 'admin_footer', array( $this, 'admin_footer' ) );
	}

	function is_active() {
		return class_exists( 'GFForms' );
	}

	function settings_section( $sections ) {

		$this->section = array(
			'dashicon' => 'dashicons dashicons-list-view',
			'id'       => 'gforms',
			'title'    => __( 'Gravity Forms', 'webmaster-user-role' ),
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_caps_gravityforms_forms',
					'type'     => 'checkbox',
					'title'    => __( 'Managing Forms', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'gravityforms_edit_forms'    => __( 'List & Edit Forms', 'webmaster-user-role' ),
						'gravityforms_create_form'   => __( 'Create & Duplicate Forms', 'webmaster-user-role' ),
						'gravityforms_delete_forms'  => __( 'Delete Forms', 'webmaster-user-role' ),
						'gravityforms_preview_forms' => __( 'Preview Forms', 'webmaster-user-role' ),
					),

					'default'  => array(
						'gravityforms_create_form'   => '0',
						'gravityforms_edit_forms'    => '1',
						'gravityforms_delete_forms'  => '0',
						'gravityforms_preview_forms' => '0',
					),
				),

				array(
					'id'       => 'webmaster_caps_gravityforms_entries',
					'type'     => 'checkbox',
					'title'    => __( 'Managing Entries (Form Submissions/Data)', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'gravityforms_view_entries'    => __( 'View Form Entries', 'webmaster-user-role' ),
						'gravityforms_view_entry_note' => __( 'View Internal Notes on Form Entries', 'webmaster-user-role' ),
						'gravityforms_edit_entries'    => __( 'Edit Form Entries', 'webmaster-user-role' ),
						'gravityforms_edit_entry_note' => __( 'Edit Internal Notes on Form Entries', 'webmaster-user-role' ),
						'gravityforms_delete_entries'  => __( 'Delete Form Entries', 'webmaster-user-role' ),
						'gravityforms_export_entries'  => __( 'Export Form Entries', 'webmaster-user-role' ),
					),

					'default'  => array(
						'gravityforms_view_entries'    => '1',
						'gravityforms_view_entry_note' => '0',
						'gravityforms_edit_entries'    => '0',
						'gravityforms_edit_entry_note' => '0',
						'gravityforms_delete_entries'  => '0',
						'gravityforms_export_entries'  => '0',
					),
				),

				array(
					'id'       => 'webmaster_caps_gravityforms_advanced',
					'type'     => 'checkbox',
					'title'    => __( 'Advanced Features', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'gravityforms_edit_settings' => __( 'Edit Settings', 'webmaster-user-role' ),
						'gravityforms_uninstall'     => __( 'Uninstall Gravity Forms', 'webmaster-user-role' ),
						'gravityforms_view_updates'  => __( 'Show when updates are available', 'webmaster-user-role' ),
						'gravityforms_view_addons'   => __( 'Show Add-Ons Available/Installed (User also needs capability to install plugins)', 'webmaster-user-role' ),
					),

					'default'  => array(
						'gravityforms_edit_settings' => '0',
						'gravityforms_uninstall'     => '0',
						'gravityforms_view_updates'  => '0',
						'gravityforms_view_addons'   => '0',
					),
				),
			),
		);

		/* Authorize.net Add-On */
		if ( class_exists( 'GFAuthorizeNet' ) ) {
			$this->section['fields'][] = array(
				'id'       => 'webmaster_caps_gravityforms_authorizenet',
				'type'     => 'checkbox',
				'title'    => __( 'Authorize.Net Add-On', 'webmaster-user-role' ),
				'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

				'options'  => array(
					'gravityforms_authorizenet' => __( 'Manage Authorize.net Feeds', 'webmaster-user-role' ),
				),
				'desc'     => __( 'This controls access to the feeds (submenu under Forms). Access to the settings area for this add-on is controlled globally by the Edit Settings capability above.', 'webmaster-user-role' ),

				'default'  => array(
					'gravityforms_authorizenet' => '0',
				),
			);
		}

		/* AWeber Add-On */
		if ( class_exists( 'GFAWeber' ) ) {
			$this->section['fields'][] = array(
				'id'       => 'webmaster_caps_gravityforms_aweber',
				'type'     => 'checkbox',
				'title'    => __( 'AWeber Add-On', 'webmaster-user-role' ),
				'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

				'options'  => array(
					'gravityforms_aweber' => __( 'Manage AWeber Feeds', 'webmaster-user-role' ),
				),
				'desc'     => __( 'This controls access to the feeds (submenu under Forms). Access to the settings area for this add-on is controlled globally by the Edit Settings capability above.', 'webmaster-user-role' ),

				'default'  => array(
					'gravityforms_aweber' => '0',
				),
			);
		}

		/* Campaign Monitor Add-On */
		if ( class_exists( 'GFCampaignMonitor' ) ) {
			$this->section['fields'][] = array(
				'id'       => 'webmaster_caps_gravityforms_campaignmonitor',
				'type'     => 'checkbox',
				'title'    => __( 'Campaign Monitor Add-On', 'webmaster-user-role' ),
				'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

				'options'  => array(
					'gravityforms_campaignmonitor' => __( 'Manage Campaign Monitor Feeds', 'webmaster-user-role' ),
				),
				'desc'     => __( 'This controls access to the feeds (submenu under Forms). Access to the settings area for this add-on is controlled globally by the Edit Settings capability above.', 'webmaster-user-role' ),

				'default'  => array(
					'gravityforms_campaignmonitor' => '0',
				),
			);
		}

		/* Coupons Add-On */
		if ( class_exists( 'GFCoupons' ) ) {
			$this->section['fields'][] = array(
				'id'       => 'webmaster_caps_gravityforms_coupons',
				'type'     => 'checkbox',
				'title'    => __( 'Coupons Add-On', 'webmaster-user-role' ),
				'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

				'options'  => array(
					'gravityforms_coupons' => __( 'Manage Coupons Feeds', 'webmaster-user-role' ),
				),
				'desc'     => __( 'This controls access to the feeds (submenu under Forms). Access to the settings area for this add-on is controlled globally by the Edit Settings capability above.', 'webmaster-user-role' ),

				'default'  => array(
					'gravityforms_coupons' => '0',
				),
			);
		}

		/* FreshBooks Add-On */
		if ( class_exists( 'GFFreshBooks' ) ) {
			$this->section['fields'][] = array(
				'id'       => 'webmaster_caps_gravityforms_freshbooks',
				'type'     => 'checkbox',
				'title'    => __( 'FreshBooks Add-On', 'webmaster-user-role' ),
				'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

				'options'  => array(
					'gravityforms_freshbooks' => __( 'Manage FreshBooks Feeds', 'webmaster-user-role' ),
				),
				'desc'     => __( 'This controls access to the feeds (submenu under Forms). Access to the settings area for this add-on is controlled globally by the Edit Settings capability above.', 'webmaster-user-role' ),

				'default'  => array(
					'gravityforms_freshbooks' => '0',
				),
			);
		}

		/* MailChimp Add-On */
		if ( class_exists( 'GFMailChimp' ) ) {
			$this->section['fields'][] = array(
				'id'       => 'webmaster_caps_gravityforms_mailchimp',
				'type'     => 'checkbox',
				'title'    => __( 'MailChimp Add-On', 'webmaster-user-role' ),
				'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

				'options'  => array(
					'gravityforms_mailchimp' => __( 'Manage MailChimp Feeds', 'webmaster-user-role' ),
				),
				'desc'     => __( 'This controls access to the feeds (submenu under Forms). Access to the settings area for this add-on is controlled globally by the Edit Settings capability above.', 'webmaster-user-role' ),

				'default'  => array(
					'gravityforms_mailchimp' => '0',
				),
			);
		}

		/* Twilio Add-On */
		if ( class_exists( 'GFTwilio' ) ) {
			$this->section['fields'][] = array(
				'id'       => 'webmaster_caps_gravityforms_twilio',
				'type'     => 'checkbox',
				'title'    => __( 'Twilio Add-On', 'webmaster-user-role' ),
				'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

				'options'  => array(
					'gravityforms_twilio' => __( 'Manage Twilio Feeds', 'webmaster-user-role' ),
				),
				'desc'     => __( 'This controls access to the feeds (submenu under Forms). Access to the settings area for this add-on is controlled globally by the Edit Settings capability above.', 'webmaster-user-role' ),

				'default'  => array(
					'gravityforms_twilio' => '0',
				),
			);
		}

		/* User Registration Add-On */
		if ( class_exists( 'GFUser' ) ) {
			$this->section['fields'][] = array(
				'id'       => 'webmaster_caps_gravityforms_userregistration',
				'type'     => 'checkbox',
				'title'    => __( 'User Registration Add-On', 'webmaster-user-role' ),
				'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

				'options'  => array(
					'gravityforms_user_registration' => __( 'Manage User Registration Feeds', 'webmaster-user-role' ),
				),
				'desc'     => __( 'This controls access to the feeds (submenu under Forms). Access to the settings area for this add-on is controlled globally by the Edit Settings capability above.', 'webmaster-user-role' ),

				'default'  => array(
					'gravityforms_user_registration' => '0',
				),
			);
		}

		/* Zapier Add-On */
		if ( class_exists( 'GFZapier' ) ) {
			$this->section['fields'][] = array(
				'id'       => 'webmaster_caps_gravityforms_zapier',
				'type'     => 'checkbox',
				'title'    => __( 'Zapier Add-On', 'webmaster-user-role' ),
				'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

				'options'  => array(
					'gravityforms_zapier' => __( 'Manage Zapier Feeds', 'webmaster-user-role' ),
				),
				'desc'     => __( 'This controls access to the feeds (submenu under Forms). Access to the settings area for this add-on is controlled globally by the Edit Settings capability above.', 'webmaster-user-role' ),

				'default'  => array(
					'gravityforms_zapier' => '0',
				),
			);
		}

		$sections[] = $this->filter_section( $this->section );
		return $sections;
	}

	function capabilities( $capabilities ) {
		$webmaster_user_role_config = $this->plugin->settings->get();
		if ( ! is_array( $webmaster_user_role_config ) ) {
			return $capabilities;
		}

		$capabilities['gravityforms_view_settings']       = isset( $webmaster_user_role_config['webmaster_caps_gravityforms_advanced'] ) ? @ (int) $webmaster_user_role_config['webmaster_caps_gravityforms_advanced']['gravityforms_edit_settings'] : 0;
		$capabilities['gravityforms_mailchimp_uninstall'] = isset( $webmaster_user_role_config['webmaster_caps_gravityforms_mailchimp'] ) ? @ (int) $webmaster_user_role_config['webmaster_caps_gravityforms_mailchimp']['gravityforms_mailchimp'] : 0;
		$capabilities['gravityforms_zapier_uninstall']    = isset( $webmaster_user_role_config['webmaster_caps_gravityforms_zapier'] ) ? @ (int) $webmaster_user_role_config['webmaster_caps_gravityforms_zapier']['gravityforms_zapier'] : 0;

		return $capabilities;
	}

	function admin_footer() {
		$screen = get_current_screen();
		if ( $screen->id != 'toplevel_page_gf_edit_forms' ) {
			return;
		}

		if ( ! Simple_Client_Dashboard::current_user_is_webmaster() ) {
			return;
		}

		$webmaster_user_role_config = $this->plugin->settings->get();
		if ( ! is_array( $webmaster_user_role_config ) ) {
			return;
		}

		if ( ! empty( $webmaster_user_role_config['webmaster_caps_gravityforms_forms']['gravityforms_create_form'] ) ) {
			return;
		}
		// Hide "Add New" button since GF didn't tie this into the gravityforms_create_form capability
		?>
		<style type="text/css">
			.toplevel_page_gf_edit_forms .add-new-h2 {
				display: none;
			}
		</style>
		<?php
	}
}