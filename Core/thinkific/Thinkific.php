<?php
/**
 * Thinkific
 *
 * @package user-sync\Core
 */

namespace MoUserSync\Core\thinkific;

require_once 'ThinkificAuthorizationHandler.php';
require_once 'ThinkificEnums.php';

/**
 * Class contains Thinkific sync related functions.
 */
class Thinkific {

	/**
	 * Attribute mapping.
	 *
	 * @var array
	 */
	private $attribute_mapping;

	/**
	 * Authorization head body.
	 *
	 * @var string[]
	 */
	private $authorization_headers;

	/**
	 * Body for api call.
	 *
	 * @var array
	 */
	private $request_body;

	/**
	 * Thinkific constructor function to set API related variables.
	 *
	 * @param string $remote_server Remote Server.
	 * @param array  $custom_attributes Custom Attributes.
	 */
	public function __construct( $remote_server, $custom_attributes ) {
		$this->authorization_headers = ( new ThinkificAuthorizationHandler( $remote_server->Subdomain, $remote_server->API_Key ) )->getauthorization_headers();
		$custom_attributes           = maybe_unserialize( $custom_attributes );
		$this->attribute_mapping     = $custom_attributes;
	}

	/**
	 * Function to create user in Thinkific.
	 *
	 * @param int    $user_id WordPress user id to sync in remote.
	 * @param string $user_data WordPress user's data to sync in remote.
	 * @return bool
	 */
	public function create_user( $user_id, $user_data = '' ) {
		foreach ( $this->attribute_mapping as $thinkific_attribute_name => $wordpress_attribute_name ) {
			if ( isset( $user[ $wordpress_attribute_name ] ) ) {
				$data[ $thinkific_attribute_name ] = $user[ $wordpress_attribute_name ];
			} else {
				$data[ $thinkific_attribute_name ] = get_user_meta( $user_id, $wordpress_attribute_name, true );
			}
		}

		$send_query_array = array(
			'email'      => array( $data['email'] ),
			'first_name' => $data['first_name'],
			'last_name'  => $data['last_name'],
			'password'   => $data['password'],
		);

		$this->request_body = $send_query_array;
		return wp_remote_post( ThinkificEnums::HOSTNAME, $this->create_api_args() );
	}

	/**
	 * Function generates api arguments to be send it Thinkific.
	 *
	 * @return array
	 */
	private function create_api_args() {
		$args = array(
			'method'      => 'POST',
			'body'        => wp_json_encode( $this->request_body, true ),
			'timeout'     => '10',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $this->authorization_headers,
		);
		return $args;
	}

	/**
	 * Function gets the attribute mapping of Thinkific.
	 *
	 * @return array
	 */
	public function get_required_attribute() {
		return ThinkificEnums::REQUIREDATTRIBUTESTHINKIFIC;
	}

}
