<?php

class MoUserSyncEnums
{

    public const HOSTNAME = "https://login.xecurify.com";
    public const PAGE_TITLE = "WP to Remote User Sync";
    public const MENU_SLUG = "wp_to_remote_user_sync";
    public const VERSION = "1.0.0";

    public const SERVER_GUIDES = array(
        "MiniOrange" => ["miniorangeServer", "wordpress-user-sync-for-third-party-apps"],
        "Tableau" => ["tableau", "tableau-automated-user-provisioning-into-wordpress-using-scim"],
        "TalentLMS LMS" => ["talent-lms", "remote-user-sync-talent-lms"],
        "NextCloud" => ["nextcloud", "wordpress-user-sync-for-third-party-apps"],
        "Okta" => ["okta", "wordpress-user-sync-for-third-party-apps"]
    );
    public const SERVER_LIST = array(
        "Tableau" => "SCIM",
        "TalentLMS" => "SCIM",
        "Okta" => "API",
        "MiniOrange" => "API",
        "NextCloud" => "API"
    );

    public const SERVER_LIST_ATTRIBUTES = array(
        "Tableau" => ["SCIM", "Tableau URL of the Remote Server :", "Tableau Bearer Token :"],
        "TalentLMS" => ["SCIM", "TalentLMS URL of the Remote Server :", "TalentLMS Bearer Token :"],
        "Okta" => ["API", "Okta Customer ID :", "Okta API key :"],
        "MiniOrange" => ["API", "MiniOrange Customer ID :", "MiniOrange API key :"],
        "NextCloud" => ["API-NextCloud", "NextCloud URL :", "NextCloud Username :"]
    );

    public const SERVER_LIST_ATTRIBTURES_FOR_DATABASE = array(
        "Tableau" => ["Remote_Server_ID", "Bearer_Token"],
        "TalentLMS" => ["Remote_Server_ID", "Bearer_Token"],
        "Okta" => ["Customer_ID", "API_Key"],
        "MiniOrange" => ["Customer_ID", "API_Key"],
        "NextCloud" => ["URL", "Username", "Password"]
    );

    public const INTEGRATIONS_TITLE = array(
        'WooCommerce' => 'WooCommerce',
        'BuddyPress' => 'BuddyPress',
        'PaidMembership_Pro' => 'PaidMembership Pro',
        'ACF' => 'ACF',
        'CPT_UI' => 'CPT UI',
        'MemberPress' => 'MemberPress',
    );

}