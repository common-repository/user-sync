<?php

require_once plugin_dir_path(__DIR__) . DIRECTORY_SEPARATOR . "Core" . DIRECTORY_SEPARATOR . "scim" . DIRECTORY_SEPARATOR . "mo-user-sync-scim-2-core.php";
require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . "Core" . DIRECTORY_SEPARATOR . "MoUserSyncFactory.php";
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Utilities' . DIRECTORY_SEPARATOR . 'MoUserSyncMessageUtilities.php';

function mo_user_sync_create_user_in_remote_server($user_id)
{
    $db = new DBUtils();
    $remoteServers = $db->mo_user_sync_get_url_token();

    if (!empty($remoteServers)) {
        foreach ($remoteServers as $key => $remoteServer) {
            $triggers = $db->mo_user_sync_get_trigger_with_id($remoteServer->id);

            if (!empty($triggers)) {
                $userSyncObject = MoUserSyncFactory::init($remoteServer);
                $req = $userSyncObject->createUser($user_id);
                try {
                    if (empty($req)) {
                        throw new MoUserSyncMessageUtilities("Failed to send User Create Request");
                    }
                } catch (MoUserSyncMessageUtilities $e) {
                    mo_user_sync_one_error_message($e);
                }
                mo_user_sync_extract_resource_id_from_response($req, $user_id, $remoteServer->id);
                return $req;
            }
        }
    }
}

function mo_user_sync_one_error_message($message)
{
    update_option('mo_user_sync_information_message', $message, true);
    update_option('mo_user_sync_information_message_type', 'warning', true);
}

function mo_user_sync_extract_resource_id_from_response($response, $user_id, $url)
{
    if (is_wp_error($response)) {
        update_user_meta($user_id, "mo_user_sync" . esc_url_raw($url), "Sync Failed");
    } else {
        if (isset($response['body'])) {
            $body = $response['body'];
            if (isset($body['id']))
                update_user_meta($user_id, "mo_user_sync" . esc_url_raw($url), sanitize_text_field($body['id']));
        }
    }
    return true;
}