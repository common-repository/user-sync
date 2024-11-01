<?php

add_filter("bulk_actions-users", "mo_user_sync_show_bulk_action");

function mo_user_sync_show_bulk_action($bulk_actions)
{
    $db = new DBUtils();
    $url_token = $db->mo_user_sync_get_url_token();
    if (!empty($url_token))
        $bulk_actions["sync_user_to_remote"] = __('Sync Users to Remote Server', 'user-sync');
    return $bulk_actions;
}