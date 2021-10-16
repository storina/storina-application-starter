<?php
$info=array(
	'name' => 'banners',
	'apppagename' => 'social-options',
	'title' => __("socials icons",'onlinerShopApp'),
);
if($pages){
array_push($pages, $info);
}else{$pages = Array ($info);}
//سلول type همیشه در اولین خانه آرابه یاشد
$options=array(
	'type' => 'banner',
	'title' => __("this icons shown in buttom of app menu. only banner address and link using ",'onlinerShopApp'),
	'banner_name' => 'social_icon',
	'banner_addresses' => 'social_icon_images',
	'banner_links' => 'social_icon_links',
	'banner_titles' => 'social_icon_titles',
	'banner_captions' => 'social_icon_captions',
	'follow' => 'social_icon_follow',
	'banner_type' => 'social_icon_type',
	'banner_expire' => 'social_icon_expire'
);
global $options_page;
if($options_page){
array_push($options_page, $options);
}else{$options_page = Array ($options);}
?>