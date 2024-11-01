<?php
/**
 * REDCap
 *
 * @package user-sync\Core
 */

namespace MoUserSync\Core\redcap;

/**
 * Enums required for REDCap sync.
 *
 * @package MoUserSync\Core
 */
class RedCapEnums {


	const REQUIREDATTRIBUTESREDCAP = array(
		'username'  => array(
			'user_login',
			'user-table',
			'type' => 'string',
		),
		'firstname' => array(
			'last_name',
			'user-table',
			'type' => 'string',
		),
		'lastname'  => array(
			'first_name',
			'user-table',
			'type' => 'string',
		),
		'email'     => array(
			'user_email',
			'user-table',
			'type' => 'string',
		),
	);
}
