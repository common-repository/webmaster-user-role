<?php
/**
 * Simple Client Dashboard Exception.
 *
 * @since   1.7.2
 * @package Simple_Client_Dashboard
 */

/**
 * Simple Client Dashboard Exception.
 *
 * @since 1.7.2
 */
class SCD_Exception extends Exception {

	public $feature;
	protected $code;

	public function __construct( string $message = null, string $code = null ) {
	}
}

// class SCD_Mailchimp_Exception extends SCD_Exception {
// public $feature = 'mailchimp';
// }
