<?php


namespace MoUserSync\Core;
require_once 'NextCloudAuthorizationHandler.php';

class NextCloud
{

    private $attribute_mapping;
    private $url;
    private $provisioning_type = 'NextCloud';
    private $authorizationHeaders;
    private $requestBody;

    public function __construct($url, $Username, $Password, $custom_attributes)
    {
        $this->url = $url;
        $this->authorizationHeaders = (new NextCloudAuthorizationHandler($Username, $Password))->getAuthorizationHeaders();
        $custom_attributes = maybe_unserialize($custom_attributes);
        $this->attribute_mapping = $custom_attributes;
    }

    public function createUser($UserId)
    {
        $data = [];
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

        $this->requestBody = $data;
        return wp_remote_post($this->url . NextCloudEnums::CREATE, $this->createApiArgs());
    }

    private function createApiArgs()
    {
        $args = array(
            'method' => 'POST',
            'body' => $this->requestBody,
            'headers' => $this->authorizationHeaders
        );
        return $args;
    }
}