<?php

// uncomment this line for testing
// set_site_transient( 'update_plugins', null );

/**
 * Allows plugins to use their own update API.
 *
 * @author  Pippin Williamson
 * @version 2.1.7.18
 */
class SCD_Upgrade {

	public $plugin;

	function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Register hooks
	 */
	private function hooks() {
		// If not the basic edition, there is nothing to do
		if ( Simple_Client_Dashboard::get_edition() !== 'basic' ) {
			return;
		}

		add_filter( 'simple_client_dashboard/config/sections', array( $this, 'settings_section' ), 100 );
	}

	function settings_section( $sections ) {
		$scd_support_url     = 'https://simpleclientdashboard.com/contact-us';
		$scd_get_license_url = 'https://simpleclientdashboard.com/#pricing';

		$sections[] = array(
			'dashicon' => 'dashicons dashicons-upload',
			'id'       => 'upgrade_pro',
			'title'    => __( 'Upgrade to PRO', 'webmaster-user-role' ),
			'active'   => true,
			'content'  => // This is a content type of sections; where it has no fields property
			array(
				'id'       => 'upgrade_to_pro',
				'type'     => 'notice',
				'title'    => __( 'Upgrade to Simple Client Dashboard Pro', 'webmaster-user-role' ),
				'class'    => 'upgrade-to-pro',
				'subtitle' => __( 'And unlock all the modules', 'webmaster-user-role' ),
				'desc'     =>
				'<p>' . __( 'Get the most out of Simple Client Dashboard by upgrading to our Pro version.', 'webmaster-user-role' ) . '</p>
						<p>' . __( 'Enjoy enhanced features, more control over permissions, expanded third-party plugin settings, and priority customer support.', 'webmaster-user-role' ) . '</p>
						<a href="' . $scd_get_license_url . '" target="_blank" class="upgrade-button">' . __( 'Upgrade Now', 'webmaster-user-role' ) . '</a>
						<p class="scd_contact-support">
							' . __( 'Need assistance or have questions? We\'re here to help!', 'webmaster-user-role' ) . '
							<a href="' . $scd_support_url . '" target="_blank" class="contact-support-button">' . __( 'Contact Support', 'webmaster-user-role' ) . '</a>
						</p>',
			),
		);

		return $sections;
	}
}
