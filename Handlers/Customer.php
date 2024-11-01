<?php

use MoUserSync\Views\PluginConstants;

class mo_user_sync_customer
{
    private $defaultCustomerKey = "16555";
    private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";


    public function mo_user_sync_submit_contact_us($email, $phone, $query, $call_setup, $demo_request = false, $integration_selected = '')
    {
        $url = MoUserSyncEnums::HOSTNAME . '/moas/rest/customer/contact-us';
        $current_user = wp_get_current_user();

        if ($call_setup)
            $query = '[Call Request - WP Remote User Sync] ' . $query;
        else {
            if ($demo_request)
                $query = '[Demo Request - WP Remote User Sync] ' . $query . ' <br><br>Requested Integration : ' . $integration_selected;
            else
                $query = '[WP Remote User Sync] ' . $query;
        }

        $fields = array(
            'firstName' => $current_user->user_firstname,
            'lastName' => $current_user->user_lastname,
            'company' => sanitize_text_field($_SERVER['SERVER_NAME']),
            'email' => $email,
            'ccEmail' => 'samlsupport@xecurify.com',
            'phone' => $phone,
            'query' => $query
        );

        $field_string = json_encode($fields);

        $headers = array("Content-Type" => "application/json", "charset" => "UTF-8", "Authorization" => "Basic");
        $args = array(
            'method' => 'POST',
            'body' => $field_string,
            'timeout' => '10',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => $headers
        );
        $response = $this->mo_user_sync_wp_remote_call($url, $args);
        return $response;
    }

    function mo_user_sync_wp_remote_call($url, $args = array(), $is_get = false)
    {

        if (!$is_get)
            $response = wp_remote_post($url, $args);
        else
            $response = wp_remote_get($url, $args);

        if (!is_wp_error($response)) {
            return $response['body'];
        } else {
            // self::mo_sf_sync_show_error_message('Unable to connect to the Internet. Please try again.');
            return false;
        }
    }

    public function mo_user_sync_send_email_alert($email, $phone, $message, $demo_request = false)
    {

        $url = MoUserSyncEnums::HOSTNAME . '/moas/api/notify/send';

        $customerKey = $this->defaultCustomerKey;
        $apiKey = $this->defaultApiKey;

        $currentTimeInMillis = self::mo_user_sync_get_timestamp();
        $currentTimeInMillis = number_format($currentTimeInMillis, 0, '', '');
        $stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
        $hashValue = hash("sha512", $stringToHash);
        $fromEmail = 'no-reply@xecurify.com';
        $subject = "Feedback: WP User Sync";
        if ($demo_request)
            $subject = "DEMO REQUEST:WP User Sync";
        $site_url = site_url();

        global $user;
        $user = wp_get_current_user();

        $query = '[ WP-User-Sync ]: ' . $message;

        $content = '<div >Hello, <br><br>First Name :' . $user->user_firstname . '<br><br>Last  Name :' . $user->user_lastname . '   <br><br>Company :<a href="' . sanitize_text_field($_SERVER['SERVER_NAME']) . '" target="_blank" >' . sanitize_text_field($_SERVER['SERVER_NAME']) . '</a><br><br>Phone Number :' . $phone . '<br><br>Email :<a href="mailto:' . $email . '" target="_blank">' . $email . '</a><br><br>Query :' . $query . '</div>';

        $fields = array(
            'customerKey' => $customerKey,
            'sendEmail' => true,
            'email' => array(
                'customerKey' => $customerKey,
                'fromEmail' => $fromEmail,
                'bccEmail' => $fromEmail,
                'fromName' => 'Xecurify',
                'toEmail' => 'samlsupport@xecurify.com',
                'subject' => $subject,
                'content' => $content
            ),
        );
        $field_string = json_encode($fields);

        $headers = array(
            "Content-Type" => "application/json",
            "Customer-Key" => $customerKey,
            "Timestamp" => $currentTimeInMillis,
            "Authorization" => $hashValue
        );
        $args = array(
            'method' => 'POST',
            'body' => $field_string,
            'timeout' => '15',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => $headers
        );
        $response = wp_remote_post($url, $args);
        return !is_wp_error($response) ? json_decode($response['body'], true) : implode($response->get_error_messages());

    }

    public function mo_user_sync_get_timestamp()
    {
        $url = MoUserSyncEnums::HOSTNAME . '/moas/rest/mobile/get-timestamp';
        $response = wp_remote_post($url, array());
        return !is_wp_error($response) ? json_decode($response['body'], true) : implode($response->get_error_messages());

    }

}