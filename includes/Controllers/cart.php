<?php

namespace STORINA\Controllers;

use \STORINA\Controllers\WC_Checkout_Editor;
use \STORINA\Libraries\JDate;
use \STORINA\Controllers\User;
use \STORINA\Controllers\General;
use \STORINA\Controllers\Yith_Role_Based_Price;
use \STORINA\Controllers\Terawallet;

defined('ABSPATH') || exit;

class Cart {

    public $yith_price_role = false;
    public $user_id = false;
    public $wc_checkout_fields_editor = false;
    public $service_container;

    public function __construct($service_container) {
        $this->service_container = $service_container;
        if (!empty(sanitize_text_field(@$_POST['userToken']))) {
            $userToken = sanitize_text_field($_POST['userToken']);
            $user_action = $this->service_container->get(User::class);
            $this->user_id = $user_action->get_userID_byToken($userToken);
        }
        $this->check_plugin_complate();
    }

    public function check_plugin_complate() {
        $this->yith_price_role = $this->service_container->get(Yith_Role_Based_Price::class);

        //Checkout Field Editor for WooCommerce BY ThemeHiGH
        if (class_exists("WC_Checkout_Field_Editor")) {
            $this->wc_checkout_fields_editor = $this->service_container->get(WC_Checkout_Editor::class);
            $this->wc_checkout_fields_editor->set_slug("old");
        } elseif (class_exists("THWCFD_Utils")) {
            $this->wc_checkout_fields_editor = $this->service_container->get(WC_Checkout_Editor::class);
            $this->wc_checkout_fields_editor->set_slug("new");
        }
    }

    public function retrive_cart() {
        $userToken = ( !empty(sanitize_text_field($_POST['userToken'])) ) ? sanitize_text_field($_POST['userToken']) : "";
        $user_action = $this->service_container->get(User::class);
        $user_id = $user_action->get_userID_byToken($userToken);
        $basket = $this->get_cart(true);
        $result = array(
            'status' => true,
            'data' => $basket
        );

        return ( $result );
    }

