<?php
/**
 * Created by PhpStorm.
 * User: T-bag
 * Date: 2017/10/14
 * Time: 10:17 AM
 */
$info=array(
	'name'        => 'custom5',
	'apppagename' => 'appHImages-options' . $elementID[ $i ],
	'title'       => __( "Banners ads", 'onlinerShopApp' ) . '<span style="display: none;"> #' . $elementID[ $i ] . '</span>',
);
global $pages;
if($pages){
    array_push($pages, $info);
}else{$pages = Array ($info);}
//سلول type همیشه در اولین خانه آرابه یاشد
$options=array(
    'type' => 'custom5',
    'title' => __("Select the banners for show in home page",'onlinerShopApp'),
    'custom_option_name' => 'custom_option',
    'custom_option2' => 'Hbanner_banner1'.$elementID[$i],
    'option-names' => [
        'banner' => 'woap_home_adc_banners'.$elementID[$i],
        'link_type' => 'woap_home_adc_link_types'.$elementID[$i],
        'link_value' => 'woap_home_adc_link_values'.$elementID[$i],
        'column' => 'woap_home_adc_columns'.$elementID[$i],
    ]
);
global $options_page;
if($options_page){
    array_push($options_page, $options);
}else{$options_page = Array ($options);}

return $options;