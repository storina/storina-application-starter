<?php 

namespace WOAP\Controllers\Web;

class ModulesController {
    
    public $boostraps;
    public $modules;

    public function __construct(){
        $this->bootstraps = [
            [            
                'class' => \WOAP\Init::class,
                'path' => trailingslashit(STORINA_PDP) . 'modules/woap-builder-service/bootstrap.php',
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
