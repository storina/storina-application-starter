<div class="osa-option-wrapper">
    <p class="osa-option-title"><strong><?php _e("Description",'storina-application'); ?></strong></p>
	<p class="osa-option-description"><?php echo esc_html($page['title']); ?></p>
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
        global $product_cats;
        $product_cats     = array();
        $product_cats[-1] = esc_html__("All",'storina-application');
        $product_cats     = storina_hierarchical_category_tree2( 0 , 'product_cat');
        global $osa_autoload;
        $general          = $osa_autoload->service_provider->get(\STORINA\Controllers\General::class);
        $action           = $general->clickEventList();
        if ( function_exists( 'dokan_get_store_info' ) ) {
            $action['VendorPage'] = esc_html__( 'Open vendor page', 'storina-application' );
        }
        $coll = array(
            1 => esc_html__("One column",'storina-application'),
            2 => esc_html__("Two column",'storina-application'),
        );
        ?>
        <div class="clear"></div>
        <table class="wp-list-table widefat fixed">
            <thead>
            <th style="width: 20px;"><strong class="sort_elem">|||</strong></th>
            <th><?php echo esc_html__( "Banner address 1", 'storina-application' ); ?></th>
            <th><?php echo esc_html__( "Link type", 'storina-application' ); ?></th>
            <th><?php echo esc_html__( "Value", 'storina-application' ); ?></th>
            <th><?php echo esc_html__( "Banner address 2", 'storina-application' ); ?></th>
            <th><?php echo esc_html__( "Link type", 'storina-application' ); ?></th>
            <th><?php echo esc_html__( "Value", 'storina-application' ); ?></th>
            <th><?php echo esc_html__( "Column", 'storina-application' ); ?></th>
            <th><?php echo esc_html__( "Show", 'storina-application' ); ?></th>
            <th><?php echo esc_html__( "Action", 'storina-application' ); ?></th>

            </thead>

            <tbody>
            <?php
            if ( $custom_option_value2 ) {
                $i = 0;
                foreach ( $custom_option_value2 as $title ) { ?>
                    <tr>
                        <td style="width: 20px; text-align: center;"><strong class="sort_elem">|||</strong></td>
                        <td>
                            <input class="target_line" type="text" name="<?php echo esc_html($custom_option_name); ?>[option2][]"
                                value="<?php echo esc_html($custom_option_value2[ $i ]); ?>"/>
                            <input type="button" name="upload-btn" class="upload-btn button-secondary"
                                value="<?php echo esc_html__( "Upload", 'storina-application' ); ?>">
                        </td>
                        <td>
                            <select class="select_box" name="<?php echo esc_html($custom_option_name); ?>[option7][]">
                                <?php foreach ( $action as $item => $value ) { ?>
                                    <option value="<?php echo esc_attr($item); ?>" <?php if ( $custom_option_value7[ $i ] == $item ) {
                                        echo 'selected="selected"';
                                    } ?>><?php echo esc_attr($value); ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td><input type="text" name="<?php echo esc_html($custom_option_name); ?>[option3][]"
                                value="<?php echo esc_html($custom_option_value3[ $i ]); ?>"/></td>
                        <td>
                            <input class="target_line" type="text" name="<?php echo esc_html($custom_option_name); ?>[option4][]"
                                value="<?php echo esc_html($custom_option_value4[ $i ]); ?>"/>
                            <input type="button" name="upload-btn" class="upload-btn button-secondary"
                                value="<?php echo esc_html__( "Upload", 'storina-application' ); ?>">
                        </td>
                        <td>
                            <select class="select_box" name="<?php echo esc_html($custom_option_name); ?>[option8][]">
                                <?php foreach ( $action as $item => $value ) { ?>
                                    <option value="<?php echo esc_attr($item); ?>" <?php if ( $custom_option_value8[ $i ] == $item ) {
                                        echo 'selected="selected"';
                                    } ?>><?php echo esc_attr($value); ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td><input type="text" name="<?php echo esc_html($custom_option_name); ?>[option5][]"
                                value="<?php echo esc_html($custom_option_value5[ $i ]); ?>"/></td>
                        <td>
                            <select class="select_box" name="<?php echo esc_html($custom_option_name); ?>[option6][]">
                                <?php foreach ( $coll as $item => $value ) { ?>
                                    <option value="<?php echo $item ?>" <?php if ( $custom_option_value6[ $i ] == $item ) {
                                        echo 'selected="selected"';
                                    } ?>><?php echo esc_attr($value); ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td>
                            <select name="<?php echo esc_html($custom_option_name); ?>[option9][]" class="widefat">
                                <?php
                                foreach ( $product_cats as $product_cat => $value ) {
                                    $select = "";
                                    if ( $product_cat == $custom_option_value9[ $i ] ) {
                                        $select = "selected";
                                    }
                                    echo '<option  value="' . $product_cat . '" ' . $select . '>' . $value . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                        <!--
                        <td><input type="text" name="<?php /*echo esc_html($custom_option_name); */?>[option7][]" value="<?php /*echo $custom_option_value7[$i]; */?>"/></td>
                        <td><input type="text" name="<?php /*echo esc_html($custom_option_name); */?>[option8][]" value="<?php /*echo $custom_option_value8[$i]; */?>"/></td>
                        <td><input type="text" name="<?php /*echo esc_html($custom_option_name); */?>[option9][]" value="<?php /*echo $custom_option_value9[$i]; */?>"/></td>-->
                    <!-- <td><select class="select_box" name="<?php /*echo esc_html($custom_option_name); */?>[option9][]">
                                <?php /*foreach ($select as $item => $value) { */?>
                                    <option value="<?/*=$item*/?>" <?php /*if($custom_option_value9[$i] == $item){echo 'selected="selected"';} */?>><?/*=$value*/?></option>
                                <?php /*} */?>
                            </select></td>-->
                        <td>
                            <input title="<?php echo esc_html($custom_option_name); ?>" type="button"
                                class="button-primary delete_row" value="<?php echo esc_html__( "Delete", 'storina-application' ); ?>">
                        </td>
                    </tr>
                    <?php
                    $i ++;
                }
            } else { ?>
                <tr>
                    <td style="width: 20px; text-align: center;"><strong class="sort_elem">|||</strong></td>
                    <td>
                        <input class="target_line" type="text" name="<?php echo esc_html($custom_option_name); ?>[option2][]"/>
                        <input type="button" name="upload-btn" class="upload-btn button-secondary"
                            value="<?php echo esc_html__( "Upload", 'storina-application' ); ?>">
                    </td>
                    <td>
                        <select class="select_box" name="<?php echo esc_html($custom_option_name); ?>[option7][]">
                            <?php foreach ( $action as $item => $value ) { ?>
                                <option value="<?php echo esc_attr($item); ?>"><?php echo $value ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td><input type="text" name="<?php echo esc_html($custom_option_name); ?>[option3][]"/></td>
                    <td>
                        <input class="target_line" type="text" name="<?php echo esc_html($custom_option_name); ?>[option4][]"/>
                        <input type="button" name="upload-btn" class="upload-btn button-secondary"
                            value="<?php echo esc_html__( "Upload", 'storina-application' ); ?>">
                    </td>
                    <td>
                        <select class="select_box" name="<?php echo esc_html($custom_option_name); ?>[option8][]">
                            <?php foreach ( $action as $item => $value ) { ?>
                                <option value="<?php echo esc_attr($item); ?>"><?php echo $value ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td><input type="text" name="<?php echo esc_attr($custom_option_name); ?>[option5][]"/></td>
                    <td>
                    <select class="select_box" name="<?php echo esc_html($custom_option_name); ?>[option6][]">
					    <?php foreach ( $coll as $item => $value ) { ?>
                            <option value="<?php echo esc_attr($item); ?>"><?php echo $value ?></option>
					    <?php } ?>
                    </select>
                </td>
                <td>
                    <select name="<?php echo esc_attr($custom_option_name); ?>[option9][]" class="widefat">
					    <?php
					    foreach ( $product_cats as $product_cat => $value ) {
						    echo '<option  value="' . $product_cat . '" >' . $value . '</option>';
					    }
					    ?>
                    </select>
                </td>


                <td>
                    <input title="<?php echo esc_html($custom_option_name); ?>" type="button" class="button delete_row"
                           value="<?php echo esc_html__( "Delete", 'storina-application' ); ?>">
                </td>
            </tr>
	    <?php }

	    //$value = storina_get_option(); ?>
        </tbody>

    </table>
<div class="osa-submit-wrapper-table">
    <input type="hidden" name="apptype_form" value="custom">
    <input type="hidden" name="appname_form" value="<?php echo($pages[$counter-1]['apppagename']); ?>">
    <input type="submit" value="<?php echo esc_html__("Save",'storina-application')?>" name="submit_theme_options" class="button save">
    <button type="button" class="button add_row"><?php echo esc_html__( "Add ", 'storina-application' ) ?></button>
</div>
</form>

</div>
