<?php
/**
 * Created by PhpStorm.
 * User: T-bag
 * Date: 2017/10/14
 * Time: 10:17 AM
 */
$info = array(
	'name'        => 'custom4',
	'apppagename' => 'scrollBox-options' . $elementID[ $i ],
	'title'       => __( "Scroll Box", 'onlinerShopApp' ) . '<span style="display: none;"> #' . $elementID[ $i ] . '</span>',
);
global $pages;
if ( $pages ) {
	array_push( $pages, $info );
} else {
	$pages = Array( $info );
}
//سلول type همیشه در اولین خانه آرابه یاشد
$options = array(
	'type'               => 'custom4',
	'title'              => __( "Scrolled box with title and icon and link", 'onlinerShopApp' ),
	'custom_option_name' => 'sb_custom_options',
	'custom_option2'     => 'Sb_customOption1' . $elementID[ $i ],
	'custom_option3'     => 'Sb_banner' . $elementID[ $i ],
	'custom_option4'     => 'Sb_linkType' . $elementID[ $i ],
	'custom_option5'     => 'Sb_linkValue' . $elementID[ $i ],
	'custom_option6'     => 'Sb_title' . $elementID[ $i ],
);
global $options_page;
if ( $options_page ) {
	array_push( $options_page, $options );
} else {
	$options_page = Array( $options );
}