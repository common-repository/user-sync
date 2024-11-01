<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "Handlers" . DIRECTORY_SEPARATOR . 'Customer.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Enums' . DIRECTORY_SEPARATOR . 'mo-user-sync-message-enums.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Utilities' . DIRECTORY_SEPARATOR . 'mo-user-sync-utilities.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Utilities' . DIRECTORY_SEPARATOR . 'MoUserSyncMessageUtilities.php';

class FormSubmissionHandler
{
    private static $instance;
    public $db;

    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        self::$instance->db = new DBUtils();
        return self::$instance;
    }

    public function mo_user_sync_db_handler()
    {
        if (mo_user_sync_utilities::mo_user_sync_check_option_admin_referer('mo_user_sync_cross_prov_save')) {
            if (array_key_exists('mo-user-sync-create', $_POST) && $_POST['mo-user-sync-create'] == 'on')
                $trigger = 'create';
            else
                $trigger = 'none';

            if (array_key_exists('remote_server_id', $_GET) && !empty($_GET['remote_server_id'])) {
                if (array_key_exists('mo-user-sync-remote-password', $_POST) && sanitize_text_field($_POST['mo-user-sync-select-server']) == "NextCloud") {
                    $this->db->mo_user_sync_save_remote_configuration($_POST['mo-user-sync-remote-name'], $_POST['mo-user-sync-remote-url'], $_POST['mo-user-sync-remote-bearer-token'], $_POST['mo-user-sync-remote-password'], $_POST['mo-user-sync-select-server'], intval($_GET['remote_server_id']), 'Deactivated', $trigger);
                } else {
                    $this->db->mo_user_sync_save_remote_configuration($_POST['mo-user-sync-remote-name'], $_POST['mo-user-sync-remote-url'], $_POST['mo-user-sync-remote-bearer-token'], '', $_POST['mo-user-sync-select-server'], intval($_GET['remote_server_id']), 'Deactivated', $trigger);
                }
                $current_attribute_id = $_GET['remote_server_id'];
                $provisioning_type = $this->db->mo_user_sync_get_provisioning_type_with_remote_server_id($current_attribute_id);
                if ($provisioning_type[0]->provisioning_type != get_option('default_server_option')) {
                    $this->db->mo_user_sync_delete_default_option_attributes($current_attribute_id);
                }
            } else {
                if (array_key_exists('mo-user-sync-remote-password', $_POST) && sanitize_text_field($_POST['mo-user-sync-select-server']) == "NextCloud") {
                    $this->db->mo_user_sync_save_remote_configuration($_POST['mo-user-sync-remote-name'], $_POST['mo-user-sync-remote-url'], $_POST['mo-user-sync-remote-bearer-token'], $_POST['mo-user-sync-remote-password'], $_POST['mo-user-sync-select-server'], '', 'Deactivated', $trigger);
                } else {
                    $this->db->mo_user_sync_save_remote_configuration($_POST['mo-user-sync-remote-name'], $_POST['mo-user-sync-remote-url'], $_POST['mo-user-sync-remote-bearer-token'], '', $_POST['mo-user-sync-select-server'], '', 'Deactivated', $trigger);
                }
            }

            MoUserSyncMessageUtilities::mo_user_sync_show_success_message(MoUserSyncMessageEnums::CONFIGURATION_SAVED);
        } else if (mo_user_sync_utilities::mo_user_sync_check_option_admin_referer('mo_user_sync_contact_us_query_option')) {

            $email = sanitize_email($_POST['mo_user_sync_contact_us_email']);
            $query = sanitize_text_field($_POST['mo_user_sync_contact_us_query']);
            if (empty($email) || empty($query)) {
                wp_send_json_error("Empty email or query");
            } else {
                $support = new mo_user_sync_customer();
                $response = $support->mo_user_sync_submit_contact_us($email, "", $query, false);
                $response == "Query submitted.";
            }
        } else if (array_key_exists('deleted_remote_server', $_GET) && !empty($_GET['deleted_remote_server']) && wp_verify_nonce($_GET['_wpnonce'])) {
            $id = $_GET['deleted_remote_server'];
            $this->db->mo_user_sync_delete_remote_server($id);
            delete_option('default_server_option');
            MoUserSyncMessageUtilities::mo_user_sync_show_success_message(MoUserSyncMessageEnums::CONFIGURATION_DELETED);
        } else if (mo_user_sync_utilities::mo_user_sync_check_option_admin_referer('mo_user_sync_demo_request')) {
            if (isset($_POST['demo_email']))
                $demo_email = sanitize_email($_POST['demo_email']);

            $demo_description = sanitize_textarea_field($_POST['demo_description']);

            $addons_selected = array();
            $addons = MoUserSyncEnums::INTEGRATIONS_TITLE;
            foreach ($addons as $key => $value) {
                if (isset($_POST[$key]) && $_POST[$key] == "true")
                    $addons_selected[$key] = $value;
            }

            $integrations_selected = implode(', ', array_values($addons_selected));

            if (empty($demo_email)) {
                wp_send_json_error("Empty email or query");
            } else {
                $demo_setup = new mo_user_sync_customer();
                $response = $demo_setup->mo_user_sync_submit_contact_us($demo_email, "", $demo_description, false, true, $integrations_selected);

                if ($response == 'Query submitted.') {
                    MoUserSyncMessageUtilities::mo_user_sync_show_success_message('Demo Request Successful');
                } else
                    MoUserSyncMessageUtilities::mo_user_sync_show_error_message($response);
            }
        } else if (mo_user_sync_utilities::mo_user_sync_check_option_admin_referer('attrMap')) {
            unset($_POST['option']);
            $remote_server_id = $_GET['remote_server_id'];
            $this->db->mo_user_sync_status_update($remote_server_id);

            $default_option_name = array();
            $default_option_value = array();
            $default_option_type = array();
            $default_options_ids_array = array();

            foreach ($_POST['mo_default_data_attributes'] as $key => $value) {
                array_push($default_option_value, $value);
            }

            foreach ($_POST['mo_default_attributes'] as $key => $value) {
                $value = explode(",", $value);
                array_push($default_option_name, $value[0]);
                array_push($default_option_type, $value[1]);
            }
            foreach ($default_option_type as $key => $value) {
                if (!isset($value)) {
                    MoUserSyncMessageUtilities::mo_user_sync_show_error_message("Entry fields cannot be empty");
                    return;
                }
            }

            $default_options_ids_objects = $this->db->mo_user_sync_get_ids_with_remote_server_id_attribute($remote_server_id);

            if (!empty($default_options_ids_objects)) {
                foreach ($default_options_ids_objects as $value) {
                    array_push($default_options_ids_array, $value->id);
                }
                foreach ($default_options_ids_array as $key => $value) {
                    $this->db->mo_user_sync_update_attribute_configuration($remote_server_id, $default_option_name[$key], $default_option_value[$key], $default_option_type[$key], $value);
                }

            } else {
                foreach ($_POST['mo_default_data_attributes'] as $key => $value) {
                    $this->db->mo_user_sync_save_attribute_configuration($remote_server_id, $default_option_name[$key], $default_option_value[$key], $default_option_type[$key]);
                }
            }

            MoUserSyncMessageUtilities::mo_user_sync_show_success_message('Attribute Mapping details saved successfull');
        }
    }

    public function mo_user_sync_test_config()
    {

        if (array_key_exists('option', $_REQUEST) && $_REQUEST['option'] == 'test_server_configuration' && wp_verify_nonce($_REQUEST['_wpnonce'])) {

            $data = $this->db->mo_user_sync_get_data_with_id($_REQUEST['id']);
            if ($data[0]->provisioning_type == "Tableau") {
                $url = $data[0]->url . '/Users';
                $url = esc_url_raw($url);

                $headers = array(
                    "Authorization" => "Bearer " . $data[0]->bearer_token
                );
                $response = wp_remote_get($url, array(
                    'headers' => $headers, 'timeout' => '10'
                ));
                $response = json_decode(json_encode($response), true);
            } else {
                $response['response']['message'] = "Test Configuration for following server does not exist";
            }
            if (array_key_exists('code', $response['response']) && isset($response['response']['code']) && $response['response']['code'] == '200') {
                echo '<div style="color: #3c763d;
				background-color: #dff0d8; padding:2%;margin-bottom:20px;text-align:center; border:1px solid #AEDB9A; font-size:18pt; border-radius:10px;margin-top:17px;">TEST SUCCESSFUL</div>
				<div style="display:block;text-align:center;margin-bottom:4%;"><svg class="animate" width="100" height="100">
				<filter id="dropshadow" height="">
				<feGaussianBlur in="SourceAlpha" stdDeviation="3" result="blur"></feGaussianBlur>
				<feFlood flood-color="rgba(76, 175, 80, 1)" flood-opacity="0.5" result="color"></feFlood>
				<feComposite in="color" in2="blur" operator="in" result="blur"></feComposite>
				<feMerge> 
					<feMergeNode></feMergeNode>
					<feMergeNode in="SourceGraphic"></feMergeNode>
				</feMerge>
				</filter>
				
				<circle cx="50" cy="50" r="46.5" fill="none" stroke="rgba(76, 175, 80, 0.5)" stroke-width="5"></circle>
				
				<path d="M67,93 A46.5,46.5 0,1,0 7,32 L43,67 L88,19" fill="none" stroke="rgba(76, 175, 80, 1)" stroke-width="5" stroke-linecap="round" stroke-dasharray="80 1000" stroke-dashoffset="-220" style="filter:url(#dropshadow)"></path>
			</svg><style>
			svg.animate path {
			animation: dash 1.5s linear both;
			animation-delay: 1s;
			}
			@keyframes dash {
			0% { stroke-dashoffset: 210; }
			75% { stroke-dashoffset: -220; }
			100% { stroke-dashoffset: -205; }
			}
			</style></div>';
                exit;
            } else {
                wp_die('Test unsuccessfull because code => ' . esc_attr($response['response']['code']) . ' message => ' . esc_attr($response['response']['message']));
                exit;
            }
        }
    }
}