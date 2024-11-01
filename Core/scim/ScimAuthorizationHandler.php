<?php


namespace MoUserSync\Core;
require_once "TableauEnums.php";


class ScimAuthorizationHandler
{
    private $remoteServerID;
    private $bearerToken;

    public function __construct($remoteServerID, $bearerToken)
    {
        $this->remoteServerID = $remoteServerID;
        $this->bearerToken = $bearerToken;
    }

    public function getAuthorizationHeaders()
    {
        $headers = array(
            'Authorization' => 'Bearer ' . $this->bearerToken . '',
            "Content-Type" => "application/scim+json",
        );
        return $headers;
    }
}