<?php

defined('ABSPATH') || exit;

class OSA_Autoload {

    public $service_provider;
    public $requires;

    public function __construct() {
        $this->init();
        $this->service_provider = new OSA_Service_Provider($this->requires);
        new OSA_Call_Api($this->service_provider);
    }

    public function init() {
        $this->requires = array(
            "OSA_wc_checkout_field_editor" => trailingslashit(__DIR__) . "Controllers/wc_checkout_field_editor.php",
            \STORINA\Controllers\Yith_Role_Based_Price::class => trailingslashit(__DIR__) . "Controllers/price_based_roles.php",
            \STORINA\Controllers\Index::class => trailingslashit(__DIR__) . "Controllers/index.php",
            \STORINA\Controllers\Cache::class => trailingslashit(__DIR__) . "Controllers/cache.php",
            \STORINA\Controllers\General::class => trailingslashit(__DIR__) . "Controllers/general.php",
            "OSA_user" => trailingslashit(__DIR__) . "Controllers/user.php",
            \STORINA\Controllers\Archive::class => trailingslashit(__DIR__) . "Controllers/archive.php",
            \STORINA\Controllers\Cart::class => trailingslashit(__DIR__) . "Controllers/cart.php",
            \STORINA\Controllers\Single::class => trailingslashit(__DIR__) . "Controllers/single.php",
            "OSA_terawallet" => trailingslashit(__DIR__) . "Controllers/terawallet.php",
        );
    }

}

global $osa_autoload;
$osa_autoload = new OSA_Autoload;
