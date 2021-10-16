<?php
$info=array(
	'name'        => 'index',
	'apppagename' => 'line-options' . $elementID[ $i ],
	'title'       => __( "Line", 'onlinerShopApp' ) . '<span style="display: none;"> #' . $elementID[ $i ] . '</span>',
);
global $pages;
if($pages){
    array_push($pages, $info);
}else{$pages = Array ($info);}
$options=array(


);
global $options_page;
if($options_page){
    array_push($options_page, $options);
}else{$options_page = Array ($options);}
