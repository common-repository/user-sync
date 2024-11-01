<?php
/**
 * Thinkific
 *
 * @package user-sync\Core
 */

namespace MoUserSync\Core\thinkific;

require_once 'ThinkificEnums.php';

/**
 * Thinkific sync additional functions related to authorization.
 *
 * @package MoUserSync\Core
 */
class ThinkificAuthorizationHandler {

	/**
	 * Subdomain for Thinkific.
	 *
	 * @var integer
	 */
	private $sub_domain;

	/**
	 * API key of Thinkific.
	 *
	 * @var integer
	 */
	private $api_key;

	/**
	 * Constructor function to set API related variables.
	 *
	 * @param integer $sub_domain Sub domain.
	 * @param string  $api_key API key.
	 */
	public function __construct( $sub_domain, $api_key ) {
		$this->sub_domain = $sub_domain;
		$this->api_key    = $api_key;
	}

	/**
	 * Authorization function to headers.
	 *
	 * @return array
	 */
	public function getauthorization_headers() {
		$headers = array(
			'Content-Type'     => 'application/json',
			'X-Auth-API-Key'   => $this->api_key,
			'X-Auth-Subdomain' => $this->sub_domain,
		);
		return $headers;
	}
}
