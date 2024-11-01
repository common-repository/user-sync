<?php


namespace MoUserSync\Core;


class NextCloudEnums
{
    const CREATE = "/ocs/v1.php/cloud/users";
    const TIMESTAMP = "moas/rest/mobile/get-timestamp";

    const REQUIREDATTRIBUTESNEXTCLOUD = array(
        "userid" => ["user_login", "user-table", "type" => "string"],
        "email" => ["first_name", "user-table", "type" => "string"],
        "displayName" => ["last_name", "user-table", "type" => "string"],
    );
}