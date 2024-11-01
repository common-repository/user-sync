<?php


namespace MoUserSync\Core;

use MoUserSync\Handler\EncryptionHandler;

require_once "NextCloudEnums.php";
require_once MO_USER_SYNC_PLUGIN_DIR . "Handlers" . DIRECTORY_SEPARATOR . "EncryptionHandler.php";


class NextCloudAuthorizationHandler
{
    private $username;
    private $password;

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function getAuthorizationHeaders()
    {
        $this->password = EncryptionHandler::mo_user_sync_decrypt_data($this->password, $this->username);
        $credentials = $this->username . ':' . $this->password;
        $credentials = base64_encode($credentials);
        $credentials = "Basic " . $credentials;

        $headers = array(
            'OCS-APIRequest' => ' true',
            'Authorization' => $credentials,
            'Content-Type' => 'application/x-www-form-urlencoded'
        );
        return $headers;
    }
}