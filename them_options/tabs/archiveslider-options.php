<?php
$info=array(
	'name' => 'sliderplus',
	'apppagename' => 'galleryslider-options',
	'title' => esc_html__("Archive sliders",'onlinerShopApp'),
);
global $pages;
if($pages){
array_push($pages, $info);
}else{$pages = Array ($info);}
//سلول type همیشه در اولین خانه آرابه یاشد
$options=array(
	'type' => 'sliderplus',
	'taxonomy' => 'product_cat',// امکان انتخاب دسته های این نوع نوشته
	'title' => esc_html__("set slider banners for show in custom archives. width 800px height 400px",'onlinerShopApp'),
	'slider_name' => 'apparchive_slider',
	'image_name' => 'apparchive_slider_images',
	'typeLink_name' => 'apparchive_slider_typeLinks',
	'link_name' => 'apparchive_slider_links',
	'title_name' => 'apparchive_slider_titles',
	'caption_name' => 'apparchive_slider_captions',
	'category_name' => 'apparchive_slider_category'
);
global $options_page;
if($options_page){
array_push($options_page, $options);
}else{$options_page = Array ($options);}
?>
