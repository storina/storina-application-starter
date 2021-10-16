<?php

namespace VOS;

use VOS\Providers\ServiceProvider;
use VOS\Providers\ApiProvider;
use VOS\Providers\TableProvider;
use VOS\Providers\CronProvider;

defined('ABSPATH') || exit;

class Init {
    
    const first_flush_option = "vos_first_flush_permalinks";

    public $version;
    public $web_slug;
    public $api_slug;

    public function __construct($version, $web_slug, $api_slug) {
        $this->version = $version;
        $this->web_slug = $web_slug;
        $this->api_slug = $api_slug;
        add_action("init", array($this, "add_rewrite_rule"));
        $this->boot_tables_provider();
        $this->boot_loader();
        $this->boot_service_provider();
        $this->boot_cron_provider();
    }

    public function add_rewrite_rule() {
        add_rewrite_rule("^{$this->api_slug}/([^/]*)/?([^/]*)/?([^/]*)/?$", 'index.php?vos_module=$matches[1]&vos_action=$matches[2]&vos_params=$matches[3]', "top");
        add_rewrite_tag("%vos_module%", "([^/]*)");
        add_rewrite_tag("%vos_action%", "([^/]*)");
        add_rewrite_tag("%vos_params%", "([^/]*)");
    }

    public function boot_loader() {
        require_once trailingslashit(__DIR__) . 'Libraries/Model.php';
        require_once trailingslashit(__DIR__) . 'Models/Viewer.php';
        require_once trailingslashit( __DIR__ ) . 'Models/SearchExpression.php';
    }

    public function boot_cron_provider(){
        $event_queue = [
            'daily' => 'woap_report_daily_cron',
        ];
        require_once trailingslashit(__DIR__) . 'Providers/CronProvider.php';
        $cron_provider = new CronProvider($event_queue);
    }

    public function boot_tables_provider(){
        $database_services = array(
            \VOS\Tables\SearchExpressionTable::class => trailingslashit(__DIR__) . "Tables/SearchExpressionTable.php",
            \VOS\Tables\ViewerTable::class => trailingslashit(__DIR__) . "Tables/ViewerTable.php",
            \VOS\Tables\ViewermetaTable::class => trailingslashit(__DIR__) . "Tables/ViewermetaTable.php",
        );
        $database_version = 1.0;
        require_once trailingslashit(__DIR__) . "Providers/TableProvider.php";
        new TableProvider($database_services,$database_version);
    }

    public function boot_service_provider() {
        # Services
        $services = array(
            #Logic
            \VOS\Controllers\Logic\PaginationLogic::class => trailingslashit(__DIR__) . 'Controllers/Logic/PaginationLogic.php',
            \VOS\Controllers\Logic\ViewerLogic::class => trailingslashit(__DIR__) . 'Controllers/Logic/ViewerLogic.php',
            \VOS\Controllers\Logic\ViewLogic::class => trailingslashit(__DIR__) . 'Controllers/Logic/ViewLogic.php',
            #Web
            \VOS\Controllers\Web\ViewController::class => trailingslashit(__DIR__) . 'Controllers/Web/ViewController.php',
            \VOS\Controllers\Web\ViewerController::class => trailingslashit(__DIR__) . 'Controllers/Web/ViewerController.php',
            \VOS\Controllers\Web\NotificationController::class => trailingslashit(__DIR__) . 'Controllers/Web/NotificationController.php',
            \VOS\Controllers\Web\ReportController::class => trailingslashit(__DIR__) . 'Controllers/Web/ReportController.php',
            \VOS\Controllers\Web\SearchExpressionController::class => trailingslashit(__DIR__) . 'Controllers/Web/SearchExpressionController.php',
            #Api
            //\VOS\Controllers\Api\Product::class => trailingslashit(__DIR__) . "Controllers/Api/Product.php",
        );
        $api_mapper = array(
            //"Product" => \VOS\Controllers\Api\Product::class,
        );
        # BootServices
        $this->app_service_providers($services, $api_mapper);
    }

    public function app_service_providers($services, $api_mapper) {
        require_once trailingslashit(__DIR__) . "Providers/ServiceProvider.php";
        require_once trailingslashit(__DIR__) . "Providers/ApiProvider.php";
        $service_provider = new ServiceProvider($services);
        new ApiProvider($service_provider,$api_mapper);
    }

}
