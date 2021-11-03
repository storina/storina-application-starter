<?php wp_enqueue_media(); ?>
<div class="osa-notification-panel-wrapper">
    <form action="" method="post" id="osa-report-notification-form-<?php echo esc_html($viewer['identifier']); ?>" class="osa-report-notification-form">
        <div class="osa-report-notification-form-output">
            <p class="osa-report-notification-form-preloader"><?php _e("Loading...","onlinerShopApp"); ?></p>
            <p class="osa-report-notification-form-result"></p>
        </div>
        <input type="hidden" name="identifier" value="<?php echo esc_html($viewer['identifier']); ?>">
        <input type="hidden" name="authentication" value="<?php echo esc_html($viewer['authentication_value']); ?>">
        <div class="osa-report-form-item">
            <div><label class="osa-report-form-label" for=""><?php _e("Title","onlinerShopApp"); ?></label></div>
            <div class="woap-report-form-field-wrapper">
                <input type="text" name="title" class="regular-text">
            </div>
        </div>
        <div class="osa-report-form-item">
            <div><label class="osa-report-form-label" for=""><?php _e("Body","onlinerShopApp"); ?></label></div>
            <div class="woap-report-form-field-wrapper">
                <textarea name="body" class="regular-text" rows="5"></textarea>
            </div>
        </div>
        <div class="osa-report-form-item">
            <div><label class="osa-report-form-label"><?php _e("Click event type","onlinerShopApp"); ?></label></div>
            <div class="woap-report-form-field-wrapper">
                <select name="click_event_type" >
                    <?php 
                    foreach($notification_click_event_list as $name => $label){
                        echo "<option value='{$name}'>{$label}</option>";
                    }
                    ?>
                </select>
                <p class="woap-notification-form-caption"><?php _e("What do you want then touch on notification?","onlinerShopApp"); ?></p>
            </div>
        </div>
        <div class="osa-report-form-item">
            <div><label class="osa-report-form-label" for=""><?php _e("Click Event Value","onlinerShopApp"); ?></label></div>
            <div class="woap-notification-icon-wrapper">
                <input type="text" name="click_event_value" class="regular-text">
                <p class="woap-notification-form-caption"><?php _e("Enter the desired value according to the link type field.","onlinerShopApp"); ?></p>
            </div>
        </div>
        <div class="osa-report-form-item">
            <div><label class="osa-report-form-label" for=""><?php _e("Notification icon","onlinerShopApp"); ?></label></div>
            <div class="woap-notification-icon-wrapper">
                <input type="text" name="notification_icon" class="woap-notification-icon-input regular-text">
                <span class="dashicons dashicons-format-image woap-icon-uploader"></span>
                <p class="woap-notification-form-caption"><?php _e("Select notification icon 512*512 px","onlinerShopApp"); ?></p>
            </div>
        </div>
        <div class="osa-report-form-item">
            <p><input class="button button-primary" type="submit" name="osa_report_form_notif_submit" value="<?php _e("send","onlinerShopApp"); ?>"></p>
        </div>
    </form>
</div>
