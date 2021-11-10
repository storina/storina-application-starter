<?php
$info=array(
	'name'        => 'index',
	'apppagename' => 'feature-options' . $elementID[ $i ],
	'title'       => esc_html__( "Amazing box", 'storina-application' ) . '<span style="display: none;"> #' . $elementID[ $i ] . '</span>',
);
global $pages;
if($pages){
    array_push($pages, $info);
}else{$pages = Array ($info);}
$options=array(
    array(
        "type" => "listbox",
        "name" => esc_html__("Amazing box",'storina-application'),
        "id" => "indexAppFeatures".$elementID[$i],
        "return_value" => false,
        "options" => $product_cats,
        "desc" => esc_html__("this box show products that have offer AND have Schedule for offers.",'storina-application'),
    ),
	array(
		"type" => "text",
		"name" => esc_html__("products count",'storina-application'),
		"id" => "indexAppFeaturesCount".$elementID[$i],
		"desc" => esc_html__("Product count for show. max 12",'storina-application'),
	),
	/*array(
		"type" => "picture",
		"name" => esc_html__('Featured logo','storina-application'),
		"id" => "app_featured_logo",
		esc_html__('Select the featured logo. PNG and 512px*512px','storina-application')
	),*/
	array(
		"type" => "picture",
		"name" => esc_html__('Feature icon','storina-application'),
		"id" => "osn_feature_product_icon",
		"desc" => esc_html__('Select icon for featured product PNG and 200px*50px','storina-application')
		),


);
global $options_page;
if($options_page){
    array_push($options_page, $options);
}else{$options_page = Array ($options);}
