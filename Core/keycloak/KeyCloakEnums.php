<?php
/**
 * Keycloak
 *
 * @package user-sync\Core\keycloak
 */

namespace MoUserSync\Core\keycloak;

/**
 * Class KeyCloakEnums
 *
 * @package MoUserSync\Core
 *
 * Enums required for Keycloak sync.
 */
class KeyCloakEnums {

	const REQUIRED_ATTRIBUTES = array(
		'username'  => 'user_login',
		'email'     => 'first_name',
		'firstName' => 'last_name',
		'lastName'  => 'user_email',
		'password'  => 'user_pass',
	);
}
