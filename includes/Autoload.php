<?php

namespace STORINA;

use STORINA\Service_Provider;

defined('ABSPATH') || exit;

class Autoload {

    public $service_provider;
    public $requires;

    public function __construct() {
        $this->init();
        $this->service_provider = new Service_Provider($this->requires);
        new Call_Api($this->service_provider);
    }

    public function init() {
        $this->requires = array(
            \STORINA\Controllers\WC_Checkout_Editor::class => trailingslashit(__DIR__) . "Controllers/wc_checkout_field_editor.php",
            \STORINA\Controllers\Yith_Role_Based_Price::class => trailingslashit(__DIR__) . "Controllers/price_based_roles.php",
            \STORINA\Controllers\Index::class => trailingslashit(__DIR__) . "Controllers/index.php",
            \STORINA\Controllers\Cache::class => trailingslashit(__DIR__) . "Controllers/cache.php",
            \STORINA\Controllers\General::class => trailingslashit(__DIR__) . "Controllers/general.php",
            \STORINA\Controllers\User::class => trailingslashit(__DIR__) . "Controllers/user.php",
            \STORINA\Controllers\Archive::class => trailingslashit(__DIR__) . "Controllers/archive.php",
            \STORINA\Controllers\Cart::class => trailingslashit(__DIR__) . "Controllers/cart.php",
            \STORINA\Controllers\Single::class => trailingslashit(__DIR__) . "Controllers/single.php",
            \STORINA\Controllers\Terawallet::class => trailingslashit(__DIR__) . "Controllers/terawallet.php",
        );
    }

}

global $osa_autoload;
$osa_autoload = new Autoload;
