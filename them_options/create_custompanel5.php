<?php 
$option_names = $page['option-names'];
global $osa_autoload;
$general = $osa_autoload->service_provider->get(\STORINA\Controllers\General::class);
$action = $general->clickEventList();
$columns = [
    1 => esc_html__("One column",'onlinerShopApp'),
    2 => esc_html__("Two column",'onlinerShopApp'),
    4 => esc_html__("Four column",'onlinerShopApp'),
];
?>
<div class="osa-option-wrapper">
    <p class="osa-option-title"><strong><?php _e("Description",'onlinerShopApp'); ?></strong></p>
	<p class="osa-option-description"><?php echo $page['title']; ?></p>
    <style>
        .woap-ads-table,
        .woap-ads-table input{
            width:auto !important;
        }
        .woap-ads-table select {
            width: 100% !important;
        }
        .d-flex {
            display: flex;
            justify-content: center;
            flex-wrap: nowrap;
            margin-bottom: 5px;
        }
        .flex-column {
            margin: auto 5px;
        }
        .woap-ads-img {
            display: flex;
        }
        .woap-ads-img .target_line {
            width: 50% !important;
        }
        .woap-adc-link-value-wrapper {
            width: 30%;
        }
        .woap-adc-link-value-wrapper input {
            width: 100% !important;
        }
    </style>
    <form action="" method="POST" id="on5_form_panel" class="panel_form textads_form">
        <div class="clear"></div>
        <table class="wp-list-table widefat fixed woap-ads-table">
            <thead>
                <th style="width: 20px;"><strong class="sort_elem">|||</strong></th>
                <th><?php _e("Banners, from the right Banner link, Link type, Link value","onlinerShopApp"); ?></th>
                <th><?php echo esc_html__( "Column", 'onlinerShopApp' ); ?></th>
                <th><?php echo esc_html__( "Action", 'onlinerShopApp' ); ?></th>
            </thead>
            <tbody class="woap-adc-tbody">
            <?php 
            $banners = storina_get_option($option_names['banner']);
            $link_types = storina_get_option($option_names['link_type']);
            $link_values = storina_get_option($option_names['link_value']);
            $banner_columns = storina_get_option($option_names['column']);
            if(!empty($banners)){
                for($i=0;$i<count($banners);$i++){
                    ?>
                    <tr class="woap-adc-row">
                        <td style="width: 20px; text-align: center;">
                            <strong class="sort_elem">|||</strong>
                        </td>
                        <td class="adc-banner-wrapper">
                        <?php 
                        $banner = $banners[$i];
                        $link_type = $link_types[$i];
                        $link_value = $link_values[$i];
                        for($j=0;$j<count($banner);$j++){
                            ?>
                            <div class="d-flex">
                                <div class="flex-column woap-ads-img">
                                    <input class="target_line" type="text" name="<?php echo $option_names['banner']; ?>[<?php echo $i; ?>][]" value="<?php echo $banner[$j]; ?>" />
                                    <input type="button" name="upload-btn" class="upload-btn button-secondary" value="<?php _e( "Upload", 'onlinerShopApp' ); ?>">
                                </div>
                                <div class="flex-column">
                                    <select class="select_box" name="<?php echo $option_names['link_type']; ?>[<?php echo $i; ?>][]">
                                        <?php foreach ( $action as $key => $label ) { ?>
                                        <option value="<?php echo $key; ?>" <?php echo ($key == $link_type[$j])? "selected" : ""; ?>><?php echo $label; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="flex-column woap-adc-link-value-wrapper">
                                    <input type="text" name="<?php echo $option_names['link_value']; ?>[<?php echo $i; ?>][]" value="<?php echo $link_value[$j]; ?>"/>
                                </div>
                            </div>
                            <!-- .d-flex -->  
                            <?php
                        }
                        ?>
                        </td>
                        <td>
                            <select class="select_box woap-adc-banner-column" name="<?php echo $option_names['column']; ?>[]">
                                <?php foreach ( $columns as $key => $label ) { ?>
                                    <option value="<?php echo $key; ?>" <?php echo ($key == $banner_columns[$i])? "selected" : ""; ?>><?php echo $label; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td>
                            <input type="button" class="button-primary delete_row" value="<?php _e( "Delete", 'onlinerShopApp' ); ?>">
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
        <div class="clear"></div>
        <div class="osa-submit-wrapper-table">
            <input type="hidden" name="apptype_form" value="woap-home-adc">
            <input type="hidden" name="appname_form" value="<?php echo($pages[$counter-1]['apppagename']); ?>">
            <input type="submit" value="<?php echo esc_html__("Save",'onlinerShopApp')?>" name="submit_theme_options" class="button save">
            <button type="button" class="button add-adc-row"><?php echo esc_html__( "Add ", 'onlinerShopApp' ) ?></button>
        </div>
    </form>
</div>

<template id="adc-row-template">
    <tr class="woap-adc-row">
        <td style="width: 20px; text-align: center;">
            <strong class="sort_elem">|||</strong>
        </td>
        <td class="adc-banner-wrapper">
            <div class="d-flex">
                <div class="flex-column woap-ads-img">
                    <input class="target_line" type="text" name="<?php echo $option_names['banner']; ?>[COUNTER_CONST][]" value="" />
                    <input type="button" name="upload-btn" class="upload-btn button-secondary" value="<?php _e( "Upload", 'onlinerShopApp' ); ?>">
                </div>
                <div class="flex-column">
                    <select class="select_box" name="<?php echo $option_names['link_type']; ?>[COUNTER_CONST][]">
                        <?php foreach ( $action as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="flex-column">
                    <input type="text" name="<?php echo $option_names['link_value']; ?>[COUNTER_CONST][]" value=""/>
                </div>
            </div>
            <!-- .d-flex -->
        </td>
        <td>
            <select class="select_box woap-adc-banner-column" name="<?php echo $option_names['column']; ?>[]">
                <?php foreach ( $columns as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                <?php } ?>
            </select>
        </td>
        <td>
            <input type="button" class="button-primary delete_row" value="<?php _e( "Delete", 'onlinerShopApp' ); ?>">
        </td>
    </tr>
</template>
