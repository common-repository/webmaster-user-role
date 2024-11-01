<?php
require_once __DIR__ . '/themes/avian-theme-module.php';
require_once __DIR__ . '/themes/avada-theme-module.php';
require_once __DIR__ . '/themes/boss-theme-module.php';
require_once __DIR__ . '/themes/canvas-theme-module.php';
require_once __DIR__ . '/themes/cardinal-theme-module.php';
require_once __DIR__ . '/themes/divi-theme-module.php';
require_once __DIR__ . '/themes/enfold-theme-module.php';
require_once __DIR__ . '/themes/genesis-theme-module.php';
require_once __DIR__ . '/themes/invicta-theme-module.php';
require_once __DIR__ . '/themes/nativechurch-theme-module.php';
require_once __DIR__ . '/themes/ken-theme-module.php';
require_once __DIR__ . '/themes/kallyas-theme-module.php';
require_once __DIR__ . '/themes/total-theme-module.php';
require_once __DIR__ . '/themes/transport-theme-module.php';

if ( ! class_exists( 'SCD_Pro' ) ) {
	class SCD_Pro {

		public $config;
		public $plugin;

		function __construct( $plugin ) {
			$this->plugin = $plugin;
			$this->hooks();
		}

		public function hooks() {
			add_action( 'admin_init', array( $this, 'maybe_update_caps_on_settings_changed' ) );
			add_action( 'updated_option', array( $this, 'maybe_update_caps_on_plugin_installed' ), 10, 3 );

			$plugin_relative_path = basename( dirname( Simple_Client_Dashboard::file ) ) . '/' . basename( Simple_Client_Dashboard::file );
			add_filter( 'plugin_action_links_' . $plugin_relative_path, array( $this, 'add_action_links' ) );
		}

		public function add_action_links( $links ) {
			if ( empty( $links ) ) {
				$links = array();
			}
			return array_merge(
				array(
					'settings' => '<a href="' . admin_url( 'options-general.php?page=simple-client-dashboard' ) . '">' . __( 'Settings', 'webmaster-user-role' ) . '</a>',
				),
				$links
			);
		}

		public function maybe_update_caps_on_settings_changed() {
			$webmaster_user_role_config = $this->plugin->settings->get();

			$last_caps_update = get_site_option( 'td-webmaster-last-caps-update' );
			if ( is_array( $webmaster_user_role_config ) && ! empty( $webmaster_user_role_config['last_updated'] ) && $last_caps_update != $webmaster_user_role_config['last_updated'] ) {
				$this->plugin->update_webmaster_caps();
				update_site_option( 'td-webmaster-last-caps-update', $webmaster_user_role_config['last_updated'] );
			}
		}


		public function maybe_update_caps_on_plugin_installed( $option, $old_value, $value ) {
			if ( empty( $option ) || $option !== 'active_plugins' ) {
				return;
			}

			// TODO check specifially for plugins we're integrating with
			$this->plugin->update_webmaster_caps();
		}
	}
}
