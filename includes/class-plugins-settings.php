<?php
/**
 * Simple Client Dashboard Plugins Settings.
 *
 * @since   1.7.2
 * @package Simple_Client_Dashboard
 */

/**
 * Simple Client Dashboard Plugins Settings.
 *
 * @since 1.7.2
 */
class SCD_Plugins_Settings extends SCD_Settings_Schema {

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

	protected $slug = 'plugins';

	public function get_schema() {
		if ( ! empty( $this->schema ) ) {
			return $this->schema;
		}

		$this->schema = array(
			'version' => '2023-04-2',
			'fields'  => array(
				'enabled'          => array(
					'name'          => 'enabled',
					'default_value' => true,
				),

				'install_plugins'  => array(
					'name'          => 'install_plugins',
					'default_value' => false,
				),

				'activate_plugins' => array(
					'name'          => 'activate_plugins',
					'default_value' => false,
				),

				'update_plugins'   => array(
					'name'          => 'update_plugins',
					'default_value' => false,
				),

				'edit_plugins'     => array(
					'name'          => 'edit_plugins',
					'default_value' => false,
				),

				'delete_plugins'   => array(
					'name'          => 'delete_plugins',
					'default_value' => false,
				),

			),
		);

		return $this->schema;
	}

	public function update( $new_settings ) {
		$this->plugin->settings->update_section( $this->slug, $new_settings );
	}
}
