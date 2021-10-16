<?php
$info=array(
	'name' => 'custom3',
	'apppagename' => 'index-options',
	'title' => __("Home page layout",'onlinerShopApp'),
);
global $pages;
if($pages){
array_push($pages, $info);
}else{$pages = Array ($info);}
//سلول type همیشه در اولین خانه آرابه یاشد
$options=array(
	'type' => 'custom3',
	'title' => __("Design your home page for application",'onlinerShopApp'),
	'custom_option_name' => 'custom_option',
	'custom_option2' => 'appindex_ID',
	'custom_option3' => 'appindex_element',
	'custom_option4' => 'appindex_webViewTitle',
	'custom_option5' => 'appindex_event',
	'custom_option6' => 'appindex_banner',
	'custom_option7' => 'appindex_option7',
	'custom_option8' => 'appindex_option8',
	'custom_option9' => 'appindex_option9'
);
global $options_page;
if($options_page){
array_push($options_page, $options);
}else{$options_page = Array ($options);}
