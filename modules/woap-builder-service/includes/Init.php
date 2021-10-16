<?php

namespace WOAP;

use WOAP\Packages\ServiceContainer;
use WOAP\Packages\Router;
use WOAP\Packages\TableProvider;

defined('ABSPATH') || exit;

class Init {
    
    const first_flush_option = "first_flush_permalinks";

    public $version;
    public $web_slug;
    public $api_slug;

    public function __construct($version, $web_slug, $api_slug) {
        $this->version = $version;
        $this->web_slug = $web_slug;
        $this->api_slug = $api_slug;
        add_action("admin_notices", array($this, "first_flush_notice"));
        add_action("update_option_permalink_structure", function() {
            update_option(self::first_flush_option, true);
        });
		$this->boot_modules();
		$this->service_container = $this->boot_services();
		$this->route_init($this->service_container);
    }

    public function boot_modules() {
		require_once trailingslashit(__DIR__) . 'Packages/Stack.php';
		require_once trailingslashit(__DIR__) . 'Packages/Request.php';
		require_once trailingslashit(__DIR__) . 'Packages/Model.php';
		require_once trailingslashit(__DIR__) . 'Packages/Controller.php';
    }

    public function first_flush_notice() {
        if (get_option(self::first_flush_option)) {
            return;
        }
        ?>
        <div class="error">
            <p>
                <?php esc_html_e("To make the api-boilerplate plugin worked Please first "); ?>
                <a href="<?php echo get_admin_url(); ?>/options-permalink.php" title="<?php esc_attr_e("Permalink Settings") ?>" >
                    <?php esc_html_e("Flush rewrite rules"); ?>
                </a>
            </p>
        </div>
        <?php
    }

	public function boot_services(){
		require_once trailingslashit(__DIR__) . 'Packages/ServiceContainer.php';
		$services = [
			Controllers\Logic\BuildLogic::class => trailingslashit(__DIR__) . 'Controllers/Logic/BuildLogic.php',
			Controllers\Logic\HttpRequest::class => trailingslashit(__DIR__) . 'Controllers/Logic/HttpRequest.php',
			Controllers\Web\BuildController::class => trailingslashit(__DIR__) . 'Controllers/Web/BuildController.php',
			Controllers\Api\Build::class =>  trailingslashit(__DIR__) . 'Controllers/Api/Build.php',
		];
		return new ServiceContainer($services);
	}

	public function route_init($service_container=null){
		require_once trailingslashit(__DIR__) . 'Packages/Router.php';
		$serviec_container = $service_container ?: $this->service_container;	
		$route_mapping = [
			'WOAPApi' => [
				'namespace' => 'WOAP\Controllers\Api',
				'type' => 'api',
			],	
		];
		$router = new Router($service_container,$route_mapping);
	}

}
