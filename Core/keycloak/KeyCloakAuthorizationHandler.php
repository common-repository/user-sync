<?php
/**
 * Keycloak
 *
 * @package user-sync\Core\keycloak
 */

namespace MoUserSync\Core\keycloak;

/**
 * Keycloak sync additional functions related to authorization.
 */
class KeyCloakAuthorizationHandler {

	/**
	 * Client ID of the Keycloak client.
	 *
	 * @var string
	 */
	private $client_id;

	/**
	 * Client secret of Keycloak client.
	 *
	 * @var string
	 */
	private $client_secret;

	/**
	 * Realm endpoint for keycloak server.
	 *
	 * @var string
	 */
	private $end_point;

	/**
	 * Keycloak raw API url.
	 *
	 * @var string
	 */
	private $token_url;

	/**
	 * Keycloak API url.
	 *
	 * @var string
	 */
	private $user_sync_url;

	/**
	 * Constructor function to set API related variables.
	 *
	 * KeyCloakAuthorizationHandler constructor.
	 *
	 * @param string $client_id Client ID of Keycloak client.
	 * @param string $client_secret Client secret of Keycloak client.
	 * @param string $end_point Realm endpoint for Keycloak server.
	 */
	public function __construct( $client_id, $client_secret, $end_point ) {
		$this->client_id     = $client_id;
		$this->client_secret = $client_secret;
		$this->end_point     = $end_point;
	}

	/**
	 * Function returns bearer token.
	 *
	 * @return string
	 */
	public function mo_user_sync_get_bearer_token_for_keycloak() {

		if ( empty( $this->token_url ) ) {
			$this->mo_user_sync_generate_api_urls_for_keycloak();
		}

		$args = array(
			'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
			'body'    => array(
				'grant_type'    => 'client_credentials',
				'client_id'     => $this->client_id,
				'client_secret' => $this->client_secret,
			),
		);

		$response = wp_remote_post( $this->token_url, $args );
		if ( is_wp_error( $response ) || ( 200 !== (int) $response['response']['code'] ) ) {
			return '';
		}

		$body         = json_decode( $response['body'] );
		$bearer_token = $body->access_token;

		return $bearer_token;
	}

	/**
	 * Function sets value for URL variables.
	 */
	private function mo_user_sync_generate_api_urls_for_keycloak() {
		$response = wp_remote_get( $this->end_point );

		if ( ! is_wp_error( $response ) ) {
			$body = json_decode( $response['body'] );
		} else {
			return;
		}

		$this->user_sync_url = $body->issuer;
		$this->token_url     = $body->token_endpoint;

		if ( '' !== $this->user_sync_url ) {
			if ( strpos( $this->user_sync_url, '/users' ) === false ) {
				$this->user_sync_url = $this->user_sync_url . '/users';
			}
		}

		$pos                 = strpos( $this->user_sync_url, '/realms' );
		$this->user_sync_url = substr_replace( $this->user_sync_url, '/admin', $pos, 0 );
	}

	/**
	 * Function creates user in Keycloak.
	 *
	 * @param int    $user_id WordPress user id.
	 * @param string $bearer_token Bearer Token.
	 * @param array  $customer_data WordPress user data.
	 * @return bool
	 */
	public function mo_user_sync_register_user_in_keycloak( $user_id, $bearer_token, $customer_data ) {
		$args     = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $bearer_token,
			),
			'body'    => wp_json_encode( $customer_data ),
		);

		$response = wp_remote_post( $this->user_sync_url, $args );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$res_code = $response['response']['code'];

		if ( 201 !== (int) $res_code ) {
			return false;
		}

		return true;
	}
}
