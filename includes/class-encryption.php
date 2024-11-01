<?php
/**
 * Simple Client Dashboard Encryption.
 *
 * @since   1.7.2
 * @package Simple_Client_Dashboard
 */

/**
 * Simple Client Dashboard Encryption.
 *
 * @since 1.7.2
 */
class SCD_Encryption {

	const SALT = 'SCDdGtpUU9yalNxQUR1dGsyWW1nbE95VcDU4c1VszSGd5c1ZVR1NIUT09ODdxUzVWNStRnRqWXhHSjqCTV54';

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

	public static function get_encryption_key() {
		$encryption_key = get_option( 'scd_ec' );
		if ( ! empty( $encryption_key ) ) {
			return $encryption_key;
		}

		$encryption_key = base64_encode( openssl_random_pseudo_bytes( 32 ) );
		update_option( 'scd_ec', $encryption_key );
		return $encryption_key;
	}

	public static function encrypt( $data, $key = null ) {
		if ( empty( $key ) ) {
			$key = self::get_encryption_key();
		}
		// Remove the base64 encoding from our key
		$encryption_key = base64_decode( $key );
		// Generate an initialization vector
		$iv = openssl_random_pseudo_bytes( openssl_cipher_iv_length( 'aes-256-cbc' ) );
		// Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
		$encrypted = openssl_encrypt( $data, 'aes-256-cbc', $encryption_key, 0, $iv );
		// The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (::)
		return base64_encode( $encrypted . '::' . $iv );
	}

	public static function decrypt( $data, $key = null ) {
		if ( empty( $key ) ) {
			$key = self::get_encryption_key();
		}
		// Remove the base64 encoding from our key
		$encryption_key = base64_decode( $key );
		// To decrypt, split the encrypted data from our IV - our unique separator used was "::"
		list($encrypted_data, $iv) = explode( '::', base64_decode( $data ), 2 );
		return openssl_decrypt( $encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv );
	}
}
