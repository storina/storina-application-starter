<?php
$info=array(
	'name' => 'banners',
	'apppagename' => 'archive-banner-options',
	'title' => esc_html__("Categories banners ADS",'storina-application'),
);
global $pages;
if($pages){
array_push($pages, $info);
}else{$pages = Array ($info);}
//سلول type همیشه در اولین خانه آرابه یاشد
$options=array(
	'type' => 'custom2',
	'title' => esc_html__("Enter the ads banners for archive pages",'storina-application'),
	'custom_option_name' => 'custom_option',
	'custom_option2' => 'HArchive_banner1',
	'custom_option3' => 'HArchive_linkBanner1',
	'custom_option4' => 'HArchive_banner2',
	'custom_option5' => 'HArchive_linkBanner2',
	'custom_option6' => 'HArchive_col',
	'custom_option7' => 'HArchive_typeLinkBanner1',
	'custom_option8' => 'HArchive_typeLinkBanner2',
	'custom_option9' => 'HArchive_option9'
);
global $options_page;
if($options_page){
array_push($options_page, $options);
}else{$options_page = Array ($options);}
