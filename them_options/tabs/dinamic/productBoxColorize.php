<?php
$info=array(
	'name'        => 'index',
	'apppagename' => 'productBoxColorize-options' . $elementID[ $i ],
	'title'       => esc_html__( "Product box colorize", 'storina-application' ) . '<span style="display: none;"> #' . $elementID[ $i ] . '</span>',
);
global $pages;
if($pages){
    array_push($pages, $info);
}else{$pages = Array ($info);}
$options=array(
	array(
		"type" => "text",
		"name" => esc_html__("Box title",'storina-application'),
		"id" => "indexAppBoxTitle".$elementID[$i],
		"desc" => esc_html__("Enter the title box",'storina-application'),
	),
    array(
        "type" => "select",
        "name" => esc_html__("category",'storina-application'),
        "id" => "indexAppBox".$elementID[$i],
        "return_value" => false,
        "options" => $product_cats,
        "desc" => esc_html__("Select the category",'storina-application'),
    ),
    array(
        'type' => 'color',
        'name' => esc_html__("Background color","storina-application"),
        'id' => "boxColor".$elementID[$i],
        'desc' => esc_html__("Background color of product box","storina-application")
    ),
    array(
        'type' => 'picture',
        'name' => esc_html__("icon","storina-application"),
        'id' => "boxIcon".$elementID[$i],
        'desc' => esc_html__("icon that will be shown on the right","storina-application"),
    ),
    array(
        'type' => 'picture',
        'name' => esc_html__("background image","storina-application"),
        'id' => "boxBackgroundImage".$elementID[$i],
        'desc' => esc_html__("Background image will be shown all over the box","storina-application"),
    ),
    array(
        "type" => "text",
        "name" => esc_html__("product count",'storina-application'),
        "id" => "indexAppBoxCount".$elementID[$i],
        "desc" => esc_html__("Enter product count for show in box. max 12",'storina-application'),
    ),
	array(
		"type" => "select",
		"name" => esc_html__("Order by",'storina-application'),
		"id" => "indexAppBoxSort".$elementID[$i],
		"return_value" => false,
		"options" => array(
			'date' => esc_html__("Created date",'storina-application'),
			'modified'=>esc_html__("Modified date",'storina-application'),
			'title' => esc_html__("Title",'storina-application'),
			'sale' => esc_html__("sale count",'storina-application'),
			'view' => esc_html__("View count",'storina-application'),
			'comment_count' => esc_html__("Comment count",'storina-application'),
			'rand' => esc_html__("Random",'storina-application')),
		"desc" => esc_html__("Select order type for show products.",'storina-application'),
	),
	array(
		"type" => "select",
		"name" => esc_html__("Sort",'storina-application'),
		"id" => "indexAppBoxOrder".$elementID[$i],
		"return_value" => false,
		"options" => array('ASC' => esc_html__("ASC",'storina-application'),'DESC'=>esc_html__("DESC",'storina-application')),
		"desc" => esc_html__("Select the sort type for show",'storina-application'),
	),
	array(
		"type" => "checkbox",
		"name" => esc_html__("exist",'storina-application'),
		"id" => array("indexAppBoxExist".$elementID[$i]),
		"return_value" => false,
		"options" => array(esc_html__("Active",'storina-application')),
		"desc" => esc_html__("Only show exists products",'storina-application'),
	),
	/*array(
		"type"         => "select",
		"name"         => esc_html__( "float", 'storina-application' ),
		"id"           => "indexAppBoxFloat" . $elementID[ $i ],
		"return_value" => false,
		"options"      => array( 'rtl' => esc_html__( "rtl", 'storina-application' ), 'ltr' => esc_html__( "ltr", 'storina-application' ) ),
		"desc"         => esc_html__( "Select the float type for show", 'storina-application' ),
	),*/
);
global $options_page;
if($options_page){
    array_push($options_page, $options);
}else{$options_page = Array ($options);}

return $options;
