<?php
/**
 * Simple Client Dashboard Bootstrap.
 *
 * @since   1.7.2
 * @package Simple_Client_Dashboard
 */

/**
 * Simple Client Dashboard Bootstrap.
 *
 * @since 1.7.2
 */
class SCD_Bootstrap {

	/**
	 * Parent plugin class
	 *
	 * @var   class
	 * @since 1.7.2
	 */
	protected $plugin = null;

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

	public static function maybe_remove_query_args( $url ) {
		$pos_of_question_mark = strpos( $url, '?' );
		if ( false === $pos_of_question_mark ) {
			return $url;
		}

		$url = substr( $url, 0, $pos_of_question_mark );
		return $url;
	}

	public static function maybe_fix_protocol( $url, $desired_protocol = null ) {
		$pos_of_colon_slash_slash = strpos( $url, '://' );
		if ( $pos_of_colon_slash_slash === false ) {
			return $url;
		}

		if ( empty( $desired_protocol ) ) {
			if ( defined( 'SCD_PROTOCOL' ) && str_to_lower( SCD_PROTOCOL ) === 'https' ) {
				$desired_protocol = 'https';
			} else {
				$desired_protocol = 'http';
			}
			if ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ) {
				$desired_protocol = 'https';
			} elseif ( ! empty( $_SERVER['REDIRECT_HTTPS'] ) && $_SERVER['REDIRECT_HTTPS'] !== 'off' ) {
				$desired_protocol = 'https';
			} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
				$desired_protocol = 'https';
			} elseif ( ! empty( $_SERVER['protocol'] ) ) {
				$desired_protocol = strtolower( substr( $_SERVER['SERVER_PROTOCOL'], 0, 5 ) ) == 'https' ? 'https' : 'http';
			}
		}

		$url = $desired_protocol . '://' . substr( $url, $pos_of_colon_slash_slash + 3 );

		return $url;
	}

	public static function maybe_fix_www_prefix( $url, $should_be_www = null ) {
		$pos_of_colon_slash_slash = strpos( $url, '://' );
		if ( $pos_of_colon_slash_slash === false ) {
			return $url;
		}

		if ( $should_be_www === null ) {
			if ( strpos( $_SERVER['HTTP_HOST'], 'www.' ) === 0 ) {
				$should_be_www = true;
			} else {
				$should_be_www = false;
			}
		}

		if ( ! empty( $should_be_www ) ) {
			$url = str_replace(
				array(
					'://',
					'://www.www.',
				),
				array(
					'://www.',
					'://www.',
				),
				$url
			);
		} else {
			$url = str_replace(
				array(
					'://www.www.',
					'://www.',
				),
				array(
					'://',
					'://',
				),
				$url
			);
		}

		return $url;
	}

	public function get_api_vars() {

		$admin_static_url = $this->plugin->url( 'admin-app/dist/static' );
		if ( defined( 'WP_SITEURL' ) && WP_SITEURL === 'http://localhost:8080' ) {
			$admin_static_url = $this->plugin->url( 'admin-app/public/static' );
		}

		$api_array = array(
			'prefix'           => rest_get_url_prefix(),
			'root'             => untrailingslashit( self::maybe_fix_protocol( self::maybe_remove_query_args( home_url( rest_get_url_prefix() . '/scd/v1' ) ) ) ),
			'admin_static_url' => self::maybe_fix_protocol( $admin_static_url ),
			'nonce'            => wp_create_nonce( 'wp_rest' ),
			'public_nonce'     => self::create_nonce( 'wp_rest' ),
			'home_url'         => self::maybe_fix_www_prefix( self::maybe_fix_protocol( home_url() ) ),
			'site_url'         => self::maybe_fix_www_prefix( self::maybe_fix_protocol( site_url() ) ),
			'network_site_url' => self::maybe_fix_www_prefix( self::maybe_fix_protocol( network_site_url() ) ),
			'admin_url'        => self::maybe_fix_www_prefix( self::maybe_fix_protocol( admin_url() ) ),
			'site_icon_url'    => self::maybe_fix_www_prefix( self::maybe_fix_protocol( get_site_icon_url() ) ),
			// 'locale' => SCD_Translation::get_locale(),
			'locale'           => 'en_US',
			'scd_version'      => Simple_Client_Dashboard::version,
			'scd_edition'      => Simple_Client_Dashboard::get_edition(),
		);

		// $api_array['embed_url'] = $api_array['root'] . '/embed-inner';

		$user_array = array();
		// $user_array['capabilities'] = $this->plugin->capabilities->current_user_all_caps();
		$user_array['user_id'] = get_current_user_id();

		return array(
			'api'          => $api_array,
			'user'         => $user_array,
			'translations' => $this->get_translations(),
		);
	}

	public static function create_nonce( $action = -1, $user_id = false ) {
		if ( $user_id !== false ) {
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}
		}

		return substr( wp_hash( $action . '|' . $user_id, 'nonce' ), -12, 10 );
	}

	public static function verify_nonce( $nonce, $action = -1, $user_id = false ) {
		$nonce = (string) $nonce;

		if ( empty( $nonce ) ) {
			return false;
		}

		$expected = self::create_nonce( $action, $user_id );

		$token = wp_get_session_token();
		$i     = wp_nonce_tick();

		if ( hash_equals( $expected, $nonce ) ) {
			return 1;
		}

		return false;
	}

	public static function nonce_permissions_check( $request ) {
		if ( empty( $request->get_headers()['x_wp_nonce']['0'] ) ) {
			return false;
		}

		$nonce    = $request->get_headers()['x_wp_nonce']['0'];
		$is_valid = wp_verify_nonce( $nonce, 'wp_rest' );

		if ( $is_valid ) {
			return $is_valid;
		}

		if ( empty( $request->get_headers()['x_public_nonce']['0'] ) ) {
			return false;
		}

		$nonce    = $request->get_headers()['x_public_nonce']['0'];
		$is_valid = self::verify_nonce( $nonce, 'wp_rest' );

		return $is_valid;
	}

	public function get_translations() {
		include $this->plugin->dir( 'languages/admin-app-translations.php' );
		return $translations;
	}
}
