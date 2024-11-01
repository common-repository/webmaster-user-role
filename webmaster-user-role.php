<?php
/*
Plugin Name: Simple Client Dashboard - WP Limited Admin User Role
Plugin URI: https://nsquared.io/webmaster-user-role/
Description: Adds an "Admin" user role between Administrator and Editor.  By default this user is the same as Administrator, without the capability to manage plugins or change themes
Version: 2.1.7.18
Author: N Squared
Author URI: https://nsquared.io
Author Email: team@nsquared.io
License:

	Copyright 2012 N Squared

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


/**
 * Autoloads files with classes when needed.
 *
 * @since 1.7.2
 * @param string $class_name Name of the class being requested.
 */
if ( ! function_exists( 'scd_autoload_classes' ) ) {
	function scd_autoload_classes( $class_name ) {

		// If our class doesn't have our prefix, don't load it.
		if ( 0 !== strpos( $class_name, 'SCD_' ) ) {
			return;
		}

		// Set up our filename.
		$filename = strtolower( str_replace( '_', '-', substr( $class_name, strlen( 'SCD_' ) ) ) );

		// Include our file.
		Simple_Client_Dashboard::include_file( 'includes/class-' . $filename );
	}
}

/**
 * Autoloads files with classes when needed.
 *
 * @since 1.7.2
 * @param string $class_name Name of the class being requested.
 */
if ( ! function_exists( 'scd_autoload_modules' ) ) {
	function scd_autoload_modules( $class_name ) {

		// If our class doesn't have our prefix, don't load it.
		if ( 0 !== strpos( $class_name, 'SCD_' ) ) {
			return;
		}

		// Set up our filename.
		$filename = strtolower( str_replace( '_', '-', substr( $class_name, strlen( 'SCD_' ) ) ) );

		// Include our file.
		Simple_Client_Dashboard::include_file( 'includes/module-' . $filename );
	}
}

spl_autoload_register( 'scd_autoload_classes' );
spl_autoload_register( 'scd_autoload_modules' );


