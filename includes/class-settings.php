<?php
/**
 * Simple Client Dashboard Settings.
 *
 * @since   1.7.2
 * @package Simple_Client_Dashboard
 */

/**
 * Simple Client Dashboard Settings.
 *
 * @since 1.7.2
 */
class SCD_Settings {

	/**
	 * Parent plugin class.
	 *
	 * @since 1.7.2
	 *
	 * @var Simple_Client_Dashboard
	 */
	protected $plugin = null;

	protected $option_name = 'scd_settings_json';

	protected $defaults = null;

	protected $schema;

	protected $settings;

	protected $sections_with_values;

	/**
	 * Constructor
	 *
	 * @since  1.7.2
	 * @param  object $plugin Main plugin object.
	 * @return void
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks
	 *
	 * @since  1.7.2
	 * @return void
	 */
	public function hooks() {
	}

	public function get_schema() {
		if ( ! empty( $this->schema ) ) {
			return $this->schema;
		}
		$schema = apply_filters( 'simple_client_dashboard/config/sections', array() );

		$this->schema = $schema;

		return $this->schema;
	}

	public function get() {
		if ( ! empty( $this->settings ) ) {
			return $this->settings;
		}

		$settings_json = get_option( $this->option_name, json_encode( array() ) );
		$settings      = json_decode( $settings_json, true );

		$schema = $this->get_schema();

		// If fields are missing from settings add their default values
		foreach ( $schema as $key => $section ) {
			if ( isset( $section['fields'] ) && ! empty( $section['active'] ) ) {
				foreach ( $section['fields'] as $field ) {
					if ( isset( $field['id'] ) && ! isset( $settings[ $field['id'] ] ) ) {
						$settings[ $field['id'] ] = $field['default'];

					} elseif ( isset( $field['id'] ) && isset( $settings[ $field['id'] ] ) ) {
						if ( is_array( $field['default'] ) && is_array( $settings[ $field['id'] ] ) ) {

							// Merge the two arrays so we preserve the settings values while ensuring any newly added key has been appended
							$settings[ $field['id'] ] = array_merge( $field['default'], $settings[ $field['id'] ] );

							// If a key does not exist anymore in the default options; while it does in the settings, clean it up
							$settings[ $field['id'] ] = array_intersect_key( $settings[ $field['id'] ], $field['default'] );

						} else {
							$settings[ $field['id'] ] = $settings[ $field['id'] ];
						}
					}
				}
			}
		}

		$this->settings = $settings;
		return $this->settings;
	}

	public function get_sections_schema_with_values() {
		if ( ! empty( $this->sections_with_values ) ) {
			return $this->sections_with_values;
		}

		$schema = $this->get_schema();

		$settings = $this->get();

		foreach ( $schema as $key => $section ) {
			if ( isset( $section['fields'] ) ) {
				foreach ( $section['fields'] as $field_key => $field ) {
					if ( isset( $field['id'] ) && isset( $settings[ $field['id'] ] ) ) {
						$schema[ $key ]['fields'][ $field_key ]['values'] = $settings[ $field['id'] ];
					}
					// Handle inactive sections that don't/won't have values in the settings
					elseif ( empty( $section['active'] ) && ! isset( $settings[ $field['id'] ] ) ) {
						$schema[ $key ]['fields'][ $field_key ]['values'] = $field['default'];
					}
				}
			}
		}

		$this->sections_with_values = $schema;
		return $this->sections_with_values;
	}

	public function update( $new_settings ) {
		$new_settings['last_updated'] = gmdate( 'Y-m-d H:i:s' );

		$old_settings = $this->get();

		$new_settings = apply_filters( 'scd/settings/new_settings', $new_settings, $old_settings );

		return $this->set( $new_settings );
	}

	public function update_section( $section_key, $new_settings ) {
	}

	private function set( $new_settings ) {
		$this->settings = $new_settings;
		update_option( $this->option_name, json_encode( $this->settings ) );

		return $this->settings;
	}
}

abstract class SCD_Settings_Schema {


	protected $schema          = array();
	protected $computed_schema = array();
	protected $slug;
	protected $parent_slug;

	abstract function get_schema();
	public function get_computed_schema() {
		return array();
	}

	public function __construct() {
		if ( empty( $this->slug ) ) {
         die( 'no slug defined for: '.get_class( $this ).' (this slug will be used as the key to save in to the settings array)' ); // phpcs:ignore
		}

		$this->parent_hooks();
	}

	public function parent_hooks() {
		add_filter( 'scd_settings_schema', array( $this, 'filter_settings_schema' ) );
		add_filter( 'scd_settings_computed_schema', array( $this, 'filter_settings_computed_schema' ) );
	}

	public function filter_settings_schema( $schema ) {
		$schema[ $this->slug ] = $this->get_schema();

		return $schema;
	}

	public function filter_settings_computed_schema( $schema ) {
		$schema[ $this->slug ] = $this->get_computed_schema();

		return $schema;
	}

	public function get_field_defaults() {
		if ( ! empty( $this->defaults ) ) {
			return $this->defaults;
		}

		$defaults = array();
		$schema   = $this->get_schema();
		if ( empty( $schema['fields'] ) ) {
			return $defaults;
		}

		$defaults = array_combine(
			wp_list_pluck( $schema['fields'], 'name' ),
			wp_list_pluck( $schema['fields'], 'default_value' )
		);

		$this->defaults = $defaults;
		return $this->defaults;
	}

	public function get() {
		if ( $this->slug !== 'installed' ) {
			if ( ! $this->plugin->settings_installed->is_enabled( $this->slug ) ) {
				return null;
			}

			if ( ! empty( $this->parent_slug ) ) {
				if ( ! $this->plugin->settings_installed->is_enabled( $this->parent_slug ) ) {
					return null;
				}
			}
		}

		return $this->plugin->settings->get()[ $this->slug ];
	}

	public function reset_to_defaults( $re_enable_feature = true ) {
		$defaults = $this->get_field_defaults();
		if ( ! empty( $re_enable_feature ) ) {
			$defaults['enabled'] = $re_enable_feature;
		}
		return $this->update( $defaults );
	}

	public function update( $new_settings ) {
		// $old_settings = $this->get();
		// $new_settings = apply_filters( 'update_'.$this->slug.'_settings', $new_settings, $old_settings );
		return $this->plugin->settings->update_section( $this->slug, $new_settings );
	}

	public function validate( $new_settings ) {
		if ( empty( $new_settings ) ) {
			return;
		}
		$wp_error = new WP_Error();
		$schema   = $this->get_schema()['fields'];
		foreach ( $new_settings as $field => $value ) {
			if ( isset( $schema[ $field ]['validate_callback'] ) ) {
				$validation_result = call_user_func( $schema[ $field ]['validate_callback'], $value );
				if ( $validation_result !== true ) {
					if ( empty( $wp_error->errors ) ) {
						// populate the general error message
						$wp_error->add( 422, $this->slug . ' ' . __( 'settings are invalid' ) );
					}
					// add the field specific error message - $validation_result is already translated
					$wp_error->add( 422, array( $field, $validation_result ) );
				}
			}
		}
		if ( ! empty( $wp_error->errors ) ) {
			return $wp_error;
		}
	}
}
