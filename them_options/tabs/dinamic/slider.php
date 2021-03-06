<?php
/**
 * Created by PhpStorm.
 * User: T-bag
 * Date: 2017/10/14
 * Time: 9:50 AM
 */
$info = array(
	'name'        => 'slider',
	'apppagename' => 'appSlider-options' . $elementID[ $i ],
	'title'       => esc_html__( "Slider", 'storina-application' ) . '<span style="display: none;"> #' . $elementID[ $i ] . '</span>',
);
global $pages;
if ($pages) {
    array_push($pages, $info);
} else {
    $pages = Array($info);
}
//سلول type همیشه در اولین خانه آرابه یاشد
$options = array(
    'type' => 'sliderplus',
    'title' => esc_html__("Enter the banners for this slider",'storina-application'),
    'slider_name' => 'top_slider' . $elementID[$i],
    'image_name' => 'top_slider_images' . $elementID[$i],
    'link_name' => 'top_slider_links' . $elementID[$i],
    'typeLink_name' => 'top_slider_typeLinks' . $elementID[$i],
    'title_name' => 'top_slider_titles' . $elementID[$i],
    'caption_name' => 'top_slider_captions' . $elementID[$i]
);
global $options_page;
if($options_page){
    array_push($options_page, $options);
}else{$options_page = Array ($options);}
