<?php
/**
 * MailChimp
 *
 * @package user-sync\Core\mailchimp
 */

namespace MoUserSync\Core\mailchimp;

use MoUserSync\Handler\EncryptionHandler;

require_once 'MailChimpEnums.php';

/**
 * MailChimp sync additional functions related to authorization.
 */
class MailChimpAuthorizationHandler {

	/**
	 * Variable stores the API key from MailChimp.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Constructor function to set API related variables.
	 *
	 * @param string $api_key Client ID of Keycloak client.
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Function gets the Headers for API.
	 *
	 * @return array
	 */
	public function get_authorization_header() {
		$credentials = 'key:' . $this->api_key;

        // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Required.
		$credentials = 'Basic ' . base64_encode( $credentials );

		$headers = array(
			'Authorization' => $credentials,
			'Content-Type'  => 'application/json',
		);
		return $headers;
	}
}
