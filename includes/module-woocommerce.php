<?php
class SCD_WooCommerce extends SCD_Base_Module {

	public $plugin;

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
		add_action( 'wp_user_dashboard_setup', array( $this, 'woocommerce_hide_orders_widget' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'woocommerce_hide_orders_widget' ) );
	}

	function is_active() {
		return ( class_exists( 'WooCommerce' ) );
	}

	function settings_section( $sections ) {
		$section = array(
			'title'    => __( 'WooCommerce', 'webmaster-user-role' ),
			'dashicon' => 'dashicons dashicons-cart',
			'id'       => 'woocommerce',
			'active'   => true,
			'fields'   => array(
				array(
					'id'       => 'webmaster_caps_woocommerce_caps',
					'type'     => 'checkbox',
					'title'    => __( 'WooCommerce Capabilities', 'webmaster-user-role' ),
					'subtitle' => __( 'Webmaster (Admin) users can', 'webmaster-user-role' ),

					'options'  => array(
						'manage_woocommerce'       => __( 'Manage WooCommerce Settings', 'webmaster-user-role' ),
						'edit_view_products'       => __( 'Edit & View Products', 'webmaster-user-role' ),
						'edit_shop_coupons'        => __( 'Edit & View Coupons', 'webmaster-user-role' ),
						'edit_shop_orders'         => __( 'Edit & View Orders', 'webmaster-user-role' ),
						'edit_shop_payments'       => __( 'Edit & View Payments', 'webmaster-user-role' ),
						'edit_shop_discounts'      => __( 'Edit & View Discounts', 'webmaster-user-role' ),
						'view_woocommerce_reports' => __( 'View Reports', 'webmaster-user-role' ),
					),

					'default'  => array(
						'manage_woocommerce'       => '1',
						'edit_view_products'       => '1',
						'edit_shop_coupons'        => '1',
						'edit_shop_orders'         => '1',
						'edit_shop_payments'       => '1',
						'edit_shop_discounts'      => '1',
						'view_woocommerce_reports' => '1',

					),
				),
			),
		);

		$sections[] = $this->filter_section( $section );
		return $sections;
	} //end settings_section function

	function capabilities( $capabilities ) {

		// Get Config
		$webmaster_user_role_config = $this->plugin->settings->get();
		if ( ! is_array( $webmaster_user_role_config ) || empty( $webmaster_user_role_config['webmaster_caps_woocommerce_caps'] ) ) {
			return $capabilities;
		}

		// This is still needed to completey hide the Product page menu item when edit_view_products is falsy
		$capabilities['moderate_comments'] = (int) $webmaster_user_role_config['webmaster_caps_woocommerce_caps']['edit_view_products'];

		/* Add WooCommerce Capabilities */
		$woo_caps = $this->get_woocommerce_capabilities();
		foreach ( $woo_caps as $woo_cap_key => $woo_cap_array ) {
			foreach ( $woo_cap_array as $key => $woo_cap ) {
				$currentCap               = $this->get_setting_capability_map( $woo_cap_key, $webmaster_user_role_config['webmaster_caps_woocommerce_caps'] );
				$capabilities[ $woo_cap ] = $currentCap;
			}
		}

		return $capabilities;
	}

	function woocommerce_hide_orders_widget() {

		$webmaster_user_role_config = $this->plugin->settings->get();
		if ( ! is_array( $webmaster_user_role_config ) || ! Simple_Client_Dashboard::current_user_is_webmaster() ) {
			return;
		}

		if ( empty( $webmaster_user_role_config['webmaster_caps_woocommerce_caps']['edit_shop_orders'] )
			|| empty( $webmaster_user_role_config['webmaster_caps_woocommerce_caps']['view_woocommerce_reports'] )
		) {
			remove_meta_box( 'woocommerce_dashboard_status', 'dashboard', 'normal' );
		}
	} //end hide_orders_widget


	public function get_woocommerce_capabilities() {
		$capabilities = array();

		// Core
		$capabilities['manage_woocommerce']       = array(
			'manage_woocommerce',
		);
		$capabilities['view_woocommerce_reports'] = array(
			'view_woocommerce_reports',
		);

		$capability_types = array( 'product', 'shop_order', 'shop_coupon', 'shop_payment', 'shop_discount' );

		foreach ( $capability_types as $capability_type ) {

			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

				// Stats
				"view_{$capability_type}_stats",
				"import_{$capability_type}s",

				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms",
			);
		}

		return $capabilities;
	}

	private function get_setting_capability_map( $key, $config ) {
		$mapping = array(
			'manage_woocommerce'       => 'manage_woocommerce',
			'product'                  => 'edit_view_products',
			'shop_coupon'              => 'edit_shop_coupons',
			'shop_order'               => 'edit_shop_orders',
			'shop_payment'             => 'edit_shop_payments',
			'shop_discount'            => 'edit_shop_discounts',
			'view_woocommerce_reports' => 'view_woocommerce_reports',
		);

		return (int) $config[ $mapping[ $key ] ];
	}
}