    public function get_cart($shipPriceHandle = false) {
        remove_filter( 'woocommerce_cart_shipping_packages', 'dokan_custom_split_shipping_packages' );
        wc_empty_cart();
        WC()->session->destroy_session();
        global $woocommerce;
        do_action('woap_prepare_woocommerce_add_to_cart',$woocommerce);
        $general = $this->service_container->get(General::class);
        $cart_array = $general->pre_get_items();
        $cart = $cart_array['cart_new'];
        $prods = $basket = array();
        $discount_total = 0;
        $total = 0;
        $subtotal = 0;
        $price = array();
        if (!empty($cart)) {
            foreach ($cart as $item => $values) {
                $pID = intval($values['product_id']);
                $pro = array();
                $product = wc_get_product($pID);
                if(false == $product instanceof WC_Product){
                    continue;
                }
                $pro['id'] = $pID;
                $pro['name'] = html_entity_decode(get_the_title($pID));
                $vendor = array();
                if (function_exists('dokan_get_store_info')) {
                    $author_id = get_post_field('post_author', $pID);
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

                    /// termOfService Vendor ...
                    $termOfService = get_user_meta($author_id, 'dokan_profile_settings', true);

                    $vendor = array(
                        'vendor_id' => intval($author_id),
                        'store_name' => $store_settings['store_name'],
                        'phone' => $store_settings['phone'],
                        'address' => $store_settings['address'],
                        'email' => ( $email ) ? $email : 'default@email.com',
                        'banner' => ( $banner ) ? $banner : '',
                        'gravatar' => ( $gravatar ) ? $gravatar : '',
                        /// add vendor term Of service
                        'termOfService' => ( $termOfService['store_tnc'] ) ? $termOfService['store_tnc'] : '',
                    );
                }
                $pro['vendor'] = $vendor;
                $pro['en_name'] = ( get_post_meta($pID, '_subtitle', true) ) ? get_post_meta($pID, '_subtitle', true) : get_post_meta($pID, '_ENtitle', true);
                $pro['type'] = ( $values['variation_id'] > 0 ) ? 'variable' : 'simple';
                if (( $values['variation_id'] > 0)) {
                    $createdVariation = $this->make_variations($values['variation_id']);
                    $pro['variation_id'] = intval($values['variation_id']);
                    $pro['variation'] = $createdVariation;
                    $pro['variation_arr'] = json_decode(stripslashes($values['variation_arr']), true);
                    //var_dump($_product->get_variation_attributes());
                }

                $pro['quantity'] = ( $values['quantity'] );
                $pro['downloadable'] = $product->is_downloadable();
                $sold_ind = get_post_meta($pID, '_sold_individually', true);
                if ($sold_ind == 'yes') {
                    $pro['stock_quantity'] = 1;
                } else {
                    $pro['stock_quantity'] = intval(@$values['stock_quantity']);
                }

                //var_dump(get_post_meta($pID));
                $pro['qty'] = $general->Advanced_Qty($pID);
                if ($pro['type'] == 'variable') {
                    $stock_quantity = intval(get_post_meta($pro['variation_id'], '_stock', true));
                } else {
                    $stock_quantity = intval(get_post_meta($pID, '_stock', true));
                }

                $stock_quantity = ( $stock_quantity ) ? $stock_quantity : 0;
                if (get_post_meta($pID, '_manage_stock', true) AND get_post_meta($pID, '_stock_status', true) == 'outofstock') {
                    if (get_post_meta($pID, '_stock', true) > 0) {
                        $pro['in_stock'] = true;
                        $pro['stock_quantity'] = $stock_quantity;
                    } else {
                        $pro['in_stock'] = false;
                        $pro['stock_quantity'] = $stock_quantity;
                    }
                } else {
                    //echo get_post_meta($pID,'_stock_status',true);
                    if (get_post_meta($pID, '_stock_status', true) == 'instock') {
                        $pro['in_stock'] = true;
                        $pro['stock_quantity'] = $stock_quantity;
                    } else {
                        $pro['in_stock'] = false;
                        $pro['stock_quantity'] = $stock_quantity;
                    }
                }
                $pro['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
                if (has_post_thumbnail($pID)) {
                    $img_id = get_post_thumbnail_id($pID);
                    $thumb = wp_get_attachment_image_src($img_id, 'medium')[0];
                } else {
                    $thumb = $img = STORINA_PLUGIN_URL . "/assets/images/notp.png";
                }
                $pro['thumbnail'] = $thumb;


                if ($values['variation_id'] > 0) {
                    $productVariations = wc_get_product($values['variation_id']);
                    if ($productVariations->is_on_sale()) {
                        if (function_exists("YITH_Role_Based_Type")) {
                            $pro['regular_price'] = $this->yith_price_role->get_compute_price_render($productVariations, $this->user_id);
                            $pro['sale_price'] = "";
                            $pro['discount'] = "";
                            $discount_total += "";
                            $total += $pro['regular_price'] * $values['quantity'];
                            $subtotal += $pro['regular_price'] * $values['quantity'];
                        }
                        if (true == storina_wbd_activation()) {
                            $owbd_price = storina_wbd_get_product_price($productVariations, $values['quantity']);
                            $pro['product_formule'] = storina_wbd_calculate_formule($productVariations);
                            $pro['regular_price'] = $owbd_price['price'];
                            $pro['sale_price'] = "";
                            $pro['owbd_discount'] = (string) $owbd_price['discount'];
                            $pro['owbd_row'] = (string) $owbd_price['row'];
                            $pro['owbd_regular_price'] = (string) $product->get_regular_price();
                            $pro['discount'] = $owbd_price['discount'];
                            $discount_total += "";
                            $total += $pro['regular_price'] * $values['quantity'];
                            $subtotal += $pro['regular_price'] * $values['quantity'];
                        } else {
                            $pro['regular_price'] = $productVariations->get_regular_price();
                            $pro['sale_price'] = $productVariations->get_sale_price();
                            $pro['discount'] = ( $pro['regular_price'] - $pro['sale_price'] );
                            $discount_total += $pro['discount'] * $values['quantity'];
                            $total += $pro['sale_price'] * $values['quantity'];
                            $subtotal += $pro['regular_price'] * $values['quantity'];
                        }
                    } else {
                        $pro['regular_price'] = ( $productVariations->get_regular_price() > 0 ) ? $productVariations->get_regular_price() : get_post_meta($pID, '_price', true);
                        $pro['sale_price'] = $productVariations->get_sale_price();
                        if (function_exists("YITH_Role_Based_Type")) {
                            $pro['regular_price'] = $this->yith_price_role->get_compute_price_render($productVariations, $this->user_id);
                            $pro['sale_price'] = "";
                        }
                        if (true == storina_wbd_activation()) {
                            $owbd_price = storina_wbd_get_product_price($productVariations, $values['quantity']);
                            $pro['product_formule'] = storina_wbd_calculate_formule($productVariations);
                            $pro['regular_price'] = $owbd_price['price'];
                            $pro['sale_price'] = "";
                            $pro['owbd_discount'] = (string) $owbd_price['discount'];
                            $pro['owbd_row'] = (string) $owbd_price['row'];
                            $pro['owbd_regular_price'] = (string) $product->get_regular_price();
                            $pro['owbd_total'] = (string) ($product->get_regular_price() * $values['quantity']);
                        }
                        $total += $pro['regular_price'] * $values['quantity'];
                        $subtotal += $pro['regular_price'] * $values['quantity'];
                    }
                } else {
                    if ($product->is_on_sale()) {
                        if (function_exists("YITH_Role_Based_Type")) {
                            $pro['regular_price'] = $this->yith_price_role->get_compute_price_render($product, $this->user_id);
                            $pro['sale_price'] = "";
                            $discount_total += "";
                            $total += $pro['regular_price'] * $values['quantity'];
                            $subtotal += $pro['regular_price'] * $values['quantity'];
                        }
                        if (true == storina_wbd_activation()) {

                            $owbd_price = storina_wbd_get_product_price($product, $values['quantity']);
                            $pro['product_formule'] = storina_wbd_calculate_formule($product);
                            $pro['regular_price'] = $owbd_price['price'];
                            $pro['sale_price'] = "";
                            $pro['owbd_discount'] = (string) $owbd_price['discount'];
                            $pro['owbd_row'] = (string) $owbd_price['row'];
                            $pro['owbd_regular_price'] = (string) $product->get_regular_price();
                            $pro['owbd_total'] = (string) ($product->get_regular_price() * $values['quantity']);
                            $pro['discount'] = $owbd_price['discount'];
                            $discount_total += "";
                            $total += $pro['regular_price'] * $values['quantity'];
                            $subtotal += $pro['regular_price'] * $values['quantity'];
                        } else {
                            $pro['regular_price'] = $product->get_regular_price();
                            $pro['sale_price'] = $product->get_sale_price();
                            $pro['discount'] = ( $pro['regular_price'] - $pro['sale_price'] );
                            $discount_total += $pro['discount'] * $values['quantity'];
                            $total += $pro['sale_price'] * $values['quantity'];
                            $subtotal += $pro['regular_price'] * $values['quantity'];
                        }
                    } else {
                        $pro['regular_price'] = $product->get_regular_price();
                        $pro['sale_price'] = "";
                        if (function_exists("YITH_Role_Based_Type")) {
                            $pro['regular_price'] = $this->yith_price_role->get_compute_price_render($product, $this->user_id);
                            $pro['sale_price'] = "";
                        }
                        if (true == storina_wbd_activation()) {
                            $owbd_price = storina_wbd_get_product_price($product, $values['quantity']);
                            $pro['product_formule'] = storina_wbd_calculate_formule($product);
                            $pro['regular_price'] = $owbd_price['price'];
                            $pro['sale_price'] = "";
                            $pro['owbd_discount'] = (string) $owbd_price['discount'];
                            $pro['owbd_row'] = (string) $owbd_price['row'];
                            $pro['owbd_regular_price'] = (string) $product->get_regular_price();
                            $pro['owbd_total'] = (string) ($product->get_regular_price() * $values['quantity']);
                        }
                        $total += $pro['regular_price'] * $values['quantity'];
                        $subtotal += $pro['regular_price'] * $values['quantity'];
                    }
                }
                $prods[] = apply_filters("osa_retrive_cart_get_cart_item", $pro, $pID);
                $woocommerce->cart->add_to_cart($pID, $values['quantity'], $values['variation_id'], $values['variation']);
            }
        }
        global $wpdb, $googleID, $sessionRecord;
        $couponDiscount = 0;
        $table = $wpdb->prefix . 'OSA_cart';
        $sessionRecord = $wpdb->get_row("SELECT * FROM $table WHERE googleID = '$googleID'");
        if (@$sessionRecord->couponCode) {
            $woocommerce->cart->add_discount($sessionRecord->couponCode);
            $woocommerce->session->set('couponCode', $sessionRecord->couponCode);
            if ($woocommerce->cart->has_discount($sessionRecord->couponCode)) {
                $couponDiscount = $woocommerce->cart->get_coupon_discount_amount(strtolower($sessionRecord->couponCode));
            }
        }


        $storina_get_option = storina_get_option('woocommerce_ship_to_countries');
        if ($storina_get_option != 'disabled') {
            $addressType = @$sessionRecord->addressType;
            $user_action = $this->service_container->get(User::class);
            $userToken = @sanitize_text_field($_POST['userToken']);
            $user_id = $user_action->get_userID_byToken($userToken);
            $address = get_user_meta($user_id, $addressType . '_address_1', true);
            $city = get_user_meta($user_id, $addressType . '_city', true);
            $postCode = get_user_meta($user_id, $addressType . '_postcode', true);
            $state = get_user_meta($user_id, $addressType . '_state', true);
            $mobile = get_user_meta($user_id, $addressType . '_mobile', true);
            $phone = get_user_meta($user_id, $addressType . '_phone', true);
            $first_name = get_user_meta($user_id, $addressType . '_first_name', true);
            $last_name = get_user_meta($user_id, $addressType . '_last_name', true);
            $data = apply_filters('woap_shipping_package_data',array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $phone,
                'mobile' => $mobile,
                'shipping_country' => 'IR',
                'shipping_state' => $state,
                'shipping_postcode' => $postCode,
                'shipping_city' => $city,
                'shipping_address_1' => $address,
                'shipping_address_2' => '',
            ),$user_id);
            wc_empty_cart();
            $cart = $general->get_items();
            if (!empty($cart)) {
                foreach ($cart as $item => $values) {
                    WC()->cart->add_to_cart($values['product_id'], $values['quantity'], $values['variation_id'], $values['variation']);
                }
            }

            WC()->customer->set_props($data);
            WC()->session->set('chosenAddress', $data);
            WC()->customer->set_id($user_id);
            WC()->customer->set_billing_address_to_base();
            //WC()->customer->set_shipping_address_to_base( );     // این خط اگه اجرا بشه بقیه روش های ارسال رو هم نشون میده
            WC()->customer->set_calculated_shipping(true);
            WC()->customer->save();
            wp_set_current_user($user_id);
            WC()->cart->set_total($cart['total']['total']);
            WC()->cart->set_subtotal($cart['total']['subtotal']);

            $packages = WC()->cart->get_shipping_packages();
            $shipping_methods = WC()->shipping->get_shipping_methods($packages);
            foreach($shipping_methods as $shipping_method){
                foreach($packages as $package){
                    $shipping_method->calculate_shipping($package);
                }
            }
            $shipping_methods = WC()->shipping->calculate_shipping($packages);
            
            unset($shipping_methods[0]['contents']);
            unset($shipping_methods[0]['contents_cost']);
            unset($shipping_methods[0]['applied_coupons']);
            unset($shipping_methods[0]['cart_subtotal']);
            //$shipping_methods = WC()->cart->get_cart();
            //$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
        } else {
            $shipping_methods = array();
        }


        $methods = array();
        foreach ($shipping_methods[0]['rates'] as $rate) {
            $tmp = $this->accessProtected($rate, 'data');
            unset($tmp['taxes']);
            unset($tmp['instance_id']);
            //$tmp['method_id'] = $tmp['id'];
            $methods[$tmp['id']] = $tmp;
        }
        //var_dump(WC()->cart->get_cart());
        //var_dump(WC()->cart);
        $basket['items'] = $prods;
        //var_dump($woocommerce->cart->discount_total);

        $shipping_cost = $methods[@$sessionRecord->shipping_method_id]['cost'] ?? 0;
        $shippingTotal = apply_filters('woap_get_cart_shipping_total_cost',$shipping_cost,$methods,$sessionRecord,$user_id);
        $basket['total'] = array(
            'subtotal' => ($subtotal),
            'discount_total' => ($discount_total) + ($couponDiscount),
            'total_tax' => ($woocommerce->cart->total_tax),
            'shipping_total' => ($shippingTotal),
            'total' => ($total - $couponDiscount + $shippingTotal),
        );
        $price_num_decimals = storina_get_option('woocommerce_price_num_decimals');
        $price_num_type = (intval($price_num_decimals) > 0)? 'float' : 'integer';
        foreach($basket['total'] as $key => &$value){
            $value = ('integer' == $price_num_type)? intval($value) : number_format($value , $price_num_decimals);
        }
        if (storina_wbd_activation()) {
            $basket['total']['total'] = intval($subtotal - $couponDiscount + $shippingTotal);
        }
        $basket['notice'] = (empty($cart_array['cart_diff'])) ? 0 : 1;

        return $basket;
    }

