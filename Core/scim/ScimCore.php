<?php
require_once "mo-user-sync-scim-2-core.php";
require_once "ScimAuthorizationHandler.php";
require_once "TableauEnums.php";
require_once "TalentLMSEnums.php";

class ScimCore extends MoUserSyncScimCore
{

    private $attribute_mapping;
    private $customerkey;
    private $provisioning_type = 'SCIM';
    private $authorizationHeaders;
    private $requestBody;

    public function __construct($remoteServerID, $bearerToken, $custom_attributes)
    {
        $this->customerkey = $remoteServerID;
        $this->authorizationHeaders = (new \MoUserSync\Core\ScimAuthorizationHandler($remoteServerID, $bearerToken))->getAuthorizationHeaders();
        $custom_attributes = maybe_unserialize($custom_attributes);
        $this->attribute_mapping = $custom_attributes;
    }

    public function createUser($UserId)
    {
        $data = [];
        $user = (array)get_user_by('ID', $UserId)->data;
        $deprovision_status = apply_filters('mo_user_sync_deprovisioning_status', true);
        $schema = self::get_schema('User');
        if (empty($user)) {
            return false;
        }
        foreach ($this->attribute_mapping as $scimAttributeName => $wordpressAttributeName) {
            if (isset($user[$wordpressAttributeName])) {
                $data[$scimAttributeName] = $user[$wordpressAttributeName];
            } else {
                $data[$scimAttributeName] = get_user_meta($UserId, $wordpressAttributeName, true);
            }
        }

        $url = $this->customerkey;

        if ($url !== '') {
            if (strpos($url, "/scim/Users") === false) {
                $url = $url . '/Users';
            }
        }

        $url = esc_url_raw($url);

        $send_query_array = array(
            'schemas' => [$schema],
            'id' => strval($user['ID']),
            'meta' => array('resourceType' => 'User'),
            'name' => array(
                "formatted" => $data["familyName"] . ' ' . $data["firstName"],
                "familyName" => $data["familyName"],
                "givenName" => $data["firstName"]
            ),
            'title' => $data["title"],
            'displayName' => $user['display_name'],
            'userName' => $data["login_value"],
            'active' => $deprovision_status,
            'emails' => array((array('primary' => true, 'value' => $data["login_value"])))

        );

        $this->requestBody = $send_query_array;
        return wp_remote_post($url, $this->createScimArgs());
    }

    private function createScimArgs()
    {
        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/scim+json',
                'Accept' => 'application/scim+json',
            ),
            'body' => json_encode($this->requestBody, true)
        );
        $args['headers'] = array_merge($args['headers'], $this->authorizationHeaders);
        return $args;
    }

}