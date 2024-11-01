<?php
/**
 * REDCap
 *
 * @package user-sync\Core
 */

namespace MoUserSync\Core\redcap;

require_once 'RedCapEnums.php';

/**
 * Class contains REDCap sync related functions.
 */
class RedCap {

	/**
	 * Variable stores the attribute mapping of the server.
	 *
	 * @var array
	 */
	private $attribute_mapping;

	/**
	 * Variable contains url of API call.
	 *
	 * @var string
	 */
	private $api_url;

	/**
	 * Variable contains api token from REDCap.
	 *
	 * @var string
	 */
	private $api_token;

	/**
	 * Variable stores the API body.
	 *
	 * @var array Body of api call.
	 */
	private $request_body;

	/**
	 * REDCap constructor function to set API related variables.
	 *
	 * @param string $remote_server Remote Server.
	 * @param array  $custom_attributes Custom Attributes.
	 */
	public function __construct( $remote_server, $custom_attributes ) {
		$this->api_url           = $remote_server->api_url;
		$this->api_token         = $remote_server->api_token;
		$custom_attributes       = maybe_unserialize( $custom_attributes );
		$this->attribute_mapping = $custom_attributes;
	}

	/**
	 * Function to create user in REDCap.
	 *
	 * @param int    $user_id WordPress user id to sync in remote.
	 * @param string $user_data WordPress user's data to sync in remote.
	 * @return bool
	 */
	public function create_user( $user_id, $user_data = '' ) {
		$data = array();
		$user = (array) get_user_by( 'ID', $user_id )->data;
		if ( empty( $user ) ) {
			return false;
		}
		foreach ( $this->attribute_mapping as $redcap_attribute_name => $wordpress_attribute_name ) {
			if ( isset( $user[ $wordpress_attribute_name ] ) ) {
				$data[ $redcap_attribute_name ] = $user[ $wordpress_attribute_name ];
			} else {
				$data[ $redcap_attribute_name ] = get_user_meta( $user_id, $wordpress_attribute_name, true );
			}
		}

		$this->request_body = wp_json_encode( array( $data ) );

		$args = array( 'body' => http_build_query( $this->create_api_args(), '', '&' ) );
		return wp_remote_post( $this->api_url, $args );
	}

	/**
	 * Function generates api arguments to be send it REDCap.
	 *
	 * @return array
	 */
	private function create_api_args() {
		$fields = array(
			'token'   => $this->api_token,
			'content' => 'user',
			'format'  => 'json',
			'data'    => $this->request_body,
		);
		return $fields;
	}

	/**
	 * Function gets the attribute mapping of REDCap.
	 *
	 * @return array
	 */
	public function get_required_attribute() {
		return RedCapEnums::REQUIREDATTRIBUTESREDCAP;
	}
}
