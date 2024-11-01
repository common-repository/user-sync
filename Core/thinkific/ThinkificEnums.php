<?php
/**
 * SCIM
 *
 * @package user-sync\Core
 */

namespace MoUserSync\Core\thinkific;

/**
 * Class ThinkificEnums
 *
 * @package MoUserSync\Core
 *
 * Enums for tableau sync.
 */
class ThinkificEnums {

	const HOSTNAME = 'https://api.thinkific.com/api/public/v1/users';

	const REQUIREDATTRIBUTESTHINKIFIC = array(
		'email'      => array(
			'first_name',
			'user-table',
			'type' => 'string',
		),
		'first_name' => array(
			'last_name',
			'user-table',
			'type' => 'string',
		),
		'last_name'  => array(
			'user_email',
			'user-table',
			'type' => 'string',
		),
		'password'   => array(
			'user_pass',
			'user-table,',
			'type' => 'string',
		),
	);
}
