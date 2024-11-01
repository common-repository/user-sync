<?php

namespace MoUserSync\Core;
require_once 'MiniorangeAuthorizationHandler.php';

class MiniOrange
{

    private $attribute_mapping;
    private $customerkey;
    private $provisioning_type = 'MiniOrange';
    private $authorizationHeaders;
    private $requestBody;

    public function __construct($customerID, $apiKey, $custom_attributes)
    {
        $this->customerkey = $customerID;
        $this->authorizationHeaders = (new MiniorangeAuthorizationHandler($customerID, $apiKey))->getAuthorizationHeaders();
        $custom_attributes = maybe_unserialize($custom_attributes);
        $this->attribute_mapping = $custom_attributes;
    }

    public function createUser($UserId)
    {
        $data = [];
        $data['customerKey'] = $this->customerkey;
        $user = (array)get_user_by('ID', $UserId)->data;
        if (empty($user)) {
            return false;
        }
        foreach ($this->attribute_mapping as $miniOrangeAttributeName => $wordpressAttributeName) {
            if (isset($user[$wordpressAttributeName])) {
                $data[$miniOrangeAttributeName] = $user[$wordpressAttributeName];
            } else {
                $data[$miniOrangeAttributeName] = get_user_meta($UserId, $wordpressAttributeName, true);
            }
        }
        $data['groups'] = [];
        $this->requestBody = $data;
        return wp_remote_post(MiniOrangeEnums::HOSTNAME . MiniOrangeEnums::CREATE, $this->createApiArgs());
    }

    private function createApiArgs()
    {
        $args = array(
            'method' => 'POST',
            'body' => json_encode($this->requestBody, true),
            'timeout' => '10',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => $this->authorizationHeaders
        );
        return $args;
    }
}