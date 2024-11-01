<?php

class mo_user_sync_utilities
{

    public static function mo_user_sync_check_option_admin_referer($option_name)
    {
        return (isset($_POST['option']) and $_POST['option'] == $option_name and check_admin_referer($option_name));
    }

    public static function mo_user_sync_sanitize_and_index($postData)
    {
        $temp = [];
        foreach ($postData as $key => $value) {
            if (empty($value)) {
                unset($key);
            } else {
                $temp[$key] = sanitize_text_field($value);
            }
        }
        return $temp;
    }
}