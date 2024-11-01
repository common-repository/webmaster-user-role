<?php
/**
 * Simple Client Dashboard Validation.
 *
 * @since   1.7.2
 * @package Simple_Client_Dashboard
 */

/**
 * Simple Client Dashboard Validation.
 *
 * @since 1.7.2
 */
class SCD_Validation {

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

	public static function validate_numeric( $value ) {
		return ( is_numeric( $value ) || __( 'Expected a numeric value but received ' ) . gettype( $value ) );
	}

	public static function validate_string( $value ) {
		return ( ( ! is_numeric( $value ) && is_string( $value ) ) || __( 'Expected a string value but received ' ) . gettype( $value ) );
	}
}