if ( ! class_exists( 'Simple_Client_Dashboard' ) ) {
	final class Simple_Client_Dashboard {



		/**
		 * Singleton instance of plugin.
		 *
		 * @var   Simple_Client_Dashboard
		 * @since 1.7.2
		 */
		protected static $single_instance = null;

		/*
		--------------------------------------------*
		* Constants
		*--------------------------------------------*/

		const name = 'Simple Client Dashboard';

		const slug = 'simple-client-dashboard';

		const version = '1.7.18';

		const file = __FILE__;

		private $default_options = array(
			'role_display_name'             => 'Admin',
			'cap_gravityforms_view_entries' => 1,
			'cap_gravityforms_edit_forms'   => 0,
		);

		protected $missing;
		protected $validation;
		protected $pro;
		protected $updater;
		protected $upgrade;
		protected $settings;
		protected $settings_installed;
		protected $plugins_settings;
		protected $themes_settings;
		protected $unsupported_themes_settings;
		protected $users_settings;
		protected $admin_menu_tools_settings;
		protected $wp_admin;
		protected $bootstrap;
		protected $encryption;
		protected $settings_api;

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @since  0.0.0
		 * @return Simple_Client_Dashboard A single instance of this class.
		 */
		public static function get_instance() {
			if ( null === self::$single_instance ) {
				self::$single_instance = new self();
			}

			return self::$single_instance;
		}

		// private $pro;

		/*
		--------------------------------------------*
		* Constructor
		*--------------------------------------------*/

		/**
		 * Initializes the plugin by setting localization, filters, and administration functions.
		 * Private constructor to prevent instantiation from outside the class.
		 */
		function __construct() {
		}

		/**
		 * Add hooks and filters.
		 *
		 * @since 0.0.0
		 */
		public function hooks() {

			$this->plugins_loaded();
			$this->check_scd_version_update();
			// Load JavaScript and stylesheets
			add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts_and_styles' ), 10 );
			add_action( 'wpmu_new_blog', array( $this, 'add_role_to_blog' ) );
			add_action( 'updated_' . self::slug . '_option', array( $this, 'updated_option' ), 10, 3 );
			add_action( 'deleted_' . self::slug . '_option', array( $this, 'deleted_option' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 999 );
			add_action( 'admin_init', array( $this, 'create_role_if_missing' ), 10 );
			add_action( 'admin_init', array( $this, 'prevent_network_admin_access' ), 10 );
			add_action( 'admin_init', array( $this, 'cleanup_dashboard_widgets' ), 20 );

			add_action( 'rest_api_init', array( $this, 'rest_api_init' ), 0 );
		}

		/**
		 * Check for updates
		 *
		 * @since 1.7.2
		 */
		public function check_scd_version_update() {
			$site_version = get_site_option( 'td-webmaster-user-role-version' );
			if ( $site_version != self::version ) {
				update_site_option( 'td-webmaster-user-role-version', self::version );
				$this->update_webmaster_caps();
			}
		}

		/**
		 * Attach other plugin classes to the base plugin class.
		 *
		 * @since 1.7.2
		 */
		public function plugin_classes() {
			$classes = array(
				'missing'                     => 'SCD_Missing',
				'validation'                  => 'SCD_Validation',

				'pro'                         => 'SCD_Pro',

				'updater'                     => 'SCD_Updater',

				'upgrade'                     => 'SCD_Upgrade',

				'settings'                    => 'SCD_Settings',
				'settings_installed'          => 'SCD_Settings_Installed',

				'plugins_settings'            => 'SCD_Plugins_Settings',
				'themes_settings'             => 'SCD_Themes_Settings',
				'unsupported_themes_settings' => 'SCD_Unsupported_Themes_Settings',
				'users_settings'              => 'SCD_Users_Settings',
				'admin_menu_tools_settings'   => 'SCD_Admin_Menu_Tools_Settings',

				'wp_admin'                    => 'SCD_Wp_Admin',
				'bootstrap'                   => 'SCD_Bootstrap',

				'encryption'                  => 'SCD_Encryption',

			// NO API CLASSES SHOULD BE HERE (should be defined in rest_api_init hook)
			);

			foreach ( $classes as $variable_name => $class_name ) {
				if ( class_exists( $class_name ) ) {
					$this->$variable_name = new $class_name( $this );
				}
			}
		} // END OF PLUGIN CLASSES FUNCTION

		/**
		 * Attach other plugin modules to the base plugin class.
		 *
		 * @since 1.7.2
		 */
		public function plugin_modules() {
			$classes = array(
				/* Load Core Modules */
				'plugins'         => 'SCD_Plugins',
				'themes'          => 'SCD_Themes',
				'users'           => 'SCD_Users',
				'tools'           => 'SCD_Tools',

				/* Load 3rd Party Modules */
				'acf'             => 'SCD_ACF',
				'cf7'             => 'SCD_Cf7',
				'events_calendar' => 'SCD_Events_Calendar',
				'gravity_forms'   => 'SCD_Gravity_Forms',
				'itsec'           => 'SCD_Itsec',
				'jetpack'         => 'SCD_Jetpack',
				'learndash'       => 'SCD_LearnDash',
				'redirection'     => 'SCD_Redirection',
				'sgcachepress'    => 'SCD_SGCachePress',
				'woocommerce'     => 'SCD_WooCommerce',
				'wpai'            => 'SCD_WPAI',
				'yoast'           => 'SCD_Yoast',
				'wordfence'       => 'SCD_Wordfence',
				'ninja_forms'     => 'SCD_Ninja_Forms',
				'simple_css'      => 'SCD_SIMPLE_CSS',
				'table_press'     => 'SCD_TABLE_PRESS',
				'businss_profile' => 'SCD_BUSINESS_PROFILE',
				'draw_attention'  => 'SCD_DRAW_ATTENTION',

			);

			foreach ( $classes as $variable_name => $class_name ) {
				if ( class_exists( $class_name ) ) {
					// $this->$variable_name = new $class_name( $this );
					new $class_name( $this );
				}
			}
		} // END OF PLUGIN CLASSES FUNCTION

		/**
		 * Attach other plugin classes to the base plugin class.
		 *
		 * @since 0.0.2
		 */
		public function rest_api_init() {
			$classes = array(
				'settings_api' => 'SCD_Settings_Api',
			);

			foreach ( $classes as $variable_name => $class_name ) {
				if ( class_exists( $class_name ) ) {
					$this->$variable_name = new $class_name( $this );
				}
			}
		}

		/**
		 * Magic getter for our object.
		 *
		 * @since 1.7.2
		 *
		 * @param  string $field Field to get.
		 * @throws Exception     Throws an exception if the field is invalid.
		 * @return mixed         Value of the field.
		 */
		public function __get( $field ) {
			switch ( $field ) {
				case 'version':
					return self::version;
				case 'name':
				case 'file':
				case 'slug':
				case 'url':
				case 'path':
				case 'pro':
				case 'settings':
				case 'settings_api':
				case 'settings_installed':
				case 'plugins_settings':
				case 'themes_settings':
				case 'unsupported_themes_settings':
				case 'users_settings':
				case 'admin_menu_tools_settings':
				case 'encryption':
				case 'validation':
				case 'wp_admin':
				case 'bootstrap':
				case 'missing':
					if ( property_exists( $this, $field ) && ! is_null( $this->$field ) ) {
						return $this->$field;
					} else {
						return $this->missing;
					}
				default:
					return $this->missing;
			}
		}

		/**
		 * Include a file from the includes directory.
		 *
		 * @since 1.7.2
		 *
		 * @param  string $filename Name of the file to be included.
		 * @return boolean          Result of include call.
		 */
		public static function include_file( $filename ) {
			$file = self::dir( $filename . '.php' );
			if ( file_exists( $file ) ) {
				return include_once $file;
			}
			return false;
		}

		/**
		 * This plugin's directory.
		 *
		 * @since 1.7.2
		 *
		 * @param  string $path (optional) appended path.
		 * @return string       Directory and path.
		 */
		public static function dir( $path = '' ) {
			static $dir;
			$dir = $dir ? $dir : trailingslashit( __DIR__ );
			return $dir . $path;
		}

		/**
		 * This plugin's url.
		 *
		 * @since 1.7.2
		 *
		 * @param  string $path (optional) appended path.
		 * @return string       URL and path.
		 */
		public static function url( $path = '' ) {
			static $url;
			$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
			return $url . $path;
		}

		public function plugins_loaded() {

			include __DIR__ . '/includes/class-exception.php';
			include __DIR__ . '/includes/class-base-module.php';

			// Initialize plugin classes.
			$this->plugin_classes();

			// Initialize plugin modules.
			$this->plugin_modules();

			load_plugin_textdomain( 'webmaster-user-role', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

			do_action( 'scd_loaded' );
		}

		public function activate() {
		}


		public function deactivate() {
			$this->remove_webmaster_role( is_multisite() );
		}

		public function remove_webmaster_role( $network_wide ) {
			if ( $network_wide ) {
				$blogs = $this->_blogs();
				foreach ( $blogs as $blog_id ) {
					switch_to_blog( $blog_id );
					remove_role( 'webmaster' );
					restore_current_blog();
				}
			} else {
				remove_role( 'webmaster' );
			}
		}

		public function add_webmaster_role( $network_wide ) {
			if ( $network_wide ) {
				$blogs = $this->_blogs();
				foreach ( $blogs as $blog_id ) {
					switch_to_blog( $blog_id );
					$capabilities = $this->capabilities();
					add_role( 'webmaster', $this->get_option( 'role_display_name' ), $capabilities );
					restore_current_blog();
				}
			} else {
				$capabilities = $this->capabilities();
				add_role( 'webmaster', $this->get_option( 'role_display_name' ), $capabilities );
			}
		}

		public function update_webmaster_caps() {
			$is_multisite = is_multisite();
			$this->remove_webmaster_role( $is_multisite );
			$this->add_webmaster_role( $is_multisite );
		}


		/*
		--------------------------------------------*
		* Core Functions
		*---------------------------------------------*/

		public static function get_edition() {
			if ( class_exists( 'SCD_Updater' ) ) {
				return 'pro';
			}
			return 'basic';
		}


		public static function current_user_is_webmaster() {
			if ( is_multisite() && is_super_admin() ) {
				return false;
			}
			return current_user_can( 'webmaster' );
		}

		public static function get_config() {
			global $webmaster_user_role_config;
			if ( is_array( $webmaster_user_role_config ) ) {
				return $webmaster_user_role_config;
			}

			$webmaster_user_role_config = scd()->settings->get();

			return $webmaster_user_role_config;
		}

		function capabilities() {
			$admin_role   = get_role( 'administrator' );
			$capabilities = $admin_role->capabilities;
			unset( $capabilities['level_10'] );
			unset( $capabilities['update_core'] );
			unset( $capabilities['install_plugins'] );
			unset( $capabilities['activate_plugins'] );
			unset( $capabilities['update_plugins'] );
			unset( $capabilities['edit_plugins'] );
			unset( $capabilities['delete_plugins'] );
			unset( $capabilities['install_themes'] );
			unset( $capabilities['update_themes'] );
			unset( $capabilities['switch_themes'] );
			unset( $capabilities['edit_themes'] );
			unset( $capabilities['delete_themes'] );
			unset( $capabilities['list_users'] );
			unset( $capabilities['create_users'] );
			unset( $capabilities['add_users'] );
			unset( $capabilities['edit_users'] );
			unset( $capabilities['delete_users'] );
			unset( $capabilities['remove_users'] );
			unset( $capabilities['promote_users'] );

			$capabilities['editor'] = 1; // Needed for 3rd party plugins that check explicitly for the "editor" role (looking at you NextGen Gallery)

			if ( is_multisite() ) {
				$capabilities['administrator'] = 1;
				$capabilities['level_10']      = 1;
			}

			$webmaster_user_role_config = self::get_config();
			if ( ! empty( $webmaster_user_role_config ) ) {
				foreach ( $webmaster_user_role_config as $config_key => $config_value ) {
					if ( strpos( $config_key, 'webmaster_cap' ) !== false && is_array( $config_value ) ) {
							$capabilities = wp_parse_args( $config_value, $capabilities );
					}
				}
			}

			$capabilities = apply_filters( 'td_webmaster_capabilities', $capabilities );
			return $capabilities;
		}

		function create_role_if_missing() {
			$wp_roles = new WP_Roles();
			if ( $wp_roles->is_role( 'webmaster' ) ) {
				return;
			}

			$this->update_webmaster_caps();
		}

		function prevent_network_admin_access() {
			if ( is_network_admin() && ! is_super_admin( get_current_user_id() ) ) {
				wp_redirect( admin_url() );
				exit();
			}
		}

		function cleanup_dashboard_widgets() {
			if ( $this->current_user_is_webmaster() ) {
				// remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
				remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
				remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
				remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
				remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
				remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
			}
		}

		function admin_menu() {
			if ( $this->current_user_is_webmaster() ) {
				$webmaster_user_role_config = self::get_config();
				remove_menu_page( 'branding' );
				if ( is_object( $webmaster_user_role_config ) && empty( $webmaster_user_role_config->sections ) ) {
					return;
				}

				if ( empty( $webmaster_user_role_config['webmaster_admin_menu_tools_settings']['options-general.php'] ) ) {
					remove_menu_page( 'options-general.php' );
				}
				if ( empty( $webmaster_user_role_config['webmaster_admin_menu_sucuri']['sucuriscan'] ) ) {
					remove_menu_page( 'sucuriscan' );
				}
				if ( empty( $webmaster_user_role_config['webmaster_admin_menu_tools_settings']['tools.php'] ) ) {
					remove_menu_page( 'tools.php' );
				}
			}
		}

		function add_role_to_blog( $blog_id ) {
			switch_to_blog( $blog_id );
			$capabilities = $this->capabilities();
			add_role( 'webmaster', 'Admin', $capabilities );
			restore_current_blog();
		}

		function updated_option( $option, $oldvalue, $newValue ) {
			if ( $option == 'role_display_name' || strpos( 'cap_', $option ) !== false ) {
				$this->update_webmaster_caps();
			}
		}

		function deleted_option( $option ) {
			if ( $option == 'role_display_name' || strpos( 'cap_', $option ) !== false ) {
				$this->update_webmaster_caps();
			}
		}

		function get_option( $option ) {
			// Allow plugins to short-circuit options.
			$pre = apply_filters( 'pre_' . self::slug . '_option_' . $option, false );
			if ( false !== $pre ) {
				return $pre;
			}

			$option = trim( $option );
			if ( empty( $option ) ) {
				return false;
			}

			$saved_options = get_option( self::slug . '_options' );

			if ( isset( $saved_options[ $option ] ) ) {
				$value = $saved_options[ $option ];
			} else {
				$saved_options = ( empty( $saved_options ) ) ? array() : $saved_options;
				$saved_options = array_merge( $this->default_options, $saved_options );
				$value         = $saved_options[ $option ];
			}

			return apply_filters( self::slug . 'option_' . $option, $value );
		}

		function update_option( $option, $newValue ) {
			$option = trim( $option );
			if ( empty( $option ) ) {
				return false;
			}

			if ( is_object( $newvalue ) ) {
				$newvalue = clone $newvalue;
			}

			$oldvalue = $this->get_option( $option );
			$newvalue = apply_filters( 'pre_update_' . self::slug . '_option_' . $option, $newvalue, $oldvalue );

			// If the new and old values are the same, no need to update.
			if ( $newvalue === $oldvalue ) {
				return false;
			}

			$_newvalue = $newvalue;
			$newvalue  = maybe_serialize( $newvalue );

			do_action( 'update_' . self::slug . '_option', $option, $oldvalue, $_newvalue );

			$options = get_option( self::slug . '_options' );
			if ( empty( $options ) ) {
				$options = array( $option => $newValue );
			} else {
				$options[ $option ] = $newValue;
			}
			update_option( self::slug . '_options', $options );

			do_action( 'update_' . self::slug . "_option_{$option}", $oldvalue, $_newvalue );
			do_action( 'updated_' . self::slug . '_option', $option, $oldvalue, $_newvalue );

			return true;
		}

		function delete_option( $option ) {
			do_action( 'delete_' . self::slug . '_option', $option );
			$options = get_option( self::slug . '_options' );
			if ( ! isset( $options[ $option ] ) ) {
				return false;
			}
			unset( $options[ $option ] );

			$result = update_option( self::slug . '_options', $options );

			if ( $result ) {
				do_action( 'delete_' . self::slug . "_option_$option", $option );
				do_action( 'deleted_' . self::slug . '_option', $option );
				return true;
			}
			return false;
		}



		/*
		--------------------------------------------*
		* Private Functions
		*---------------------------------------------*/

		function _blogs() {
			global $wpdb;
			$blogs = $wpdb->get_col(
				$wpdb->prepare(
					"
			SELECT blog_id
			FROM {$wpdb->blogs}
			WHERE site_id = %d
			AND spam = '0'
			AND deleted = '0'
			AND archived = '0'
			ORDER BY registered DESC
		",
					$wpdb->siteid
				)
			);

			return $blogs;
		}

		/**
		 * Registers and enqueues stylesheets for the administration panel and the
		 * public facing site.
		 */
		public function register_scripts_and_styles() {
			if ( is_admin() ) {
				// $this->load_file( self::slug . '-admin-script', '/js/admin.js', true );
				$this->load_file( self::slug . '-admin-style', '/css/admin.css' );
			} else {
				// $this->load_file( self::slug . '-script', '/js/widget.js', true );
				// $this->load_file( self::slug . '-style', '/css/widget.css' );
			} // end if/else
		} // end register_scripts_and_styles

		/**
		 * Helper function for registering and enqueueing scripts and styles.
		 *
		 * @name      The  ID to register with WordPress
		 * @file_path The path to the actual file
		 * @is_script Optional argument for if the incoming file_path is a JavaScript source file.
		 */
		private function load_file( $name, $file_path, $is_script = false ) {

			$url  = plugins_url( $file_path, __FILE__ );
			$file = plugin_dir_path( __FILE__ ) . $file_path;

			if ( file_exists( $file ) ) {
				if ( $is_script ) {
					wp_register_script( $name, $url, array( 'jquery' ) );
					wp_enqueue_script( $name );
				} else {
					wp_register_style( $name, $url );
					wp_enqueue_style( $name );
				} // end if
			} // end if
		} // end load_file
	} // end class
} // end class_exists()


/**
 * Grab the Simple_Client_Dashboard object and return it.
 * Wrapper for Simple_Client_Dashboard::get_instance().
 *
 * @since  1.7.2
 * @return Simple_Client_Dashboard  Singleton instance of plugin class.
 */
function scd() {
	return Simple_Client_Dashboard::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', array( scd(), 'hooks' ), 0 );

// Activation and deactivation.
register_activation_hook( __FILE__, array( scd(), 'activate' ) );
register_deactivation_hook( __FILE__, array( scd(), 'deactivate' ) );
