<?php
$info=array(
	'name'        => 'index',
	'apppagename' => 'postBox-options' . $elementID[ $i ],
	'title'       => __( "Post box", "onlinerShopApp" ) . '<span style="display: none;"> #' . $elementID[ $i ] . '</span>',
);
$category[-1] = __("All",'onlinerShopApp');
$category = storina_hierarchical_category_tree3(0,'category');
global $pages;
if($pages){
    array_push($pages, $info);
}else{$pages = Array ($info);}
$options=array(
	array(
		"type" => "text",
		"name" => __("Box title",'onlinerShopApp'),
		"id" => "indexAppBoxTitle".$elementID[$i],
		"desc" => __("Enter the title box",'onlinerShopApp'),
	),
    array(
        "type" => "select",
        "name" => __("category",'onlinerShopApp'),
        "id" => "indexAppBox".$elementID[$i],
        "return_value" => false,
        "options" => $category,
        "desc" => __("Select the category",'onlinerShopApp'),
    ),
array(
    "type" => "text",
    "name" => __("post count",'onlinerShopApp'),
    "id" => "indexAppBoxCount".$elementID[$i],
    "desc" => __("Enter post count for show in box. max 12",'onlinerShopApp'),
),
	array(
		"type" => "select",
		"name" => __("Order by",'onlinerShopApp'),
		"id" => "indexAppBoxSort".$elementID[$i],
		"return_value" => false,
		"options" => array(
			'date' => __("Created date",'onlinerShopApp'),
			'modified'=>__("Modified date",'onlinerShopApp'),
			'title' => __("Title",'onlinerShopApp'),
			'sale' => __("sale count",'onlinerShopApp'),
			'view' => __("View count",'onlinerShopApp'),
			'comment_count' => __("Comment count",'onlinerShopApp'),
			'rand' => __("Random",'onlinerShopApp')),
		"desc" => __("Select order type for show posts.",'onlinerShopApp'),
	),
	array(
		"type" => "select",
		"name" => __("Sort",'onlinerShopApp'),
		"id" => "indexAppBoxOrder".$elementID[$i],
		"return_value" => false,
		"options" => array('ASC' => __("ASC",'onlinerShopApp'),'DESC'=>__("DESC",'onlinerShopApp')),
		"desc" => __("Select the sort type for show",'onlinerShopApp'),
	),
	array(
		"type"         => "select",
		"name"         => __( "float", 'onlinerShopApp' ),
		"id"           => "indexAppBoxFloat" . $elementID[ $i ],
		"return_value" => false,
		"options"      => array( 'rtl' => __( "rtl", 'onlinerShopApp' ), 'ltr' => __( "ltr", 'onlinerShopApp' ) ),
		"desc"         => __( "Select the float type for show", 'onlinerShopApp' ),
	),
);
global $options_page;
if($options_page){
    array_push($options_page, $options);
}else{$options_page = Array ($options);}

 return $options;
