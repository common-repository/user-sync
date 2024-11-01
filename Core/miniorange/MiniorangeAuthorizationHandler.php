<?php

namespace MoUserSync\Core;
require_once "MiniOrangeEnums.php";

class MiniorangeAuthorizationHandler
{

    private $customerKey;
    private $apiKey;

    public function __construct($customerKey, $apiKey)
    {
        $this->customerKey = $customerKey;
        $this->apiKey = $apiKey;


    }

    public function getAuthorizationHeaders()
    {
        $currentTimeInMillis = round(microtime(true) * 1000);
        $currentTimeInMillis = number_format($currentTimeInMillis, 0, '', '');
        $stringToHash = $this->customerKey . $currentTimeInMillis . $this->apiKey;
        $hashValue = hash("sha512", $stringToHash);
        $headers = array(
            "Content-Type" => "application/json",
            "Customer-Key" => $this->customerKey,
            "Timestamp" => $currentTimeInMillis,
            "Authorization" => $hashValue
        );
        return $headers;
    }
}