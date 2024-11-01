<?php
include_once __DIR__ . '/mo-user-sync-attr-mapping.php';

function mo_user_sync_configuration_body()
{
    if (!array_key_exists('remote_server_id', $_GET))
        mo_user_sync_remote_url_table();
    else {
        mo_user_sync_idp_guide_list();
        mo_user_sync_cross_provisioning_data();
    }
}

function mo_user_sync_remote_url_table()
{
    $db = new DBUtils();
    $remote_list = $db->mo_user_sync_get_remote_server_list();
    $remote_server_configured = (isset($remote_list) && !empty($remote_list));
    $table = new mo_user_sync_remote_list();
    ?>
    <form id="mo-user-remote-sync-table" method="post" action="">
        <div class="mo-user-sync-tab-content">
            <div class="mo-user-sync-tab-content-tile mo-user-sync-mt-4">
                <h1 class="mo-user-sync-form-head">Configure Remote Sync</h1>
                <?php
                $table->prepare_items();
                $table->search_box('Search', 'search');
                $table->display();
                ?>
                <div class="mo_user_sync_add_remote_server">
                    <a class="mo-user-sync-btn-cstm"
                       href="?page=wp_to_remote_user_sync&tab=sp_config&attributeMapping=0&remote_server_id">Add New
                        Remote server</a>
                </div>
            </div>
        </div>
    </form>
    <?php
}


