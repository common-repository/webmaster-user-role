<?php
/**
 * Simple Client Dashboard Missing.
 *
 * @since   1.7.2
 * @package Simple_Client_Dashboard
 */

/**
 * Simple Client Dashboard Missing.
 *
 * @since 1.7.2
 */
class SCD_Missing {

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

	public function __get( $name ) {
		return $this;
	}

	public function __call( $name, $args ) {
		return null;
	}

	public static function __callStatic( $name, $args ) {
		return null;
	}
}