    public function make_variations($item_id) {
        $general = $this->service_container->get(General::class);
        $_product = new WC_Product_Variation($item_id);
        $variation = $_product->get_variation_attributes();
        if (is_a($variation, 'WC_Product_Variation')) {
            $variation_attributes = $variation->get_attributes();
            $product = $variation;
        } else {
            $variation_attributes = $variation;
            $product = false;
        }
        $variation_list = array();
        foreach ($variation_attributes as $name => $slug) {
            if (!$slug) {
                continue;
            }
            $tax = str_replace('attribute_', '', urldecode($name));
            // If this is a term slug, get the term's nice name
            if (taxonomy_exists($tax)) {

                $term = get_term_by('slug', $slug, $tax);
                $name = wc_attribute_label($tax, $product);
                if (!is_wp_error($term) && !empty($term->name)) {
                    $term_id = $term->term_id;
                    $attrID = wc_attribute_taxonomy_id_by_name($tax);
                    $general = $this->service_container->get(General::class);
                    $jcaa_attribute_type = $general->get_attr_setting($attrID, 'jcaa_attribute_type');
                    //var_dump(wc_get_attribute_id());
                    //$jcaa_attribute_label = $this->get_attr_setting($attrID, 'jcaa_attribute_label');
                    if ($jcaa_attribute_type == 'color') {
                        $value = get_term_meta($term_id, '_jcaa_product_attr_color', true);
                        if (!$value) {
                            $value = get_term_meta($term->term_id, 'pa_color_swatches_id_color', true);
                        }
                        $value = $this->color_name_to_hex($value);
                    } elseif ($jcaa_attribute_type == 'image') {
                        $value = wp_get_attachment_thumb_url(get_term_meta($term_id, '_jcaa_product_attr_thumbnail_id', true));
                        if (!$value) {
                            $value = wp_get_attachment_thumb_url(get_term_meta($term->term_id, 'pa_brand_swatches_id_photo', true));
                        }
                    } else {
                        $jcaa_attribute_type = 'text';
                        $value = $term->name;
                    }
                }
            }
            $attr = array(
                'id' => intval($attrID),
                'term_id' => intval($term_id),
                'tax' => str_replace('pa_', '', $term->taxonomy),
                'type' => $jcaa_attribute_type,
                'name' => $name,
                'option' => $term->name,
                'value' => $value,
            );
            $variation_list[] = $attr;
        }
        $cart = $general->get_items();
        $meta_variation_tmp = ( @$cart[$_product->get_parent_id() . @$_product->get_id()]['meta_variation'] );
        $meta_variations = json_decode(stripcslashes($meta_variation_tmp), true);
        if (!empty($meta_variations)) {
            foreach ($meta_variations as $meta_id => $term_id) {

                $meta = $this->get_woo_attribute_by('id', $meta_id);
                $term = get_term_by('id', $term_id, 'pa_' . $meta->attribute_name);

                $jcaa_attribute_type = $general->get_attr_setting($meta_id, 'jcaa_attribute_type');
                if ($jcaa_attribute_type == 'color') {
                    $value = get_term_meta($term_id, '_jcaa_product_attr_color', true);
                    if (!$value) {
                        $value = get_term_meta($term->term_id, 'pa_color_swatches_id_color', true);
                    }
                    $value = $this->color_name_to_hex($value);
                } elseif ($jcaa_attribute_type == 'image') {
                    $value = wp_get_attachment_thumb_url(get_term_meta($term_id, '_jcaa_product_attr_thumbnail_id', true));
                    if (!$value) {
                        $value = wp_get_attachment_thumb_url(get_term_meta($term->term_id, 'pa_brand_swatches_id_photo', true));
                    }
                } else {
                    $jcaa_attribute_type = 'text';
                    $value = $term->name;
                }
                $variation_list[] = array(
                    'id' => $meta_id,
                    'term_id' => $term_id,
                    'tax' => $meta->attribute_name,
                    'type' => $jcaa_attribute_type,
                    'name' => $meta->attribute_label,
                    'option' => $term->name,
                    'value' => $value,
                );
            }
        }

        return $variation_list;
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
        if (!empty($colors[$color_name])) {
            return ( '#' . $colors[$color_name] );
        } else {
            return ( $color_name );
        }
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

    private function accessProtected($obj, $prop) {
        $reflection = new ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }

    public function add_to_cart() {
        global $googleID;
        global $woocommerce;
        do_action('woap_prepare_woocommerce_add_to_cart',$woocommerce);
        $validation = [
            'status' => true,
            'messages' => []
        ];
        $product_id = ( !empty(sanitize_text_field($_POST['product_id'])) ) ? sanitize_text_field($_POST['product_id']) : null;
        if(empty($product_id)){
            return;
        }
        $quantity = ( !empty(sanitize_text_field($_POST['quantity'])) && ("yes" !=  get_post_meta($product_id, "_sold_individually", true))) ? sanitize_text_field($_POST['quantity']) : 1;

        $variation_id = ( !empty(sanitize_text_field($_POST['variation_id'])) ) ? sanitize_text_field($_POST['variation_id']) : null;
        $meta_variations_tmp = ( !empty(sanitize_text_field($_POST['meta_variation'])) ) ? sanitize_text_field($_POST['meta_variation']) : null;


        $variation = array();
        if ($variation_id > 0) {
            $productVariations = new WC_Product_variation($variation_id);

            $manage_stock = get_post_meta($variation_id, '_manage_stock', true);
            $stock_status = get_post_meta($variation_id, '_stock_status', true);
            if ($stock_status == 'instock') {
                if ($manage_stock == 'no') {
                    $max_quantity = 99999999999;
                } else {
                    //var_dump($productVariations);
                    $max_quantity = $productVariations->get_stock_quantity();
                }
            } else {
                $max_quantity = 0;
            }


            //$variations = wc_get_product($variation_id);
            //$variation = $variations->get_variation_attributes();
            $createdVariation = $this->make_variations($variation_id);
            foreach ($createdVariation as $item) {
                $variation[$item['name']] = $item['option'];
            }
            $variation_arr = (!empty(sanitize_text_field($_POST['variation']))) ? sanitize_text_field($_POST['variation']) : 0;
        } else {
            $manage_stock = get_post_meta($product_id, '_manage_stock', true);
            $stock = get_post_meta($product_id, '_stock', true);
            $stock_status = get_post_meta($product_id, '_stock_status', true);
            if ($stock_status == 'instock') {
                if ($manage_stock == 'no') {
                    $max_quantity = 99999999999;
                } else {
                    $max_quantity = $stock;
                }
            } else {
                $max_quantity = 0;
            }
        }
        $item = array(
            'product_id' => $product_id,
            'quantity' => $quantity,
            'variation_id' => $variation_id,
            'variation' => $variation,
            'variation_arr' => $variation_arr,
            'meta_variation' => $meta_variations_tmp
        );


        global $wpdb, $sessionRecord;
        $general = $this->service_container->get(General::class);
        $table = $wpdb->prefix . 'OSA_cart';
        $cart = $general->get_items();
        $validation = apply_filters('woap_add_to_cart_validation',$validation,$item,$cart);
        if(!$validation['status']){
            wp_send_json($validation);
            return [
                'status' => false,
                'messages' => $validation['messages']
            ];
        }
        if (empty($sessionRecord)) {
            $cart[$product_id . $variation_id] = $item;

            $wpdb->insert(
                    $table,
                    array(
                        'userID' => '',
                        'googleID' => $googleID,
                        'cart' => json_encode($cart)
                    ),
                    array(
                        '%d',
                        '%s',
                        '%s'
                    )
            );
        } else {
            // اگر در داخل سبد محصول وجود داشت/
            if (empty($cart)) {
                $cart[$product_id . $variation_id] = $item;
            } else {

                foreach ($cart as $index => $cartItem) {

                    if ($cartItem['product_id'] . $cartItem['variation_id'] == $product_id . $variation_id) {
                        $find = true;
                        $product_index = $index;
                    }
                }
                if ($find) {
                    if ($cart[$index]['quantity'] + $item['quantity'] > $max_quantity) {
                        // تعداد درخواستی از موجودی انبار بیشتر است.
                        $text = "تعداد درخواستی شما از موجودی انبار ($max_quantity) بیشتر است.";

                        $result = array(
                            'status' => false,
                            'messages' => array(
                                'message' => $text,
                            )
                        );

                        return ( $result );
                    } elseif("yes" == get_post_meta($product_id, "_sold_individually", true)){
                        return array(
                            "status" => false,
                            "error" => array(
                                "message" => esc_html__("Sold individually","storina-application")
                            )
                        );
                    } else {
                        $cart[$product_index]['quantity'] += $item['quantity'];
                    }
                } else {
                    $cart[$product_id . $variation_id] = $item;
                }
            }


            $this->update_items($cart);
        }

        $basket = $this->get_cart(true);

        if (!empty($basket)) {
            $result = array(
                'status' => true,
                'data' => $basket
            );
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => 'Error'
                )
            );
        }

