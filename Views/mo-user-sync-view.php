<?php
require_once "mo-user-sync-configuration.php";

function mo_user_sync()
{
    $currenttab = "sp_config";
    if (array_key_exists('tab', $_GET)) {
        $currenttab = sanitize_text_field($_GET['tab']);
    }
    mo_user_sync_display_header();
    mo_user_sync_display_tabs($currenttab);

    echo '<div class="mo-user-sync-row mo-user-sync-container-fluid">
				<div class="mo-user-sync-col-md-8">';
    mo_user_sync_display_body($currenttab);
    echo '</div>
            <div class="mo-user-sync-col-md-4">';
    \MoUserSyncMessageUtilities::mo_user_sync_show_information_message();
    mo_user_sync_support();
    echo '		</div></div>';
}

function mo_user_sync_display_header()
{
    ?>
    <div class="mo-user-sync-dflex1">
        <div>
            <h1>WP Remote User Sync</h1>
        </div>
        <div class="mo-sf-col-md-6" style="margin-top: 12px;margin-left:auto">
            <a href="https://forum.miniorange.com/" target="_blank">
                <button class="orange-oval"><strong>Forum</strong></button>
            </a>
            <a href="https://faq.miniorange.com/" target="_blank">
                <button class="orange-oval"><strong>FAQ's</strong></button>
            </a>

        </div>
    </div>


    <?php
}

function mo_user_sync_display_tabs($currenttab)
{

    ?>
    <div id="mo_user_sync_tab_view" class="mo-user-sync-tab user-sync-tab-background mo-user-sync-tab-border">
        <ul id="mo_user_sync_tab_view_ul" class="mo-user-sync-tab-ul" role="toolbar">
            <li id="config" class="mo-user-sync-tab-li" role="presentation" title="Configuration">
                <a href="<?php echo esc_url(add_query_arg('tab', 'sp_config')); ?>">
                    <div class="mo-user-sync-tab-li-div <?php if ($currenttab == 'sp_config') {
                        echo 'mo-user-sync-tab-li-div-active';
                    } ?>" role="button" tabindex="0">
                        <div id="add_app_label" class="mo-user-sync-tab-li-label">
                            Configuration
                        </div>
                    </div>
                </a>
            </li>

            <li id="config" class="mo-user-sync-tab-li" role="presentation" title="Configuration">
                <a href="<?php echo esc_url(add_query_arg('tab', 'request_demo')); ?>">
                    <div class="mo-user-sync-tab-li-div <?php if ($currenttab == 'request_demo') {
                        echo 'mo-user-sync-tab-li-div-active';
                    } ?>" role="button" tabindex="0">
                        <div id="add_app_label" class="mo-user-sync-tab-li-label">
                            Request Demo
                        </div>
                    </div>
                </a>
            </li>
        </ul>
    </div>
    <?php
}

function mo_user_sync_display_body($currenttab)
{
    switch ($currenttab) {
        case 'sp_config':
        {
            mo_user_sync_configuration_body();
            break;
        }
        case 'request_demo':
        {
            include_once __DIR__ . '/Request-demo.php';
            mo_user_sync_request_demo();
            break;
        }
    }
}

// todo: write code for support form handling
function mo_user_sync_support($currenttab = '')
{
    ?>
    <div class="mo-user-sync-support-layout mo-user-sync-mt-4">
        <h1 class="mo-user-sync-cnt-head">
            <?php _e('Feature Request/Contact Us <br> (24*7 Support)', 'WP Remote User Sync'); ?>
        </h1>
        <div>
            <div class="mo-user-sync-text"><b>
                    <?php _e('Call us at +1 978 658 9387 in case of any help', 'WP Remote User Sync'); ?>
                </b></div>
        </div>
        <p class="mo-user-sync-text">
            <?php _e('We can help you with configuring your plugin. Just send us a query and we will get back to you soon.', 'WP Remote User Sync'); ?>
            <br>
        </p>

        <form id="mo_user_sync_support_form" method="post" action="">
            <input type="hidden" name="option" value="mo_user_sync_contact_us_query_option"/>
            <input type="hidden" name="tab" value="<?php echo esc_attr($currenttab) ?>">
            <input type="hidden" name="nonce_"
                   value="<?php echo wp_nonce_field('mo_user_sync_contact_us_query_option'); ?>">
            <table class="mo-user-sync-settings-table">
                <tr>
                    <td><input type="email" id="mo_user_sync_support_email"
                               placeholder="<?php _e('Enter your email', 'WP Remote User Sync'); ?>"
                               class="mo-user-sync-table-textbox" name="mo_user_sync_contact_us_email"
                               value="<?php echo get_option('admin_email') ?>" required>
                    </td>
                </tr>
                <tr>
                    <td><textarea class="mo-user-sync-table-textbox" onkeypress="mo_user_sync_valid_query(this)"
                                  onkeyup="mo_user_sync_valid_query(this)" onblur="mo_user_sync_valid_query(this)"
                                  name="mo_user_sync_contact_us_query" rows="4" style="resize: vertical;" required
                                  placeholder="<?php _e('Write your query here', 'WP Remote User Sync'); ?>"
                                  id="mo_user_sync_query"></textarea>
                    </td>
                </tr>
            </table>
            <div>
                <input type="submit" name="submit" style="margin:15px; width:120px;" class="mo-user-sync-btn-cstm"/>
            </div>
        </form>
    </div><br>

    <?php
}