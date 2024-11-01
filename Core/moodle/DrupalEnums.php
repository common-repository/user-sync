<?php
/**
 * Drupal
 *
 * @package user-sync\Core
 */

namespace MoUserSync\Core\moodle;

/**
 * Class Drupal
 *
 * @package MoUserSync\Core
 *
 * Enums required for Drupal sync.
 */
class DrupalEnums {

	const REQUIREDATTRIBUTESSCIMDRUPAL = array(
		'username'   => array( 'first_name', 'user-table' ),
		'first_name' => array( 'last_name', 'user-table' ),
		'last_name'  => array( 'title', 'user-table' ),
		'email'      => array( 'user_email', 'user-table' ),
		'password'   => array( 'user_pass', 'user-table' ),
	);
}
