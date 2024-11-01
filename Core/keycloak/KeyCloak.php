<?php
/**
 * File contains Keycloak sync related class.
 *
 * @package user-sync\Core\keycloak
 */

namespace MoUserSync\Core\keycloak;

use MoUserSync\Utilities\DBUtils;

require_once 'KeyCloakEnums.php';
require_once 'KeyCloakAuthorizationHandler.php';

/**
 * Class contains Keycloak sync related functions.
 */
class KeyCloak {

	/**
	 * Variable stores the attribute mapping of the server.
	 *
	 * @var array
	 */
	private $attribute_mapping;

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
	 * The remote server of the configuration.
	 *
	 * @var string
	 */
	private $provisioning_type;

	/**
	 * Realm endpoint for keycloak server.
	 *
	 * @var string
	 */
	private $end_point;

	/**
	 * KeyCloak constructor function to set API related variables.
	 *
	 * @param string $remote_server Keycloak API details.
	 * @param array  $custom_attributes Keycloak attributes.
	 */
	public function __construct( $remote_server, $custom_attributes ) {
		$this->id                = $remote_server->id;
		$this->client_id         = $remote_server->client_id;
		$this->client_secret     = $remote_server->client_secret;
		$this->end_point         = $remote_server->end_point;
		$custom_attributes       = maybe_unserialize( $custom_attributes );
		$this->attribute_mapping = $custom_attributes;
		$this->provisioning_type = $remote_server->provisioning_type;
	}

	/**
	 * Function to create user in Keycloak's client.
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

		foreach ( $this->attribute_mapping as $keycloak_attribute_name => $wordpress_attribute_name ) {
			if ( isset( $user[ $wordpress_attribute_name ] ) ) {
				$data[ $keycloak_attribute_name ] = $user[ $wordpress_attribute_name ];
			} else {
				$meta_attribute                   = get_user_meta( $user_id, $wordpress_attribute_name, true );
				$data[ $keycloak_attribute_name ] = is_array( $meta_attribute ) ? '' : $meta_attribute;
			}
		}

		$keycloak_register_object = new KeyCloakAuthorizationHandler( $this->client_id, $this->client_secret, $this->end_point );
		$bearer_token             = $keycloak_register_object->mo_user_sync_get_bearer_token_for_keycloak();

		if ( empty( $bearer_token ) ) {
			return false;
		}

		$password = isset( $user_data['user_pass'] ) ? $user_data['user_pass'] : wp_generate_password();

		$customer_data = array(
			'username'    => $data['username'],
			'email'       => $data['email'],
			'firstName'   => ( $data['firstName'] ? $data['firstName'] : 'first_name' ),
			'lastName'    => ( $data['lastName'] ? $data['lastName'] : 'last_name' ),
			'enabled'     => 'true',
			'attributes'  => array(
				'city'  => 'USER_CITY',
				'state' => 'USER_STATE',
			),
			'credentials' => array(
				array(
					'type'      => 'password',
					'value'     => $password,
					'temporary' => false,
				),
			),
		);

		return $keycloak_register_object->mo_user_sync_register_user_in_keycloak( $user_id, $bearer_token, $customer_data );
	}

	/**
	 * Function gets the attribute mapping of Keycloak.
	 *
	 * @return array
	 */
	public function get_required_attribute() {
		return KeyCloakEnums::REQUIRED_ATTRIBUTES;
	}
}
