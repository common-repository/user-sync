<?php


class MoUserSyncMessageUtilities extends Exception
{

    public static function init_admin_notice()
    {
        //add_action('admin_init', [self::class, "mo_user_sync_show_information_message"]);
    }

    public static function mo_user_sync_show_information_message()
    {
        $message = get_option('mo_user_sync_information_message');
        if (empty($message))
            return;
        $message_type = get_option('mo_user_sync_information_message_type');
        echo "<div id='user_sync_notification' class='mo-user-sync-alert mo-user-sync-alert-$message_type'>
				<span class='dashicons dashicons-$message_type'></span>
				<span>$message</span>
			</div>
		";
        delete_option('mo_user_sync_information_message');
        delete_option('mo_user_sync_information_message_type');
        return;
    }

    public static function mo_user_sync_show_error_message($message)
    {
        update_option('mo_user_sync_information_message', $message, true);
        update_option('mo_user_sync_information_message_type', 'warning', true);
    }


    public static function mo_user_sync_information_message($message)
    {
        update_option('mo_user_sync_information_message', $message, true);
        update_option('mo_user_sync_information_message_type', 'config', true);
    }


    public static function mo_user_sync_show_success_message($message)
    {
        update_option('mo_user_sync_information_message', $message, true);
        update_option('mo_user_sync_information_message_type', 'info', true);
    }

}