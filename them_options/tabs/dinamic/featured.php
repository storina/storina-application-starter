<?php
$info=array(
	'name'        => 'index',
	'apppagename' => 'feature-options' . $elementID[ $i ],
	'title'       => __( "Amazing box", 'onlinerShopApp' ) . '<span style="display: none;"> #' . $elementID[ $i ] . '</span>',
);
global $pages;
if($pages){
    array_push($pages, $info);
}else{$pages = Array ($info);}
$options=array(
    array(
        "type" => "listbox",
        "name" => __("Amazing box",'onlinerShopApp'),
        "id" => "indexAppFeatures".$elementID[$i],
        "return_value" => false,
        "options" => $product_cats,
        "desc" => __("this box show products that have offer AND have Schedule for offers.",'onlinerShopApp'),
    ),
	array(
		"type" => "text",
		"name" => __("products count",'onlinerShopApp'),
		"id" => "indexAppFeaturesCount".$elementID[$i],
		"desc" => __("Product count for show. max 12",'onlinerShopApp'),
	),
	/*array(
		"type" => "picture",
		"name" => __('Featured logo','onlinerShopApp'),
		"id" => "app_featured_logo",
		__('Select the featured logo. PNG and 512px*512px','onlinerShopApp')
	),*/
	array(
		"type" => "picture",
		"name" => __('Feature icon','onlinerShopApp'),
		"id" => "osn_feature_product_icon",
		"desc" => __('Select icon for featured product PNG and 200px*50px','onlinerShopApp')
		),


);
global $options_page;
if($options_page){
    array_push($options_page, $options);
}else{$options_page = Array ($options);}
