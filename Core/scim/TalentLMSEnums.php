<?php


namespace MoUserSync\Core;


class TalentLMSEnums
{

    const REQUIREDATTRIBUTESSCIMTALENTLMS = array(
        "Login" => ["user_login", "user-table"],
        "Firstname" => ["first_name", "user-meta-table"],
        "Lastname" => ["last_name", "user-meta-table"],
        "Email" => ["user_email", "user-table"]
    );

}