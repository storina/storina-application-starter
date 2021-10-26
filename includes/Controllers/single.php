<?php

use STORINA\Controllers\Cache;
use \STORINA\Controllers\Yith_Role_Based_Price;


defined('ABSPATH') || exit;


class OSA_single {

    public $user_id = false;
    public $yith_price_role = false;
    public $service_container;

    public function __construct($service_container) {
        $this->service_container = $service_container;
        require_once( ABSPATH . "wp-load.php" );
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if (class_exists('JCWC_AdvancedAttrubutes')) {
            $jcaa = OSA_PLUGIN_PATH . '../jc-woocommerce-advanced-attributes/libs/jcaa-integrations.php';
            $bigikala = OSA_PLUGIN_PATH . '../Bigilala-Attributes/libs/jcaa-integrations.php';
            if (file_exists($bigikala)) {
                include_once $bigikala;
            } else {
                include_once $jcaa;
            }
        }

        $this->yith_price_role = $this->service_container->get(Yith_Role_Based_Price::class);
    }

    public function getContent(){
        $post_id = (is_numeric($_POST['id']))? $_POST['id'] : 0;
        $product = wc_get_product($post_id);
        $general = $this->service_container->get(General::class);
        return $general->extractContent($product->get_description());
    }
    
    public function getView() {
        $googleID = $_POST['googleID'];
        $count = $_POST['count'];
        $posts = array();
        global $wpdb;
        $table = $wpdb->prefix . 'OSA_view_log';
        $Record = $wpdb->get_row("SELECT * FROM $table WHERE googleID = '$googleID'");
        $ids = json_decode($Record->param);
        $ids = ( array_slice(array_reverse($ids), 0, $count) );
        foreach ($ids as $post_id) {
            $product = wc_get_product($post_id);
            $Post['id'] = intval($post_id);
            $Post['title'] = html_entity_decode(get_the_title($post_id));
            if (has_post_thumbnail($post_id)) {
                $img_id = get_post_thumbnail_id($post_id);
                $src = wp_get_attachment_image_src($img_id, 'medium')[0];
            } else {
                $src = OSA_PLUGIN_URL . "/assets/images/notp.png";
            }
            $Post['image'] = $src;
            $instock = get_post_meta($post_id, '_stock_status', true);
            $Post['in_stock'] = ( $instock == 'instock' ) ? true : false;
            $Post['price'] = get_post_meta($post_id, '_regular_price', true);
            $Post['sale_price'] = get_post_meta($post_id, '_sale_price', true);
            if ("variable" == $product->get_type()) {
                $to = __("To", "onlinerShopApp");
                $min = $product->get_variation_sale_price('min');
                $max = $product->get_variation_sale_price('max');
                $Post['price'] = "{$min} {$to} {$max}";
                $min_sale = $product->get_variation_sale_price('min');
                $max_sale = $product->get_variation_sale_price('max');
                $Post['sale_price'] = (empty($min_sale)) ? "{$min_sale} {$to} {$max_sale}" : "";
            }
            if (function_exists("YITH_Role_Based_Type")) {
                $Post['price'] = $this->yith_price_role->get_compute_price_render($product, $this->user_id);
                $Post['sale_price'] = "";
            }

            $Post['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
            $posts[] = $Post;
        }

        $result = array(
            'status' => true,
            'data' => $posts
        );

        return ( $result );
    }

    public function get() {
        $index_object = $this->service_container->get(Index::class);
        date_default_timezone_set('Asia/Tehran');
        $masterID = $_POST['id'];

        $userToken = ( isset($_POST['userToken']) ) ? $_POST['userToken'] : "";
        $user_action = $this->service_container->get("OSA_user");
        $user_id = $user_action->get_userID_byToken($userToken);
        $this->user_id = $user_id;
        do_action('osa_single_get_action_init',$masterID,$this->user_id);
        $priceChart = array();
        //     check cache
        $OSA_cache = $this->service_container->get(Cache::class);
        $record = $OSA_cache->getCache('single', $masterID);
        $backorder_text = osa_get_option('app_backorder_text');
        $general = $this->service_container->get(General::class);
        $cache = ( osa_get_option('appCacheStatus') == 'inactive' ) ? false : true;
        $viewCounter = $this->save_post_view($masterID); // افزایش تعداد بازدید
        if (!empty($record) AND $cache == true) {
            $this->saveView($masterID); // ثبت در جدول بازدید شده ها
            $single = $record;
            $single['data']['in_wishlist'] = $this->wishlistStatus();
            $single['data']['viewCount'] = intval($viewCounter);

            return ( $single );
        }
        // end check cache

        $arg = array(
            'p' => $masterID,
            'post_type' => 'product'
        );
        $query = new WP_Query(apply_filters("osa_single_get_query_args",$arg, $this->user_id));
        if ($query->have_posts()):
            $this->saveView($masterID); // ثبت در جدول بازدید شده ها
            while ($query->have_posts()) :
                $query->the_post();
                $product_id = get_the_ID();
                $product = wc_get_product(get_the_ID());

                $prices = $index_object->filter_prices($product);
                $createdVariation = array();
                $attributesGrouped = $this->options($product->get_attributes());
                if ($product->get_type() == 'variable') {
                    $product_new = wc_get_product($masterID);
                    $attributes = $product_new->get_attributes();
                    $variationAttributes = array();
                    foreach ($attributes as $attribute) {
                        if ($attribute['variation'] == 1) {
                            $variationAttributes[$attribute['id']] = $this->accessProtected($attribute, 'data');
                        }
                    }
                    foreach ($product->get_available_variations() as $variation) {

                        //var_dump($variation);
                        $variationID = $variation['variation_id'];
                        $product_variation = new WC_Product_Variation($variationID);
                        $product_variation = $this->accessProtected($product_variation, 'data');
                        $tmp['id'] = $variationID;
                        $tmp['regular_price'] = $product_variation['regular_price'];
                        $tmp['sale_price'] = $product_variation['sale_price'];

                        if (function_exists("YITH_Role_Based_Type")) {
                            $float_price = $this->yith_price_role->get_compute_price_render(wc_get_product($variationID), $this->user_id);
                            $tmp['regular_price'] = round($float_price);
                            $tmp['sale_price'] = "";
                        }

                        $tmp['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
                        $tmp['stock_quantity'] = ( $product_variation['stock_quantity'] ) ? $product_variation['stock_quantity'] : 0;


                        $tmp['in_stock'] = ( $product_variation['stock_status'] == 'instock' ) ? true : false;

                        $tmp['stock_status'] = $product_variation['stock_status']; // onbackorder - instock - outofstock

                        $tmp['backorder_text'] = ( $backorder_text ) ? $backorder_text : 'پیش خرید';

                        $tmp['image'] = $product_variation['image_id'];
                        $attrkham = $product_variation['attributes'];
                        // add termid and color
                        if (!empty($attrkham)) {
                            $i = 0;
                            $singleAttra = $singleAttrb = array();
                            //var_dump($attrkham);
                            $attr = $this->options($product->get_attributes());

                            foreach ($attrkham as $key => $val) {
                                //var_dump($product->get_attributes()[$key]);
                                //var_dump($attr);
                                $found = array();
                                $key = str_replace('pa_', '', $key);
                                $singleAttr = $this->get_woo_attribute_by('tax', $key);
                                $manualATTRname = ( $attr[$key]['name'] ) ? $attr[$key]['name'] : $attr[str_replace('-', '+', $key)]['name'];
                                $manualATTRvalue = ( $attr[$key]['options'] ) ? $attr[$key]['options'] : $attr[str_replace('-', '+', $key)]['options'];
                                $label = ( $singleAttr ) ? $singleAttr->attribute_label : $manualATTRname;
                                $attrID = $singleAttr->attribute_id;
                                $thisAttrs = ( $singleAttr ) ? $attr[$attrID]['options'] : $manualATTRvalue;
                                //var_dump($attr);
                                //var_dump($thisAttrs);
                                //var_dump($manualATTRvalue);
                                foreach ($thisAttrs as $this_attr) {
                                    $term = get_term($this_attr['term_id']);
                                    //var_dump(($this_attr['term_id'] == 0 AND $this_attr['label'] == urldecode( $val )));
                                    //echo $term->slug .'='. urldecode($val) .'|'. $val .'='. $term->name.'<br>';
                                    if (urldecode($term->slug) == urldecode($val)
                                            OR $val == $term->name
                                            OR urldecode($val) == $term->name
                                            OR ( $this_attr['term_id'] == 0 AND $this_attr['label'] == urldecode($val) )) {
                                        $found = array(
                                            'term_id' => ( $term->term_id ) ? $term->term_id : 0,
                                            'label' => ( $term->term_id ) ? $term->name : $this_attr['label']
                                        );
                                        break;
                                    }
                                }

                                $jctype = $general->get_attr_setting($attrID, 'jcaa_attribute_type');
                                //$term = get_term_by('slug','%d8%b3%d8%a8%d8%b2','pa_color');
                                if ($jctype == 'color') {
                                    $value = get_term_meta($found['term_id'], '_jcaa_product_attr_color', true);
                                    if (!$value) {
                                        $value = get_term_meta($found['term_id'], 'pa_color_swatches_id_color', true);
                                    }

                                    $value = $this->color_name_to_hex($value);
                                } elseif ($jctype == 'image') {
                                    $value = wp_get_attachment_thumb_url(get_term_meta($found['term_id'], '_jcaa_product_attr_thumbnail_id', true));
                                    if (!$value) {
                                        $value = wp_get_attachment_thumb_url(get_term_meta($found['term_id'], 'pa_brand_swatches_id_photo', true));
                                    }
                                } else {
                                    $jctype = 'text';
                                    $value = $found['label'];
                                }
                                if ($singleAttr == false) {
                                    $is_manual_attr = true;
                                }
                                $singleAttr = array(
                                    'id' => intval($attrID),
                                    'name' => $label,
                                    'option' => ( $found['label'] AND $found['term_id'] != null ) ? $found['label'] : $value,
                                    'term_id' => ( $found['term_id'] >= 0 AND $found['term_id'] != null ) ? $found['term_id'] : 'attrIsArray',
                                    'type' => ( $jctype ) ? $jctype : 'text',
                                    'value' => ( $value ) ? $value : '',
                                    'all_options' => array()
                                );
                                if ($singleAttr['term_id'] == 'attrIsArray') {
                                    if ($is_manual_attr) {
                                        /* $counter = 99999;
                                          for ( $i=0;$i<count($thisAttrs);$i++ ) {
                                          $thisAttrs[$i]['term_id'] = $counter++;
                                          } */
                                        $singleAttr['all_options'] = $thisAttrs;
                                    } else {
                                        $singleAttr['all_options'] = $attributesGrouped[$attrID]['options'];
                                    }
                                    $singleAttr['type'] = 'text';
                                    $singleAttr['option'] = $singleAttr['name'];
                                    foreach ($singleAttr['all_options'] as &$options) {
                                        $options['value'] = $options['label'];
                                    }
                                    $singleAttra[] = $singleAttr;
                                } else {
                                    $singleAttrb[] = $singleAttr;
                                }
                                $i ++;
                            }
                            $tmp['attributes'] = $this->sort_attributes(array_merge($singleAttra, $singleAttrb));
                        }
                        $createdVariation[] = apply_filters("osa_single_get_variation_item", $tmp ,$variationID ,$masterID);
                        $mergedPriceChart = get_post_meta($variationID, '_priceChart', true);
                        if (function_exists('addFieldPriceChartPro') AND $mergedPriceChart) {
                            $priceChart[$variationID] = explode('|', $mergedPriceChart);
                        }
                    }
                } else {
                    $mergedPriceChart = get_post_meta($masterID, '_simplePriceChart', true);
                    if (function_exists('addFieldPriceChartPro') AND $mergedPriceChart) {
                        $priceChart[$masterID] = explode('|', $mergedPriceChart);
                    }
                }

                $final['id'] = get_the_id();
                $final['url'] = $product->get_permalink();
                $final['type'] = ( $product->get_type() == 'grouped' OR $product->get_type() == 'simple_catalogue' ) ? 'simple_catalogue' : $product->get_type();
                if ($product->get_type() == 'external') {
                    $final['BuyLink'] = $product->get_product_url();
                    $final['ButtonText'] = $product->get_button_text();
                }

                $final['name'] = html_entity_decode($product->get_name());
                if ("true" == osa_get_option('app_showProductNameEnglish')) {
                    $final['en_name'] = "";
                } else {
                    $final['en_name'] = ( get_post_meta($final['id'], '_subtitle', true) ) ? get_post_meta($final['id'], '_subtitle', true) : get_post_meta($final['id'], '_ENtitle', true);
                }
                $final['date_created'] = $product->get_date_created();
                $final['viewCount'] = intval($viewCounter) + 1;
                $final['qty'] = $general->Advanced_Qty($masterID);
                $imgs = array();
                if (has_post_thumbnail($final['id'])) {
                    $img_id = get_post_thumbnail_id($final['id']);
                    $src = wp_get_attachment_image_src($img_id, 'woocommerce_single')[0];
                    $alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                    $imgs[] = array(
                        'id' => '',
                        'date_created' => '',
                        'date_created_gmt' => '',
                        'date_modified' => '',
                        'date_modified_gmt' => '',
                        'src' => $src,
                        'name' => '',
                        'alt' => $alt,
                        'position' => 0
                    );
                }

                $gallery_attachment_ids = $product->get_gallery_attachment_ids();
                foreach ($gallery_attachment_ids as $index => $gallery_attachment_id) {
                    $src = wp_get_attachment_image_src($gallery_attachment_id, 'woocommerce_single')[0];
                    $alt = get_post_meta($gallery_attachment_id, '_wp_attachment_image_alt', true);
                    $imgs[] = array(
                        'id' => '',
                        'date_created' => '',
                        'date_created_gmt' => '',
                        'date_modified' => '',
                        'date_modified_gmt' => '',
                        'src' => $src,
                        'name' => '',
                        'alt' => $alt,
                        'position' => $index + 1
                    );
                }
                $final['images'] = $imgs; //shop_catalog
                $final['regular_price'] = $prices['regular_price'];
                $sales_price_to = $prices['sale_price_dates_to'];
                $sales_price_from = $prices['sale_price_dates_from'];
                $now = time();
                if ($sales_price_from > 0) {
                    if ($now > $sales_price_from AND $now < $sales_price_to) {
                        $final['sale_price'] = $prices['sale_price'];
                        //$final['sale_price_dates_to']   = $sales_price_to;
                        //$final['sale_price_dates_from'] = $sales_price_from;
                    } else {
                        $final['sale_price'] = '';
                    }
                } else {
                    $final['sale_price'] = $prices['sale_price'];
                }
                if (function_exists("YITH_Role_Based_Type")) {
                    $final['regular_price'] = $this->yith_price_role->get_compute_price_render($product, $this->user_id);
                    $final['sale_price'] = "";
                }
                if ($sales_price_to) {
                    $final['different'] = $index_object->extract_time_with_now($sales_price_to);
                }
                $final['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
                //var_dump($originalData);
                if ($product->get_manage_stock()) {
                    if ($product->get_stock_quantity() > 0) {
                        $final['in_stock'] = true;
                        $final['stock_quantity'] = ( $product->get_stock_quantity() ) ? intval($product->get_stock_quantity()) : 0;
                    } else {
                        $final['in_stock'] = false;
                        $final['stock_quantity'] = ( $product->get_stock_quantity() ) ? intval($product->get_stock_quantity()) : 0;
                    }
                } else {
                    if ($product->get_stock_status() != 'outofstock') {
                        $final['in_stock'] = true;
                        $final['stock_quantity'] = 0;
                    } else {
                        $final['in_stock'] = false;
                        $final['stock_quantity'] = 0;
                    }
                }
                $sold_ind = get_post_meta($final['id'], '_sold_individually', true);
                if ($sold_ind == 'yes') {
                    $final['stock_quantity'] = 1;
                }
                $final['stock_status'] = $product->get_stock_status(); // onbackorder - instock - outofstock
                $final['backorder_text'] = ( $backorder_text ) ? $backorder_text : 'پیش خرید';
                //var_dump($originalData);
                $final['average_rating'] = $product->get_average_rating();
                $final['rating_count'] = intval($product->get_rating_count());
                $final['reviews_allowed'] = $product->get_reviews_allowed();
                $final['in_wishlist'] = $this->wishlistStatus();
                if (function_exists('dokan_get_store_info')) {
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
                    $state = $general->get_states($store_settings['address']['state']);
                    $store_settings['address']['state'] = $state;
                    $dps_processing = get_user_meta(get_the_author_meta('ID'), '_dps_pt', true);
                    $vendor_biography = $store_settings['vendor_biography'];
                    $vendor_send_time = ( $dps_processing ) ? "آماده ارسال از $dps_processing روز آینده" : 'آماده ارسال از انبار ' . get_bloginfo('name');
					$vendor_id = get_the_author_meta('ID');
                    $vendor_info = (osa_get_option('VendorAvatar') == 'Show')? array(
                        'vendor_id' => intval(get_the_author_meta('ID')),
                        'store_name' => $store_settings['store_name'],
                        'phone' => $store_settings['phone'],
                        'address' => $store_settings['address'],
                        'email' => ( $email ) ? $email : '',
                        'banner' => ( $banner ) ? $banner : '',
                        'gravatar' => ( $gravatar ) ? $gravatar : '',
                        'vendor_send_time' => (!empty($vendor_biography))? $vendor_biography : $vendor_send_time,
                    ) : [];
                    $final['vendor'] = apply_filters( "osa_single_get_vendor_info", $vendor_info ,$vendor_id, $this->user_id );
                }

                $final['description'] = $general->extractContent($this->get_short_description($product));
                if (!empty(get_post_meta($product->get_id(), "_bulkdiscount_text_info", true))) {
                    $final['owbd_description'] = get_post_meta($product->get_id(), "_bulkdiscount_text_info", true);
                }

                $final['chart'] = $priceChart;
                $final['related'] = $this->related($masterID);
                //var_dump($product->get_categories());
                $final['categories'] = array();
                $final['mostSales'] = $this->getMostSales($final['categories']);
                $final['attributes'] = $this->attributes($masterID);
                if (is_array($createdVariation)) {
                    $final['variations'] = $createdVariation;
                }
                $final['attributesGrouped'] = $attributesGrouped;
                $final['weight_unit'] = get_option( 'woocommerce_weight_unit' );
                $final['dimension_unit'] = get_option( 'woocommerce_dimension_unit' );
                $final['weight'] = $product->get_weight();
                $final['dimension'] = [
                    'length' => $product->get_length(),
                    'width'  => $product->get_width(),
                    'height' => $product->get_height(),
                ];
                $result = array(
                    'status' => true,
                    'data' => apply_filters("osa_single_get_data", $final, $masterID,$this->user_id),
                );
                $OSA_cache->setCache(json_encode($result), 'single', $masterID);

                return ( $result );
            endwhile;
        else:
            $result = array(
                'status' => false,
                'data' => array(
                    'message' => 'No post exist.'
                )
            );

            return ( $result );
        endif;
    }

    public function sort_attributes($attributes) {
        $numeric = $string = array();
        foreach ($attributes as $attribute) {
            if (is_numeric($attribute['term_id'])) {
                $numeric[] = $attribute;
            } else {
                $string[] = $attribute;
            }
        }
        return array_merge($numeric, $string);
    }

    private function save_post_view($postID) {
        $viewCountFiled = ( osa_get_option('viewCounterField') ) ? osa_get_option('viewCounterField') : 'post-views';
        if (!$viewCountFiled) {
            $viewCountFiled = 'post-views';
        }
        if ($viewCountFiled) {
            $viewCounter = get_post_meta($postID, $viewCountFiled, true);
            update_post_meta($postID, $viewCountFiled, $viewCounter + 1);
        }

        return get_post_meta($postID, $viewCountFiled, true);
    }

    private function saveView($postID) {
        $googleID = strip_tags($_POST['googleID']);
        global $wpdb;
        $table = $wpdb->prefix . 'OSA_view_log';
        $Record = $wpdb->get_row("SELECT * FROM $table WHERE googleID = '$googleID'");

        if (!$Record) {
            $param = json_encode(array($postID));
            $result = $wpdb->insert(
                    $table,
                    array(
                        'googleID' => $googleID,
                        'param' => $param
                    ),
                    array(
                        '%s',
                        '%s'
                    )
            );
        } else {
            $param = json_decode($Record->param);
            $param = array_reverse($param);
            if (count($param) > 99) {
                $param = array_slice($param, 0, 99);
            }
            $pos = ( array_search($postID, $param) );
            $param = array_reverse($param);

            if ($pos > 15 OR $pos === false) {
                //echo $pos;
                $param[] = $postID;
                $param = json_encode($param);
                $result = $wpdb->update(
                        $table,
                        array('param' => $param),
                        array(
                            'googleID' => $googleID
                        ),
                        array(
                            '%s'
                        ),
                        array('%s')
                );
            }
        }

        return (!empty($result) ) ? true : false;
    }

    private function wishlistStatus() {
        $userToken = $_POST['userToken'];
        $id = $_POST['id'];
        $user_action = $this->service_container->get("OSA_user");
        $user_id = $user_action->get_userID_byToken($userToken);
        if ($user_id) {
            global $wpdb;
            $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "yith_wcwl WHERE prod_id = %d AND user_id = %d", $id, $user_id));
            if ($result) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function options($data) {
        $options = array();
        foreach ($data as $attributes) {
            $attributes = $this->accessProtected($attributes, 'data');
            if ($attributes['variation']) {
                global $variationAttributes;
                //$optionsTmp = $attributes['options'];
                unset($attributes['position']);
                unset($attributes['visible']);
                unset($attributes['variation']);
                $general = $this->service_container->get(General::class);
                //$jcaa_attribute_size = $general->get_attr_setting($attributes['id'], 'jcaa_attribute_size');
                //$jcaa_attribute_style = $general->get_attr_setting($attributes['id'], 'jcaa_attribute_style');
                $jcaa_attribute_type = $general->get_attr_setting($attributes['id'], 'jcaa_attribute_type');
                if ($jcaa_attribute_type == 'default') {
                    $jcaa_attribute_type = 'text';
                }
                //$jcaa_attribute_label = $general->get_attr_setting($attributes['id'], 'jcaa_attribute_label');
                //$jcaa_attribute_grouped = $general->get_attr_setting($attributes['id'], 'jcaa_attribute_grouped');
                //$jcaa_attribute_catalog = $general->get_attr_setting($attributes['id'], 'jcaa_attribute_catalog');
                $singleAttr = $this->get_woo_attribute_by('id', $attributes['id']);
                $attributes['label'] = ( $singleAttr->attribute_label ) ? $singleAttr->attribute_label : $attributes['name'];
                $tmpvariation = $values = array();

                if (class_exists('JCWC_AdvancedAttrubutes') AND $jcaa_attribute_type) {
                    //$attributes['name'] = $variationAttributes[$attributes['id']]['name'];
                    //$attributes['size'] = $jcaa_attribute_size;
                    //$attributes['style'] = $jcaa_attribute_style;
                    $attributes['type'] = $jcaa_attribute_type;
                } else {
                    //$attributes['name'] = $this->get_woo_attribute_by('id', $attributes['id'])->attribute_name;
                    //$attributes['size'] = '';
                    //$attributes['style'] = '';
                    $attributes['type'] = 'text';
                }
                foreach ($attributes['options'] as $termID) {
                    $term = get_term($termID);
                    if ($attributes['type'] == 'color') {
                        $value = get_term_meta($termID, '_jcaa_product_attr_color', true);
                        if (!$value) {
                            $value = get_term_meta($term->term_id, 'pa_color_swatches_id_color', true);
                        }
                        $value = $this->color_name_to_hex($value);
                    } elseif ($attributes['type'] == 'image') {
                        $value = wp_get_attachment_thumb_url(get_term_meta($termID, '_jcaa_product_attr_thumbnail_id', true));
                        if (!$value) {
                            $value = wp_get_attachment_thumb_url(get_term_meta($term->term_id, 'pa_brand_swatches_id_photo', true));
                        }
                    } else {
                        $value = $term->name;
                    }
                    $tmpvariation[] = array(
                        'term_id' => intval($termID),
                        'label' => ( $term->name ) ? $term->name : $termID,
                        'value' => ( $value ) ? $value : $termID,
                    );
                }
                $attributes['options'] = $tmpvariation;
                if ($attributes['id'] > 0) {
                    $options[$attributes['id']] = $attributes;
                } else {
                    $options[strtolower(urlencode($attributes['name']))] = $attributes;
                }
            }
        }

        return $options;
    }

    private function accessProtected($obj, $prop) {
        $reflection = new ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);

        return $property->getValue($obj);
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

    private function color_name_to_hex($color_name) {
        // standard 147 HTML color names
        $colors = array(
            'aliceblue' => 'F0F8FF',
            'antiquewhite' => 'FAEBD7',
            'aqua' => '00FFFF',
            'aquamarine' => '7FFFD4',
            'azure' => 'F0FFFF',
            'beige' => 'F5F5DC',
            'bisque' => 'FFE4C4',
            'black' => '000000',
            'blanchedalmond ' => 'FFEBCD',
            'blue' => '0000FF',
            'blueviolet' => '8A2BE2',
            'brown' => 'A52A2A',
            'burlywood' => 'DEB887',
            'cadetblue' => '5F9EA0',
            'chartreuse' => '7FFF00',
            'chocolate' => 'D2691E',
            'coral' => 'FF7F50',
            'cornflowerblue' => '6495ED',
            'cornsilk' => 'FFF8DC',
            'crimson' => 'DC143C',
            'cyan' => '00FFFF',
            'darkblue' => '00008B',
            'darkcyan' => '008B8B',
            'darkgoldenrod' => 'B8860B',
            'darkgray' => 'A9A9A9',
            'darkgreen' => '006400',
            'darkgrey' => 'A9A9A9',
            'darkkhaki' => 'BDB76B',
            'darkmagenta' => '8B008B',
            'darkolivegreen' => '556B2F',
            'darkorange' => 'FF8C00',
            'darkorchid' => '9932CC',
            'darkred' => '8B0000',
            'darksalmon' => 'E9967A',
            'darkseagreen' => '8FBC8F',
            'darkslateblue' => '483D8B',
            'darkslategray' => '2F4F4F',
            'darkslategrey' => '2F4F4F',
            'darkturquoise' => '00CED1',
            'darkviolet' => '9400D3',
            'deeppink' => 'FF1493',
            'deepskyblue' => '00BFFF',
            'dimgray' => '696969',
            'dimgrey' => '696969',
            'dodgerblue' => '1E90FF',
            'firebrick' => 'B22222',
            'floralwhite' => 'FFFAF0',
            'forestgreen' => '228B22',
            'fuchsia' => 'FF00FF',
            'gainsboro' => 'DCDCDC',
            'ghostwhite' => 'F8F8FF',
            'gold' => 'FFD700',
            'goldenrod' => 'DAA520',
            'gray' => '808080',
            'green' => '008000',
            'greenyellow' => 'ADFF2F',
            'grey' => '808080',
            'honeydew' => 'F0FFF0',
            'hotpink' => 'FF69B4',
            'indianred' => 'CD5C5C',
            'indigo' => '4B0082',
            'ivory' => 'FFFFF0',
            'khaki' => 'F0E68C',
            'lavender' => 'E6E6FA',
            'lavenderblush' => 'FFF0F5',
            'lawngreen' => '7CFC00',
            'lemonchiffon' => 'FFFACD',
            'lightblue' => 'ADD8E6',
            'lightcoral' => 'F08080',
            'lightcyan' => 'E0FFFF',
            'lightgoldenrodyellow' => 'FAFAD2',
            'lightgray' => 'D3D3D3',
            'lightgreen' => '90EE90',
            'lightgrey' => 'D3D3D3',
            'lightpink' => 'FFB6C1',
            'lightsalmon' => 'FFA07A',
            'lightseagreen' => '20B2AA',
            'lightskyblue' => '87CEFA',
            'lightslategray' => '778899',
            'lightslategrey' => '778899',
            'lightsteelblue' => 'B0C4DE',
            'lightyellow' => 'FFFFE0',
            'lime' => '00FF00',
            'limegreen' => '32CD32',
            'linen' => 'FAF0E6',
            'magenta' => 'FF00FF',
            'maroon' => '800000',
            'mediumaquamarine' => '66CDAA',
            'mediumblue' => '0000CD',
            'mediumorchid' => 'BA55D3',
            'mediumpurple' => '9370D0',
            'mediumseagreen' => '3CB371',
            'mediumslateblue' => '7B68EE',
            'mediumspringgreen' => '00FA9A',
            'mediumturquoise' => '48D1CC',
            'mediumvioletred' => 'C71585',
            'midnightblue' => '191970',
            'mintcream' => 'F5FFFA',
            'mistyrose' => 'FFE4E1',
            'moccasin' => 'FFE4B5',
            'navajowhite' => 'FFDEAD',
            'navy' => '000080',
            'oldlace' => 'FDF5E6',
            'olive' => '808000',
            'olivedrab' => '6B8E23',
            'orange' => 'FFA500',
            'orangered' => 'FF4500',
            'orchid' => 'DA70D6',
            'palegoldenrod' => 'EEE8AA',
            'palegreen' => '98FB98',
            'paleturquoise' => 'AFEEEE',
            'palevioletred' => 'DB7093',
            'papayawhip' => 'FFEFD5',
            'peachpuff' => 'FFDAB9',
            'peru' => 'CD853F',
            'pink' => 'FFC0CB',
            'plum' => 'DDA0DD',
            'powderblue' => 'B0E0E6',
            'purple' => '800080',
            'red' => 'FF0000',
            'rosybrown' => 'BC8F8F',
            'royalblue' => '4169E1',
            'saddlebrown' => '8B4513',
            'salmon' => 'FA8072',
            'sandybrown' => 'F4A460',
            'seagreen' => '2E8B57',
            'seashell' => 'FFF5EE',
            'sienna' => 'A0522D',
            'silver' => 'C0C0C0',
            'skyblue' => '87CEEB',
            'slateblue' => '6A5ACD',
            'slategray' => '708090',
            'slategrey' => '708090',
            'snow' => 'FFFAFA',
            'springgreen' => '00FF7F',
            'steelblue' => '4682B4',
            'tan' => 'D2B48C',
            'teal' => '008080',
            'thistle' => 'D8BFD8',
            'tomato' => 'FF6347',
            'turquoise' => '40E0D0',
            'violet' => 'EE82EE',
            'wheat' => 'F5DEB3',
            'white' => 'FFFFFF',
            'whitesmoke' => 'F5F5F5',
            'yellow' => 'FFFF00',
            'yellowgreen' => '9ACD32'
        );

        $color_name = strtolower($color_name);
        if (isset($colors[$color_name])) {
            return ( '#' . $colors[$color_name] );
        } else {
            return ( $color_name );
        }
    }

    private function related($masterID) {
        global $woocommerce;
        // Get Related Products from SAME Sub-category
        $related_type = osa_get_option('related_product_by');
        //echo $related_type;
        if ($related_type == 1) {
            $tags_array = array(0);
            $cats_array = array(0);
            // Get tags
            $terms = wp_get_post_terms($masterID, 'product_tag');
            foreach ($terms as $term) {
                $tags_array[] = $term->term_id;
            }
            // Get categories
            $terms = wp_get_post_terms($masterID, 'product_cat');
            foreach ($terms as $key => $term) {
                $check_for_children = get_categories(array(
                    'parent' => $term->term_id,
                    'taxonomy' => 'product_cat'
                        ));
                if (empty($check_for_children)) {
                    $cats_array[] = $term->term_id;
                }
            }
            // Don't bother if none are set
            if (sizeof($cats_array) == 1 && sizeof($tags_array) == 1) {
                return array();
            }
            // Meta query
            $meta_query = array();
            //$meta_query[] = $woocommerce->query->visibility_meta_query();
            $meta_query[] = array(
                'key' => '_stock_status',
                'value' => 'instock',
                'compare' => '='
            );
            $meta_query = array_filter($meta_query);
            // Get the posts
            $args = array(
                'orderby' => 'rand',
                'posts_per_page' => 8,
                'post_type' => array('product'),
                'meta_query' => $meta_query,
                'tax_query' => array(
                    'relation' => 'OR',
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'id',
                        'terms' => $cats_array
                    ),
                    array(
                        'taxonomy' => 'product_tag',
                        'field' => 'id',
                        'terms' => $tags_array
                    )
                )
            );
            $general = $this->service_container->get(General::class);
            $activeVendors = $general->vendor_ids();
            if (strlen($_POST['vendor_town']) > 2) {
                $args['author__in'] = $activeVendors;
            }

            //$args     = apply_filters( 'woocommerce_related_products_args', $args);
        } else {
            $args = apply_filters('woocommerce_related_products_args', array(
                'post_type' => 'product',
                'posts_per_page' => 8,
                /* 'orderby'				 => 'meta_value', */
                'meta_key' => '_stock_status',
                'meta_value' => 'instock',
                'post__not_in' => array($masterID)
                    ));
            $general = $this->service_container->get(General::class);
            $activeVendors = $general->vendor_ids();
            if (strlen($_POST['vendor_town']) > 2) {
                $args['author__in'] = $activeVendors;
            }
        }

        $products = new WP_Query(apply_filters("osa_single_related_query_args", $args, $this->user_id, $masterID));
        $related = array();
        while ($products->have_posts()) : $products->the_post();
            $related_id = get_the_id();
//			$product              = wc_get_product( $related_id );
            $product = wc_get_product($related_id);
            $relatedPost['id'] = intval($related_id);
            $relatedPost['title'] = html_entity_decode(get_the_title($related_id));
            if (has_post_thumbnail($related_id)) {
                $img_id = get_post_thumbnail_id($related_id);
                $src = wp_get_attachment_image_src($img_id, 'medium')[0];
            } else {
                $src = OSA_PLUGIN_URL . "/assets/images/notp.png";
            }
            $relatedPost['image'] = $src;
            $relatedPost['stock_quantity'] = ( $product->get_stock_quantity() ) ? intval($product->get_stock_quantity()) : 0;
            $relatedPost['type'] = ( $product->get_type() == 'grouped' OR $product->get_type() == 'simple_catalogue' ) ? 'simple_catalogue' : $product->get_type();

            $instock = get_post_meta($related_id, '_stock_status', true);
            $relatedPost['in_stock'] = ( $instock == 'instock' ) ? true : false;
            $relatedPost['stock_status'] = $product->get_stock_status();
            $relatedPost['price'] = get_post_meta($related_id, '_price', true);

            if (function_exists("YITH_Role_Based_Type")) {
                $relatedPost['price'] = $this->yith_price_role->get_compute_price_render($product, $this->user_id);
            }

            $relatedPost['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
            $related[] = apply_filters("osa_single_get_related_product_item_info", $relatedPost, $related_id, $this->user_id);

        endwhile;

        return apply_filters('osa_single_related_result',$related,$activeVendors);
    }

    private function getMostSales($cats) {
        $products = array();
        $tax_query['operator'] = 'OR';
        foreach ($cats as $cat) {
            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $cat['parent']
            );
        }
        $args = array(
            'tax_query' => $tax_query,
            'post_type' => 'product',
            'post__not_in' => array($_POST['id']),
            'posts_per_page' => 8,
            'meta_key' => 'total_sales',
            'orderby' => 'meta_value_num',
        );
        $general = $this->service_container->get(General::class);
        $activeVendors = $general->vendor_ids();
        if (strlen($_POST['vendor_town']) > 2) {
            $args['author__in'] = $activeVendors;
        }
        if (osa_get_option('app_disableExist') == 'true') {
            $args['meta_query'] = array(
                array(
                    'key' => '_stock_status',
                    'value' => 'instock',
                )
            );
        }
        $loop = new WP_Query(apply_filters("osa_single_getMostSales_query_args", $args, $this->user_id));
        if ($loop->have_posts()) {

            $product = wc_get_product(get_the_ID());
            while ($loop->have_posts()) : $loop->the_post();
                $product_id = get_the_ID();
                $pro['id'] = get_the_id();
                $ms_product = wc_get_product(get_the_ID());
                $pro['title'] = html_entity_decode(get_the_title());
                if (has_post_thumbnail()) {
                    $img_id = get_post_thumbnail_id(get_the_id());
                    $src = wp_get_attachment_image_src($img_id, 'medium')[0];
                } else {
                    $src = OSA_PLUGIN_URL . "/assets/images/notp.png";
                }
                $pro['image'] = $src;
                $productInfo['stock_quantity'] = ( $product->get_stock_quantity() ) ? intval($product->get_stock_quantity()) : 0;
                $productInfo['in_stock'] = $product->is_in_stock();
                $productInfo['type'] = ( $product->get_type() == 'grouped' OR $product->get_type() == 'simple_catalogue' ) ? 'simple_catalogue' : $product->get_type();

                $pro['price'] = get_post_meta(get_the_id(), '_price', true);

                if (function_exists("YITH_Role_Based_Type")) {
                    $pro['price'] = $this->yith_price_role->get_compute_price_render($ms_product, $this->user_id);
                }

                $pro['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
                $pro['total_sales'] = intval(get_post_meta(get_the_id(), 'total_sales', true));
                $instock = get_post_meta($pro['id'], '_stock_status', true);
                $pro['in_stock'] = ( $instock == 'instock' ) ? true : false;
                $pro['stock_status'] = $product->get_stock_status();
                $products[] = apply_filters("osa_single_get_getMostSale_product_item_info",$pro, $product_id, $this->user_id);
            endwhile;
        }
        wp_reset_postdata();

        return $products;
    }

    private function attributes($masterID) {

        if (function_exists('jcaa_get_product_attributes')) {

            $product_new = wc_get_product($masterID);
            $attributes = $product_new->get_attributes();

            $grouped_attributes = ( jcaa_get_product_attributes($attributes) );
            $unique_id = 'unique_id_';
            $counter = 0;
            if (!empty($grouped_attributes)) {
                foreach ($grouped_attributes as $grouped):
                    if ($grouped['visible'] == false) {
                        continue;
                    }
                    $attrGroup = array();
                    if (!method_exists($grouped, 'has_grouped_terms') || !$grouped->has_grouped_terms()) {
                        $optionsLabel = array();

                        foreach ($grouped['options'] as $item) {
                            if ($grouped['id'] == 0):
                                $optionsLabel[] = $item;
                            else:
                                $term = get_term($item);
                                $optionsLabel[] = $term->name;
                            endif;
                        }
                        $attrGroup[] = array(
                            'name' => wc_attribute_label($grouped['name']),
                            'options' => implode(' , ', $optionsLabel)
                        );
                        $groupd['subAttributes'] = $attrGroup;
                        $groupd['title'] = '';
                        $groupd['id'] = $unique_id . $counter ++;
                        $tmps[] = $groupd;
                        continue;
                    }
                    $alt = 1;
                    //var_dump($attrGroup);
                    $groupd['title'] = wc_attribute_label($grouped['name']);
                    $groupd['id'] = $unique_id . $counter ++;
                    $getGrouped = $grouped->get_grouped_terms();
                    //var_dump($grouped);
                    //echo $groupd['title'];
                    foreach ($getGrouped as $attribute):
                        $values = array();

                        if ($attribute->is_taxonomy()) {

                            $attribute_taxonomy = $attribute->get_taxonomy_object();
                            $attribute_values = wc_get_product_terms($masterID, $attribute->get_name(), array('fields' => 'all'));

                            foreach ($attribute_values as $attribute_value) {
                                $value_name = esc_html($attribute_value->name);

                                if ($attribute_taxonomy->attribute_public) {
                                    $values[] = $value_name;
                                } else {
                                    $values[] = $value_name;
                                }
                            }
                        } else {

                            $values = $attribute->get_options();

                            foreach ($values as &$value) {
                                $value = make_clickable(esc_html($value));
                            }
                        }
                        $attrGroup[] = array(
                            'name' => wc_attribute_label($attribute['name']),
                            'options' => wp_strip_all_tags(html_entity_decode(implode(', ', $values)))
                        );

                    endforeach;

                    $groupd['subAttributes'] = $attrGroup;
                    $tmps[] = $groupd;
                endforeach;
            }

            if (!empty($tmps) AND is_array($tmps)) {
                return $tmps;
            }
        } else {
            $attrGroup = array();
            $product = wc_get_product($masterID);
            $attributes = $product->get_attributes();
            foreach ($attributes as $attribute) {
                if(!$attribute->get_visible()){
                    continue;
                }
                $optionsLabel = array();
                $options = $attribute->get_options();
                foreach ($options as $item) {
                    if ($attribute->is_taxonomy()) {
                        $term = get_term($item);
                        $option_value = $term->name;
                    } else {
                        $option_value = $item;
                    }
                    $optionsLabel[] = $option_value;
                }
                $attrGroup[] = array(
                    'name' => wc_attribute_label($attribute['name']),
                    'options' => html_entity_decode(implode(' , ', $optionsLabel))
                );
            }
        }


        return array(array('title' => __('Details', 'onlinerShopApp'), 'subAttributes' => $attrGroup));
        //$final['attributes'][0] = $originalData['attributes'];
    }

    private function getParent($cat) {
        $ancestors = get_ancestors($cat['id'], 'product_cat');
        $cat['parent'] = ( $ancestors[count($ancestors) - 1] ) ? $ancestors[count($ancestors) - 1] : $cat['id'];
        unset($cat['slug']);

        return $cat;
    }

    private function get_short_description($product){
        if(is_numeric($product)){
            $product = wc_get_product($product);
        }
        $product_id = $product->get_id();
        $custom_content = (!empty($product->get_short_description()))? $product->get_short_description() : get_the_content($product_id);
        $custom_content = strip_tags($custom_content);
        $short_description_char_count = apply_filters( 'woap_single_short_description_char_count', 150 );
        $custom_content = mb_substr($custom_content , 0 , $short_description_char_count);
        if(mb_strlen($custom_content) > $short_description_char_count){
            $custom_content = mb_substr($custom_content , 0 , mb_strrpos($custom_content, " "));
        }
	return $custom_content;
    }
    
    private function stringDecode($string) {
        $string['option'] = urlDecode($string['option']);
        $string['pa_text'] = urlDecode($string['pa_text']);
        $string['pa_colors'] = urlDecode($string['pa_colors']);

        return $string;
    }

    public function report_product() {
        $product_id = $_POST['product_id'];
        $user_token = $_POST['userToken'];
        $report_content = $_POST['report_content'];

        $product_object = wc_get_product($product_id);
        $user = $this->service_container->get("OSA_user");
        $user_id = $user->get_userID_byToken($user_token);
        $user_object = get_user_by('ID', $user_id);

        $user_login = $user_object->user_login;
        $user_email = $user_object->user_email;

        $product_title = $product_object->get_title();
        $admin_email = osa_get_option('admin_email');

        ob_start();
        ?>
        <div class="report-mail-body">
            <h3>گزارش محتوای نامناسب توسط کاربران</h3>
            <h4>متن گزارش : </h4>
            <p><?php echo sanitize_textarea_field($report_content); ?></p>
            <h4>اطلاعات کاربر گزارش دهنده : </h4>
            <table class="table-mail-report" style="border-collapse: collapse; text-align: right;direction: rtl;">
                <tr>
                    <td style="border: 1px solid black;padding: 6px 10px;">ایمیل کاربر</td>
                    <td style="border: 1px solid black;padding: 6px 10px;"><?php echo $user_email; ?></td>
                </tr>
                <tr>
                    <td style="border: 1px solid black;padding: 6px 10px;">نام کاربری</td>
                    <td style="border: 1px solid black;padding: 6px 10px;"><?php echo $user_login; ?></td>
                </tr>
                <tr>
                    <td style="border: 1px solid black;padding: 6px 10px;">نام محصول گزارش شده</td>
                    <td style="border: 1px solid black;padding: 6px 10px;"><?php echo $product_title; ?></td>
                </tr>
                <tr>
                    <td style="border: 1px solid black;padding: 6px 10px;">شناسه محصول</td>
                    <td style="border: 1px solid black;padding: 6px 10px;"><?php echo $product_id; ?></td>
                </tr>
            </table>
        </div>
        <?php
        $mail_body = ob_get_clean();

        $to = $admin_email;
        $subject = 'گزارش محتوای نامناسب توسط کاربران';
        $body = $mail_body;
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $mail_result = wp_mail($to, $subject, $body, $headers);
        $result = array(
            "status" => $mail_result,
        );
        wp_send_json($result);
    }

}
