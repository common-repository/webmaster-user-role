<?php
/**
 * Simple Client Dashboard Users Settings.
 *
 * @since   1.7.2
 * @package Simple_Client_Dashboard
 */

/**
 * Simple Client Dashboard Users Settings.
 *
 * @since 1.7.2
 */
class SCD_Users_Settings extends SCD_Settings_Schema {

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

	protected $slug = 'users';

	public function get_schema() {
		if ( ! empty( $this->schema ) ) {
			return $this->schema;
		}

		$this->schema = array(
			'version' => '2023-04-2',
			'fields'  => array(
				'enabled'       => array(
					'name'          => 'enabled',
					'default_value' => true,
				),

				'list_users'    => array(
					'name'          => 'list_users',
					'default_value' => false,
				),

				'create_users'  => array(
					'name'          => 'create_users',
					'default_value' => false,
				),

				'delete_users'  => array(
					'name'          => 'delete_users',
					'default_value' => false,
				),

				'promote_users' => array(
					'name'          => 'promote_users',
					'default_value' => false,
				),

				'edit_users'    => array(
					'name'          => 'edit_users',
					'default_value' => false,
				),

				'remove_users'  => array(
					'name'          => 'remove_users',
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
