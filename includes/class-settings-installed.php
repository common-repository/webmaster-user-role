<?php
/**
 * Simple Client Dashboard Settings Installed.
 *
 * @since   1.7.2
 * @package Simple_Client_Dashboard
 */

/**
 * Simple Client Dashboard Settings Installed.
 *
 * @since 1.7.2
 */
class SCD_Settings_Installed extends SCD_Settings_Schema {

	/**
	 * Parent plugin class.
	 *
	 * @since 1.7.2
	 *
	 * @var Simple_Client_Dashboard
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since 1.7.2
	 *
	 * @param Simple_Client_Dashboard $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		parent::__construct();
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since 1.7.2
	 */
	public function hooks() {
	}

	protected $slug = 'installed';

	public function get_schema() {
		if ( ! empty( $this->schema ) ) {
			return $this->schema;
		}

		$this->schema = array(
			'version' => '2023-03-21',
			'fields'  => array(),
		);

		return $this->schema;
	}

	public function get_computed_schema() {
		if ( ! empty( $this->computed_schema ) ) {
			return $this->computed_schema;
		}

		$this->computed_schema = array(
			'fields' => array(
				'plugins' => array(
					'name'         => 'plugins',
					'get_function' => 'class_exists',
					'get_input'    => 'SCD_Plugins',
				),
				'themes'  => array(
					'name'         => 'themes',
					'get_function' => 'class_exists',
					'get_input'    => 'SCD_Themes',
				),

			),
		);

		return $this->computed_schema;
	}

	public function is_installed( $slug ) {
		$installed = $this->plugin->settings_installed->get();
		if ( ! empty( $installed[ $slug ] ) ) {
			return true;
		}

		return false;
	}

	public function is_enabled( $slug = '' ) {
		if ( empty( $slug ) ) {
			return false;
		}

		if ( ! $this->is_installed( $slug ) ) {
			return false;
		}

		if ( in_array(
			$slug,
			array(
				'global',
				'styles',
				'developer',
				'capacity',
			)
		)
		) {
			return true;
		}

		if ( ! empty( $this->plugin->$slug->parent_slug ) ) {
			if ( ! $this->is_enabled( $this->plugin->$slug->parent_slug ) ) {
				return false;
			}
		}

		$settings = $this->plugin->settings->get();
		if ( ! empty( $settings[ $slug ]['enabled'] ) ) {
			return true;
		}

		return false;
	}

	public function is_activated( $slug, $force_check = false ) {
		if ( ! $this->is_enabled( $slug ) ) {
			return false;
		}

		if ( ! method_exists( $this->plugin->$slug, 'is_activated' ) ) {
			return true;
		}

		$is_activated = $this->plugin->$slug->is_activated( $force_check );
		if ( empty( $is_activated ) ) {
			return false;
		}

		return true;
	}
}
