<?php
/**
 * MailChimp
 *
 * @package user-sync\Core\mailchimp
 */

namespace MoUserSync\Core\mailchimp;

require_once 'MailChimpAuthorizationHandler.php';
require_once 'MailChimpEnums.php';

/**
 * Class contains MailChimp sync related functions.
 */
class MailChimp {

	/**
	 * Variable stores the attribute mapping of the MailChimp.
	 *
	 * @var array
	 */
	private $attribute_mapping;

	/**
	 * Variable stores the API server value of MailChimp.
	 *
	 * @var string
	 */
	private $api_server;

	/**
	 * Variable stores the API key from MailChimp.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Variable stores the audience list from MailChimp.
	 *
	 * @var string
	 */
	private $audience_list;

	/**
	 * The remote server of the configuration.
	 *
	 * @var string
	 */
	private $provisioning_type;

	/**
	 * Variable stores authorization header of API call.
	 *
	 * @var array
	 */
	private $authorization_headers;

	/**
	 * Variable stores the API body.
	 *
	 * @var array
	 */
	private $request_body;

	/**
	 * Variable store the API url.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * MailChimp constructor function to set API related variables.
	 *
	 * @param string $remote_server MailChimp API details.
	 * @param array  $custom_attributes MailChimp attributes.
	 */
	public function __construct( $remote_server, $custom_attributes ) {
		$this->api_server            = $remote_server->api_server;
		$this->api_key               = $remote_server->api_key;
		$this->audience_list         = $remote_server->audience_list;
		$this->authorization_headers = ( new MailChimpAuthorizationHandler( $this->api_key ) )->get_authorization_header();
		$this->attribute_mapping     = maybe_unserialize( $custom_attributes );
		$this->provisioning_type     = $remote_server->provisioning_type;
	}

	/**
	 * Function to create user in MailChimp.
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

		foreach ( $this->attribute_mapping as $mailchimp_attribute_name => $wordpress_attribute_name ) {
			if ( isset( $user[ $wordpress_attribute_name ] ) ) {
				$data[ $mailchimp_attribute_name ] = $user[ $wordpress_attribute_name ];
			} else {
				$meta_attribute                    = get_user_meta( $user_id, $wordpress_attribute_name, true );
				$data[ $mailchimp_attribute_name ] = is_array( $meta_attribute ) ? '' : $meta_attribute;
			}
		}

		$send_query_array = array(
			'email_address' => $data['email_address'],
			'status'        => 'subscribed',
			'merge_fields'  => array(
				'FNAME' => $data['FNAME'],
				'LNAME' => $data['LNAME'],
			),
		);

		$this->url = MailChimpEnums::HTTPS . $this->api_server . MailChimpEnums::API_URL . $this->audience_list . MailChimpEnums::MEMBERS;

		$this->request_body = $send_query_array;
		return wp_remote_post( $this->url, $this->create_api_args() );
	}

	/**
	 * Function creates API arguments for MailChimp Sync.
	 *
	 * @return array
	 */
	private function create_api_args() {
		$args = array(
			'method'  => 'POST',
			'body'    => wp_json_encode( $this->request_body, true ),
			'headers' => $this->authorization_headers,
		);
		return $args;
	}

	/**
	 * Function gets the attribute mapping of MailChimp.
	 *
	 * @return array
	 */
	public function get_required_attribute() {
		return MailChimpEnums::REQUIRED_ATTRIBUTES;
	}
}
