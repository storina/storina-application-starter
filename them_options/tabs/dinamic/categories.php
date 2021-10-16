<?php
$info=array(
	'name'        => 'index',
	'apppagename' => 'categiry-options' . $elementID[ $i ],
	'title'       => __( "product Categories", 'onlinerShopApp' ) . '<span style="display: none;"> #' . $elementID[ $i ] . '</span>',
);
global $pages;
if($pages){
    array_push($pages, $info);
}else{$pages = Array ($info);}
$options=array(
    array(
        "type" => "listbox",
        "name" => __("Product categories",'onlinerShopApp'),
        "id" => "indexAppCats".$elementID[$i],
        "return_value" => false,
        "options" => $product_cats,
        "desc" => __("you can insert categories in button type. please select that.",'onlinerShopApp'),
    ),
	array(
		"type"         => "select",
		"name"         => __( "type", 'onlinerShopApp' ),
		"id"           => "indexAppCatType" . $elementID[ $i ],
		"return_value" => false,
		"options"      => array( 'Thumbnail'       => __( "show categories thumbnails", 'onlinerShopApp' ),
		                         'scrollThumbnail' => __( "show categories thumbnails with scroll", 'onlinerShopApp' ),
		                         'scrollButtons'   => __( "show categories green buttons", 'onlinerShopApp' )
		),
		"desc"         => __( "Select the category", 'onlinerShopApp' ),
	),
);
global $options_page;
if($options_page){
    array_push($options_page, $options);
}else{$options_page = Array ($options);}
