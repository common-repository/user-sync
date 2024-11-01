<?php


namespace MoUserSync\Core;


class TableauEnums
{
    const REQUIREDATTRIBUTESSCIMTABLEAU = array(
        "firstName" => ["first_name", "user-table"],
        "familyName" => ["last_name", "user-table"],
        "title" => ["title", "user-table"],
        "login_value" => ["user_email", "user-table"]
    );
}