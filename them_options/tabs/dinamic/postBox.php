<?php
$info=array(
	'name'        => 'index',
	'apppagename' => 'postBox-options' . $elementID[ $i ],
	'title'       => esc_html__( "Post box", "storina-application" ) . '<span style="display: none;"> #' . $elementID[ $i ] . '</span>',
);
$category[-1] = esc_html__("All",'storina-application');
$category = storina_hierarchical_category_tree3(0,'category');
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
        "options" => $category,
        "desc" => esc_html__("Select the category",'storina-application'),
    ),
array(
    "type" => "text",
    "name" => esc_html__("post count",'storina-application'),
    "id" => "indexAppBoxCount".$elementID[$i],
    "desc" => esc_html__("Enter post count for show in box. max 12",'storina-application'),
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
		"desc" => esc_html__("Select order type for show posts.",'storina-application'),
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
		"type"         => "select",
		"name"         => esc_html__( "float", 'storina-application' ),
		"id"           => "indexAppBoxFloat" . $elementID[ $i ],
		"return_value" => false,
		"options"      => array( 'rtl' => esc_html__( "rtl", 'storina-application' ), 'ltr' => esc_html__( "ltr", 'storina-application' ) ),
		"desc"         => esc_html__( "Select the float type for show", 'storina-application' ),
	),
);
global $options_page;
if($options_page){
    array_push($options_page, $options);
}else{$options_page = Array ($options);}

 return $options;
