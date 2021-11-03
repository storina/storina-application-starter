<?php
$info=array(
	'name'        => 'index',
	'apppagename' => 'postBox-options' . $elementID[ $i ],
	'title'       => esc_html__( "Post box", "onlinerShopApp" ) . '<span style="display: none;"> #' . $elementID[ $i ] . '</span>',
);
$category[-1] = esc_html__("All",'onlinerShopApp');
$category = storina_hierarchical_category_tree3(0,'category');
global $pages;
if($pages){
    array_push($pages, $info);
}else{$pages = Array ($info);}
$options=array(
	array(
		"type" => "text",
		"name" => esc_html__("Box title",'onlinerShopApp'),
		"id" => "indexAppBoxTitle".$elementID[$i],
		"desc" => esc_html__("Enter the title box",'onlinerShopApp'),
	),
    array(
        "type" => "select",
        "name" => esc_html__("category",'onlinerShopApp'),
        "id" => "indexAppBox".$elementID[$i],
        "return_value" => false,
        "options" => $category,
        "desc" => esc_html__("Select the category",'onlinerShopApp'),
    ),
array(
    "type" => "text",
    "name" => esc_html__("post count",'onlinerShopApp'),
    "id" => "indexAppBoxCount".$elementID[$i],
    "desc" => esc_html__("Enter post count for show in box. max 12",'onlinerShopApp'),
),
	array(
		"type" => "select",
		"name" => esc_html__("Order by",'onlinerShopApp'),
		"id" => "indexAppBoxSort".$elementID[$i],
		"return_value" => false,
		"options" => array(
			'date' => esc_html__("Created date",'onlinerShopApp'),
			'modified'=>esc_html__("Modified date",'onlinerShopApp'),
			'title' => esc_html__("Title",'onlinerShopApp'),
			'sale' => esc_html__("sale count",'onlinerShopApp'),
			'view' => esc_html__("View count",'onlinerShopApp'),
			'comment_count' => esc_html__("Comment count",'onlinerShopApp'),
			'rand' => esc_html__("Random",'onlinerShopApp')),
		"desc" => esc_html__("Select order type for show posts.",'onlinerShopApp'),
	),
	array(
		"type" => "select",
		"name" => esc_html__("Sort",'onlinerShopApp'),
		"id" => "indexAppBoxOrder".$elementID[$i],
		"return_value" => false,
		"options" => array('ASC' => esc_html__("ASC",'onlinerShopApp'),'DESC'=>esc_html__("DESC",'onlinerShopApp')),
		"desc" => esc_html__("Select the sort type for show",'onlinerShopApp'),
	),
	array(
		"type"         => "select",
		"name"         => esc_html__( "float", 'onlinerShopApp' ),
		"id"           => "indexAppBoxFloat" . $elementID[ $i ],
		"return_value" => false,
		"options"      => array( 'rtl' => esc_html__( "rtl", 'onlinerShopApp' ), 'ltr' => esc_html__( "ltr", 'onlinerShopApp' ) ),
		"desc"         => esc_html__( "Select the float type for show", 'onlinerShopApp' ),
	),
);
global $options_page;
if($options_page){
    array_push($options_page, $options);
}else{$options_page = Array ($options);}

 return $options;
