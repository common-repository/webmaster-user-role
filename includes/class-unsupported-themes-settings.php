<?php
/**
 * Simple Client Dashboard Unsupported Themes Settings.
 *
 * @since   1.7.2
 * @package Simple_Client_Dashboard
 */

/**
 * Simple Client Dashboard Unsupported Themes Settings.
 *
 * @since 1.7.2
 */
class SCD_Unsupported_Themes_Settings extends SCD_Settings_Schema {

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

	protected $slug = 'unsupported_themes';

	public function get_schema() {
		if ( ! empty( $this->schema ) ) {
			return $this->schema;
		}

		$this->schema = array(
			'version' => '2023-04-2',
			'fields'  => array(
				'enabled'                    => array(
					'name'          => 'enabled',
					'default_value' => true,
				),

				'access_theme_options_panel' => array(
					'name'          => 'access_theme_options_panel',
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
