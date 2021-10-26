<?php

namespace VOS\Controllers\Web;

use \STORINA\Libraries\JDate;
use VOS\Models\Viewer;
use VOS\Models\SearchExpression;
use VOS\Controllers\Logic\ViewerLogic;
use VOS\Controllers\Logic\PaginationLogic;

class ReportController {

    public $service_container;
    public $pagination_logic;
    public $viewer_logic;
    public $notification_controller;

    const viewer_action = 'osa_report_viewer_paginate';

    public function __construct($service_container){
        $this->service_container = $service_container;
        $this->notification_controller = $service_container->get(NotificationController::class);
        $this->pagination_logic = $this->service_container->get(PaginationLogic::class);
        $this->viewer_logic = $this->service_container->get(ViewerLogic::class);
        add_action('osa_admin_menu_application_main',[$this,'application_report_panel']);
        add_action('wp_ajax_' . self::viewer_action ,[$this,'viewer_pagination_action']);
    }

    public function viewer_pagination_action(){
        $paged = (int) $_POST['paged'];
        if(!is_numeric($paged)){
            wp_send_json([
                'status' => false,
                'messages' => __("viewer paginate not working","onlinerShopApp")
            ]);
        }
        $pagination_args = $this->pagination_logic->get_pagination_args($paged);
        $viewers = Viewer::get_online_viewers($pagination_args['limit'],$pagination_args['offset']);
        $viewers_data = $this->viewer_logic->prepare_viewers_data($viewers);
        ob_start();
        require_once trailingslashit( VOS_ADM ) . 'panels/online-viewers-rows.php';
        $html = ob_get_clean();
        wp_send_json([
            'status' => true,
            'html' => $html
        ]);
    }

    public function application_report_panel(){
        add_submenu_page('ONLINER_options', __('Application Report', 'onlinerShopApp'), __('Application Report', 'onlinerShopApp'), 'manage_options', 'onliner-report-panel', [$this,'application_report_contents']);
    }

    public function application_report_contents(){
        extract($this->application_views_params());
        extract($this->application_most_views_products());
        extract($this->application_viewers());
        extract($this->application_search_expressions());
        require_once trailingslashit(VOS_ADM) . "report-panel.php";
    }

    public function application_views_params(){
        $views_period = $views_count = [];
        $views_count_name_prefix = ViewController::daily_postviews_key;
        for($i=0;$i<7;$i++){
            $active_time = (0 == $i)? time() : $active_time - 86400;
            $views_period[] = JDate::jdate('y/m/d',$active_time,'','Asia/Tehran','en');
            $views_count_index = (0 == $i)? "" : "_{$i}";
            $views_count[] = osa_get_option($views_count_name_prefix . $views_count_index);
        }
        return [
            'views_period' => array_reverse($views_period),
            'views_count' => array_reverse($views_count)
        ];
    }

    public function application_most_views_products(){
        $meta_key = ViewController::postviews_key;
        $query_args = [
            'post_type' => 'product',
            'post_per_pages' => 20,
            'orderby' => 'meta_value_num',
            'meta_key' => $meta_key,
        ];
        $products = get_posts($query_args);
        foreach($products as $product){
            $product_id = $product->ID;
            $product_view = get_post_meta($product_id,$meta_key,true);
            $output['product_id'] = $product_id;
            $output['name'] = $product->post_title;
            $output['count'] = $product_view;
            $most_viewes_products[] = $output;
        }
        return [
            'most_views_products' => $most_viewes_products
        ];
    }

    public function application_viewers(){
        $founded_viewers = Viewer::get_online_viewers_count();
        $pagination_params = $this->pagination_logic->get_pagination_params(1,min([50,$founded_viewers]));
        $pagination_args = $this->pagination_logic->get_pagination_args(1);
        $viewers = Viewer::get_online_viewers($pagination_args['limit'],$pagination_args['offset']);
        $viewers_data = $this->viewer_logic->prepare_viewers_data($viewers);
        $notification_click_event_list = $this->notification_controller->get_notification_handler()->clickEventList();
        return [
            'notification_click_event_list' => $notification_click_event_list,
            'viewers_data' => $viewers_data,
            'pagination' => $pagination_params,
            'admin_ajax' => admin_url("admin-ajax.php"),
            'viewer_action' => self::viewer_action,
            'notification_action' => NotificationController::notification_action
        ];
    }

    public function application_search_expressions(){        
        $search_expressions = SearchExpression::search_expressions_orderby_count(25);
        if(!empty($search_expressions)){
            foreach($search_expressions as $search_expression){
                $expressions[] = $search_expression->get_data();
            }
        }
        return [
            'expressions' => $expressions ?? []
        ];
    }

}
