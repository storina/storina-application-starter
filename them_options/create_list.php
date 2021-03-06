<?php
/*
posts categories
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $wp_cats;
$wp_cats = array();

$wp_cats = storina_hierarchical_category_tree2( 0 , 'category');
$wp_cats[0] = esc_html__("All",'storina-application');
$wp_cats[-1] = esc_html__("Nothing",'storina-application');

/*
all_pages
*/
$all_pages = get_pages('child_of=0');
$wp_pages = array();
$wp_pages[0] = "---";
foreach ($all_pages as $page_list ) {
       $wp_pages[$page_list->ID] = $page_list->post_title;
}
/*
product categories
*/
global $product_cats;
$product_cats = array();
$product_cats[0] = esc_html__("All",'storina-application');
$product_cats = storina_hierarchical_category_tree2( 0 , 'product_cat');
