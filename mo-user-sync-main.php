<?php
/**
 * Plugin Name: User Sync
 * Plugin URI: https://plugins.miniorange.com
 * Description: User sync for WordPress plugin enables automated user sync from WP to Salesforce, Zoom, Tableau, NextCloud and to other WordPress sites
 * Version: 1.0.1
 * Author: miniOrange
 * Author URI: https://miniorange.com
 * License: GPL2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

use MoUserSync\Customer;

define('MO_USER_SYNC_PLUGIN_DIR', plugin_dir_path(__FILE__));


class mo_user_sync_main
{
    public static function set_screen($status, $option, $value)
    {
        return $value;
    }

    public function inithooks()
    {
        $this->includes();
        add_action('admin_menu', array($this, 'mo_user_sync_miniorange_menu'), 11);
        add_action('admin_init', array($this, 'mo_user_sync_form_handler'), 1);
        add_action('init', array($this, 'mo_user_sync_test_configuration'));
        add_filter('set-screen-option', [__CLASS__, 'set_screen'], 10, 3);
        add_action('admin_footer', array($this, 'mo_user_sync_feedback_request'));
        add_action('wp_ajax_mo_server_type', [$this, 'mo_user_sync_server']);
        register_activation_hook(__FILE__, array($this, 'mo_user_sync_plugin_init'));


    }

    function includes()
    {

        require_once __DIR__ . DIRECTORY_SEPARATOR . "Enums" . DIRECTORY_SEPARATOR . "mo-user-sync-enums.php";
        require_once __DIR__ . DIRECTORY_SEPARATOR . "Views" . DIRECTORY_SEPARATOR . "mo-user-sync-view.php";
        require_once __DIR__ . DIRECTORY_SEPARATOR . "Views" . DIRECTORY_SEPARATOR . "mo-user-sync-feedback-form.php";
        require_once __DIR__ . DIRECTORY_SEPARATOR . "Handlers" . DIRECTORY_SEPARATOR . "mo-user-sync-db-handler.php";
        require_once __DIR__ . DIRECTORY_SEPARATOR . "Handlers" . DIRECTORY_SEPARATOR . "mo-user-sync-http-requests-handler.php";
        require_once __DIR__ . DIRECTORY_SEPARATOR . "Views" . DIRECTORY_SEPARATOR . "mo-user-sync-bulk-action-user.php";
        require_once __DIR__ . DIRECTORY_SEPARATOR . "Handlers" . DIRECTORY_SEPARATOR . "mo-user-sync-bulk-action-handler.php";
        require_once __DIR__ . DIRECTORY_SEPARATOR . "Utilities" . DIRECTORY_SEPARATOR . "mo-user-sync-DBUtils.php";
        require_once __DIR__ . DIRECTORY_SEPARATOR . "Utilities" . DIRECTORY_SEPARATOR . "MoUserSyncMessageUtilities.php";
        require_once __DIR__ . DIRECTORY_SEPARATOR . "mo_user_sync_remote_table.php";
        require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
    }

    public function constants()
    {
        define('MOUSERSYNC_DIR', plugin_dir_path(__FILE__));
    }

    public function mo_user_sync_miniorange_menu()
    {
        global $mo_user_sync_page;
        $mo_user_sync_page = add_menu_page(MoUserSyncEnums::PAGE_TITLE, MoUserSyncEnums::PAGE_TITLE, 'administrator', MoUserSyncEnums::MENU_SLUG, 'mo_user_sync', plugin_dir_url(__FILE__) . 'Images/miniorange.png');
        if (!array_key_exists('remote_server_id', $_GET))
            add_action("load-$mo_user_sync_page", [$this, "mo_user_sync_screen_options"]);

        add_action('admin_enqueue_scripts', [$this, 'mo_user_sync_settings_style']);
        add_action('admin_enqueue_scripts', [$this, 'mo_user_sync_settings_script']);
    }

    function mo_user_sync_screen_options()
    {
        $option = 'per_page';
        $args = [
            'label' => 'List Of Remote Servers',
            'default' => 5,
            'option' => 'servers_per_page'
        ];

        add_screen_option($option, $args);

        $server_list_obj = new mo_user_sync_remote_list();
    }

    public function mo_user_sync_settings_style($page)
    {
        if ($page != 'toplevel_page_wp_to_remote_user_sync')
            return;
        $css_url = plugins_url('/Views/include/css/mo-user-sync-settings.css', __FILE__);
        // wp_enqueue_style( 'mo-user-sync-datatable-style', plugins_url('/Views/include/css/jquery.dataTables.min.css', __FILE__));
        wp_enqueue_style('mo-user-sync-css', $css_url, array(), MoUserSyncEnums::VERSION);
    }

    public function mo_user_sync_settings_script($page)
    {
        if ($page != 'toplevel_page_wp_to_remote_user_sync')
            return;
        wp_enqueue_script('jquery');

        wp_enqueue_script('mo-user-sync-settings', plugins_url('/Views/include/js/mo-user-sync-settings.js', __FILE__), ['jquery'], MoUserSyncEnums::VERSION);
        wp_localize_script('mo-user-sync-settings', 'ajax_object_user_sync', array('ajax_url_user_sync' => admin_url('/admin-ajax.php')));
        wp_localize_script('mo-user-sync-settings', 'ajax_var', array(
                'url' => admin_url('/admin-ajax.php'),
                'nonce' => wp_create_nonce('ajax-nonce'))
        );
        // wp_enqueue_script( 'mo-user-sync-datatable-script', plugins_url+
        //('/Views/include/js/jquery.dataTables.min.js', __FILE__ ), array('jquery'));
    }

    public function mo_user_sync_plugin_init()
    {
        $db = new DBUtils();
        $db->mo_user_sync_create_remote_server_list_table();
        $db->mo_user_sync_create_triggers();
        $db->mo_user_sync_remote_server_option();
    }

    public function mo_user_sync_form_handler()
    {
        if (isset($_POST['option'])) {
            $option = sanitize_text_field($_POST['option']);
            if ($option == 'mo_user_sync_feedback') {
                $miniorange_feedback_submit = sanitize_text_field($_POST['miniorange_feedback_submit']);
                $email = sanitize_text_field($_POST['query_mail']);
                $message = 'Plugin Deactivated';
                if ($miniorange_feedback_submit == "Send") {
                    $rate = sanitize_text_field($_POST['rate']);
                    $query = sanitize_text_field($_POST['query_feedback']);
                    $get_reply = isset($_POST['get_reply']) ? 'yes' : 'no';
                    $message .= ', [Reply:' . $get_reply . '], Feedback: ' . $query . ', [Rating: ' . $rate . ']';
                } else {
                    $message .= ', Feedback: Skipped';
                }
                $support = new mo_user_sync_customer();
                $response = $support->mo_user_sync_send_email_alert($email, "", $message);
                deactivate_plugins(__FILE__);
                wp_redirect('plugins.php');
            }
        }
        $form_submission_handler = FormSubmissionHandler::instance();
        $form_submission_handler->mo_user_sync_db_handler();
    }

    public function mo_user_sync_feedback_request()
    {
        mo_user_sync_display_feedback_form();
    }

    public function mo_user_sync_test_configuration()
    {
        $test_connect = FormSubmissionHandler::instance();
        $test_connect->mo_user_sync_test_config();
    }

    public function mo_user_sync_server()
    {
        if (isset($_POST['mo_server_type_ID'])) {
            $id = sanitize_text_field($_POST['mo_server_type_ID']);
            $str1 = '';
            foreach (MoUserSyncEnums::SERVER_LIST_ATTRIBUTES as $key => $value) {
                if ($id == $key) {
                    if ($value[0] == "SCIM") {
                        $type = 'url';
                        $placeholder1 = "Enter URL of the Remote Server";
                        $placeholder2 = "Enter bearer token of the Remote Server";
                    } elseif ($value[0] == "API") {
                        $type = 'text';
                        $placeholder1 = "Enter Customer Id of Remote Server";
                        $placeholder2 = "Enter API Key of the Remoter Server";
                    } elseif ($value[0] == "API-NextCloud") {
                        $type = 'url';
                        $placeholder = "Enter NextCloud Password";
                        $placeholder1 = "Enter NextCloud URL";
                        $placeholder2 = "Enter NextCloud Username";
                        $nextCloudPassword = "NextCloud Password :";

                        $str1 = '
                                <div class="mo-user-sync-dflex mo-sf-ml-1">
                                <div class="mo-user-sync-col-md-6 ">
                                    <h2>' . $nextCloudPassword . '<span class="mo-user-sync-text-danger">*</span></h2>
                                </div>
                                <div class="mo-user-sync-col-md-6">
                                    <div>
                                        <input type="password" id="mo-user-sync-password" class="mo-user-sync-fields" required name="mo-user-sync-remote-password" value="" placeholder="' . $placeholder . '">
                                    </div>
                                </div>
                                </div>
                                ';
                    }

                    $str = '
                        <div id="select-server">
                        <div class="mo-user-sync-dflex mo-sf-ml-1">
                            <div class="mo-user-sync-col-md-6 ">
                                <h2>' . $value[1] . '<span class="mo-user-sync-text-danger">*</span></h2>
                            </div>
                            <div class="mo-user-sync-col-md-6">
                                <div>
                                    <input type="' . $type . '" id="mo-user-sync-url" class="mo-user-sync-fields" required name="mo-user-sync-remote-url" value="" placeholder="' . $placeholder1 . '">
                                </div>
                            </div>
                        </div>

                        <div class="mo-user-sync-dflex mo-sf-ml-1">
                            <div class="mo-user-sync-col-md-6 ">
                                <h2>' . $value[2] . '<span class="mo-user-sync-text-danger">*</span></h2>
                            </div>
                            <div class="mo-user-sync-col-md-6">
                                <div>
                                    <input type="text" id="mo-user-sync-bearer-token" class="mo-user-sync-fields" required name="mo-user-sync-remote-bearer-token" value="" placeholder="' . esc_attr($placeholder2) . '">
                                </div>
                            </div>
                        </div>
                    </div>
                            ';
                }
            }
            $str .= $str1;
            wp_send_json_success($str);
        }
    }
}

$instance = new mo_user_sync_main();
$instance->inithooks();