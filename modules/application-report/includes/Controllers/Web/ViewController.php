<?php

namespace VOS\Controllers\Web;

use VOS\Controllers\Logic\ViewLogic;

class ViewController {

    public $service_container;
    public $view_logic;

    const postviews_key = 'osa_app_report_postviews';
    const daily_postviews_key = 'osa_app_report_daily_postviews';

    public function __construct($service_container){
        $this->service_container = $service_container;
        $this->view_logic = $this->service_container->get(ViewLogic::class);
        add_action('osa_single_get_action_init',array($this,'set_product_view'),10,2);
        add_action('woap_report_daily_cron',[$this,'trigger_day_cron']);
    }

    public function set_product_view($product_id,$user_id){
        $postmeta_arr = [
            self::postviews_key => get_post_meta($product_id,self::postviews_key,true),
        ];
        $options_arr = [
            self::daily_postviews_key => get_option(self::daily_postviews_key,true)
        ];
        $this->view_logic->set_post_meta($product_id,$postmeta_arr);
        $this->view_logic->set_option($options_arr);
    }

    public function trigger_day_cron(){
        for($i=6;$i>0;$i--){
            $origin_index = $i - 1;
            $destination_index = $i;
            $origin_name = (0 == $origin_index)? self::daily_postviews_key : self::daily_postviews_key . "_{$origin_index}";
            $destination_name =  self::daily_postviews_key . "_{$destination_index}";
            $origin_value = storina_get_option($origin_name);
            $origin_value_validated = (empty($origin_value))? 0 : $origin_value;
            storina_update_option($destination_name,$origin_value_validated);
        }
        update_option(self::daily_postviews_key,0);
    }

}
