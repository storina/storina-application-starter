<?php
$info=array(
	'name' => 'text_adsplus',
	'apppagename' => 'menu-options',
	'title' => __("Custom menu",'onlinerShopApp'),
);
global $pages;
if($pages){
array_push($pages, $info);
}else{$pages = Array ($info);}
//سلول type همیشه در اولین خانه آرابه یاشد
$options=array(
	'type' => 'text_adsplus',
	'title' => __("can you make custom menu for your application",'onlinerShopApp'),
	'slider_name' => 'menu',
	'title_name' => 'menu_titles',
	'link_name' => 'menu_links',
	'caption_name' => 'menu_texts',
	'typeLink_name' => 'menu_typeLink'
);
global $options_page;
if($options_page){
array_push($options_page, $options);
}else{$options_page = Array ($options);}
