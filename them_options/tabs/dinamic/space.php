<?php
$info=array(
	'name'        => 'index',
	'apppagename' => 'space-options' . $elementID[ $i ],
	'title'       => __( "Space", 'onlinerShopApp' ) . '<span style="display: none;"> #' . $elementID[ $i ] . '</span>',
);
global $pages;
if($pages){
    array_push($pages, $info);
}else{$pages = Array ($info);}
$options=array(
    array(
        "type" => "text",
        "name" => __("space value",'onlinerShopApp'),
        "id" => "space".$elementID[$i],
        "desc" =>__("Enter the space value for pixel",'onlinerShopApp'),
    ),

);
global $options_page;
if($options_page){
    array_push($options_page, $options);
}else{$options_page = Array ($options);}
