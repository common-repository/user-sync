<?php

require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . "Handlers" . DIRECTORY_SEPARATOR . "EncryptionHandler.php";
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

use MoUserSync\Handler\EncryptionHandler;

class DBUtils
{
    public $wpdb;
    public $table_name;
    public $trigger_table_name;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
        $this->table_name = "mo_user_sync_remote_server_list";
        $this->trigger_table_name = "mo_user_sync_triggers";
        $this->attribute_table_name = "mo_user_sync_remote_server_option";
    }

    public function mo_user_sync_create_remote_server_list_table()
    {
        $current_charset_collate = $this->wpdb->get_charset_collate();
        $create_table_query = "CREATE TABLE $this->table_name (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `provisioning_type` varchar(255) NOT NULL,
            `url` varchar(255) NOT NULL,
            `bearer_token` varchar(255) NOT NULL,
            `status` varchar(255) NOT NULL,
            PRIMARY KEY (id)
            ) ENGINE=InnoDB $current_charset_collate";

        $created_table = dbDelta($create_table_query, true);

        if (empty($created_table))
            return false;
        return true;
    }

    public function mo_user_sync_create_triggers()
    {
        $current_charset_collate = $this->wpdb->get_charset_collate();
        $create_table_query = "CREATE TABLE $this->trigger_table_name (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `remote_server_id` bigint(20) unsigned NOT NULL,
            `triggers` text NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (remote_server_id) REFERENCES $this->table_name(id)
            ON DELETE CASCADE ) ENGINE=InnoDB $current_charset_collate";

        $created_table = dbDelta($create_table_query, true);

        if (empty($created_table))
            return false;
        return true;
    }

    public function mo_user_sync_remote_server_option()
    {
        $current_charset_collate = $this->wpdb->get_charset_collate();
        $create_table_query = "CREATE TABLE $this->attribute_table_name (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `remote_server_id` bigint(20) unsigned NOT NULL,
            `option_name` text NOT NULL,
            `option_value` text NOT NULL,
            `option_type` text DEFAULT 'NULL' NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (remote_server_id) REFERENCES $this->table_name(id)
            ON DELETE CASCADE ) ENGINE=InnoDB $current_charset_collate";

        $created_table = dbDelta($create_table_query, true);

        if (empty($created_table))
            return false;
        return true;
    }


    public function mo_user_sync_save_remote_configuration($name, $url, $bearer_token, $password = '', $provisioning_type = '', $id = '', $status = 'Deactivated', $triggers = '')
    {
        $data = array(
            'name' => sanitize_text_field($name),
            'url' => sanitize_text_field($url),
            'bearer_token' => sanitize_text_field($bearer_token),
            'provisioning_type' => $provisioning_type,
            'status' => $status
        );

        $format = array(
            '%s', '%s', '%s', '%s', '%s', '%s', '%s'
        );

        $trigg = $this->mo_user_sync_get_trigger_with_id($id);

        if (!empty($id) && isset($trigg[0]) && $trigg[0]->remote_server_id == $id && $trigg[0]->triggers == $triggers) {
            $this->wpdb->update($this->table_name, $data, array('id' => $id), $format, array('%d'));
        } elseif (!empty($id) && $triggers != 'none') {
            $this->wpdb->update($this->table_name, $data, array('id' => $id), $format, array('%d'));
            $this->mo_user_sync_save_trigger_configuration($triggers, $id);
        } elseif (!empty($id) && $triggers == 'none') {
            $this->wpdb->update($this->table_name, $data, array('id' => $id), $format, array('%d'));
            $this->mo_user_sync_delete_trigger_configuration($id);
        } else {
            $data_id = $this->wpdb->insert($this->table_name, $data, $format);
            $new_id = $this->wpdb->insert_id;
            if ($triggers != 'none') {
                $this->mo_user_sync_save_trigger_configuration($triggers, $new_id);
            }
        }

        $value = MoUserSyncEnums::SERVER_LIST_ATTRIBTURES_FOR_DATABASE[$provisioning_type];
        if (isset($value)) {
            if (!empty($id)) {
                $iter_id = $this->mo_user_sync_get_ids_with_remote_server_id($id);

                $this->mo_user_sync_update_option_server_configuration($id, $value[0], $url, "Authorization", $iter_id[0]->id);
                $this->mo_user_sync_update_option_server_configuration($id, $value[1], $bearer_token, "Authorization", $iter_id[1]->id);
                if (isset($iter_id[2]->id) and $provisioning_type != "NextCloud")
                    $this->mo_user_sync_delete_password_row($id, $iter_id[2]->id);
                if ($provisioning_type == "NextCloud") {
                    $password = EncryptionHandler::mo_user_sync_encrypt_data($password, $bearer_token);
                    if (isset($iter_id[2]->id))
                        $this->mo_user_sync_update_option_server_configuration($id, $value[2], $password, "Authorization", $iter_id[2]->id);
                    else
                        $this->mo_user_sync_save_attribute_configuration($id, $value[2], $password, "Authorization");
                }
            } else {
                $this->mo_user_sync_save_attribute_configuration($new_id, $value[0], $url, "Authorization");
                $this->mo_user_sync_save_attribute_configuration($new_id, $value[1], $bearer_token, "Authorization");
                if ($provisioning_type == "NextCloud") {
                    $password = EncryptionHandler::mo_user_sync_encrypt_data($password, $bearer_token);
                    $this->mo_user_sync_save_attribute_configuration($new_id, $value[2], $password, "Authorization");
                }
            }
        }

        if (empty($data_id))
            return false;

        return true;
    }

    public function mo_user_sync_get_trigger_with_id($id)
    {
        $select_query = "SELECT `id`,`remote_server_id`,`triggers` FROM $this->trigger_table_name WHERE `remote_server_id` = '%d'";
        $select_query = $this->wpdb->prepare($select_query, $id);
        return $this->wpdb->get_results($select_query);
    }

    public function mo_user_sync_save_trigger_configuration($triggers = '', $remote_server_id = '')
    {
        $data = array(
            'triggers' => $triggers,
            'remote_server_id' => $remote_server_id
        );
        $format = array(
            '%s', '%d'
        );

        $data_id = $this->wpdb->insert($this->trigger_table_name, $data, $format);
        if (empty($data_id))
            return false;

        return true;
    }

    public function mo_user_sync_delete_trigger_configuration($remote_server_id = '')
    {
        $data_id = $this->wpdb->delete($this->trigger_table_name, array('remote_server_id' => $remote_server_id), array('%d'));
        if (empty($data_id))
            return false;

        return true;
    }

    public function mo_user_sync_get_ids_with_remote_server_id($id)
    {
        $select_query = "SELECT `id` FROM $this->attribute_table_name WHERE `remote_server_id` = '%d' AND `option_type` = 'Authorization'";
        $select_query = $this->wpdb->prepare($select_query, $id);
        return $this->wpdb->get_results($select_query);
    }

    public function mo_user_sync_update_option_server_configuration($remote_server_id, $option_name, $option_value, $option_type, $id)
    {
        $data = array(
            'id' => $id,
            'remote_server_id' => $remote_server_id,
            'option_name' => $option_name,
            'option_value' => $option_value,
            'option_type' => $option_type
        );

        $format = array(
            '%d', '%d', '%s', '%s', '%s'
        );

        $data_id = $this->wpdb->update($this->attribute_table_name, $data, array('remote_server_id' => $remote_server_id, 'id' => $id), $format, array('%d', '%d'));
        if (empty($data_id))
            return false;
        return true;
    }

    public function mo_user_sync_delete_password_row($remote_server_id, $id)
    {
        $select_query = "DELETE FROM $this->attribute_table_name WHERE `remote_server_id` = '%d' AND `id` = '%d'";
        $select_query = $this->wpdb->prepare($select_query, $remote_server_id, $id);
        return $this->wpdb->query($select_query);
    }

    public function mo_user_sync_save_attribute_configuration($remote_server_id, $option_name, $option_value, $option_type)
    {
        $data = array(
            'remote_server_id' => $remote_server_id,
            'option_name' => $option_name,
            'option_value' => $option_value,
            'option_type' => $option_type
        );
        $format = array(
            '%d', '%s', '%s', '%s'
        );

        $data_id = $this->wpdb->insert($this->attribute_table_name, $data, $format);
        if (empty($data_id))
            return false;
        return true;
    }

    public function mo_user_sync_update_attribute_configuration($remote_server_id, $option_name, $option_value, $option_type, $id)
    {
        $data = array(
            'remote_server_id' => $remote_server_id,
            'option_name' => $option_name,
            'option_value' => $option_value,
            'option_type' => $option_type
        );
        $format = array(
            '%d', '%s', '%s', '%s'
        );

        $data_id = $this->wpdb->update($this->attribute_table_name, $data, array('remote_server_id' => $remote_server_id, 'id' => $id), $format, array('%d'));
        if (empty($data_id))
            return false;
        return true;
    }

    public function mo_user_sync_status_update($remote_server_id)
    {
        $select_query = "UPDATE $this->table_name SET `status`= %s WHERE `id` = $remote_server_id ";
        $select_query = $this->wpdb->prepare($select_query, 'Active');
        return $this->wpdb->get_results($select_query);
    }

    public function mo_user_sync_show_attributes_fields($remote_server_id)
    {
        $select_query = "SELECT `id`,`option_name`,`option_value`,`option_type` FROM $this->attribute_table_name WHERE `remote_server_id` = '%d' AND `option_type` != 'Authorization'";
        $select_query = $this->wpdb->prepare($select_query, $remote_server_id);
        return $this->wpdb->get_results($select_query);
    }

    public function mo_user_sync_delete_default_option_attributes($remote_server_id = '')
    {
        $select_query = "DELETE FROM $this->attribute_table_name WHERE `remote_server_id` = '%d' AND `option_type` != 'Authorization'";
        $select_query = $this->wpdb->prepare($select_query, $remote_server_id);
        return $this->wpdb->query($select_query);
    }

    public function mo_user_sync_get_remote_server_list()
    {
        $select_query = "SELECT `id`,`url`,`status`,`name` FROM $this->table_name";
        return $this->wpdb->get_results($select_query);
    }

    public function mo_user_sync_get_data_with_id($id)
    {
        $select_query = "SELECT `name`,`url`,`bearer_token`,`id`,`provisioning_type` FROM $this->table_name WHERE `id` = '%d'";
        $select_query = $this->wpdb->prepare($select_query, $id);
        return $this->wpdb->get_results($select_query);
    }

    public function mo_user_sync_get_ids_with_remote_server_id_attribute($id)
    {
        $select_query = "SELECT `id` FROM $this->attribute_table_name WHERE `remote_server_id` = '%d' AND `option_type` != 'Authorization'";
        $select_query = $this->wpdb->prepare($select_query, $id);
        return $this->wpdb->get_results($select_query);
    }

    public function mo_user_sync_get_attribute_options_with_id($id)
    {
        $select_query = "SELECT `option_name`,`option_value` FROM $this->attribute_table_name WHERE`remote_server_id` = '%d' AND `option_type` != 'Authorization'";
        $select_query = $this->wpdb->prepare($select_query, $id);
        return $this->wpdb->get_results($select_query);
    }

    public function mo_user_sync_get_password_of_nextCloud($id)
    {
        $select_query = "SELECT `option_value` FROM $this->attribute_table_name WHERE`remote_server_id` = '%d' AND `option_name` = 'Password' AND `option_type` = 'Authorization'";
        $select_query = $this->wpdb->prepare($select_query, $id);
        return $this->wpdb->get_results($select_query);
    }

    public function mo_user_sync_get_url_token()
    {
        $select_query = "SELECT `id`,`name`,`provisioning_type` FROM $this->table_name WHERE `status` = %s ";
        $select_query = $this->wpdb->prepare($select_query, 'Active');
        $result = $this->wpdb->get_results($select_query);

        foreach ($result as $key => $value) {
            $ID = $value->id;
            $option = $this->mo_user_sync_get_server_options_with_id($ID);
            foreach ($option as $item => $stuff) {
                $url = $stuff->option_name;
                $value->$url = $stuff->option_value;
            }
        }

        return $result;
    }

    public function mo_user_sync_get_server_options_with_id($id)
    {
        $select_query = "SELECT `option_name`,`option_value` FROM $this->attribute_table_name WHERE`remote_server_id` = '%d' AND `option_type` = 'Authorization'";
        $select_query = $this->wpdb->prepare($select_query, $id);
        return $this->wpdb->get_results($select_query);
    }

    public function mo_user_sync_delete_remote_server($id)
    {
        $_SERVER['REQUEST_URI'] = remove_query_arg(array('deleted_remote_server', 'action'), $_SERVER['REQUEST_URI']);
        $select_query = "DELETE FROM $this->table_name WHERE `id` = %d";
        $select_query = $this->wpdb->prepare($select_query, $id);
        return $this->wpdb->get_results($select_query);
    }

    public function mo_user_sync_bulk_delete($ids)
    {
        $ids = implode(" , ", $ids);
        $select_query = "DELETE FROM $this->table_name WHERE `id` IN($ids) ";
        return $this->wpdb->get_results($select_query);
    }

    public function mo_user_sync_bulk_activate($ids)
    {
        $ids = implode(" , ", $ids);
        $select_query = "UPDATE $this->table_name SET `status`= %s WHERE `id` IN($ids) ";
        $select_query = $this->wpdb->prepare($select_query, 'Active');
        return $this->wpdb->get_results($select_query);
    }

    public function mo_user_sync_bulk_deactivate($ids)
    {
        $ids = implode(" , ", $ids);
        $select_query = "UPDATE $this->table_name SET `status`= %s WHERE `id` IN($ids) ";
        $select_query = $this->wpdb->prepare($select_query, 'Deactivated');
        return $this->wpdb->get_results($select_query);
    }

    public function mo_user_sync_get_search_data($search)
    {
        $select_query = "SELECT `id`,`url`,`status` FROM $this->table_name WHERE `id` like '% %s %' OR `url` like '% %s %' OR `status` like '% %s %' OR `name` like '% %s %'";
        $select_query = $this->wpdb->prepare($select_query, $search);
        return $this->wpdb->get_results($select_query);
    }

    public function mo_user_sync_get_row_count()
    {
        $select_query = "SELECT COUNT(*) FROM $this->table_name";
        return $this->wpdb->get_var($select_query);
    }

    public function mo_user_sync_get_provisioning_type_with_remote_server_id($remoter_server_id)
    {
        $select_query = "SELECT `provisioning_type` FROM $this->table_name WHERE `id` = '$remoter_server_id'";
        return $this->wpdb->get_results($select_query);
    }
}