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
        $custom_option7 = $page['custom_option7'];
        $custom_option8 = $page['custom_option8'];
        $custom_option9 = $page['custom_option9'];
        $custom_option_value2 = storina_get_option($custom_option2);
        $custom_option_value3 = storina_get_option($custom_option3);
        $custom_option_value4 = storina_get_option($custom_option4);
        $custom_option_value5 = storina_get_option($custom_option5);
        $custom_option_value6 = storina_get_option($custom_option6);
        $custom_option_value7 = storina_get_option($custom_option7);
        $custom_option_value8 = storina_get_option($custom_option8);
        $custom_option_value9 = storina_get_option($custom_option9);
        $types = array(
            'sliderItems' => __( "Slider", 'onlinerShopApp' ),
            'categories'  => __( "Category", 'onlinerShopApp' ),
            /*'featured'    => __( "Amazing box", 'onlinerShopApp' ),*/
            'oneColADV'   => __( "Banners ads", 'onlinerShopApp' ),
            'scrollADV'   => __( "scroll Banners ads", 'onlinerShopApp' ),
            'productBox'  => __( "Product box", 'onlinerShopApp' ),
            'line'        => __( "Line", 'onlinerShopApp' ),
            'space'       => __( "Space", 'onlinerShopApp' ),
            'postBox'     => __("Post box","onlinerShopApp"),
            'productBoxColorize' => __("Product box Colorize","onlinerShopApp"),
            'scrollBox' => __("Scroll Box","onlinerShopApp"),
        );
        ?>
        <div class="clear"></div>
        <table class="wp-list-table widefat fixed">
            <thead>
            <th style="width: 20px;"><strong class="sort_elem">|||</strong></th>
            <th><?php echo __( "Item", 'onlinerShopApp' ); ?></th>
            <th><?php echo __( "Action", 'onlinerShopApp' ); ?></th>
            </thead>
            <tfoot>
            <th style="width: 20px;"><strong class="sort_elem">|||</strong></th>
            <th><?php echo __( "Item", 'onlinerShopApp' ); ?></th>
            <th><?php echo __( "Action", 'onlinerShopApp' ); ?></th>
            </tfoot>
            <tbody>
            <?php
            if ( $custom_option_value2 ) {
                $i = 0;
                foreach ( $custom_option_value2 as $title ) { ?>
                    <tr>
                        <td style="width: 20px; text-align: center;"><strong class="sort_elem">|||</strong></td>
                        <td>
                            <input class="element_id regular-text ltr" type="hidden"
                                name="<?php echo $custom_option_name; ?>[option2][]"
                                value="<?php echo $custom_option_value2[ $i ]; ?>"/>
                            <select class="element select_box" name="<?php echo $custom_option_name; ?>[option3][]">
                                <?php foreach ( $types as $item => $value ) { ?>
                                    <option value="<?php echo $item ?>" <?php if ( $custom_option_value3[ $i ] == $item ) {
                                        echo 'selected="selected"';
                                    } ?>><?php echo $value ?></option>
                                <?php } ?>
                            </select>
                        </td>

                        <td>
                            <input title="<?php echo $custom_option_name; ?>" type="button"
                                class="button-primary delete_row" value="<?php echo __( "Delete", 'onlinerShopApp' ); ?>">
                        </td>
                    </tr>
                    <?php
                    $i ++;
                }
            } else { ?>
                <tr>
                    <td style="width: 20px; text-align: center;"><strong class="sort_elem">|||</strong></td>
                    <td>
                        <input class="element_id regular-text ltr" type="hidden"
                            name="<?php echo $custom_option_name; ?>[option2][]"/>
                        <select class="element select_box" name="<?php echo $custom_option_name; ?>[option3][]">
                            <?php foreach ( $types as $item => $value ) { ?>
                                <option value="<?php echo $item ?>"><?php echo $value ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td>
                        <input title="<?php echo $custom_option_name; ?>" type="button" class="button delete_row"
                            value="<?php echo __( "Delete", 'onlinerShopApp' ); ?>">
                    </td>
                </tr>
            <?php }

            //$value = storina_get_option(); ?>
            </tbody>
        </table>
        <div class="osa-submit-wrapper-table">
            <input type="hidden" name="apptype_form" value="custom">
            <input type="hidden" name="appname_form" value="<?php echo($pages[$counter-1]['apppagename']); ?>">
            <input type="submit" value="<?php echo__("Save",'onlinerShopApp')?>" name="submit_theme_options" class="button save">
            <button type="button" class="button add_row"><?php echo __( "Add ", 'onlinerShopApp' ) ?></button>
        </div>
    </form>

</div>
<template id="osa_custom_option_id_template" >
    <input class="element_id regular-text ltr" type="hidden" name="<?php echo $custom_option_name; ?>[option2][]" value="ELEMENT_ID"/>
</template>
