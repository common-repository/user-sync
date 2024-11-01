<?php
/**
 * MailChimp
 *
 * @package user-sync\Core\mailchimp
 */

namespace MoUserSync\Core\mailchimp;

/**
 * Class MailChimpEnums
 *
 * @package MoUserSync\Core
 *
 * Enums required for MailChimp sync.
 */
class MailChimpEnums {

	const HTTPS   = 'https://';
	const API_URL = '.api.mailchimp.com/3.0/lists/';
	const MEMBERS = '/members';

	const REQUIRED_ATTRIBUTES = array(
		'email_address' => 'email',
		'FNAME'         => 'first_name',
		'LNAME'         => 'last_name',
	);
}
