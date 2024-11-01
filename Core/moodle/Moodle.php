<?php

namespace MoUserSync\Core\moodle;

require_once 'MoodleEnums.php';
require_once 'MoodleAuthorizationHandler.php';
require_once 'DrupalEnums.php';

use MoUserSync\Core\scim\Mo_User_Sync_Scim_Core;
use MoUserSync\Core\moodle\MoodleAuthorizationHandler;

/**
 * Class contains MailChimp sync related functions.
 */
class Moodle extends Mo_User_Sync_Scim_Core {

	/**
	 * Variable stores the attribute mapping of the Moodle.
	 *
	 * @var array
	 */
	private $attribute_mapping;

	/**
	 * Variable stores the url for Moodle sync.
	 *
	 * @var array
	 */
	private $url;

	/**
	 * Variable stores the server name for sync.
	 *
	 * @var array
	 */
	private $provisioning_type;

	/**
	 * Variable stores authorization header of API call.
	 *
	 * @var array authorization headers.
	 */
	private $authorization_headers;

	/**
	 * Variable stores body of the API call.
	 *
	 * @var array body for api call.
	 */
	private $request_body;

	/**
	 * Moodle/Drupal constructor function to set API related variables.
	 *
	 * @param string $remote_server Moodle/Drupal API details.
	 * @param array  $custom_attributes Moodle/Drupal attributes.
	 */
	public function __construct( $remote_server, $custom_attributes ) {
		$this->id                    = $remote_server->id;
		$this->bearer_token          = $remote_server->bearer_token;
		$this->url                   = $remote_server->remote_server_id;
		$this->authorization_headers = MoodleAuthorizationHandler::get_authorization_headers( $this->bearer_token );
		$this->attribute_mapping     = maybe_unserialize( $custom_attributes );
		$this->provisioning_type     = $remote_server->provisioning_type;
	}

	/**
	 * Function to create user in Moodle/Drupal.
	 *
	 * @param int $user_id WordPress user id to sync in remote.
	 * @param int $user_data WordPress user data of newly created user.
	 * @return bool
	 */
	public function create_user( $user_id, $user_data = '' ) {
		$data        = array();
        $user_object = get_user_by( 'ID', $user_id );
		$user        = (array) $user_object->data;
		$role        = $user_object->roles;
		$schema      = self::get_schema( 'User' );

		if ( empty( $user ) ) {
			return false;
		}

		foreach ( $this->attribute_mapping as $scim_attribute_name => $wordpress_attribute_name ) {
			if ( isset( $user[ $wordpress_attribute_name ] ) ) {
				$data[ $scim_attribute_name ] = $user[ $wordpress_attribute_name ];
			} else {
				$data[ $scim_attribute_name ] = get_user_meta( $user_id, $wordpress_attribute_name, true );
			}
		}

		$last_name  = isset( $data['familyName'] ) ? $data['familyName'] : $data['last_name'];
		$first_name = isset( $data['firstName'] ) ? $data['firstName'] : $data['first_name'];
		$title      = isset( $data['title'] ) ? $data['title'] : $data['username'];
		$email      = isset( $data['login_value'] ) ? $data['login_value'] : $data['email'];
		$password   = isset( $user_data['user_pass'] ) ? $user_data['user_pass'] : wp_generate_password();
		$url        = $this->url;

		if ( !empty( $url ) ) {
			if ( strpos( $url, '/scim/Users' ) === false ) {
				$url = $url . '/Users';
			}
		}

		$url = esc_url_raw( $url );

		$send_query_array = array(
			'schemas'     => array( $schema ),
			'id'          => strval( $user['ID'] ),
			'meta'        => array( 'resourceType' => 'User' ),
			'name'        => array(
				'formatted'  => $last_name . ' ' . $first_name,
				'familyName' => $last_name,
				'givenName'  => $first_name,
			),
			'title'       => $title,
			'role'        => $role[0],
			'displayName' => $user['display_name'],
			'user_pass'   => $password,
			'userName'    => $title,
			'emails'      => array(
				( array(
					'primary' => true,
					'value'   => $email,
				) ),
			),
		);

		$this->request_body = $send_query_array;
		$scim_response      = wp_remote_post( $url, $this->create_scim_args() );
		if ( is_wp_error( $scim_response ) ) {
			return false;
		}

		return $scim_response;
	}

	/**
	 * Fucntion will create Headers arguments for API call.
	 *
	 * @param string $method HTTP method.
	 * @return array
	 */
	private function create_scim_args( $method = 'POST' ) {
		$args = array(
			'method'  => $method,
			'headers' => array(
				'Content-Type' => 'application/scim+json',
				'Accept'       => 'application/scim+json',
			),
			'timeout' => '10',
			'body'    => wp_json_encode( $this->request_body, true ),
		);

		$args['headers'] = array_merge( $args['headers'], $this->authorization_headers );
		return $args;
	}

	/**
	 * Function gets the attribute mapping of Moodle/Drupal.
	 *
	 * @return array
	 */
	public function get_required_attribute() {

		switch ( $this->provisioning_type ) {
			case 'Moodle':
				return MoodleEnums::REQUIREDATTRIBUTESSCIMMOODLE;
				break;
			case 'Drupal':
				return DrupalEnums::REQUIREDATTRIBUTESSCIMDRUPAL;
				break;
			default:
				return null;
				break;
		}
	}
}
