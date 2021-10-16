<?php 

namespace WOAP\Controllers\Web;

class ModulesController {
    
    public $boostraps;
    public $modules;

    public function __construct(){
        $this->bootstraps = [
            [            
                'class' => \VOS\Init::class,
                'path' => trailingslashit(WOAP_PDP) . 'modules/application-report/bootstrap.php',
                'version' => 1.0,
                'web_slug' => 'vos',
                'api_slug' => 'VOSApi'
            ],
            [            
                'class' => \CRN\Init::class,
                'path' => trailingslashit(WOAP_PDP) . 'modules/customer-vendor-talk/bootstrap.php',
                'version' => 1.0,
                'web_slug' => 'crn',
                'api_slug' => 'CRNApi'
            ],
            [            
                'class' => \WOAP\Init::class,
                'path' => trailingslashit(WOAP_PDP) . 'modules/woap-builder-service/bootstrap.php',
                'version' => 1.0,
                'web_slug' => 'woap',
                'api_slug' => 'WOAPApi'
            ],
        ];
        $this->bootstrap_modules();
    }

    public function bootstrap_modules(){
        $boostraps = $this->bootstraps ?: [];
        if(empty($boostraps)){
            return;
        }
        foreach($boostraps as $module){
            extract($module);
            require_once $path;
            $this->modules[$class] = new $class($version,$web_slug,$api_slug);
        }
        return $this->modules;
    }

}

new ModulesController;