function mo_user_sync_idp_guide_list()
{
    ?>
    <div class="mo-user-sync-tab-content">
        <div class="mo-user-sync-tab-content-tile mo-user-sync-mt-4 parent" style="background: #d5e2ff;">
            <?php
            foreach (MoUserSyncEnums::SERVER_GUIDES as $key => $value) {
                $image_path = ".." . DIRECTORY_SEPARATOR . "Images" . DIRECTORY_SEPARATOR;
                ?>
                <div class="logo-user-sync-cstm child">
                    <a target="__blank" href="https://plugins.miniorange.com/<?php echo esc_attr($value[1]); ?>">
                        <img loading="lazy" width="90px"
                             src="<?php echo plugins_url($image_path . $value[0] . '.png', __FILE__); ?>"
                             margin-top="0.9rem">
                        <br>
                        <div>
                            <h6><?php echo esc_attr($key) ?></h6>
                        </div>
                    </a>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
}

function mo_user_tabs()
{
    ?>
    <div style="width: 100%;padding: 0 15px;margin-top: 25px;margin-bottom: 5px;">
        <!-- Tab links -->
        <div class="mo_user_sync_new_tab">
            <a href="?page=wp_to_remote_user_sync&tab=sp_config&attributeMapping=0&remote_server_id=<?php echo esc_attr($_GET['remote_server_id']); ?>">
                <button class="mo_user_sync_tablinks" id="remote-sync">Remote Sync</button>
            </a>
            <a href= <?php echo esc_url_raw($_SERVER['REQUEST_URI'] . "&attributeMapping=1"); ?>>
                <button class="mo_user_sync_tablinks"
                        <?php
                        if (array_key_exists('remote_server_id', $_GET) && empty(sanitize_text_field($_GET['remote_server_id']))) {
                            echo "disabled ";
                        } else {
                            echo '';
                        }
                        ?>id="attr-mapping">Attribute Mapping
                </button>
            </a>
        </div>
    </div>
    <?php
}

function mo_user_sync_cross_provisioning_data()
{

    $dbUtil = new DBUtils();
    $remoter_server_id = sanitize_text_field($_GET['remote_server_id']);

    $trigg = $dbUtil->mo_user_sync_get_trigger_with_id($remoter_server_id);
    $already_present = false;
    if (array_key_exists('remote_server_id', $_GET) && !empty($remoter_server_id)) {
        $already_present = true;
        $db = new DBUtils();
        $remote_data_row = $db->mo_user_sync_get_data_with_id(intval($remoter_server_id));
        if (sanitize_text_field($remote_data_row[0]->provisioning_type) == "NextCloud")
            $password = $dbUtil->mo_user_sync_get_password_of_nextCloud($remoter_server_id);
    }
    ?>
    <div class="mo-user-sync-tab-content">
        <div class="mo-user-sync-tab-content-tile mo-user-sync-mt-4">
            <div class="mo-user-sync-row">
                <div style="display:flex;width: 100%;">
                    <div class="mo-user-sync-col-md-6">
                        <h1 class="mo-user-sync-ml-1">Configure Remote Sync</h1>
                    </div>
                    <div class="mo-user-sync-col-md-6 mo-user-sync-dflex mo-user-sync-justify-content-end"
                         style="right: 44px;">
                        <a href="?page=wp_to_remote_user_sync&tab=sp_config" class="mo_remote_server_list_button"
                           style="padding: 10px;" type="button">Back To Remote Server List</a>
                    </div>
                </div>

                <?php
                mo_user_tabs();
                ?>

            </div>

            <?php
            if (array_key_exists('attributeMapping', $_GET) && $_GET['attributeMapping'] == 1 && isset($_GET['remote_server_id'])) {
                show_attribute();
            } else {
                ?>

                <form name="mo_user_sync_cross_prov_save" id="mo_user_sync_cross_prov_save" method="post"
                      action="<?php if ($already_present == false) {
                          echo admin_url() . 'admin.php?page=wp_to_remote_user_sync&tab=sp_config';
                      } ?>">
                    <?php wp_nonce_field('mo_user_sync_cross_prov_save'); ?>
                    <input type="hidden" name="option" value="mo_user_sync_cross_prov_save"/>
                    <div class="mo-user-sync-dflex mo-sf-ml-1">
                        <div class="mo-user-sync-col-md-6 ">
                            <h2>Name of the Remote Server :<span class="mo-user-sync-text-danger">*</span></h2>
                        </div>
                        <div class="mo-user-sync-col-md-6">
                            <div>
                                <input type="text" class="mo-user-sync-fields" required name="mo-user-sync-remote-name"
                                       value="<?php if ($already_present) echo sanitize_text_field($remote_data_row[0]->name); ?>"
                                       placeholder="Enter name of the Remote Server like tableau,talentlms etc ">
                            </div>
                        </div>
                    </div>

                    <div class="mo-user-sync-dflex mo-sf-ml-1">
                        <div class="mo-user-sync-col-md-6 ">
                            <h2>Select Remote Server :<span class="mo-user-sync-text-danger">*</span></h2>
                        </div>
                        <div class="mo-user-sync-col-md-6">
                            <div>
                                <select name="mo-user-sync-select-server" id="select-server"
                                        class="mo-user-sync-fields">
                                    <?php
                                    foreach (MoUserSyncEnums::SERVER_LIST as $key => $value) {
                                        $_temp = '';
                                        echo "<option value=" . esc_attr($key);
                                        if (!empty($remote_data_row[0]->provisioning_type))
                                            $_temp = $key == $remote_data_row[0]->provisioning_type ? " selected" : "";
                                        echo esc_attr($_temp);
                                        echo ">" . esc_attr($key) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="append_div">
                        <?php
                        foreach (MoUserSyncEnums::SERVER_LIST_ATTRIBUTES as $key => $value) {
                            if (array_key_exists('remote_server_id', $_GET) && !empty(sanitize_text_field($_GET['remote_server_id'])) && sanitize_text_field($remote_data_row[0]->provisioning_type) == $key) {
                                if (sanitize_text_field($value[0]) == "SCIM") {
                                    $type = 'url';
                                    $placeholder1 = "Enter URL of the Remote Server";
                                    $placeholder2 = "Enter bearer token of the Remote Server";
                                } elseif ($value[0] == "API") {
                                    $type = 'text';
                                    $placeholder1 = "Enter Customer Id of Remote Server";
                                    $placeholder2 = "Enter API Key of the Remoter Server";
                                } elseif ($value[0] == "API-NextCloud") {
                                    $type = 'text';
                                    $placeholder = "Enter NextCloud Password";
                                    $placeholder1 = "Enter NextCloud URL";
                                    $placeholder2 = "Enter NextCloud Username";
                                }
                                ?>
                                <div class="mo-user-sync-dflex mo-sf-ml-1">
                                    <div class="mo-user-sync-col-md-6 ">
                                        <h2><?php echo esc_attr($value[1]) ?><span
                                                    class="mo-user-sync-text-danger">*</span></h2>
                                    </div>
                                    <div class="mo-user-sync-col-md-6">
                                        <div>
                                            <input type= <?php echo $type ?> id="mo-user-sync-url"
                                                   class="mo-user-sync-fields" required name="mo-user-sync-remote-url"
                                                   value="<?php if ($already_present) echo sanitize_text_field($remote_data_row[0]->url); ?>"
                                                   placeholder= <?php echo $placeholder1 ?>>
                                        </div>
                                    </div>
                                </div>

                                <div class="mo-user-sync-dflex mo-sf-ml-1">
                                    <div class="mo-user-sync-col-md-6 ">
                                        <h2><?php echo esc_attr($value[2]) ?><span
                                                    class="mo-user-sync-text-danger">*</span></h2>
                                    </div>
                                    <div class="mo-user-sync-col-md-6">
                                        <div>
                                            <input type="text" id="mo-user-sync-bearer-token"
                                                   class="mo-user-sync-fields" required
                                                   name="mo-user-sync-remote-bearer-token"
                                                   value="<?php if ($already_present) echo sanitize_text_field($remote_data_row[0]->bearer_token); ?>"
                                                   placeholder= <?php $placeholder2 ?>>
                                        </div>
                                    </div>
                                </div>

                                <?php
                                if (sanitize_text_field($remote_data_row[0]->provisioning_type) == "NextCloud") {
                                    ?>
                                    <div class="mo-user-sync-dflex mo-sf-ml-1">
                                        <div class="mo-user-sync-col-md-6 ">
                                            <h2><?php echo esc_attr($placeholder) ?><span
                                                        class="mo-user-sync-text-danger">*</span></h2>
                                        </div>
                                        <div class="mo-user-sync-col-md-6">
                                            <div>
                                                <input type="password" id="mo-user-sync-password"
                                                       class="mo-user-sync-fields" required
                                                       name="mo-user-sync-remote-password"
                                                       value="<?php if ($already_present) echo $password[0]->option_value; ?>"
                                                       placeholder= <?php $placeholder2 ?>>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } elseif (empty(sanitize_text_field(isset($remote_data_row[0]->provisioning_type)))) {
                                $key == "Tableau";
                                ?>

                                <div class="mo-user-sync-dflex mo-sf-ml-1">
                                    <div class="mo-user-sync-col-md-6 ">
                                        <h2><?php echo esc_attr($value[1]) ?><span
                                                    class="mo-user-sync-text-danger">*</span></h2>
                                    </div>
                                    <div class="mo-user-sync-col-md-6">
                                        <div>
                                            <input type="url" id="mo-user-sync-url" class="mo-user-sync-fields" required
                                                   name="mo-user-sync-remote-url"
                                                   value="<?php if ($already_present) echo esc_url_raw($remote_data_row[0]->url); ?>"
                                                   placeholder="Enter URL of the Remote Server">
                                        </div>
                                    </div>
                                </div>

                                <div class="mo-user-sync-dflex mo-sf-ml-1">
                                    <div class="mo-user-sync-col-md-6 ">
                                        <h2><?php echo esc_attr($value[2]) ?><span
                                                    class="mo-user-sync-text-danger">*</span></h2>
                                    </div>
                                    <div class="mo-user-sync-col-md-6">
                                        <div>
                                            <input type="text" id="mo-user-sync-bearer-token"
                                                   class="mo-user-sync-fields" required
                                                   name="mo-user-sync-remote-bearer-token"
                                                   value="<?php if ($already_present) echo sanitize_text_field($remote_data_row[0]->bearer_token); ?>"
                                                   placeholder="Enter bearer token of the Remote Server">
                                        </div>
                                    </div>
                                </div>
                                <?php
                                break;
                            }
                        }
                        ?>
                    </div>
                    <div class="mo-user-sync-dflex mo-sf-ml-1">
                        <div class="mo-user-sync-col-md-6 ">
                            <h2>Create User in Remote when User is created in WordPress :<span
                                        class="mo-user-sync-text-danger">*</span></h2>
                        </div>

                        <div class="mo-user-sync-col-md-6">
                            <div>
                                <label class="switch" name="mo-user-sync-create">
                                    <input type="checkbox" name="mo-user-sync-create" <?php
                                    if (isset($trigg[0]->triggers)) {
                                        echo 'checked';
                                    } else
                                        echo ' ';
                                    ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mo-user-sync-dflex mo-sf-ml-1 mo-user-sync-prem-info">
                        <div class="mo-user-sync-col-md-6 ">
                            <h2>Update User in Remote when User is updated in WordPress :</h2>
                        </div>
                        <div class="mo-user-sync-col-md-6">
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="mo-user-sync-update" disabled>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="mo-user-sync-prem-lock">
                            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../Images/lock.png'); ?>" alt="">
                            <p class="mo-user-sync-prem-text">Available in premium plugin. <a href=""
                                                                                              class="mo-user-sync-text-warning">Click
                                    here to upgrade</a></p>
                        </div>
                    </div>
                    </br>
                    <div class="mo-user-sync-dflex mo-sf-ml-1 mo-user-sync-prem-info">
                        <div class="mo-user-sync-col-md-6">
                            <h2>Delete User in Remote when User is deleted in WordPress :</h2>
                        </div>
                        <div class="mo-user-sync-col-md-6">
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="mo-user-sync-delete" disabled>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="mo-user-sync-prem-lock">
                            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../Images/lock.png'); ?>" alt="">
                            <p class="mo-user-sync-prem-text">Available in premium plugin. <a href=""
                                                                                              class="mo-user-sync-text-warning">Click
                                    here to upgrade</a></p>
                        </div>
                    </div>
                    </br>
                    <div style="text-align: center ;">
                        <input type="submit" class="mo-user-sync-btn-cstm" value="Save Configuration"
                               name="mo-user-sync-save-remote-config" onclick="">

                        <input type="button"
                            <?php if ($already_present == false)
                                echo 'disabled';
                            else
                                echo ' ';
                            ?>
                               name="mo_user_sync_test_connection" class="mo-user-sync-btn-cstm"
                               value="Test Configuration"
                               onclick="mo_user_sync_show_test_window('<?php echo wp_nonce_url(home_url() . '/?option=test_server_configuration&id=' . (($already_present) ? $remote_data_row[0]->id : '')) ?>');">
                    </div>
                </form>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
    echo '
	<script>
	function mo_user_sync_show_test_window(url){
	    window.open(url,"TEST USER SYNC","scrollbars=1 width=800, height=600")
	}
	</script>
	';
}