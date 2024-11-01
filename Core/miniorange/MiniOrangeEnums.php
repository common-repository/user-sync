<?php


namespace MoUserSync\Core;


class MiniOrangeEnums
{

    const HOSTNAME = "https://login.xecurify.com/";
    const UPDATE = "moas/api/admin/users/update";
    const CREATE = "moas/api/admin/users/create";
    const DELETE = "moas/api/admin/users/delete";
    const DISABLE = "moas/api/admin/users/disable";
    const TIMESTAMP = "moas/rest/mobile/get-timestamp";

    const OPTIONALFIELDS = array(
        "groups" => ["type" => "array"],
        "alternateEmail" => ["type" => "string"],
        "sendEmail" => ["type" => "string"]
    );

    const REQUIREDATTRIBUTESMINIORANGE = array(
        "username" => ["user_login", "user-table", "type" => "string"],
        "email" => ["first_name", "user-table", "type" => "string"],
        "firstName" => ["last_name", "user-table", "type" => "string"],
        "lastName" => ["user_email", "user-table", "type" => "string"]
    );
}