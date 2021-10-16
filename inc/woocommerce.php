<?php

/**
 * remove maybe_set_cart_cookies action from @woocommerce_add_to_cart hook
 * this action is prevent from 504 error when adding multiple product to cart
 */

add_action('woap_prepare_woocommerce_add_to_cart',function($woocommerce){
    $woocommerce_cart = $woocommerce->cart;
    $reflection = new ReflectionClass($woocommerce_cart);
    $property = $reflection->getProperty('session');
    $property->setAccessible(true);
    $woocommerce_cart_session = $property->getValue($woocommerce_cart);
    remove_action('woocommerce_add_to_cart',[$woocommerce_cart_session,'maybe_set_cart_cookies']);
},10,1);