<?php
$info=array(
	'name'        => 'index',
	'apppagename' => 'categiry-options' . $elementID[ $i ],
	'title'       => esc_html__( "product Categories", 'storina-application' ) . '<span style="display: none;"> #' . $elementID[ $i ] . '</span>',
);
global $pages;
if($pages){
    array_push($pages, $info);
}else{$pages = Array ($info);}
$options=array(
    array(
        "type" => "listbox",
        "name" => esc_html__("Product categories",'storina-application'),
        "id" => "indexAppCats".$elementID[$i],
        "return_value" => false,
        "options" => $product_cats,
        "desc" => esc_html__("you can insert categories in button type. please select that.",'storina-application'),
    ),
	array(
		"type"         => "select",
		"name"         => esc_html__( "type", 'storina-application' ),
		"id"           => "indexAppCatType" . $elementID[ $i ],
		"return_value" => false,
		"options"      => array( 'Thumbnail'       => esc_html__( "show categories thumbnails", 'storina-application' ),
		                         'scrollThumbnail' => esc_html__( "show categories thumbnails with scroll", 'storina-application' ),
		                         'scrollButtons'   => esc_html__( "show categories green buttons", 'storina-application' )
		),
		"desc"         => esc_html__( "Select the category", 'storina-application' ),
	),
);
global $options_page;
if($options_page){
    array_push($options_page, $options);
}else{$options_page = Array ($options);}
