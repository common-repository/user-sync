<?php

use MoUserSync\Core\MiniOrangeEnums;
use MoUserSync\Core\NextCloudEnums;
use MoUserSync\Core\TableauEnums;
use MoUserSync\Core\TalentLMSEnums;


require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'scim' . DIRECTORY_SEPARATOR . 'TalentLMSEnums.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'okta' . DIRECTORY_SEPARATOR . 'OktaEnums.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'scim' . DIRECTORY_SEPARATOR . 'TableauEnums.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'nextCloud' . DIRECTORY_SEPARATOR . 'NextCloudEnums.php';


function show_attribute()
{
    $data = get_user_info_list();
    $current_attribute_id = sanitize_text_field($_GET['remote_server_id']);
    $table = new DBUtils();

    $attribute_fields = $table->mo_user_sync_show_attributes_fields($current_attribute_id);
    $id = array();
    $mo_attributes = array();
    foreach ($attribute_fields as $value) {
        $mo_attributes[] = array($value->option_value, $value->option_name);
        array_push($id, $value->id);
    }
    $provisioning_type = $table->mo_user_sync_get_provisioning_type_with_remote_server_id($current_attribute_id);
    ?>

    <?php

    update_option('default_server_option', $provisioning_type[0]->provisioning_type, true);
    ?>

    <div class="mo-user-sync-tab-content-tile">
        <form method="post" action="#">
            <input type="hidden" name="option" value="attrMap">
            <?php wp_nonce_field('attrMap'); ?>
            <table class="mo-sf-sync-custom-attr-mapping-table" id="mo-user-sync-attr-table" style="width: 100%">
                <thead>
                <tr>
                    <th class="left-div" style="text-align: left;text-align: center;text-align: left;">
                        <h2>Provisioner attributes</h2>
                    </th>
                    <th class="right-div" style="text-align: center;text-align: left;">
                        <h2>Wordpress attributes</h2>
                    </th>
                </tr>
                </thead>
                <tbody id="mo-user-sync-attr">
                <?php

                $loop_var = array();

                if ($provisioning_type[0]->provisioning_type == "MiniOrange") {
                    $enumKey = array_keys(MiniOrangeEnums::REQUIREDATTRIBUTESMINIORANGE);
                } elseif ($provisioning_type[0]->provisioning_type == "Tableau") {
                    $enumKey = array_keys(TableauEnums::REQUIREDATTRIBUTESSCIMTABLEAU);
                } elseif ($provisioning_type[0]->provisioning_type == "TalentLMS") {
                    $enumKey = array_keys(TalentLMSEnums::REQUIREDATTRIBUTESSCIMTALENTLMS);
                } elseif ($provisioning_type[0]->provisioning_type == "Okta") {
                    $enumKey = array_keys(OktaEnums::REQUIREDATTRIBUTESOKTA);
                } elseif ($provisioning_type[0]->provisioning_type == "NextCloud") {
                    $enumKey = array_keys(NextCloudEnums::REQUIREDATTRIBUTESNEXTCLOUD);
                }

                for ($i = 0; $i < sizeof($enumKey); $i++) {
                    if (isset($mo_attributes[$i])) {
                        $value = $mo_attributes[$i];
                        $loop_var[$enumKey[$i]] = $value[1];
                    } else
                        $loop_var[$enumKey[$i]] = '';
                }

                foreach ($loop_var as $key => $value) {
                    ?>
                    <tr>
                        <td class="left-div">
                            <p class="form-control object_field"
                               style="width:300px;font-size: medium"><?php echo esc_attr($key) ?><span
                                        class="mo-user-sync-text-danger">*</span></p>
                            <input type="hidden" id="cust" name="mo_default_data_attributes[]"
                                   value="<?php echo esc_attr($key) ?>">
                        </td>
                        <td class="right-div">
                            <select class="form-control wordpress_field" required id="testingid"
                                    name="mo_default_attributes[]">
                                <?php
                                $opt_type = '';
                                echo "<option value=''>__none__</option>";
                                foreach ($data as $ke => $field) {
                                    if ($value == $ke) {
                                        echo "<option selected='selected' value='" . implode(",", array(esc_attr($ke), esc_attr($field))) . "'>" . esc_attr($ke) . "</option>";
                                    } else {
                                        if ($opt_type != $field) {
                                            if ($opt_type != '') {
                                                echo '</optgroup>';
                                            }
                                            echo '<optgroup label="' . esc_attr($field) . '">';
                                        }
                                        echo "<option value='" . implode(",", array(esc_attr($ke), esc_attr($field))) . "'>" . esc_attr($ke) . "</option>";
                                        $opt_type = $field;
                                    }
                                }
                                if ($opt_type != '') {
                                    echo '</optgroup>';
                                }
                                echo "<option>__custom__</option>";
                                ?>
                        </td>

                    </tr> <?php
                }
                ?>
                </tbody>
            </table>
            <table class="mo-user-sync-tab-content-app-config-table">
                <tr>
                    <td>
                        <div class="mo-user-sync-text-center mo-user-mt-4">
                            <input id="savebutton" type="submit" class="mo-user-btn-cstm field-submit" value="Save">
                        </div>

                    </td>
                </tr>
            </table>

        </form>
    </div>
    <?php

}

function get_user_info_list()
{
    global $wpdb;
    $current_user = wp_get_current_user();
    $userMetaTable = $wpdb->prefix . 'usermeta';
    $user_info = $wpdb->get_results("SELECT DISTINCT meta_key FROM " . $userMetaTable);
    $user_attr = array();

    foreach ($current_user->data as $key => $value)
        $user_attr[$key] = "user-table";
    foreach ($user_info as $key => $value)
        $user_attr[$value->meta_key] = "user-meta-table";

    return $user_attr;
}