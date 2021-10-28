<?php

namespace STORINA\Controllers;

defined('ABSPATH') || exit;

class Yith_Role_Based_Price{

    public $settings;
    public $user_settings;

    public function __construct() {
        $this->settings = array(
            "add_to_cart" => "add_to_cart",
            "show_price" => array(
                "regular", "on_sale", "your_price"
            ),
        );
        $this->user_settings = storina_get_option("ywcrbp_show_prices_for_role");
    }

    public function get_user_role($user_id) {
        if (!isset($user_id) || empty($user_id) || !is_numeric($user_id)) {
            $current_role = "guest";
        } else {
            $user = get_user_by('id', $user_id);
            $current_role = current($user->roles);
        }
        return $current_role;
    }

    public function user_settings_show_price($user_role) {
        foreach ($this->settings['show_price'] as $price_type) {
            $validation[] = isset($this->user_settings[$user_role][$price_type]);
        }
        return !in_array(true, $validation);
    }

    public function user_settings_add_to_cart($user_id) {
        $user_role = $this->get_user_role($user_id);
        return (isset($this->user_settings[$user_role][$this->settings['add_to_cart']])) ? 'true' : 'false';
    }

    public function get_compute_price($product, $user_id = false) {
        $current_role = $this->get_user_role($user_id);
        $global_rules = YITH_Role_Based_Type()->get_price_rule_by_user_role($current_role, false);
        $price = ywcrbp_calculate_product_price_role($product, $global_rules, $current_role);
        if (!is_numeric($price) || $price == "no_price") {
            return $product->get_price();
        }
        return round($price);
    }

    public function get_compute_price_render($product, $user_id, $format = true) {
        $user_role = $this->get_user_role($user_id);
        if ($this->user_settings_show_price($user_role)) {
            return 'zero';
        }
        if (!$product->is_type('variable')) {
            return (string) $this->get_compute_price($product, $user_id);
        }
        $ids = (array) $product->get_children();
        $children_prices = array();
        foreach ($ids as $id) {
            $product_child = wc_get_product($id);
            $children_prices[] = $this->get_compute_price($product_child, $user_id);
        }
        $children_prices = array_map("intval", $children_prices);
        $children_prices_min = min($children_prices);
        $children_prices_max = max($children_prices);
        $to = __('To', 'onlinerShopApp');
        if ("0" == storina_get_option("get_compute_price_render")) {
            return (string) $children_prices_min;
        }
        return "{$children_prices_min} {$to} {$children_prices_max}";
    }

    public function set_compute_price($product, $user_id) {
        $price = $this->get_compute_price($product, $user_id);
        $product->set_price($price);
    }

}
