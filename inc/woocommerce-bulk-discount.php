<?php

/**
 * check is woocommerce bulk discount active
 */
function storina_wbd_activation() {
    return (class_exists('Woo_Bulk_Discount_Plugin_t4m'));
}

/**
 * get cart row price
 * @param type $product
 * @param type $quantity
 * @return type
 */
function storina_wbd_get_product_price($product, $quantity) {
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }
    $type = storina_get_option("woocommerce_t4m_discount_type");
    $real_product_id = ("simple" == $product->get_type()) ? $product->get_id() : $product->get_parent_id();
    $quantity_enabled = get_post_meta($real_product_id, "_bulkdiscount_enabled", true);
    $quantity_key = array(
        "_bulkdiscount_quantity_1" => get_post_meta($real_product_id, "_bulkdiscount_quantity_1", true),
        "_bulkdiscount_quantity_2" => get_post_meta($real_product_id, "_bulkdiscount_quantity_2", true),
        "_bulkdiscount_quantity_3" => get_post_meta($real_product_id, "_bulkdiscount_quantity_3", true),
        "_bulkdiscount_quantity_4" => get_post_meta($real_product_id, "_bulkdiscount_quantity_4", true),
    );
    $filterd_quantity = array_filter($quantity_key, function($value) {
        return !is_null($value) && $value !== '';
    });
    if ("yes" != $quantity_enabled || empty($filterd_quantity)) {
        return array(
            "row" => $product->get_price() * $quantity,
            "price" => $product->get_price(),
            "discount" => $product->get_regular_price() - $product->get_sale_price(),
        );
    }
    $type_key = (empty($type)) ? "" : "_{$type}";
    $quantity_map = array(
        "_bulkdiscount_quantity_1" => "_bulkdiscount_discount{$type_key}_1",
        "_bulkdiscount_quantity_2" => "_bulkdiscount_discount{$type_key}_2",
        "_bulkdiscount_quantity_3" => "_bulkdiscount_discount{$type_key}_3",
        "_bulkdiscount_quantity_4" => "_bulkdiscount_discount{$type_key}_4",
    );
    $quantity_final_key = false;
    foreach ($quantity_key as $key => $value) {
        if (intval($quantity) >= intval($value) && !empty($value)) {
            $quantity_final_key = $key;
        }
    }

    $quantity_discount_value = get_post_meta($real_product_id, $quantity_map[$quantity_final_key], true);
    $result = array(
        "row" => ($quantity_final_key) ? storina_wbd_calculate_price_row($product, $quantity, $type, $quantity_discount_value) : $product->get_price() * $quantity,
        "price" => ($quantity_final_key) ? storina_wbd_calculate_price_column($product, $quantity, $type, $quantity_discount_value) : $product->get_price(),
        "discount" => ($quantity_final_key) ? storina_wbd_calculate_price_discount($product, $quantity, $type, $quantity_discount_value) : $product->get_regular_price() - $product->get_sale_price(),
    );
    return $result;
}

/**
 * calculate cart row based on type
 * @param type $product
 * @param type $quantity
 * @param type $type
 * @param type $value
 * @return type
 */
function storina_wbd_calculate_price_row($product, $quantity, $type, $value) {
    $price = (int) $product->get_price();
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }
    switch ($type):
        case 'flat':
            return intval($price) * intval($quantity) - intval($value);
        case 'fixed':
            return (intval($price) - intval($value)) * intval($quantity);
        default:
            return (intval($price) - (intval($value) * intval($price) / 100)) * intval($quantity);
    endswitch;
}

/**
 * calculate product price based on quantity
 * @param type $product
 * @param type $quantity
 * @param type $type
 * @param type $value
 * @return type
 */
function storina_wbd_calculate_price_column($product, $quantity, $type, $value) {
    $price = (int) $product->get_price();
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }
    switch ($type):
        case 'flat':
            return ((intval($price) * intval($quantity)) - intval($value)) / intval($quantity);
        case 'fixed':
            return (intval($price) - intval($value));
        default :
            return (intval($price) - (intval($value) * intval($price) / 100));
    endswitch;
}

/**
 * calculate product discount value based on quantity
 * @param type $product
 * @param type $quantity
 * @param type $type
 * @param type $value
 * @return type
 */
function storina_wbd_calculate_price_discount($product, $quantity, $type, $value) {
    $price = (int) $product->get_price();
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }
    switch ($type):
        case 'flat':
            return intval($value);
        case 'fixed':
            return intval($quantity) * intval($value);
        default:
            return (intval($value) * intval($price) / 100);
    endswitch;
}

/**
 * get item calculate formule
 * @param type $product
 * @return type
 */
function storina_wbd_calculate_formule($product) {
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }
    $product_id = ("simple" == $product->get_type()) ? $product->get_id() : $product->get_parent_id();
    $type = storina_get_option("woocommerce_t4m_discount_type");
    $type_key = (empty($type))? "percent" : $type;
    $type_string = (empty($type))? $type : "_{$type}";
    $result = array(
        "quantity_key" => array(
            "_bulkdiscount_quantity_1" => get_post_meta($product_id, "_bulkdiscount_quantity_1", true),
            "_bulkdiscount_quantity_2" => get_post_meta($product_id, "_bulkdiscount_quantity_2", true),
            "_bulkdiscount_quantity_3" => get_post_meta($product_id, "_bulkdiscount_quantity_3", true),
            "_bulkdiscount_quantity_4" => get_post_meta($product_id, "_bulkdiscount_quantity_4", true),
        ),
        "{$type_key}" => array(
            "_bulkdiscount_quantity_1" => get_post_meta($product_id, "_bulkdiscount_discount{$type_string}_1", true),
            "_bulkdiscount_quantity_2" => get_post_meta($product_id, "_bulkdiscount_discount{$type_string}_2", true),
            "_bulkdiscount_quantity_3" => get_post_meta($product_id, "_bulkdiscount_discount{$type_string}_3", true),
            "_bulkdiscount_quantity_4" => get_post_meta($product_id, "_bulkdiscount_discount{$type_string}_4", true),
        )
    );
    return $result;
}

/**
 * set product price for woocommmerce bulk product
 * @param type $product
 * @param type $quantity
 */
function storina_wbd_set_product_price($product, $quantity) {
    if (is_numeric($product)) {
        $product = wc_get_prodcut($product);
    }
    $price = storina_wbd_get_product_price($product, $quantity);
    $product->set_price($price['price']);
}
