<div class="osa-option-wrapper">
    <p class="osa-option-title"><strong><?php _e("Description",'onlinerShopApp'); ?></strong></p>
	<p class="osa-option-description"><?php echo $page['title']; ?></p>
    <form action="" method="POST" id="on5_form_panel" class="panel_form slider_form">
        <?php
        $slider = $page['slider_name'];
        $tax = $page['taxonomy'];
        $description = $page['title'];
        $link_name = $page['link_name'];
        $typeLink_name = $page['typeLink_name'];
        $title_name = $page['title_name'];
        $caption_name = $page['caption_name'];
        $category_name = $page['category_name'];
        $links = storina_get_option($link_name);
        $typeLinks = storina_get_option($typeLink_name);
        $titles = storina_get_option($title_name);
        $captions = storina_get_option($caption_name);
        $cats = storina_get_option($category_name);


        global $product_cats;
        $product_cats     = array();
        $product_cats[-1] = esc_html__("All",'onlinerShopApp');
        $product_cats     = storina_hierarchical_category_tree2( 0 , $tax);
        global $osa_autoload;
        $general          = $osa_autoload->service_provider->get(\STORINA\Controllers\General::class);
        $action           = $general->clickEventList();
        if ( function_exists( 'dokan_get_store_info' ) ) {
            $action['VendorPage'] = esc_html__( 'Open vendor page', 'onlinerShopApp' );
        }
        ?>
        <div class="clear"></div>
        <table class="wp-list-table widefat fixed">
            <thead>
            <th><?php echo__("Title",'onlinerShopApp');?></th><th><?php echo__("Text",'onlinerShopApp');?></th><th><?php echo__("Link type",'onlinerShopApp');?></th><th><?php echo__("Value",'onlinerShopApp');?></th><th><?php echo__("Action",'onlinerShopApp');?></th>
            </thead>
            <tfoot>
            <th><?php echo__("Title",'onlinerShopApp');?></th><th><?php echo__("Text",'onlinerShopApp');?></th><th><?php echo__("Link type",'onlinerShopApp');?></th><th><?php echo__("Value",'onlinerShopApp');?></th><th><?php echo__("Action",'onlinerShopApp');?></th>
            </tfoot>
            <?php
            if($titles){
                $i = 0;
                foreach($titles as $title){

                    ?>
                    <tr >

                        <td><input type="text" name="<?php echo $slider; ?>[title][]" value="<?php echo $titles[$i]; ?>"/></td>
                        <td><textarea name="<?php echo $slider; ?>[text][]" ><?php echo $captions[$i]; ?></textarea></td>
                        <td>
                            <select class="select_box" name="<?php echo $slider; ?>[typeLink][]">
                                <?php foreach ($action as $item => $value) { ?>
                                    <option value="<?php echo$item?>" <?php if($typeLinks[$i] == $item){echo 'selected="selected"';} ?>><?php echo$value?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td><input type="text" name="<?php echo $slider; ?>[link][]" value="<?php echo $links[$i]; ?>"/></td>

                        <td>
                            <input title="<?php echo $slider; ?>" type="button" class="button-primary delete_row" value="<?php echo__("Delete",'onlinerShopApp');?>">
                        </td>
                    </tr>
                    <?php
                    $i++;
                }
                }else{ ?>
                <tr>

                    <td><input type="text" name="<?php echo $slider; ?>[title][]" /></td>
                    <td><textarea name="<?php echo $slider; ?>[text][]" ></textarea></td>
                    <td>
                        <select class="select_box" name="<?php echo $slider; ?>[typeLink][]">
                            <?php foreach ($action as $item => $value) { ?>
                                <option value="<?php echo$item?>" ><?php echo$value?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td><input type="text" name="<?php echo $slider; ?>[link][]" /></td>
                    <td>
                        <input title="<?php echo $slider; ?>" type="button" class="button-primary delete_row" value="<?php echo__("Delete",'onlinerShopApp');?>">
                    </td>
                </tr>
            <?php }

            //$value = storina_get_option(); ?>

        </table>
<div class="osa-submit-wrapper-table">
<input type="hidden" name="apptype_form" value="text_adsplus">
        <input type="hidden" name="appname_form" value="<?php echo($pages[$counter-1]['apppagename']); ?>">
        <input type="submit" value="<?php echo__("Save",'onlinerShopApp')?>" name="submit_theme_options" class="button save">
        <button type="button" class="button add_row"><?php echo esc_html__( "Add ", 'onlinerShopApp' ) ?></button>
</div>
    </form>
</div>
