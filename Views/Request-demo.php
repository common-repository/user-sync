<?php
function mo_user_sync_request_demo()
{
    ?>

    <div class="mo-user-sync-tab-content">
        <div class="mo-user-sync-tab-content-left-border">
            <form class="mo_user_sync_ajax_submit_form_2" id="mo_user_sync_demo_form" method="post">
                <input type="hidden" name="option" value="mo_user_sync_demo_request">
                <input type="hidden" name="tab" value="demo_setup">
                <?php wp_nonce_field('mo_user_sync_demo_request'); ?>
                <div class="mo-user-sync-tab-content-tile mo-user-mt-4">
                    <h1 class="mo-user-form-head">Request for Demo</h1>

                    <table class="mo-user-sync-tab-content-app-config-table">
                        <tr>
                            <td class="left-div"><span>Email<sup style="color:red">*</sup></span></td>
                            <td>
                                <input class="mo-user-w-3" type="email" required placeholder="person@example.com"
                                       name="demo_email"
                                       value="<?php echo sanitize_email(wp_get_current_user()->user_email) ?>">
                            </td>
                        </tr>
                        <tr>
                            <td class="left-div"><span>Description<sup style="color:red">*</sup></span></td>
                            <td>
                                <textarea class="mo-user-w-3" rows="4" type="text" required
                                          placeholder="Tell us about your requirement."
                                          name="demo_description"></textarea>

                            </td>
                        </tr>
                    </table>
                    <h2 class="mo-user-form-head mo-user-form-head-bar">Select the Add-ons you are interested in
                        (Optional) :</h2>
                    <?php
                    $column = 0;
                    $column_start = 0;
                    $INTEGRATIONS_TITLE = MoUserSyncEnums::INTEGRATIONS_TITLE;
                    foreach ($INTEGRATIONS_TITLE as $key => $value) { ?>

                        <?php if ($column % 3 === 0) {
                            $column_start = $column; ?>
                            <div class="mo-user-row mo-user-ml-1 mo-user-mt-4 mo-user_sync-opt-add-ons">
                        <?php } ?>
                        <div class="mo-user-col-md-4">
                            <input type="checkbox" name="<?php echo esc_attr($key); ?>" value="true"> <span
                                    class="mo-user-text"><?php echo esc_attr($value); ?></span>
                        </div>
                        <?php if ($column === $column_start + 2) { ?>
                            </div>
                        <?php } ?>

                        <?php $column++;
                    }
                    ?>
                    <div class="mo-user-mt-4">
                        <input type="submit" class="mo-user-btn-cstm" name="submit" value="Send Request">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
}