<?php
/**
 * The uninstall file deletes database when plugin is deleted.
 *
 * @package user-sync
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Utilities' . DIRECTORY_SEPARATOR . 'mo-user-sync-DBUtils.php';
use MoUserSync\Utilities\DBUtils;

$table = new DBUtils();
$table->mo_user_sync_delete_plugin_database_tables();
