<?php
class SCD_Events_Calendar extends SCD_Base_Module {

	public $plugin;
	public $caps;

	function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();

		$this->caps = array(
			'events'     => array(
				'edit_tribe_event'   => __( 'Manage Events', 'webmaster-user-role' ),
				'delete_tribe_event' => __( 'Delete Events', 'webmaster-user-role' ),
			),
			'venues'     => array(
				'edit_tribe_venue'   => __( 'Manage Venues', 'webmaster-user-role' ),
				'delete_tribe_venue' => __( 'Delete Venues', 'webmaster-user-role' ),
			),
			'organizers' => array(
				'edit_tribe_organizer'   => __( 'Manage Organizers', 'webmaster-user-role' ),
				'delete_tribe_organizer' => __( 'Delete Organizers', 'webmaster-user-role' ),
			),
		);
	}

	function hooks() {
		if ( empty( $this->is_active() ) ) {
			return;
		}
		add_filter( 'simple_client_dashboard/config/sections', array( $this, 'settings_section' ) );

		if ( $this->should_filter_capabilities() ) {
			add_filter( 'td_webmaster_capabilities', array( $this, 'capabilities' ) );
		}
	}

	function is_active() {
		return class_exists( 'Tribe__Events__Main' );
	}

	function settings_section( $sections ) {
		$section = array(
			'title'    => __( 'Events Calendar', 'webmaster-user-role' ),
			'dashicon' => 'dashicons dashicons-calendar',
			'id'       => 'events_calendar',
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_events_calendar_events_settings',
					'type'     => 'checkbox',
					'title'    => __( 'Event Capabilities', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster users have the following access for Events', 'webmaster-user-role' ) . ':',

					'options'  => $this->caps['events'],

					'default'  => array_combine( array_keys( $this->caps['events'] ), array_fill( 1, count( $this->caps['events'] ), '1' ) ),
				),
				array(
					'id'       => 'webmaster_events_calendar_venues_settings',
					'type'     => 'checkbox',
					'title'    => __( 'Venue Capabilities', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster users have the following access for Venues', 'webmaster-user-role' ) . ':',

					'options'  => $this->caps['venues'],

					'default'  => array_combine( array_keys( $this->caps['venues'] ), array_fill( 1, count( $this->caps['venues'] ), '1' ) ),
				),
				array(
					'id'       => 'webmaster_events_calendar_organizers_settings',
					'type'     => 'checkbox',
					'title'    => __( 'Organizers Capabilities', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster users have the following access for Organizers', 'webmaster-user-role' ) . ':',

					'options'  => $this->caps['organizers'],

					'default'  => array_combine( array_keys( $this->caps['organizers'] ), array_fill( 1, count( $this->caps['organizers'] ), '1' ) ),
				),
			),
		);

		$sections[] = $this->filter_section( $section );
		return $sections;
	}

	function default_capabilities( $capabilities ) {
		foreach ( $this->caps as $cap_cpt => $cap_array ) {
			foreach ( $cap_array as $cap => $cap_label ) {
				if ( ! isset( $webmaster_user_role_config[ 'webmaster_events_calendar_' . $cap_cpt . '_settings' ][ $cap ] ) ) {
					$webmaster_user_role_config[ 'webmaster_events_calendar_' . $cap_cpt . '_settings' ][ $cap ] = $this->section['fields'][ $cap_cpt ]['default'][ $cap ];
				}
			}
		}

		return $capabilities;
	}

	function capabilities( $capabilities ) {
		$webmaster_user_role_config = $this->plugin->settings->get();
		if ( ! is_array( $webmaster_user_role_config ) ) {
			return $capabilities;
		}
		if ( empty( $webmaster_user_role_config['webmaster_events_calendar_events_settings'] ) ) {
			return $capabilities;
		}

		/* Fill in events caps */
		$capabilities['read_tribe_events']         = (int) $webmaster_user_role_config['webmaster_events_calendar_events_settings']['edit_tribe_event'];
		$capabilities['read_private_tribe_events'] = (int) $webmaster_user_role_config['webmaster_events_calendar_events_settings']['edit_tribe_event'];

		$capabilities['publish_tribe_events'] = (int) $webmaster_user_role_config['webmaster_events_calendar_events_settings']['edit_tribe_event'];

		$capabilities['edit_tribe_events']           = (int) $webmaster_user_role_config['webmaster_events_calendar_events_settings']['edit_tribe_event'];
		$capabilities['edit_published_tribe_events'] = (int) $webmaster_user_role_config['webmaster_events_calendar_events_settings']['edit_tribe_event'];
		$capabilities['edit_others_tribe_events']    = (int) $webmaster_user_role_config['webmaster_events_calendar_events_settings']['edit_tribe_event'];
		$capabilities['edit_private_tribe_events']   = (int) $webmaster_user_role_config['webmaster_events_calendar_events_settings']['edit_tribe_event'];

		$capabilities['delete_tribe_events']           = (int) $webmaster_user_role_config['webmaster_events_calendar_events_settings']['delete_tribe_event'];
		$capabilities['delete_published_tribe_events'] = (int) $webmaster_user_role_config['webmaster_events_calendar_events_settings']['delete_tribe_event'];
		$capabilities['delete_others_tribe_events']    = (int) $webmaster_user_role_config['webmaster_events_calendar_events_settings']['delete_tribe_event'];
		$capabilities['delete_private_tribe_events']   = (int) $webmaster_user_role_config['webmaster_events_calendar_events_settings']['delete_tribe_event'];

		/* Fill in venues caps */
		$capabilities['read_private_tribe_venues'] = (int) $webmaster_user_role_config['webmaster_events_calendar_venues_settings']['edit_tribe_venue'];
		$capabilities['read_tribe_venue']          = (int) $webmaster_user_role_config['webmaster_events_calendar_venues_settings']['edit_tribe_venue'];

		$capabilities['publish_tribe_venues'] = (int) $webmaster_user_role_config['webmaster_events_calendar_venues_settings']['edit_tribe_venue'];

		$capabilities['edit_tribe_venues']           = (int) $webmaster_user_role_config['webmaster_events_calendar_venues_settings']['edit_tribe_venue'];
		$capabilities['edit_published_tribe_venues'] = (int) $webmaster_user_role_config['webmaster_events_calendar_venues_settings']['edit_tribe_venue'];
		$capabilities['edit_others_tribe_venues']    = (int) $webmaster_user_role_config['webmaster_events_calendar_venues_settings']['edit_tribe_venue'];
		$capabilities['edit_private_tribe_venues']   = (int) $webmaster_user_role_config['webmaster_events_calendar_venues_settings']['edit_tribe_venue'];

		$capabilities['delete_tribe_venues']           = (int) $webmaster_user_role_config['webmaster_events_calendar_venues_settings']['delete_tribe_venue'];
		$capabilities['delete_published_tribe_venues'] = (int) $webmaster_user_role_config['webmaster_events_calendar_venues_settings']['delete_tribe_venue'];
		$capabilities['delete_others_tribe_venues']    = (int) $webmaster_user_role_config['webmaster_events_calendar_venues_settings']['delete_tribe_venue'];
		$capabilities['delete_private_tribe_venues']   = (int) $webmaster_user_role_config['webmaster_events_calendar_venues_settings']['delete_tribe_venue'];

		/* Fill in organizers caps */
		$capabilities['read_private_tribe_organizers'] = (int) $webmaster_user_role_config['webmaster_events_calendar_organizers_settings']['edit_tribe_organizer'];
		$capabilities['read_tribe_organizer']          = (int) $webmaster_user_role_config['webmaster_events_calendar_organizers_settings']['edit_tribe_organizer'];

		$capabilities['publish_tribe_organizers'] = (int) $webmaster_user_role_config['webmaster_events_calendar_organizers_settings']['edit_tribe_organizer'];

		$capabilities['edit_tribe_organizers']           = (int) $webmaster_user_role_config['webmaster_events_calendar_organizers_settings']['edit_tribe_organizer'];
		$capabilities['edit_published_tribe_organizers'] = (int) $webmaster_user_role_config['webmaster_events_calendar_organizers_settings']['edit_tribe_organizer'];
		$capabilities['edit_others_tribe_organizers']    = (int) $webmaster_user_role_config['webmaster_events_calendar_organizers_settings']['edit_tribe_organizer'];
		$capabilities['edit_private_tribe_organizers']   = (int) $webmaster_user_role_config['webmaster_events_calendar_organizers_settings']['edit_tribe_organizer'];

		$capabilities['delete_tribe_organizers']           = (int) $webmaster_user_role_config['webmaster_events_calendar_organizers_settings']['delete_tribe_organizer'];
		$capabilities['delete_published_tribe_organizers'] = (int) $webmaster_user_role_config['webmaster_events_calendar_organizers_settings']['delete_tribe_organizer'];
		$capabilities['delete_others_tribe_organizers']    = (int) $webmaster_user_role_config['webmaster_events_calendar_organizers_settings']['delete_tribe_organizer'];
		$capabilities['delete_private_tribe_organizers']   = (int) $webmaster_user_role_config['webmaster_events_calendar_organizers_settings']['delete_tribe_organizer'];

		return $capabilities;
	}
}
