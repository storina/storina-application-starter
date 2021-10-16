<?php

namespace CRN;

use CRN\Providers\ServiceProvider;
use CRN\Providers\ApiProvider;
use CRN\Providers\TableProvider;

defined('ABSPATH') || exit;

class Init {
    
    const first_flush_option = "first_flush_permalinks";

    public $version;
    public $web_slug;
    public $api_slug;
    public $service_container;

    public function __construct($version, $web_slug, $api_slug) {
        $this->version = $version;
        $this->web_slug = $web_slug;
        $this->api_slug = $api_slug;
        add_action("init", array($this, "add_rewrite_rule"));
        add_action("admin_notices", array($this, "first_flush_notice"));
        add_action("update_option_permalink_structure", function() {
            update_option(self::first_flush_option, true);
        });
        $this->boot_modules();
        $this->boot_tables_provider();
        $this->boot_service_provider();
    }

    public function add_rewrite_rule() {
        add_rewrite_rule("^{$this->api_slug}/([^/]*)/?([^/]*)/?([^/]*)/?$", 'index.php?crn_module=$matches[1]&crn_action=$matches[2]&crn_params=$matches[3]', "top");
        add_rewrite_tag("%crn_module%", "([^/]*)");
        add_rewrite_tag("%crn_action%", "([^/]*)");
        add_rewrite_tag("%crn_params%", "([^/]*)");
    }

    public function first_flush_notice() {
        if (get_option(self::first_flush_option)) {
            return;
        }
        ?>
        <div class="notice notice-info">
            <p>
                <?php _e("To make the customer vendor talk api worked Please first ","onlinerShopApp"); ?>
                <a href="<?php echo get_admin_url(); ?>/options-permalink.php" title="<?php esc_attr_e("Permalink Settings") ?>" >
                    <?php _e("Flush rewrite rules","onlinerShopApp"); ?>
                </a>
            </p>
        </div>
        <?php
    }

    public function boot_modules() {
        require_once trailingslashit(__DIR__) . "Libraries/Model.php";
        require_once trailingslashit(__DIR__) . "Models/Message.php";
    }

    public function boot_tables_provider(){
        $database_services = array(
            \CRN\Tables\MessagesTable::class => trailingslashit(__DIR__) . "Tables/MessagesTable.php",
            \CRN\Tables\MessagemetaTable::class => trailingslashit(__DIR__) . "Tables/MessagemetaTable.php"
        );
        $database_version = 0.8;
        require_once trailingslashit(__DIR__) . "Providers/TableProvider.php";
        new TableProvider($database_services,$database_version);
    }

    public function boot_service_provider() {
        # Services
        $services = array(
            #Controlles\Web
            \CRN\Controllers\Logic\UserLogic::class => trailingslashit(__DIR__) . "Controllers/Logic/UserLogic.php",
            \CRN\Controllers\Logic\NotifAction::class => trailingslashit(__DIR__) . "Controllers/Logic/NotifAction.php",
            \CRN\Controllers\Web\UserController::class => trailingslashit(__DIR__) . "Controllers/Web/UserController.php",
            \CRN\Controllers\Logic\MessageAction::class => trailingslashit(__DIR__) . "Controllers/Logic/MessageAction.php",
            \CRN\Controllers\Logic\AttachmentAction::class => trailingslashit(__DIR__) . "Controllers/Logic/AttachmentAction.php",
            \CRN\Controllers\Web\ProductController::class => trailingslashit(__DIR__) . "Controllers/Web/ProductController.php",
			\CRN\Controllers\Web\HomeController::class => trailingslashit(__DIR__) . "Controllers/Web/HomeController.php",
            #Middlewares
            \CRN\Middlewares\Authentication::class => trailingslashit( __DIR__ ) . "Middlewares/Authentication.php",
            #Controllers\Api
            \CRN\Controllers\Api\Product::class => trailingslashit(__DIR__) . "Controllers/Api/Product.php",
            \CRN\Controllers\Api\Message::class => trailingslashit(__DIR__) . "Controllers/Api/Message.php",
            \CRN\Controllers\Api\Vendor::class => trailingslashit(__DIR__) . "Controllers/Api/Vendor.php"
        );
        $api_mapper = array(
            "Product" => \CRN\Controllers\Api\Product::class,
            "Message" => \CRN\Controllers\Api\Message::class,
            "Vendor" => \CRN\Controllers\Api\Vendor::class
        );
        # BootServices
        require_once trailingslashit(__DIR__) . "Providers/ServiceProvider.php";
        require_once trailingslashit(__DIR__) . "Providers/ApiProvider.php";
        $service_provider = $this->service_container = new ServiceProvider($services);
        new ApiProvider($service_provider,$api_mapper);
    }

}
