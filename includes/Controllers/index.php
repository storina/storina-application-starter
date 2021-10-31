<?php

namespace STORINA\Controllers;

use \STORINA\Controllers\User;
use STORINA\Controllers\Cache;
use \STORINA\Controllers\General;
use \STORINA\Controllers\Yith_Role_Based_Price;
use \STORINA\Controllers\Terawallet;


defined('ABSPATH') || exit;

class Index {

    public $user_id = false;
    public $yith_price_role = false;
    public $service_container;

    public function __construct($service_container) {
        $this->service_container = $service_container;
        require_once( ABSPATH . "wp-load.php" );
        $this->yith_price_role = $this->service_container->get(Yith_Role_Based_Price::class);
    }

    public function get() {

        //     check cache

        $OSA_cache = $this->service_container->get(Cache::class);
        $itemID = ( strlen(sanitize_text_field($_POST['vendor_town'])) > 2 ) ? sanitize_text_field($_POST['vendor_town']) : 0;
        $record = $OSA_cache->getCache('index', $itemID);
        $general = $this->service_container->get(General::class);
        $cache = ( storina_get_option('appCacheStatus') == 'inactive' ) ? false : true;

        $userToken = sanitize_text_field($_POST['userToken']);
        $user_action = $this->service_container->get(User::class);
        $user_id = $user_action->get_userID_byToken($userToken);
        $this->user_id = $user_id;
        do_action('osa_index_get_action_init',$this->user_id);
        $woo_wallet = $this->service_container->get(Terawallet::class);
        $woo_ballance = (function_exists("woo_wallet")) ? $woo_wallet->get_wallet_ballance($user_id) : -1;
        $min_topup_amount = ( function_exists("woo_wallet") ) ? (int) woo_wallet()->settings_api->get_option('min_topup_amount', '_wallet_settings_general', 0) : -1;
        $max_topup_amount = ( function_exists("woo_wallet") ) ? (int) woo_wallet()->settings_api->get_option('max_topup_amount', '_wallet_settings_general', 0) : -1;
        $user = get_user_by('id', $user_id);
        $element = storina_get_option('appindex_element');
        $elementID = storina_get_option('appindex_ID');
        // caching
        if (!empty($record) AND $cache == true AND ! in_array('reseler', (array) $user->roles) AND false) {
            $index = $record;
            $version = sanitize_text_field($_POST['currentVersion']);
            $appinfo = array(
                "zeroPriceText" => __("Call", "onlinerShopApp"),
                    /* "new_app_version" => intval( storina_get_option( 'app_version' ) ),
                      "description"     => array(),
                      "isForce"         => false,
                      "url"             => '' */
            );
            if (floatval($version) < floatval(storina_get_option('app_version'))) {

                $description = explode(PHP_EOL, storina_get_option('app_versionText'));
                $appinfo = array(
                    "zeroPriceText" => ( storina_get_option('zeroPriceText') ) ? storina_get_option('zeroPriceText') : __('Call', 'onlinerShopApp'),
                    "new_app_version" => intval(storina_get_option('app_version')),
                    "description" => $description,
                    "isForce" => ( storina_get_option('app_UpdateFource') == 'true' ) ? true : false,
                    "url" => storina_get_option('app_url')
                );
            }

            $appInfo["min_topup_amount"] = $min_topup_amount;
            $appInfo["max_topup_amount"] = $max_topup_amount;
            $appinfo["woo_ballance"] = $woo_ballance;
            $index['data']['appInfo'] = $appinfo;
            $counterx = 0;
            $tmp = array();
            foreach ($index['data']['home'] as $box) {
                $tmp[$counterx] = $index['data']['home'][$counterx];
                if ($box['type'] == 'featured') {
                    //echo $counterx;
                    $featured = $this->featured($elementID, $counterx);
                    $tmp[$counterx] = $featured;
                }
                $counterx ++;
            }
            $record = $index;
            //$index['data']['cartCount'] = count($general->get_items());
            //$index['data']['appInfo'] = (object) $index['data']['appInfo'];
            return ( $record );
        }
        // end check cache

        $iiii = 0;
        $home = $uploadModel = array();
        foreach ($element as $index => $item) {
            $home[] = $this->$item($elementID, $iiii);
            $iiii ++;
        }
        $version = sanitize_text_field($_POST['currentVersion']);
        if (floatval($version) < floatval(storina_get_option('app_version'))) {
            $description = explode(PHP_EOL, storina_get_option('app_versionText'));
            $appInfo = array(
                "zeroPriceText" => ( storina_get_option('zeroPriceText') ) ? storina_get_option('zeroPriceText') : __('Call', 'onlinerShopApp'),
                "new_app_version" => intval(storina_get_option('app_version')),
                "description" => $description,
                "isForce" => ( storina_get_option('app_UpdateFource') == 'true' ) ? true : false,
                "url" => storina_get_option('app_url'),
            );
        } else {
            $appInfo = array(
                'zeroPriceText' => ( storina_get_option('zeroPriceText') ) ? storina_get_option('zeroPriceText') : __('Call', 'onlinerShopApp'),
                    /* "new_app_version" => intval( storina_get_option( 'app_version' ) ),
                      "description"     => array(),
                      "isForce"         => false,
                      "url"             => '' */
            );
        }
        $socials = array();
        $socialUrls = storina_get_option('social_icon_links');
        $socialIcons = storina_get_option('social_icon_images');
        if (!empty($socialIcons)) {
            for ($i = 0; $i < count($socialIcons); $i ++) {
                $socials[] = array(
                    'url' => ( $socialUrls[$i] ) ? $socialUrls[$i] : '',
                    'icon' => ( $socialIcons[$i] ) ? $socialIcons[$i] : '',
                );
            }
        }

        $menuTitles = storina_get_option('menu_titles');
        $menuLinks = storina_get_option('menu_links');
        $menuTypeLinks = storina_get_option('menu_typeLink');
        $menuItems = array();
        foreach ($menuTitles as $i => $menu_title) {
            $onClickModel = $general->clickEvent($menuTypeLinks[$i], $menuLinks[$i]);

            $menuItems[] = array(
                'menuTitle' => $menuTitles[$i],
                'link' => $onClickModel,
            );
        }

        $states = $general->get_states();
        $address_fields = array(
            'billing' => $general->get_address_fields('billing'),
            'shipping' => $general->get_address_fields('shipping')
        );
        $add_to_cart_permission = (function_exists("YITH_Role_Based_Type")) ? $this->yith_price_role->user_settings_add_to_cart($user_id) : 'true';
        $backorder_text = storina_get_option('app_backorder_text');
        $appInfo['min_topup_amount'] = $min_topup_amount;
        $appInfo['max_topup_amount'] = $max_topup_amount;
        $appInfo['woo_ballance'] = $woo_ballance;
        $appInfo['store_notice'] = array(
            "key" => (empty(storina_get_option("woocommerce_demo_store"))) ? "no" : storina_get_option("woocommerce_demo_store"),
            "value" => storina_get_option("woocommerce_demo_store_notice")
        );
        $app_settings = array(
            'outofstockorder' => ( storina_get_option('stock_out_order') == 'true' ) ? true : false,
            'ArchiveBrowse' => ( storina_get_option('appArchiveType') ) ? storina_get_option('appArchiveType') : 'sub',
            'payType' => ( storina_get_option('payType') ) ? storina_get_option('payType') : 'inAppPay',
            'blog' => ( storina_get_option('appblogsetting') == 'Hidden' ) ? false : true,
            'shopinglist' => ( storina_get_option('appShopinglist') == 'Hidden' ) ? false : true,
            'vendorlist' => ( storina_get_option('app_hidden_menu_vendor_list') == "true" || !(function_exists('dokan_is_seller_enabled')) ) ? false : true,
            'newMenu' => ( storina_get_option('showNewMenu') == 'Show' ) ? true : false,
            'vendor_grouping' => ( storina_get_option('app_vendor_grouping') == 'true' ) ? true : false,
            'compare' => ( storina_get_option('appcompareactive') == 'Active' ) ? true : false,
            'dokan' => ( function_exists('dokan_get_store_info') ) ? true : false,
            'callNumber' => ( storina_get_option('app_callNumber') ) ? storina_get_option('app_callNumber') : '',
            'map_api' => ( storina_get_option('app_map_api') == 'true' ) ? true : false,
            'backorder_text' => ( $backorder_text ) ? $backorder_text : 'پیش خرید',
            'registerType' => ( storina_get_option('registerType') AND storina_get_option('registerType') != 'Choose that' ) ? storina_get_option('registerType') : 'email',
            'app_verifyFource' => ( storina_get_option('app_verifyFource') == 'true' ) ? true : false,
            'app_registerNameField' => ( storina_get_option('app_registerNameField') == 'true' ) ? true : false,
            'app_showVendorPhone' => ( storina_get_option('app_showVendorPhone') == 'false' ) ? false : true,
            'app_loginVerifyType' => ( storina_get_option('app_loginVerifyType') == 'password' ) ? 'password' : 'sms',
            'send_time_field' => ( storina_get_option('app_send_time_field') == 'true' ) ? true : false,
            'socials' => $socials,
            'address_fields' => apply_filters('woap_index_address_fields',$address_fields,$user_id),
            'menuItems' => $menuItems,
            'lngs' => array(
                array('key' => 'fa', 'label' => 'فارسی'),
                array('key' => 'en', 'label' => 'انگلیسی')
            ),
            'default_en' => 'fa',
            'addToCartPermission' => $add_to_cart_permission,
            'force_login' => ("true" == (storina_get_option("app_ForceLogin"))) ? "true" : "false",
            'woocommerce_ship_to_destination' => get_option('woocommerce_ship_to_destination'),
        );
        $result = array(
            'status' => true,
            'data' => array(
                'home' => $home,
                'cartCount' => count($general->get_items()),
                'logos' => array(
                    'app_icon' => storina_get_option('app_icon'),
                    'splashLogo' => ( storina_get_option('app_logo') ) ? str_replace('https://', 'http://', storina_get_option('app_logo')) : '',
                    'indexLogo' => ( storina_get_option('app_TopLogo') ) ? str_replace('https://', 'http://', storina_get_option('app_TopLogo')) : '',
                    'appTitle' => ( storina_get_option('app_title') ) ? storina_get_option('app_title') : __('Onliner', 'onlinerShopApp'),
                    'masterColor' => ( storina_get_option('app_masterColor') ) ? storina_get_option('app_masterColor') : false,
                    'secondColor' => ( storina_get_option('app_secondColor') ) ? storina_get_option('app_secondColor') : false,
                    'IconsColor' => ( storina_get_option('app_IconColor') ) ? storina_get_option('app_IconColor') : false,
                ),
                'appInfo' => apply_filters("osa_index_get_app_info", $appInfo, $this->user_id),
                'appSetting' => apply_filters("osa_index_get_app_settings", $app_settings),
                'states' => $states,
            )
        );
        $itemID = ( strlen(sanitize_text_field($_POST['vendor_town'])) > 2 ) ? sanitize_text_field($_POST['vendor_town']) : 0;
        $OSA_cache->setCache(json_encode($result), 'index', $itemID);

        return ( $result );
    }

