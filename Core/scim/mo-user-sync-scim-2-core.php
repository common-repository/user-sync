<?php

class MoUserSyncScimCore
{

    /**
     * @param WP_User $user
     * @return array with user data in SCIM 2.0 Schema
     */
    static public function create_user_schema(WP_User $user)
    {
        $login_value = $user->user_email ?? $user->user_login;
        $firstName = get_user_meta($user->ID, 'first_name', true) ?? '';
        $familyName = get_user_meta($user->ID, 'last_name', true) ?? '';
        $title = get_user_meta($user->ID, 'title', true) ?? '';
        $deprovision_status = apply_filters('mo_user_sync_deprovisioning_status', true);
        $schema = self::get_schema('User');
        $custom_schema = self::get_schema('CustomExtension');

        $send_query_array = array(
            'schemas' => [$schema],
            'meta' => array('resourceType' => 'User'),
            'name' => array(
                "formatted" => $familyName . ' ' . $firstName,
                'familyName' => $familyName,
                'givenName' => $firstName
            ),
            'title' => $title,
            'displayName' => $user->display_name,
            'userName' => $login_value,
            'active' => $deprovision_status,
            'emails' => array((array('primary' => true, 'value' => $login_value)))
            //primary should be a bool
        );

        return apply_filters('mo_user_sync_user_details', $send_query_array, $user);
    }

    /**
     * @param $schema
     * @return string
     */
    static public function get_schema($schema)
    {
        switch ($schema) {
            case 'User':
                return "urn:ietf:params:scim:schemas:core:2.0:User";
                break;
            case 'CustomExtension':
                return 'urn:ietf:params:scim:schemas:extension:CustomExtensionName:2.0:User';
                break;
            case 'EnterpriseUser':
                return 'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User';
                break;
        }
    }

}