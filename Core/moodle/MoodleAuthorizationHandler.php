<?php
/**
 * Moodle
 *
 * @package user-sync\Core
 */

namespace MoUserSync\Core\moodle;

require_once 'MoodleEnums.php';

/**
 * Moodle sync additional functions related to authorization.
 */
class MoodleAuthorizationHandler {

	/**
	 * Authorization function to headers.
	 *
	 * @param string $bearer_token Bearer Token.
	 * @return array
	 */
	public static function get_authorization_headers( $bearer_token ) {
		$headers = array(
			'Authorization' => 'Bearer ' . $bearer_token . '',
		);
		return $headers;
	}
}
