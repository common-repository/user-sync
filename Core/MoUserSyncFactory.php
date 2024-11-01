<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . "miniorange" . DIRECTORY_SEPARATOR . "MiniOrange.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "scim" . DIRECTORY_SEPARATOR . "ScimCore.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "nextCloud" . DIRECTORY_SEPARATOR . "NextCloud.php";

use MoUserSync\Core\MiniOrange;
use MoUserSync\Core\NextCloud;

require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . "Utilities" . DIRECTORY_SEPARATOR . "Instance.php";

class MoUserSyncFactory
{
    use Instance;

    public static function init($remoteServer)
    {

        $db = new DBUtils();
        $remoteServerType = $remoteServer->provisioning_type;
        $attributeMapping = $db->mo_user_sync_get_attribute_options_with_id($remoteServer->id);

        $RemoteServerAttributeMapping = array();

        foreach ($attributeMapping as $key => $value) {
            $RemoteServerAttributeMapping[$value->option_value] = $value->option_name;
        }

        switch ($remoteServerType) {
            case "MiniOrange":
                return new MiniOrange($remoteServer->Customer_ID, $remoteServer->API_Key, $RemoteServerAttributeMapping);
                break;
            case "Tableau":
                return new ScimCore($remoteServer->Remote_Server_ID, $remoteServer->Bearer_Token, $RemoteServerAttributeMapping);
                break;
            case "NextCloud":
                return new NextCloud($remoteServer->URL, $remoteServer->Username, $remoteServer->Password, $RemoteServerAttributeMapping);
            default:
                return new ScimCore($remoteServer->Remote_Server_ID, $remoteServer->Bearer_Token, $RemoteServerAttributeMapping);
        }
    }

}