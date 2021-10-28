<div class="osa-option-wrapper">
<p class="osa-option-title"><strong><?php _e("Description",'onlinerShopApp'); ?></strong></p>
	<p class="osa-option-description"><?php echo $page['title']; ?></p>
    <form action="" method="POST" id="on5_form_panel" class="panel_form textads_form">
        <?php
        $description = $page['title'];
        $custom_option_name = $page['custom_option_name'];
        $custom_option2 = $page['custom_option2'];
        $custom_option3 = $page['custom_option3'];
        $custom_option4 = $page['custom_option4'];
        $custom_option5 = $page['custom_option5'];
        $custom_option6 = $page['custom_option6'];
        $custom_option_value2 = storina_get_option($custom_option2);
        $custom_option_value3 = storina_get_option($custom_option3);
        $custom_option_value4 = storina_get_option($custom_option4);
        $custom_option_value5 = storina_get_option($custom_option5);
        $custom_option_value6 = storina_get_option($custom_option6);
        global $product_cats;
        $product_cats        = array();
        $product_cats[ - 1 ] = __( "All", 'onlinerShopApp' );
        $product_cats        = storina_hierarchical_category_tree2( 0, 'product_cat' );
        global $osa_autoload;
        $general             = $osa_autoload->service_provider->get(\STORINA\Controllers\General::class);
        $action              = $general->clickEventList();
        if ( function_exists( 'dokan_get_store_info' ) ) {
            $action['VendorPage'] = __( 'Open vendor page', 'onlinerShopApp' );
        }
        ?>
        <div class="clear"></div>
        <table class="wp-list-table widefat fixed">
            <thead>
            <th><?= __( "Title", 'onlinerShopApp' ); ?></th>
            <th><?= __( "Banner address 1", 'onlinerShopApp' ); ?></th>
            <th><?= __( "Link type", 'onlinerShopApp' ); ?></th>
            <th><?= __( "Value", 'onlinerShopApp' ); ?></th>
            <th><?= __( "Action", 'onlinerShopApp' ); ?></th>

            </thead>
            <tfoot>
            <th><?= __( "Title", 'onlinerShopApp' ); ?></th>
            <th><?= __( "Banner address 1", 'onlinerShopApp' ); ?></th>
            <th><?= __( "Link type", 'onlinerShopApp' ); ?></th>
            <th><?= __( "Value", 'onlinerShopApp' ); ?></th>
            <th><?= __( "Action", 'onlinerShopApp' ); ?></th>

            </tfoot>
            <!-- <tr class="header_th">

            </tr>-->
            <?php
            if ( $custom_option_value2 ) {
                $i = 0;
                foreach ( $custom_option_value2 as $title ) { ?>
                    <tr>
                        <td>
                            <input type="text" name="<?php echo $custom_option_name ?>[option6][]" 
                                value="<?php echo $custom_option_value6[$i] ?>">
                        </td>
                        <td>
                            <input class="target_line" type="text" name="<?php echo $custom_option_name; ?>[option3][]"
                                value="<?php echo $custom_option_value3[ $i ]; ?>"/>
                            <input type="button" name="upload-btn" class="upload-btn button-secondary"
                                value="<?= __( "Upload", 'onlinerShopApp' ); ?>">
                        </td>
                        <td>
                            <select class="select_box" name="<?php echo $custom_option_name; ?>[option4][]">
                                <?php foreach ( $action as $item => $value ) { ?>
                                    <option value="<?= $item ?>" <?php if ( $custom_option_value4[ $i ] == $item ) {
                                        echo 'selected="selected"';
                                    } ?>><?= $value ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td><input type="text" name="<?php echo $custom_option_name; ?>[option5][]"
                                value="<?php echo $custom_option_value5[ $i ]; ?>"/></td>
                        <td>
                            <input title="<?php echo $custom_option_name; ?>" type="button"
                                class="button-primary delete_row" value="<?= __( "Delete", 'onlinerShopApp' ); ?>">
                        </td>
                    </tr>
                    <?php
                    $i ++;
                }
            } else { ?>
                <tr>
                    <td>
                        <input type="text" name="<?php echo $custom_option_name ?>[option6][]" 
                            value="<?php echo $custom_option_value6[$i] ?>">
                    </td>
                    <td>
                        <input class="target_line" type="text" name="<?php echo $custom_option_name; ?>[option3][]"/>
                        <input type="button" name="upload-btn" class="upload-btn button-secondary"
                            value="<?= __( "Upload", 'onlinerShopApp' ); ?>">
                    </td>
                    <td>
                        <select class="select_box" name="<?php echo $custom_option_name; ?>[option4][]">
                            <?php foreach ( $action as $item => $value ) { ?>
                                <option value="<?= $item ?>"><?= $value ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td><input type="text" name="<?php echo $custom_option_name; ?>[option5][]"/></td>

                    <td>
                        <input title="<?php echo $custom_option_name; ?>" type="button" class="button delete_row"
                            value="<?= __( "Delete", 'onlinerShopApp' ); ?>">
                    </td>
                </tr>
            <?php }

            //$value = storina_get_option(); ?>

        </table>
        <div class="osa-submit-wrapper-table">
            <input type="hidden" name="apptype_form" value="custom">
            <input type="hidden" name="appname_form" value="<?php echo($pages[$counter-1]['apppagename']); ?>">
            <input type="submit" value="<?=__("Save",'onlinerShopApp')?>" name="submit_theme_options" class="button save">
            <button type="button" class="button add_row"><?= __( "Add ", 'onlinerShopApp' ) ?></button>
        </div>
    </form>
</div>