        return ( $result );
    }

    private function update_items($cart) {
        global $wpdb, $googleID;
        $table = $wpdb->prefix . 'OSA_cart';
        $wpdb->update(
                $table,
                array(
                    'cart' => json_encode($cart)
                ),
                array('googleID' => $googleID),
                array(
                    '%s'
                ),
                array('%s')
        );

        return $cart;
    }

    public function removeFromCart() {
        $id = sanitize_text_field($_POST['id']);
        $general = $this->service_container->get(General::class);
        $cart = $general->get_items();

        foreach ($cart as $index => $cart_item) {
            if(!isset($cart[$index])){
                continue;
            }
            if ($id == $cart_item['product_id'] OR $id == $cart_item['variation_id']) {
                unset($cart[$index]);
                $this->update_items($cart);
            }
        }

        $basket = $this->get_cart();
        $result = array(
            'status' => true,
            'data' => $basket
        );

        return ( $result );
    }

    public function orderHistory() {

        $userToken = sanitize_text_field($_POST['userToken']);
        $user_action = $this->service_container->get(User::class);
        $user_id = $user_action->get_userID_byToken($userToken);
        if ($user_id) {
            $AllOrders = array();
            $args = array(
                'limit' => - 1,
                'customer_id' => $user_id,
            );
            $orders = wc_get_orders($args);
            //$orders = $client->customers->get_orders( $user_id )->orders;
//var_dump($orders);
            foreach ($orders as $order) {
                $order_data = $order->get_data(); // The Order data
                $masterOrder['order_id'] = intval($order_data['id']);
                $masterOrder['order_number'] = 'OKC-' . $order_data['id'];
                //$date_created                = $order_data['date_created']->date( 'Y-m-d H:i' );
                $timestamp = $order_data['date_created']->getTimestamp();
                $orderDate = (is_rtl())? JDate::jdate('h:i j/m/y', $timestamp) : date('j/m/y h:i', $timestamp);
                $masterOrder['created_at'] = $orderDate;
                $files = $this->exportOrderFiles($masterOrder['order_id']);
                $products = array();
                $total_line_items_quantity = 0;
                foreach ($order->get_items() as $item_key => $line_item) {
                    $tmpPro = array();
                    $product = $line_item->get_product();
                    if(false == $product instanceof WC_Product){
                        continue;
                    }
                    $tmpPro['product_id'] = $line_item->get_product_id();
                    $tmpPro['regular_price'] = $product->get_regular_price();
                    $tmpPro['sale_price'] = $product->get_sale_price();
                    $tmpPro['title'] = $line_item->get_name();
                    $tmpPro['quantity'] = $line_item->get_quantity();
                    $tmpPro['subtotal'] = $line_item->get_subtotal();
                    $tmpPro['total'] = $line_item->get_total();
                    $tmpPro['product_type'] = ("variation" == $product->get_type())? "variable" : $product->get_type();
                    if("variation" == $product->get_type()){
                        $tmpPro['attributes'] = $this->make_variations($line_item->get_variation_id());
                        $tmpPro['variation_id'] = $line_item->get_variation_id();
                    }
                    $vendor = array();
                    if (function_exists('dokan_get_store_info')) {
                        $author_id = get_post_field('post_author', $line_item->get_product_id());
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
                    $tmpPro['vendor'] = $vendor;
                    $total_line_items_quantity += $tmpPro['quantity'];
                    if (has_post_thumbnail($tmpPro['product_id'])) {
                        $img_id = get_post_thumbnail_id($tmpPro['product_id']);
                        $thumb = wp_get_attachment_image_src($img_id, 'medium')[0];
                    } else {
                        $thumb = $img = STORINA_PLUGIN_URL . "/assets/images/notp.png";
                    }
                    $tmpPro['image'] = $thumb;
                    $tmpPro['files'] = ( $files[$tmpPro['product_id']] ) ? $files[$tmpPro['product_id']] : array();

                    $products[] = $tmpPro;
                }
                $masterOrder['line_items'] = $products;
                $masterOrder['status'] = $order_data['status'];
                $masterOrder['total'] = intval($order_data['total']);
                $masterOrder['subtotal'] = intval($order_data['total'] - $order_data['shipping_total']);
                $masterOrder['total_line_items_quantity'] = ( $total_line_items_quantity );
                $masterOrder['total_tax'] = intval($order_data['total_tax']);
                $masterOrder['total_shipping'] = intval($order->total_shipping);
                $masterOrder['total_discount'] = intval($order_data['discount_total']);
                $masterOrder['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
                foreach ($order->get_items('shipping') as $item_id => $shipping_item_obj) {
                    // Get the data in an unprotected array
                    $shipping_item_data = $shipping_item_obj->get_data();
//                    $shipping_data_id           = $shipping_item_data['id'];
//                    $shipping_data_order_id     = $shipping_item_data['order_id'];
                    $shipping_data_name = $shipping_item_data['name'];
//                    $shipping_data_method_title = $shipping_item_data['method_title'];
//                    $shipping_data_method_id    = $shipping_item_data['method_id'];
//                    $shipping_data_total        = $shipping_item_data['total'];
//                    $shipping_data_total_tax    = $shipping_item_data['total_tax'];
//                    $shipping_data_taxes        = $shipping_item_data['taxes'];
                }
                $masterOrder['shipping_methods'] = $shipping_data_name;
                $masterOrder['payment_details'] = array(
                    'method_id' => $order_data['payment_method'],
                    'method_title' => $order_data['payment_method_title'],
                    'paid' => ( $order_data['date_paid'] ) ? true : false
                );
                $masterOrder['redirect'] = get_post_meta($order->order_number, 'pay_link', true);
                $AllOrders[] = $masterOrder;
            }
            $result = array(
                'status' => true,
                'data' => $AllOrders
            );
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => esc_html__('Token is invalid', 'storina-application')
                )
            );
        }

        return ( $result );
    }

    private function exportOrderFiles($orderID) {
        $ordering = array();
        $customer_id = get_post_meta($orderID, '_customer_user', true);
        $files = wc_get_customer_available_downloads($customer_id);
        foreach ($files as $file) {
            if ($file['order_id'] != $orderID) {
                continue;
            }
            $product_id = $file['product_id'];
            unset($file['download_id']);
            unset($file['product_id']);
            unset($file['product_name']);
            unset($file['order_id']);
            unset($file['order_key']);
            unset($file['downloads_remaining']);
            unset($file['access_expires']);
            unset($file['file']);
            $ordering[$product_id][] = $file;
        }

        return ( $ordering );
    }

    public function removeCoupon() {
        $userToken = sanitize_text_field($_POST['userToken']);
        $user_action = $this->service_container->get(User::class);
        $user_id = $user_action->get_userID_byToken($userToken);
        if ($user_id) {

            global $wpdb, $googleID, $sessionRecord;
            $cart = array();
            $table = $wpdb->prefix . 'OSA_cart';
            $sessionRecord = $wpdb->get_row("SELECT * FROM $table WHERE googleID = '$googleID'");

            if ($sessionRecord->couponCode) {
                $updated = $wpdb->update(
                        $table,
                        array(
                            'couponCode' => ''
                        ),
                        array('googleID' => $googleID),
                        array(
                            '%s'
                        ),
                        array('%s')
                );
                WC()->cart->remove_coupons();
                $message = esc_html__('Coupons removed successfully.', 'storina-application');
            }
            WC()->cart->calculate_totals();
            $cart = $this->get_cart();
            unset($cart['items']);
            $cart['message'] = $message;


            $result = array(
                'status' => true,
                'data' => $cart
            );
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => esc_html__('Token is invalid', 'storina-application')
                )
            );
        }

        return ( $result );
    }

    public function applyCoupon() {
        $couponCode = strtolower(sanitize_text_field($_POST['couponCode']));
        $userToken = sanitize_text_field($_POST['userToken']);
        $googleID = sanitize_text_field($_POST['googleID']);
        $user_action = $this->service_container->get(User::class);
        $user_id = $user_action->get_userID_byToken($userToken);
        if ($user_id) {
            wc_empty_cart();
			$general = $this->service_container->get(General::class);
            $cart = $general->get_items();
            foreach ($cart as $item => $values) {
                WC()->cart->add_to_cart($values['product_id'], $values['quantity'], $values['variation_id'], $values['variation']);
            }

            global $wpdb, $googleID, $sessionRecord;
            $cart = array();
            $table = $wpdb->prefix . 'OSA_cart';
            $sessionRecord = $wpdb->get_row("SELECT * FROM $table WHERE googleID = '$googleID'");


            if (!$sessionRecord->couponCode) {
                $validation = $this->validate_coupon(WC()->cart, $couponCode, $user_id);
                if (true == $validation['status']) {
                    $updated = $wpdb->update(
                            $table,
                            array(
                                'couponCode' => $couponCode
                            ),
                            array('id' => $sessionRecord->id),
                            array(
                                '%s'
                            ),
                            array('%d')
                    );

                    $message = esc_html__('Offer coupon applied.', 'storina-application');
                    WC()->session->set('couponCode', $couponCode);
                    $cart = $this->get_cart();
                    unset($cart['items']);
                    $cart['message'] = $message;

                    $result = array(
                        'status' => true,
                        'data' => $cart
                    );
                } else {
                    $message = esc_html__('Coupon is invalid', 'storina-application');
                    $cart['message'] = $message;
                    $cart['messages'] = $validation['messages'];
                    $result = array(
                        'status' => false,
                        'data' => $cart
                    );
                };
            } else {
                $message = esc_html__('Basket have coupon.', 'storina-application');
                $cart['message'] = $message;
                $result = array(
                    'status' => false,
                    'data' => $cart
                );
            }
            //WC()->cart->remove_coupon( $couponCode );
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => esc_html__('Token is invalid', 'storina-application')
                )
            );
        }

        return ( $result );
    }

    public function validate_coupon($cart, $code, $user_id) {
        $validation = array(
            'status' => $cart->add_discount($code),
            'messages' => array()
        );
        if (!wc_coupons_enabled()) {
            $validation['messages'][] = 'کوپن ها فعال نیستند';
        }
        $coupon = new WC_Coupon($code);
        if ($coupon->get_code() !== $code) {
            $validation['messages'][] = 'کوپن موجود نمی باشد';
        }
        if (!$coupon->is_valid()) {
            $validation['messages'][] = strip_tags($coupon->get_error_message());
        }
        /*if (!$cart->has_discount($code)) {
            $validation['messages'][] = 'این کوپن قبلا اعمال شده است.';
        }*/
        $user_usages = $coupon->get_used_by();
        $user_usage_limit_count = $coupon->get_usage_limit_per_user();
        $user_usage_count = 0;
        foreach ($user_usages as $usage_user_id) {
            $user_usage_count = ($usage_user_id == $user_id) ? $user_usage_count + 1 : $user_usage_count;
        }
        if ($user_usage_count >= $user_usage_limit_count && 0 != $user_usage_count && 0 != $user_usage_limit_count) {
            $validation['status'] = false;
            $validation['messages'][] = "محدودیت استفاده از کوپن";
        }
        return $validation;
    }

    public function getOrder() {
        $userToken = sanitize_text_field($_POST['userToken']);
        $masterID = sanitize_text_field($_POST['id']);
        $user_action = $this->service_container->get(User::class);
        $user_id = $user_action->get_userID_byToken($userToken);
        if ($user_id) {
            $orderID = sanitize_text_field($_POST['id']);
            $order = wc_get_order($orderID);
            //var_dump($order);
            $order_data = $order->get_data();
            //global $woocommerceAPI;
            //$order = $woocommerceAPI->get('orders/'.$orderID);
            $customer_id = get_post_meta($orderID, '_customer_user', true);
            if ($customer_id != $user_id) {
                $result = array(
                    'status' => false,
                    'error' => array(
                        'errorCode' => - 12,
                        'message' => esc_html__('This order is not for you.', 'storina-application')
                    )
                );

                return ( $result );
            }
            //$order = json_decode(json_encode($order), true);

            $files = $this->exportOrderFiles($orderID);
            $masterOrder['order_number'] = 'OKC-' . $order_data['id'];
            $timestamp = $order_data['date_created']->getTimestamp();
            $orderDate = (is_rtl())? JDate::jdate('h:i j/m/y', $timestamp) : date('j/m/y h:i', $timestamp);
            $masterOrder['created_at'] = $orderDate;
            $masterOrder['status'] = $order_data['status'];
            $masterOrder['total'] = intval($order_data['total']);
            $masterOrder['subtotal'] = intval($order_data['total'] + $order_data['discount_total']);
            $masterOrder['total_discount'] = intval($order_data['discount_total']);
            $masterOrder['total_tax'] = intval($order_data['total_tax']);
            $masterOrder['total_shipping'] = intval($order_data['shipping_total']);
            $ship_method = $order->get_shipping_methods();
            foreach ($ship_method as $SHPM) {
                $wooorder['_selected_shipping_method'] = $SHPM['name'];
            }

            $masterOrder['shipping_methods'] = $wooorder['_selected_shipping_method'];
            $paydetail = array(
                'method_id' => $order_data['payment_method'],
                'method_title' => $order_data['payment_method_title'],
                'paid' => ( $order_data['date_paid'] ) ? true : false
            );
            $masterOrder['payment_details'] = $paydetail;
            $masterOrder['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
            $line_items = $order_data['line_items'];
            $items = array();
            foreach ($line_items as $line_item) {
                if (has_post_thumbnail($line_item['product_id'])) {
                    $img_id = get_post_thumbnail_id($line_item['product_id']);
                    $src = wp_get_attachment_image_src($img_id, 'medium')[0];
                } else {
                    $src = STORINA_PLUGIN_URL . "/assets/images/notp.png";
                }
                $en_name = ( get_post_meta($line_item['product_id'], '_subtitle', true) ) ? get_post_meta($line_item['product_id'], '_subtitle', true) : get_post_meta($line_item['product_id'], '_ENtitle', true);
                $vendor = array();
                if (function_exists('dokan_get_store_info')) {
                    $author_id = get_post_field('post_author', $line_item['product_id']);
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
                $tmp = array(
                    'product_id' => intval($line_item['product_id']),
                    'quantity' => $line_item['quantity'],
                    'name' => $line_item['name'],
                    'vendor' => $vendor,
                    'en_name' => $en_name,
                    'price' => $line_item['total'],
                    'image' => $src,
                    'files' => ( $files[$line_item['product_id']] ) ? $files[$line_item['product_id']] : array(),
                );
                if (( $line_item['variation_id'])) {
                    $tmp['product_type'] = 'variable';
                    $createdVariation = $this->make_variations($line_item['variation_id']);
                    $tmp['variation_id'] = intval($line_item['variation_id']);
                    $tmp['variation'] = $createdVariation;

                    //var_dump($_product->get_variation_attributes());
                } else {
                    $tmp['product_type'] = 'simple';
                }

                $items[] = $tmp;
            }
            $masterOrder['line_items'] = $items;
            $masterOrder['redirect'] = get_post_meta($order_data['id'], 'pay_link', true);
            $result = array(
                'status' => true,
                'data' => $masterOrder
            );
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => esc_html__('Token is invalid', 'storina-application')
                )
            );
        }

        return ( $result );
    }

    public function goPayment() {
        $userToken = sanitize_text_field($_POST['userToken']);
        $timestamp = sanitize_text_field($_POST['timestamp']);

        if (!empty(sanitize_text_field($_POST['orderNote']))) {
            $orderNote = sanitize_text_field($_POST['orderNote']);
        }
        global $woocommerce;
        do_action('woap_prepare_woocommerce_add_to_cart',$woocommerce);
        $user_action = $this->service_container->get(User::class);
        $user_id = $user_action->get_userID_byToken($userToken);
        if ($user_id) {
            wc_empty_cart();
            $general = $this->service_container->get(General::class);
            $cart = $general->get_items();
            foreach ($cart as $item => $values) {
                WC()->cart->add_to_cart($values['product_id'], $values['quantity'], $values['variation_id'], $values['variation']);
                if ($values['variation_id']) {
                    $product_variation = new WC_Product_Variation($values['variation_id']);
                    $is_virtual = $product_variation->get_virtual();
                } else {
                    $product_new = new WC_Product($values['product_id']);
                    $is_virtual = $product_new->get_virtual();
                }
            }
            global $wpdb, $googleID;
            $table = $wpdb->prefix . 'OSA_cart';
            $sessionRecord = $wpdb->get_row("SELECT * FROM $table WHERE googleID = '$googleID'");
            $addressType = $sessionRecord->addressType;

            $user = get_userdata($user_id);

            $chosenShippingMethodID = $sessionRecord->shipping_method_id;
            WC()->session->set('chosen_shipping_methods', array($chosenShippingMethodID));
            $addresses_fields = array(
                'billing' => $general->get_address_fields('billing'),
                'shipping' => $general->get_address_fields('shipping')
            );
            $addresses = array(
                'billing' => array(
                    'address_1' => ' ',
                ),
                'shipping' => array(
                    'address_1' => ' ', // حتما باید پر بشه تا آدرس دوم نمایش داده بشه
                ),
            );
            foreach ($addresses_fields as $key => $address_fields) {
                foreach ($address_fields as $address_field) {
                    $addresses[$key][str_replace($key . '_', '', $address_field['id'])] = get_user_meta($user_id, $address_field['id'], true);
                }
            }
            WC()->customer->set_props($addresses[$addressType]);
            WC()->session->set('chosenAddress', $addresses[$addressType]);
            if ($addressType == 'shipping') {
                WC()->customer->set_shipping_address_to_base();
            } else {
                WC()->customer->set_billing_address_to_base();
            }

            WC()->customer->save();
            WC()->cart->calculate_totals();
            $packages = WC()->cart->get_shipping_packages();
            $shipping_methods = WC()->shipping->calculate_shipping($packages);
            /* $state_name       = $general->get_states( $chosenAddress['shipping_state'] );
              $address          = array(
              'first_name' => $chosenAddress['first_name'],
              'last_name'  => $chosenAddress['last_name'],
              'company'    => '',
              'email'      => $chosenAddress['email'],
              'phone'      => $chosenAddress['phone'],
              'address_1'  => $chosenAddress['shipping_address_1'],
              'address_2'  => $chosenAddress['shipping_address_2'],
              'city'       => $chosenAddress['shipping_city'],
              'state'      => $state_name,
              'postcode'   => $chosenAddress['shipping_postcode'],
              'country'    => $chosenAddress['shipping_country']
              ); */

            // Now we create the order
            $order = wc_create_order();
            if (shj_order_delivery_activation()) {
                storina_fire_function("odt_implement_goPayment", array($order));
            }
            if (bhr_order_delivery_activation()) {
                bhr_implement_goPayment($order);
            }
            // The add_product() function below is located in /plugins/woocommerce/includes/abstracts/abstract_wc_order.php
            $items = $this->get_cart();
            if (1 == $items['notice']) {
                return (
                        array(
                            'status' => false,
                            'error' => array(
                                'errorCode' => - 10,
                                'message' => esc_html__('Cart is empty', 'storina-application')
                            )
                        )
                        );
                return;
            }
            foreach ($items['items'] as $item) {
                if ($item['type'] == 'simple') {
                    $product = wc_get_product($item['id']);
                    if (function_exists("YITH_Role_Based_Type")) {
                        $price_based_role = $this->service_container->get(Yith_Role_Based_Price::class);
                        $price_based_role->set_compute_price($product, $user_id);
                    }
                    if (storina_wbd_activation()) {
                        storina_wbd_set_product_price($product, $item['quantity']);
                    }
                    $order->add_product($product, $item['quantity']); // This is an existing SIMPLE product
                } else {
                    $variations = $this->make_variations($item['variation_id']);
                    foreach ($variations as $variation_meta) {
                        $formatted_variation_meta[$variation_meta['name']] = $variation_meta['option'];
                        //$formatted_variation_meta[ $variation_meta['name'] ] = $variation_meta['value'];  // image link OR color code
                    }

                    $args = array(
                        'variation' => $formatted_variation_meta,
                    );
                    $product = wc_get_product($item['variation_id']);
                    if (function_exists("YITH_Role_Based_Type")) {
                        $price_based_role = $this->service_container->get(Yith_Role_Based_Price::class);
                        $price_based_role->set_compute_price($product, $user_id);
                    }
                    if (storina_wbd_activation()) {
                        storina_wbd_set_product_price($product, $item['quantity']);
                    }
                    $variation_arr = $item['variation_arr'];
                    $variation_arr2 = array();
                    $wc_variation = $this->make_variations($product->get_id());
                    if (!empty($wc_variation)) {
                        foreach ($wc_variation as $item_variation) {
                            $variation_arr2[$item_variation['name']] = $item_variation['value'];
                        }
                    }
                    $variation_merge = array_merge((array) $variation_arr, (array) $variation_arr2);
                    $order->add_product($product, $item['quantity'], array('variation' => $variation_merge)); // This is an existing variable product
                }
            }
            $order->set_customer_id($user_id);
            do_action('woap_go_payment_address_props',$order,$addresses,$addressType);
            $order->set_address($addresses['billing'], 'billing');
            if ($addressType == 'shipping') {
                $order->set_address($addresses['shipping'], 'shipping');
            }
            $paymentMethod = sanitize_text_field($_POST['paymentMethod']);

			$ship_destination=get_option('woocommerce_ship_to_destination');
			if('billing' == $ship_destination){
				$order->set_address($addresses['billing'],'shipping');
			}
            global $wpdb, $googleID;
            $table = $wpdb->prefix . 'OSA_cart';
            $wpdb->update(
                    $table,
                    array(
                        'paymentMethod' => $paymentMethod
                    ),
                    array('googleID' => $googleID),
                    array(
                        '%s'
                    ),
                    array('%s')
            );
            update_post_meta($order->id, 'purchase_type', 'app');
            //WC()->session->set( 'purchase_type' , 'app' );
            $available_gateways = WC()->payment_gateways->payment_gateways();
            update_post_meta($order->id, 'time4SendTimestamp', $timestamp);

            if ($this->wc_checkout_fields_editor) {
                if ($addressType == 'shipping') {
                    $this->wc_checkout_fields_editor->addressType = 'shipping';
                }
                $this->wc_checkout_fields_editor->set_order_fields($user_id, $order);
            }

            if (is_rtl()) {
                update_post_meta($order->id, 'time4Send', JDate::jdate('l d F Y - H:i', $timestamp));
            } else {
                update_post_meta($order->id, 'time4Send', date('l d F Y - H:i', $timestamp));
            }

            update_post_meta($order->id, 'address_type', $addressType);
            update_post_meta($order->id, '_payment_method', $paymentMethod);
            update_post_meta($order->id, '_payment_method_title', $available_gateways[$paymentMethod]->title);
            WC()->session->order_awaiting_payment = $order->id;
            $shipping_status = apply_filters('woap_gopayment_shipping_status',true);
            $shipping_method_resources = $this->get_shipping_method();
            if (!$is_virtual && $shipping_status) {
                $currentShip = current($shipping_method_resources);
                $shipping = new WC_Shipping_Rate($currentShip['id'], $currentShip['title'], $currentShip['cost']);
                $order->add_shipping($shipping);
            }


            $couponCode = WC()->session->get('couponCode');
            if ($couponCode) {
                //$coupon_id = wc_get_coupon_id_by_code( $couponCode );
                $result = $order->apply_coupon(wc_clean($couponCode));
                remove_action('woocommerce_order_status_pending', 'wc_update_coupon_usage_counts');
                remove_action('woocommerce_order_status_completed', 'wc_update_coupon_usage_counts');
                remove_action('woocommerce_order_status_processing', 'wc_update_coupon_usage_counts');
                remove_action('woocommerce_order_status_on-hold', 'wc_update_coupon_usage_counts');
                remove_action('woocommerce_order_status_cancelled', 'wc_update_coupon_usage_counts');
                $order->calculate_shipping();
                $order->calculate_totals();
            }


            /// add order note
            if ($orderNote) {
                $order->set_customer_note($orderNote);
            }

			if(function_exists('dokan_sync_insert_order')){
				dokan_sync_insert_order($order->get_id());
			}
            $order = apply_filters( 'woap_order_process_handle',$order,$user_id,$googleID );

            $order->calculate_totals();


            $order_status = $order->get_status();
            if ($order_status == 'completed' || $order_status == "processing" || $order->get_total() < 100) {
                global $wpdb, $googleID;
                $table = $wpdb->prefix . 'OSA_cart';
                $wpdb->delete($table, array('googleID' => $googleID));
                $order_complate_result = $this->order_complation_proccess($order);

                return ( $order_complate_result );
            }
            $order->set_created_via( 'application' );
            $order->update_status('pending');
            $result = $available_gateways[$paymentMethod]->process_payment($order->id);
            /* if ( class_exists( 'Dokan_Order_Manager' ) ) {
              $Dokan_Order_Manager = new Dokan_Order_Manager();
              $Dokan_Order_Manager->maybe_split_orders( $order->id );
              //dokan_sync_insert_order( $order->id );
              } */
            $data = WC()->checkout->get_posted_data();
            //do_action('woocommerce_checkout_create_order', $order, $data);
            $order_id = $order->save();
            do_action('woocommerce_checkout_update_order_meta', $order_id, $data);
            do_action('woocommerce_before_checkout_process');
            do_action('woocommerce_checkout_process');
            if (class_exists('WoocommerceIR_SMS_Orders')) {
                $sms_order = new WoocommerceIR_SMS_Orders();
                $sms_order->sendOrderSms($order_id);
            }
            do_action('woap_gopayment_vendor_shipping', $shipping_method_resources, $order);
            /// Save Note To Order
            if ($result['result'] == 'success' && 0 != count($order->get_items())) {
                update_post_meta($order->id, 'pay_link', $result['redirect']);
                wc_empty_cart();
                WC()->session->destroy_session();
                global $wpdb, $googleID;
                $table = $wpdb->prefix . 'OSA_cart';
                $wpdb->delete($table, array('googleID' => $googleID));
                /* $wpdb->update(
                  $table,
                  array(
                  'cart' => json_encode((object) array())
                  ),
                  array( 'googleID' => $googleID ),
                  array(
                  '%s'
                  ),
                  array( '%s' )
                  ); */
                $orderresult = apply_filters('woocommerce_payment_successful_result', $result, $order->id);
                //var_dump($orderresult);
                $result = array(
                    'status' => true,
                    'data' => array(
                        'status' => $result['result'],
                        'orderID' => intval($order->id),
                        'redirect' => $result['redirect'],
                    )
                );
                //var_dump($result);

                WC()->cart->empty_cart();
                //wp_redirect( $result['redirect'] );
                //exit;
            } else {
                $order->delete(true);
                $result = array(
                    'status' => false,
                    'error' => array(
                        'errorCode' => - 19,
                        'message' => esc_html__('something wrong happen.', 'storina-application')
                    )
                );
            }
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => esc_html__('Token is invalid', 'storina-application')
                )
            );
        }

        return ( $result );
    }

    private function get_shipping_method() {
        wc_empty_cart();
        $general = $this->service_container->get(General::class);
        $cart = $general->get_items();
        foreach ($cart as $item => $values) {
            WC()->cart->add_to_cart($values['product_id'], $values['quantity'], $values['variation_id'], $values['variation']);
        }
        global $wpdb, $googleID;
        $table = $wpdb->prefix . 'OSA_cart';
        $sessionRecord = $wpdb->get_row("SELECT * FROM $table WHERE googleID = '$googleID'");
        WC()->cart->calculate_totals();
        //woocommerce_order_review();
        $packages = WC()->cart->get_shipping_packages();
        $shipping_methods = WC()->shipping->calculate_shipping($packages);
        //var_dump($shipping_methods);
        unset($shipping_methods[0]['contents']);
        unset($shipping_methods[0]['contents_cost']);
        unset($shipping_methods[0]['applied_coupons']);
        unset($shipping_methods[0]['cart_subtotal']);
        foreach ($shipping_methods[0]['rates'] as $rate) {
            $tmp = $this->accessProtected($rate, 'data');
            unset($tmp['taxes']);
            unset($tmp['instance_id']);
            $methods[$tmp['id']] = $tmp;
        }
        $chosen_shipping_method_ids = apply_filters('woap_get_shipping_methods_chosen_ids',[$sessionRecord->shipping_method_id],$methods,$sessionRecord);
        foreach($chosen_shipping_method_ids as $key => $shipping_method_id){
            $chosen_shipping_methods[] = $methods[$shipping_method_id]['method_id'];
            $shipping_method_resources[$key] = [
                'id' => $methods[$shipping_method_id]['id'],
                'title' => $methods[$shipping_method_id]['label'],
                'cost' => $methods[$shipping_method_id]['cost'],
            ];
        }
        WC()->session->set('chosen_shipping_methods', $chosen_shipping_methods);
        return $shipping_method_resources;
    }

    public function gatewayValidation(&$gateways, $method = null) {
        if (isset($gateways['wallet'])) {
            unset($gateways['wallet']);
        }
        $code = $gateways['cod'];
        $enable_methods = (array) $code->settings['enable_for_methods'];
        foreach ($enable_methods as $enable_method) {
            $postion = strpos($method, $enable_method);
            if (is_numeric($postion)) {
                return true;
            }
        }
        if (count($enable_methods) > 0 && !in_array($method, $enable_methods)) {
            unset($gateways['cod']);
        }
    }

    public function paymentMethod() {
        $userToken = sanitize_text_field($_POST['userToken']);
        global $woocommerce;
        do_action('woap_prepare_woocommerce_add_to_cart',$woocommerce);
        $user_action = $this->service_container->get(User::class);
        $user_id = $user_action->get_userID_byToken($userToken);
        if ($user_id) {
            $this->cartReview();
            wc_empty_cart();
            $general = $this->service_container->get(General::class);
            $cart = $general->get_items();
            foreach ($cart as $item => $values) {
                WC()->cart->add_to_cart($values['product_id'], $values['quantity'], $values['variation_id'], $values['variation']);
            }


            $available_gateways = WC()->payment_gateways->payment_gateways();
            $this->gatewayValidation($available_gateways, sanitize_text_field($_POST['shipping_method_id']));
            $gateway = array();
            foreach ($available_gateways as $available_gateway) {
                $tmp = null;
                if ('yes' == $available_gateway->enabled) {
                    $tmp['id'] = $available_gateway->id;
                    $tmp['title'] = strip_tags($available_gateway->title);
                    $tmp['description'] = $available_gateway->description;
                    $tmp['icon'] = $available_gateway->icon;
                    $gateway[] = $tmp;
                }
            }
            $cart = $this->get_cart();
           
            if (!$cart['total']['shipping_total']) {
                $cart['total']['shipping_total'] = '0';
            }
            unset($cart['items']);
            $cart['paymentMethods'] = $gateway;
            $cart['coupons_enabled'] = ( wc_coupons_enabled() == 1 ) ? true : false;

            $result = array(
                'status' => true,
                'data' => $cart
            );
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => esc_html__('Token is invalid', 'storina-application')
                )
            );
        }
        return ( $result );
    }

    public function cartReview() {
        $userToken = sanitize_text_field($_POST['userToken']);
        $user_action = $this->service_container->get(User::class);
        $user_id = $user_action->get_userID_byToken($userToken);
        if ($user_id) {
            $chosenShippingMethodID = sanitize_text_field($_POST['shipping_method_id']);
            global $wpdb, $googleID;
            $table = $wpdb->prefix . 'OSA_cart';
            $wpdb->update(
                    $table,
                    array(
                        'shipping_method_id' => $chosenShippingMethodID
                    ),
                    array('googleID' => $googleID),
                    array(
                        '%s'
                    ),
                    array('%s')
            );
        }
    }

    public function ConfirmShipping() {
        global $woocommerce;
        do_action('woap_prepare_woocommerce_add_to_cart',$woocommerce);
        remove_filter( 'woocommerce_cart_shipping_packages', 'dokan_custom_split_shipping_packages' );
        $userToken = sanitize_text_field($_POST['userToken']);
        $product_count = sanitize_text_field($_POST['product_count']);

        $product_count = json_decode(stripcslashes($product_count), true);
        $general = $this->service_container->get(General::class);
        $cart = $general->get_items();
        foreach ($product_count as $productID => $item) {
            $tmp = explode('_', $productID);
            $productID = $tmp[0];
            $variationID = ( isset($tmp[1]) ) ? $tmp[1] : null;
            foreach ($cart as $cartItem) {
                if ($productID == $variationID) {
                    $variationID = null;
                }

                if ($cartItem['product_id'] == $productID AND $cartItem['variation_id'] == $variationID) {
                    $cart[$productID . $variationID]['quantity'] = $item;
                }
                if ($variationID) {
                    $product_variation = new WC_Product_Variation($variationID);
                    $is_virtual = $product_variation->get_virtual();
                } else {
                    $product_new = new WC_Product($productID);
                    $is_virtual = $product_new->get_virtual();
                }
            }
        }
        $cart = $this->update_items($cart);

        $user_action = $this->service_container->get(User::class);
        $user_id = $user_action->get_userID_byToken($userToken);
        //var_dump($user_id);
        if ($user_id) {
            $data = array();
            //global $woocommerceAPI;
            //$userInfo = $woocommerceAPI->get('customers/' . $user_id);
            //$userInfo = json_decode(json_encode($userInfo), true);
            $address_types = array('billing', 'shipping');
            foreach ($address_types as $address_type) {
                $status = get_user_meta($user_id, $address_type . '_status', true);
                if($status == 'active'){
                    $address_fields = $general->get_address_fields($address_type);
                    //$data[$address_type] = storina_get_formatted_addresses( $address_type, $user_id );
                    foreach ($address_fields as $address_field) {
                        $value = get_user_meta($user_id, $address_field['id'], true);
                        if ($address_field['id'] == 'billing_state' OR $address_field['id'] == 'shipping_state') {
                            $value = $general->get_states($value);
                        }
                        $data[$address_type][] = array(
                            'id' => $address_field['id'],
                            'label' => $address_field['label'],
                            'priority' => $address_field['priority'],
                            'required' => $address_field['required'],
                            'value' => $value
                        );
                    }
                    //add lat lng fields manually
                    $value = get_user_meta($user_id, $address_type . '_lat', true);
                    $data[$address_type][] = array(
                        'id' => $address_type . '_lat',
                        'label' => 'Latitude',
                        'priority' => '1542',
                        'value' => $value
                    );
                    $value = get_user_meta($user_id, $address_type . '_lng', true);
                    $data[$address_type][] = array(
                        'id' => $address_type . '_lng',
                        'label' => 'Longitude',
                        'priority' => '1543',
                        'value' => $value
                    );
                }
            }

            $addresses = $data;

            $data = null;

            $addressType = sanitize_text_field($_POST['addressType']);
            global $wpdb, $googleID;
            if (function_exists("woo_wallet")) {
                $terawallet = $this->service_container->get(Terawallet::class);
                $cart_id = $terawallet->get_cart_id_by_googleID($googleID);
                $terawallet->delete_cart_meta($cart_id);
            }
            $table = $wpdb->prefix . 'OSA_cart';
            $wpdb->update(
                    $table,
                    array(
                        'addressType' => $addressType
                    ),
                    array('googleID' => $googleID),
                    array(
                        '%s'
                    ),
                    array('%s')
            );


            $storina_get_option = storina_get_option('woocommerce_ship_to_countries');
            if ($storina_get_option != 'disabled') {

                $address = get_user_meta($user_id, $addressType . '_address_1', true);
                $city = get_user_meta($user_id, $addressType . '_city', true);
                $postCode = get_user_meta($user_id, $addressType . '_postcode', true);
                $state = get_user_meta($user_id, $addressType . '_state', true);

                $mobile = get_user_meta($user_id, $addressType . '_mobile', true);
                $phone = get_user_meta($user_id, $addressType . '_phone', true);
                $first_name = get_user_meta($user_id, $addressType . '_first_name', true);
                $last_name = get_user_meta($user_id, $addressType . '_last_name', true);
                $data = apply_filters('woap_shipping_package_data',array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'phone' => $phone,
                    'mobile' => $mobile,
                    'shipping_country' => 'IR',
                    'shipping_state' => $state,
                    'shipping_postcode' => $postCode,
                    'shipping_city' => $city,
                    'shipping_address_1' => $address,
                    'shipping_address_2' => '',
                ),$user_id);
                wc_empty_cart();
                $cart = $general->get_items();
                foreach ($cart as $item => $values) {
                    WC()->cart->add_to_cart($values['product_id'], $values['quantity'], $values['variation_id'], $values['variation']);
                }
                
                WC()->customer->set_props($data);
                WC()->session->set('chosenAddress', $data);
                WC()->customer->set_id($user_id);
                WC()->customer->set_billing_address_to_base();
                //WC()->customer->set_shipping_address_to_base( );     // این خط اگه اجرا بشه بقیه روش های ارسال رو هم نشون میده
                WC()->customer->set_calculated_shipping(true);
                WC()->customer->save();
                wp_set_current_user($user_id);
                //woocommerce_order_review();
                $packages = WC()->cart->get_shipping_packages();
                $shipping_methods = WC()->shipping->get_shipping_methods($packages);
                foreach($shipping_methods as $shipping_method){
                    foreach($packages as $package){
                        $shipping_method->calculate_shipping($package);
                    }
                }
                $shipping_methods = WC()->shipping->calculate_shipping($packages);
                
                unset($shipping_methods[0]['contents']);
                unset($shipping_methods[0]['contents_cost']);
                unset($shipping_methods[0]['applied_coupons']);
                unset($shipping_methods[0]['cart_subtotal']);
                //$shipping_methods = WC()->cart->get_cart();
                //$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
            } else {
                $shipping_methods = array();
            }

            
            $methods = array();
            if (!$is_virtual) {
                foreach ($shipping_methods[0]['rates'] as $rate) {
                    $costs[] = $rate->get_cost();
                    $tmp = $this->accessProtected($rate, 'data');
                    unset($tmp['taxes']);
                    unset($tmp['instance_id']);
                    $tmp['method_id'] = $tmp['id'];
                    $methods[] = $tmp;
                }
            }
            $result = array(
                'status' => true,
                'data' => array(
                    'addresses' => apply_filters("osa_ConfirmShipping_ConfirmShipping_addresses", $addresses),
                    'shippingMethods' => apply_filters('woap_confirm_shipping_method_resources',$methods,$cart,$user_id),
                )
            );
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => esc_html__('Token is invalid', 'storina-application')
                )
            );
        }
        return ( $result );
    }

    private function order_complation_proccess($order) {
        if ('completed' !== $order->get_status()) {
            $order->set_status('processing');
        }
        $checkout_permalink = get_permalink(storina_get_option("woocommerce_checkout_page_id"));
        $order_received_endpoint = storina_get_option("woocommerce_checkout_order_received_endpoint");
        $order_id = $order->id;
        $order_key = $order->order_key;
        $order_received_link = trailingslashit($checkout_permalink) . trailingslashit($order_received_endpoint) . trailingslashit($order_id);
        $redirect_url = add_query_arg(array('key' => $order_key), $order_received_link);
        $order_complate_result = array(
            'status' => true,
            'data' => array(
                'status' => "success",
                'orderID' => intval($order_id),
                'redirect' => $redirect_url,
            )
        );
        $order->save();
        return $order_complate_result;
    }

}
