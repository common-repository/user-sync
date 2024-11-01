<?php
/**
 * Moodle
 *
 * @package user-sync\Core
 */

namespace MoUserSync\Core\moodle;

/**
 * Class MoodleEnums
 *
 * @package MoUserSync\Core
 *
 * Enums required for Moodle sync.
 */
class MoodleEnums {

	const REQUIREDATTRIBUTESSCIMMOODLE = array(
		'username'   => array( 'first_name', 'user-table' ),
		'first_name' => array( 'last_name', 'user-table' ),
		'last_name'  => array( 'title', 'user-table' ),
		'email'      => array( 'user_email', 'user-table' ),
		'password'   => array( 'user_pass', 'user-table' ),
	);
}
