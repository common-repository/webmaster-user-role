<?php
/**
 * Simple Client Dashboard Themes Settings.
 *
 * @since   1.7.2
 * @package Simple_Client_Dashboard
 */

/**
 * Simple Client Dashboard Themes Settings.
 *
 * @since 1.7.2
 */
class SCD_Themes_Settings extends SCD_Settings_Schema {

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

	protected $slug = 'themes';

	public function get_schema() {
		if ( ! empty( $this->schema ) ) {
			return $this->schema;
		}

		$this->schema = array(
			'version' => '2023-04-2',
			'fields'  => array(
				'enabled'            => array(
					'name'          => 'enabled',
					'default_value' => true,
				),

				'edit_theme_options' => array(
					'name'          => 'edit_theme_options',
					'default_value' => false,
				),

				'install_themes'     => array(
					'name'          => 'install_themes',
					'default_value' => false,
				),

				'update_themes'      => array(
					'name'          => 'update_themes',
					'default_value' => false,
				),

				'switch_themes'      => array(
					'name'          => 'switch_themes',
					'default_value' => false,
				),

				'edit_themes'        => array(
					'name'          => 'edit_themes',
					'default_value' => false,
				),

				'delete_themes'      => array(
					'name'          => 'delete_themes',
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
