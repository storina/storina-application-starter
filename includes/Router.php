<?php

defined('ABSPATH') || exit;

class Router {

    public $map;

    public function __construct() {
        $this->map = array(
            \STORINA\Controllers\User::class => array(
                "resendCode" => array("resendCode"),
                "verify" => array("verify"),
                "Register" => array("register"),
                "doLogout" => array("logout"),
                "doLogin" => array("login"),
                "forgetPass" => array("forgetPass"),
                "setPassword" => array("setPassword"),
                "commentLike" => array("commentLike"),
                "setProfile" => array("setProfile"),
                "getProfile" => array("getProfile"),
                "addToWishlist" => array("addToWishlist"),
                "removeFromWishlist" => array("removeFromWishlist"),
                "Wishlist" => array("Wishlist"),
                "get_addresses" => array("get_addresses"),
                "edit_address" => array("edit_address"),
                "vendorProduct" => array("vendorProduct")
            ),
             \STORINA\Controllers\Index::class => array(
                "index" => array("get"),
            ),
            \STORINA\Controllers\General::class => array(
                "layeredCategories" => array("layeredCategories"),
                "backorderForm" => array("backorderForm"),
                "singleComment" => array("singleComment"),
                "faq" => array("faq"),
                "about" => array("aboutUS"),
                "Announcements" => array("Announcements"),
                "blogArchive" => array("blogArchive"),
                "blogSingle" => array("blogSingle"),
                "insertComment" => array("insertComment"),
                "strings" => array("getStrings"),
                "get_vendor_towns" => array("get_vendor_towns"),
                "getState" => array("getState"),
                "staticContents" => array("staticContents"),
                "cities" => array("cities")
            ),
            \STORINA\Controllers\Single::class => array(
                "productReport" => array("report_product"),
                "single" => array("get"),
                "lastViewed" => array("getView"),
                "getContent" => array("getContent")
            ),
            \STORINA\Controllers\Archive::class => array(
                "archive" => array("archive"),
                "search" => array("search"),
                "bulkSearch" => array("bulkSearch"),
                "vendorStore" => array("store"),
                "vendors" => array("vendors"),
                "VendorListBasedCat" => array("vendors", "category_id"),
                'categoryHierarchy' => ['categoryHierarchy']
            ),
             \STORINA\Controllers\Cart::class=> array(
                "retrive_cart" => array("retrive_cart"),
                "add_to_cart" => array("add_to_cart"),
                "removeFromCart" => array("removeFromCart"),
                "ConfirmShipping" => array("ConfirmShipping"),
                "paymentMethod" => array("paymentMethod"),
                "goPayment" => array("goPayment"),
                "getOrder" => array("getOrder"),
                "applyCoupon" => array("applyCoupon"),
                "removeCoupon" => array("removeCoupon"),
                "orderHistory" => array("orderHistory"),
            ),
            \STORINA\Controllers\Terawallet::class => array(
                "walletCharge" => array("increase_ballance"),
                "applyWallet" => array("trigger_charge","apply"),
                "spyWallet" => array("trigger_charge","spy"),
            ),
        );
    }

    public function get($action) {
        foreach($this->map as $class => $values){
            if(!isset($values[$action])){
                continue;
            }
            return array(
                "class" => $class,
                "method" => current($values[$action]),
                "params" => next($values[$action])
            );
        }
    }

}