    private function featured($elementID, $iiii) {
        //                             featured
        $cat_ids = storina_get_option('indexAppFeatures' . $elementID[$iiii]);
        $count = storina_get_option('indexAppFeaturesCount' . $elementID[$iiii]);

        date_default_timezone_set(storina_get_option('timezone_string'));
        $data = $productInfo = array();
        $data['type'] = 'featured';
        $data['icon'] = storina_get_option("osn_feature_product_icon");
        if (!empty($cat_ids)) {

            $args = array(
                'post_type' => array('product_variation', 'product'),
                'taxonomy' => 'product_cat',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(// Simple products type
                        'key' => '_sale_price_dates_to',
                        'value' => time(),
                        'compare' => '>',
                        'type' => 'numeric'
                    ),
                    array(
                        'key' => '_sale_price',
                        'value' => 0,
                        'compare' => '>',
                        'type' => 'numeric'
                    )
                ),
                'posts_per_page' => $count
            );
            $general = $this->service_container->get(General::class);
            $activeVendors = $general->vendor_ids();
            if (strlen(sanitize_text_field($_POST['vendor_town'])) > 2) {
                $args['author__in'] = $activeVendors;
            }
            if (array_search('0', $cat_ids) === false) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => $cat_ids,
                        'operator' => 'IN'
                    )
                );
            }
            wp_reset_query();
            $wp_query = new WP_Query();
            $wp_query->query(apply_filters("osa_index_featured_query_args", $args, $this->user_id));
            if ($wp_query->have_posts()):
                while ($wp_query->have_posts()):
                    $wp_query->the_post();
                    $product = wc_get_product(get_the_ID());
                    $product_id = get_the_ID();
                    if ("simple" == $product->product_type) {
                        $img_id = get_post_thumbnail_id($product->get_id());
                        $thumb = wp_get_attachment_image_src($img_id, 'medium')[0];
                    } else {
                        $img_id = get_post_thumbnail_id($product->get_parent_id());
                        $thumb = wp_get_attachment_image_src($img_id, 'medium')[0];
                    }
                    if (empty($thumb)) {
                        $thumb = $img = STORINA_PLUGIN_URL . "/assets/images/notp.png";
                    }

                    $sales_price_to = get_post_meta($product_id, '_sale_price_dates_to', true);
                    $sale_price_dates_to = ( $sales_price_to ) ? date('Y-m-d', $sales_price_to) : '';

                    $different = $this->extract_time_with_now($sales_price_to);
                    $productInfo['id'] = ("simple" == $product->get_type()) ? $product->get_id() : $product->get_parent_id();
                    $productInfo['title'] = html_entity_decode(get_the_title());
                    $productInfo['thumbnail'] = $thumb;

                    $product_type = $product->product_type;
                    if ($product_type == 'variable') {
                        $variations = $product->get_available_variations();
                        //$productInfo['regular_price'] = get_post_meta(get_the_ID(),'_price',true);
                        //$productInfo['sale_price'] = get_post_meta(get_the_ID(),'_sale_price',true);
                        $productInfo['regular_price'] = "{$variations[0]['display_regular_price']}";
                        $productInfo['sale_price'] = "{$variations[0]['display_price']}";
                        if (function_exists("YITH_Role_Based_Type")) {
                            $first_child_id = current($product->get_children());
                            $first_child = wc_get_product($first_child_id);
                            $productInfo['regular_price'] = $this->yith_price_role->get_compute_price_render($first_child, $this->user_id);
                            $productInfo['sale_price'] = "";
                        }
                        if ($productInfo['regular_price'] == $productInfo['sale_price']) {
                            $productInfo['regular_price'] = '0';
                        }
                    } else {
                        $productInfo['regular_price'] = get_post_meta(get_the_ID(), '_regular_price', true);
                        $productInfo['sale_price'] = get_post_meta(get_the_ID(), '_sale_price', true);
                        if (function_exists("YITH_Role_Based_Type")) {
                            $productInfo['regular_price'] = $this->yith_price_role->get_compute_price_render($product, $this->user_id);
                            $productInfo['sale_price'] = "";
                        }
                    }
                    $productInfo['in_stock'] = $product->is_in_stock();
                    $productInfo['stock_status'] = $product->get_stock_status();
                    $productInfo['different'] = $different;
                    $productInfo['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
                    $product_id = ("simple" == $product->get_type()) ? $product->get_id() : $product->get_parent_id();
                    $data['data'][] = apply_filters("osa_archive_get_featured_product_item_info", $productInfo, $product_id, $this->user_id);
                endwhile;
            endif;
        }

        return $this->make_featured_product_unique($data);
    }

    public function make_featured_product_unique($data) {
        $cashe = array();
        foreach ($data['data'] as $product_info) {
            if (!in_array($product_info['id'], $cashe)) {
                $output[] = $product_info;
            }
            $cashe[] = $product_info['id'];
        }
        $data['data'] = $output;
        return $data;
    }

    private function sliderItems($elementID, $iiii) {
        $general = $this->service_container->get(General::class);
        $titles = storina_get_option('top_slider_titles' . $elementID[$iiii]);
        $banners = storina_get_option('top_slider_images' . $elementID[$iiii]);
        $links = storina_get_option('top_slider_links' . $elementID[$iiii]);
        $typeLinks = storina_get_option('top_slider_typeLinks' . $elementID[$iiii]);
        //var_dump($banners);
        $i = 0;
        $slides['type'] = 'slider';
        if (!empty($banners)) {
            foreach ($titles as $title) {
                $onClickModel = $general->clickEvent($typeLinks[$i], $links[$i]);
                $slides['data'][] = array(
                    'image' => $banners[$i],
                    'link' => $onClickModel,
                );

                $i ++;
            }
        }

//echo json_encode($sliderResult);
        return $slides;
    }

    private function categories($elementID, $iiii) {
        //                             category
        $cat_ids = storina_get_option('indexAppCats' . $elementID[$iiii]);
        $show_type = storina_get_option('indexAppCatType' . $elementID[$iiii]);
        $data = array();
        $data['type'] = 'categories';
        $data['showtype'] = ( $show_type ) ? $show_type : 'scrollButtons';
        $data['data'] = array();
        if (!empty($cat_ids)) {

            foreach ($cat_ids as $cat_id) {
                if ($cat_id > 0) {
                    $taxonomy_name = 'product_cat';
                    $terms = get_term_children($cat_id, $taxonomy_name);
                    $ArchiveBrowse = (!empty($terms) && !is_wp_error($terms) ) ? 'parent' : 'sub';
                    $term = get_term_by('id', $cat_id, $taxonomy_name);
                    $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                    $catThumb = wp_get_attachment_image_src($thumbnail_id, 'medium');
                    $data['data'][] = array(
                        'id' => intval($cat_id),
                        'title' => $term->name,
                        'ArchiveBrowse' => $ArchiveBrowse,
                        'image' => ( $catThumb[0] ) ? $catThumb[0] : '',
                        'parent' => ( $this->getParent($cat_id) ) ? intval($this->getParent($cat_id)) : intval($cat_id)
                    );
                }
            }
            $categoriesResult = $data;
        } else {
            $categoriesResult = $data;
        }

//echo json_encode($categoriesResult);
        return $categoriesResult;
    }

    private function getParent($cat_id) {
        $ancestors = get_ancestors($cat_id, 'product_cat');

        return $ancestors[count($ancestors) - 1];
    }

    private function oneColADV($elementID, $i) {
        $element_options = include trailingslashit( STORINA_PDP ) . "them_options/tabs/dinamic/oneColADV.php";
        $options = $element_options['option-names'];
        $banners_row = storina_get_option($options['banner']);
        $link_types_row = storina_get_option($options['link_type']);
        $link_values_row = storina_get_option($options['link_value']);
        $banner_columns = storina_get_option($options['column']);
        $general = $this->service_container->get(General::class);
        $onClickModel = $general->clickEvent($typeLinkBanner1[$i], $linkBanner1[$i]);
        if(!empty($banners_row)){
            for($i=0;$i<count($banners_row);$i++){
                $output_banners = [];
                $banners = $banners_row[$i];
                $link_types = $link_types_row[$i];
                $link_values = $link_values_row[$i];
                for($j=0;$j<count($banners);$j++){
                    $source = $banners[$j];
                    $link_type = $link_types[$j];
                    $link_value = $link_values[$j];
                    $link = $general->clickEvent($link_type,$link_value);
                    $output_banners[] = [
                        'source' => $source,
                        'link' => $link,
                    ];
                }
                $data[] = [
                    'banners' => $output_banners ?? [],
                    'column' => $banner_columns[$i] ?? 0
                ];
            }
        }
        return [
            'type' => 'oneColADV',
            'data' => $data ?? []
        ];
    }

    private function scrollADV($elementID, $iiii) {
        $general = $this->service_container->get(General::class);
        //                             Horizontal images
        $banners = storina_get_option('Sbanner_banner' . $elementID[$iiii]);
        $typeLinkBanner = storina_get_option('Sbanner_linkType' . $elementID[$iiii]);
        $linkBanner = storina_get_option('Sbanner_linkValue' . $elementID[$iiii]);
        $i = 0;
        $rows = array();
        $rows['type'] = 'scrollAds';
        $rows['data'] = array();
        if (!empty($banners)) {


            foreach ($banners as $banner1) {
                $onClickModel = $general->clickEvent($typeLinkBanner[$i], $linkBanner[$i]);


                $i ++;
                $rows['data'][] = array(
                    'banner' => $banner1,
                    'link' => $onClickModel,
                );
            }
        }

        return $rows;
    }

    private function productBox($elementID, $iiii) {
        //                             featured
        $BoxTitle = storina_get_option('indexAppBoxTitle' . $elementID[$iiii]);
        $BoxSort = storina_get_option('indexAppBoxSort' . $elementID[$iiii]);
        $BoxOrder = storina_get_option('indexAppBoxOrder' . $elementID[$iiii]);
        $BoxFloat = storina_get_option('indexAppBoxFloat' . $elementID[$iiii]);
        $BoxExist = storina_get_option('indexAppBoxExist' . $elementID[$iiii]);
        $cat_ids = storina_get_option('indexAppBox' . $elementID[$iiii]);
        $count = storina_get_option('indexAppBoxCount' . $elementID[$iiii]);
        date_default_timezone_set(storina_get_option('timezone_string'));
        $termtmp = get_term_by('id', $cat_ids, 'product_cat');
        $data = $productInfo = array();
        $data['type'] = 'productBox';
        $data['category'] = array(
            'id' => $cat_ids,
            'title' => ( $BoxTitle ) ? $BoxTitle : $termtmp->name,
        );
        // make sort params
        if ($BoxSort) {
            switch ($BoxSort) {
                case 'title':
                    $args['orderby'] = $BoxSort;
                    break;
                case 'date':
                    $args['orderby'] = $BoxSort;
                    break;
                case 'modified':
                    $args['orderby'] = $BoxSort;
                    break;
                case 'rand':
                    $args['orderby'] = $BoxSort;
                    break;
                case 'comment_count':
                    $args['orderby'] = $BoxSort;
                    break;
                case 'sale':
                    $args['orderby'] = 'meta_value_num';
                    $args['meta_key'] = 'total_sales';
                    break;
                case 'view':
                    $viewCounterField = ( storina_get_option('viewCounterField') ) ? storina_get_option('viewCounterField') : 'post-views';
                    if ($viewCounterField) {
                        $args['orderby'] = 'meta_value_num';
                        $args['meta_key'] = $viewCounterField;
                    }
                    break;
            }
            $args['order'] = $BoxOrder;
        }
        $general = $this->service_container->get(General::class);
        $activeVendors = $general->vendor_ids();
        if (strlen(sanitize_text_field($_POST['vendor_town'])) > 2) {
            $args['author__in'] = $activeVendors;
        }
        $args['post_type'] = 'product';
        $args['taxonomy'] = 'product_cat';
        $args['posts_per_page'] = ( $count > 14 ) ? 14 : $count;

        if ($BoxExist == "true") {
            $args['meta_query'] = array(
                array(// Simple products type
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '=',
                )
            );
        }
        if ($cat_ids > 0) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $cat_ids,
                    'operator' => 'IN'
                )
            );
        }
        if ($BoxSort == 'sale') {
            unset($args['order']);
            unset($args['taxonomy']);
        }
        if ($BoxSort == 'date') {
            unset($args['taxonomy']);
        }


        wp_reset_query();
        $wp_query = new WP_Query();
        $wp_query->query(apply_filters("osa_index_productBox_query_args", $args, $this->user_id));
        $data['data'] = array();
        if ($wp_query->have_posts()):
            while ($wp_query->have_posts()):
                $productInfo = array();
                $wp_query->the_post();
                $product_id = get_the_ID();
                global $post, $product;
                if (has_post_thumbnail()) {
                    $img_id = get_post_thumbnail_id($post->ID);
                    $thumb = wp_get_attachment_image_src($img_id, 'medium')[0];
                } else {
                    $thumb = $img = STORINA_PLUGIN_URL . "/assets/images/notp.png";
                }
                $productInfo['id'] = get_the_ID();
                $productInfo['title'] = html_entity_decode(get_the_title());
                //$productInfo['EN_title'] = get_post_meta(get_the_ID(), '_subtitle', true);
                $productInfo['thumbnail'] = $thumb;
                $productInfo['stock_quantity'] = ( $product->get_stock_quantity() ) ? intval($product->get_stock_quantity()) : 0;
                $productInfo['in_stock'] = $product->is_in_stock();
                $productInfo['stock_status'] = $product->get_stock_status();
                $productInfo['type'] = ( $product->get_type() == 'grouped' OR $product->get_type() == 'simple_catalogue' ) ? 'simple_catalogue' : $product->get_type();
                $productInfo['qty'] = $general->Advanced_Qty($productInfo['id']);
                $prices = $this->filter_prices($product);
                $productInfo['regular_price'] = $prices['regular_price'];
                $productInfo['sale_price'] = $prices['sale_price'];

                if (function_exists("YITH_Role_Based_Type")) {
                    $productInfo['regular_price'] = $this->yith_price_role->get_compute_price_render($product, $this->user_id);
                    $productInfo['sale_price'] = "";
                }
                //$productInfo['sale_price_dates_to']   = $prices['sale_price_dates_to'];
                //$productInfo['sale_price_dates_from'] = $prices['sale_price_dates_from'];

                if (function_exists('dokan_get_store_info') AND storina_get_option('VendorAvatar') == 'Show') {
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
                //$productInfo['total_sales'] = get_post_meta(get_the_ID(),'total_sales',true);
                $data['data'][] = apply_filters("osa_index_get_productBox_product_item_info",$productInfo, $product_id, $this->user_id);
            endwhile;
        endif;

        $data['data'] = ( $BoxFloat == 'rtl' ) ? array_reverse($data['data']) : $data['data'];

        return $data;
    }

    public function filter_prices($product) {
        $product_type = @$product->product_type;
        $prices = array();
        $prices['sale_price_dates_to'] = $prices['sale_price_dates_from'] = '';
        if ($product_type == 'variable') {

            $variations = $product->get_available_variations();
            //$productInfo['regular_price'] = get_post_meta(get_the_ID(),'_price',true);
            //$productInfo['sale_price'] = get_post_meta(get_the_ID(),'_sale_price',true);
            $variation_priceType = storina_get_option('variation_priceType');
            $PriceRange = null;
            if ($variation_priceType == 1) {
                $PriceRange = $this->getVariationPriceRange($variations);
            }
            $sales_price_to = get_post_meta(get_the_ID(), '_sale_price_dates_to', true);
            $sales_price_from = get_post_meta(get_the_ID(), '_sale_price_dates_from', true);
            $now = time();

            $to = __("To", "onlinerShopApp");
            if ($PriceRange) {
                if ($PriceRange['regularPrice']['min'] != $PriceRange['regularPrice']['max']) {
                    $prices['regular_price'] = "{$PriceRange['regularPrice']['min']} $to {$PriceRange['regularPrice']['max']}";
                } else {
                    $prices['regular_price'] = "{$PriceRange['regularPrice']['min']}";
                }
                if (99999999 == $PriceRange['salePrice']['min']) {
                    $PriceRange['salePrice']['min'] = '';
                }
                if ($PriceRange['salePrice']['min'] != $PriceRange['salePrice']['max']) {
                    $prices['sale_price'] = "{$PriceRange['salePrice']['min']} $to {$PriceRange['salePrice']['max']}";
                } else {
                    $prices['sale_price'] = "{$PriceRange['salePrice']['min']}";
                }
            } else {
                $prices['regular_price'] = $variations[0]['display_regular_price'];
                $prices['sale_price'] = $variations[0]['display_price'];
                if ($now > $sales_price_from AND $now < $sales_price_to) {
                    $prices['sale_price'] = $variations[0]['display_price'];
                    $prices['sale_price_dates_to'] = $sales_price_to;
                    $prices['sale_price_dates_from'] = $sales_price_from;
                }

                if ($prices['regular_price'] == $prices['sale_price']) {
                    $prices['sale_price'] = '';
                }
            }
        } else {

            $prices['regular_price'] = get_post_meta(get_the_ID(), '_regular_price', true);

            $sales_price_to = get_post_meta(get_the_ID(), '_sale_price_dates_to', true);
            $sales_price_from = get_post_meta(get_the_ID(), '_sale_price_dates_from', true);
            $now = time();
            if ($sales_price_from > 0) {
                if ($now > $sales_price_from AND $now < $sales_price_to) {
                    $prices['sale_price'] = $product->get_sale_price();
                    $prices['sale_price_dates_to'] = $sales_price_to;
                    $prices['sale_price_dates_from'] = $sales_price_from;
                } else {
                    $prices['sale_price'] = '';
                }
            } else {
                $prices['sale_price'] = $product->get_sale_price();
            }
        }
        if (!$product->is_on_sale()) {
            $prices['sale_price'] = "";
        }
        return $prices;
    }

    public function getVariationPriceRange($variations) {
        $regularMax = $saleMax = 0;
        $regularMin = $saleMin = 99999999;
        if (count($variations) > 1) {
            $now = time();
            foreach ($variations as $variation) {
                $sales_price_to = get_post_meta($variation['id'], '_sale_price_dates_to', true);
                $sales_price_from = get_post_meta($variation['id'], '_sale_price_dates_from', true);

                if ($variation['display_regular_price'] > $regularMax) {
                    $regularMax = $variation['display_regular_price'];
                }
                if ($variation['display_regular_price'] < $regularMin) {
                    $regularMin = $variation['display_regular_price'];
                }
                if ($now > $sales_price_from AND $now < $sales_price_to) {
                    if ($variation['display_regular_price'] != $variation['display_price']) {
                        if ($variation['display_price'] > $saleMax) {
                            $saleMax = $variation['display_price'];
                        }
                        if ($variation['display_price'] < $saleMin) {
                            $saleMin = $variation['display_price'];
                        }
                    }
                }
            }
            $result = array(
                'salePrice' => array('min' => intval($saleMin), 'max' => intval($saleMax)),
                'regularPrice' => array('min' => intval($regularMin), 'max' => intval($regularMax))
            );
        } else {
            $result = null;
        }


        return $result;
    }

    private function line($elementID, $iiii) {
        //                Divider
        $Divider['type'] = 'line';

        //echo json_encode($Divider);
        return $Divider;
    }

    private function space($elementID, $iiii) {
        //                        Free space
        $FreeSpace['type'] = 'space';
        $FreeSpace['dp'] = intval(storina_get_option('space' . $elementID[$iiii]));

//echo json_encode($FreeSpace);
        return $FreeSpace;
    }

    public function postBox($elementID,$i){
        $option_keys = include trailingslashit( STORINA_PDP ) . 'them_options/tabs/dinamic/postBox.php';
        foreach($option_keys as $option_key){
            $option_id = $option_key['id'];
            $post_box[$option_id] = storina_get_option($option_id);
        }
        $query_args = [
            'post_type' => ['post'],
            'posts_per_page' => (int) $post_box["indexAppBoxCount".$elementID[$i]] ?: 12,
            'order_by' => $post_box["indexAppBoxSort".$elementID[$i]],
            'order' => $post_box["indexAppBoxOrder".$elementID[$i]],
        ];
        $category_id = $post_box["indexAppBox".$elementID[$i]];
        if(-1 != $category_id){
            $query_args['cat'] = $category_id;
        }
        $wp_query = new WP_Query(apply_filters('woap_index_post_box_query_args',$query_args));
        if($wp_query->have_posts(  )){
            while($wp_query->have_posts(  )){
                $wp_query->the_post(  );
                //OUTPUT
                $output['id'] = get_the_ID(  );
                $output['title'] = get_the_title(  );
                $output['image'] = (has_post_thumbnail())? get_the_post_thumbnail_url() : STORINA_PLUGIN_URL . "/assets/images/notp.png"; 
                $output['excerpt'] = storina_content_excerpt(get_the_content( ),150);
                $data[] = $output;
                //OUTPUT
            }
        }
        return [
            'type' => 'postBox',
            'title' => $post_box["indexAppBoxTitle".$elementID[$i]],
            'category_id' => $category_id,
            'float' => $post_box["indexAppBoxFloat" . $elementID[ $i ]],
            'data' => $data ?? [],
        ];
    }

    public function scrollBox($element_id,$i){
        $general = $this->service_container->get(General::class);
        $titles = storina_get_option('Sb_title' . $element_id[$i]);
        $icon_links = storina_get_option('Sb_banner' . $element_id[$i]);
        $link_types = storina_get_option('Sb_linkType' . $element_id[$i]);
        $link_values = storina_get_option('Sb_linkValue' . $element_id[$i]);
        $count = max(count($titles),count($icon_links),count($link_types),count($link_vlaues));
        $data = $output = [];
        for($i=0;$i<$count;$i++){
            $link_resource = $general->clickEvent($link_types[$i], $link_values[$i]);
            $data[] = [
                'title' => $titles[$i],
                'icon_link' => $icon_links[$i],
                'link' => $link_resource,
            ];
        }
        return [
            'type' => 'scrollBox',
            'data' => $data
        ];
    }

    public function productBoxColorize($elementID,$i){
        $option_keys = include trailingslashit( STORINA_PDP ) . 'them_options/tabs/dinamic/productBoxColorize.php';
        foreach($option_keys as $option_key){
            $option_id = (is_array($option_key['id']))? current($option_key['id']) : $option_key['id'];
            $product_box_colorize[$option_id] = storina_get_option($option_id);
        }
        $query_args = [
            'post_type' => ['product'],
            'posts_per_page' => (int) $product_box_colorize["indexAppBoxCount".$elementID[$i]] ?: 12,
            'order_by' => $product_box_colorize["indexAppBoxSort".$elementID[$i]],
            'order' => $product_box_colorize["indexAppBoxOrder".$elementID[$i]],
        ];
        $product_cat_id =$product_box_colorize["indexAppBox".$elementID[$i]];
        $query_args['meta_query'][] = [
            'key' => '_sale_price_dates_to',
            'value' => time(),
            'compare' => '>',
            'type' => 'number'
        ];
        if(0 != $product_cat_id){
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => [$product_cat_id],
                    'operator' => 'IN'
                ]
            ];
        }
        if('true' == $product_box_colorize["indexAppBoxExist".$elementID[$i]]) {
            $query_args['meta_query']['relation'] = 'AND';
            $query_args['meta_query'][] = [
                'key' => '_stock_status',
                'value' => 'instock',
                'compare' => '=',
            ];
        }
        //wp_send_json(apply_filters('woap_index_product_box_colorize_query_args',$query_args));
        $wp_query = new WP_Query(apply_filters('woap_index_product_box_colorize_query_args',$query_args));
        if($wp_query->have_posts(  )){
            while($wp_query->have_posts(  )){
                $wp_query->the_post(  );
                $product_id = get_the_ID(  );
                $product = wc_get_product($product_id);
                $date_time = $product->get_date_on_sale_to();
                $sale_date_timestamp = ($date_time instanceof WC_DateTime)? $date_time->getTimestamp() : 0;
                //OUTPUT
                $output['id'] = $product_id;
                $output['title'] = $product->get_title();
                $output['image_id'] = $product->get_image_id();
                $output['sale_date_to'] = isset($date_time)? $this->extract_time_with_now($sale_date_timestamp) : false;
                $output['image'] = wp_get_attachment_url( intval($product->get_image_id()) ) ?: trailingslashit( STORINA_PLUGIN_URL ) . "assets/images/notp.png";
                $output['stock_quantity'] = $product->get_stock_quantity() ?? 0;
                $output['stock_status'] = $product->get_stock_status();
                $output['type'] = $product->get_type();
                $output['regular_price'] = $product->get_regular_price();
                $output['sale_price'] = $product->get_sale_price();
                $output['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
                $data[] = $output;
                //OUTPUT
            }
        }
        return [
            'type' => 'productBoxColorize',
            'title' => $product_box_colorize["indexAppBoxTitle".$elementID[$i]],
            'product_cat_id' => $product_cat_id,
            'background' => [
                'color' => $product_box_colorize["boxColor".$elementID[$i]],
                'image' => $product_box_colorize["boxBackgroundImage".$elementID[$i]]
            ],
            'icon' => $product_box_colorize["boxIcon".$elementID[$i]],
            'data' => $data ?? []
        ];
    }

    public function extract_time_with_now($to) {
        $to = intval($to);
        $now = (int) time();
        $diff = (($to - $now) > 0) ? intval(($to - $now)) : 0;
        $years = floor($diff / (365 * 60 * 60 * 24));
        $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
        $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
        $hours = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));
        $minutes = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);
        $seconds = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minutes * 60));

        return (
                array(
                    'years' => $years,
                    'months' => $months,
                    'days' => $days,
                    'hours' => $hours,
                    'minuts' => $minutes,
                    'secunds' => $seconds,
                )
                );
    }

}
