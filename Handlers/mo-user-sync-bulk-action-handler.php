<?php
require_once "mo-user-sync-http-requests-handler.php";
require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . "Utilities" . DIRECTORY_SEPARATOR . "Instance.php";

add_filter('handle_bulk_actions-users', "mo_user_sync_bulk_action_handler", 10, 3);
add_action('admin_notices', function () {
    if (!empty($_REQUEST['sync_user_to_remote'])) {
        $num_changed = (int)sanitize_text_field($_REQUEST['sync_user_to_remote']);
        printf('<div id="message" class="updated notice is-dismissable"><p>' . __('%d Users will be synced to remote sites.', 'user-sync') . '</p></div>', esc_attr($num_changed));
    }
});

function mo_user_sync_bulk_action_handler($redirect_url, $action, $user_ids)
{

    if ($action == 'sync_user_to_remote' && !empty($user_ids)) {
        foreach ($user_ids as $key => $user_id)
            mo_user_sync_create_user_in_remote_server($user_id);
        $redirect_url = esc_url_raw(add_query_arg('sync_user_to_remote', count($user_ids), $redirect_url));
    }
    return $redirect_url;
}

class MoUserSyncBulkAction
{
    use Instance;
}

if (true) {
    add_action("user_register", "mo_user_sync_create_user_in_remote_server");
}