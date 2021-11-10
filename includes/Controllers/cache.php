<?php

namespace STORINA\Controllers;

use \STORINA\Controllers\General;
use \STORINA\Controllers\Index;

defined('ABSPATH') || exit;

class Cache {

    public $index_object;
    public $service_container;

    public function __construct($service_container) {
        $this->service_container = $service_container;
        $this->index_object = $this->service_container->get(Index::class);
    }

    public function setCache($json, $type, $itemID, $page = null, $param = null) {
        global $wpdb;
        if ($page) {
            $itemID = $itemID . '/' . $page . $param;
        }
        $table = $wpdb->prefix . 'OSA_cache';
        $Record = $wpdb->get_row("SELECT * FROM $table WHERE type = '$type' AND itemID = '$itemID'");
        if (!$Record) {
            $wpdb->insert(
                    $table,
                    array(
                        'itemID' => $itemID,
                        'json' => $json,
                        'type' => $type
                    ),
                    array(
                        '%s',
                        '%s',
                        '%s'
                    )
            );
        }
    }

    public function getCache($type, $itemID, $page = null, $param = null) {
        global $wpdb;
        if ($page) {
            $itemID = $itemID . '/' . $page . $param;
        }
        $result = '';
        $general = $this->service_container->get(General::class);
        $table = $wpdb->prefix . 'OSA_cache';
        $Record = $wpdb->get_row("SELECT * FROM $table WHERE type = '$type' AND itemID = '$itemID'");
        if (!$Record) {
            return false;
        }
        if ($type == 'index') {
            $index = json_decode($Record->json, true);
            $this->update_featured_sale_to($index);
            $index['data']['cartCount'] = count($general->get_items());
            $currentVersion = @sanitize_text_field($_POST['currentVersion']);
            if (floatval($currentVersion) < floatval(storina_get_option('app_version'))) {
                $uploadModel = array(
                    "new_app_version" => storina_get_option('app_version'),
                    "description" => explode(PHP_EOL, storina_get_option('app_versionText')),
                    "isForce" => ( storina_get_option('app_UpdateFource') == 'true' ) ? true : false,
                    "url" => storina_get_option('app_url'),
                );
            } else {
                $uploadModel = array();
            }
            $index['data']['appInfo'] = (object) $uploadModel;
            $result = ( $index );
        } elseif ($type == 'single') {
            $single = json_decode($Record->json, true);

            $stock_quantity = intval(get_post_meta($itemID, '_stock', true));
            $stock_quantity = ( $stock_quantity ) ? $stock_quantity : 0;
            if (get_post_meta($itemID, '_manage_stock', true) AND get_post_meta($itemID, '_stock_status', true) == 'outofstock') {

                if (get_post_meta($itemID, '_stock', true) > 0) {
                    $single['data']['in_stock'] = true;
                    $single['data']['stock_quantity'] = $stock_quantity;
                } else {
                    $single['data']['in_stock'] = false;
                    $single['data']['stock_quantity'] = $stock_quantity;
                }
            } else {
                //echo get_post_meta($pID,'_stock_status',true);
                if (get_post_meta($itemID, '_stock_status', true) == 'instock') {
                    $single['data']['in_stock'] = true;
                    $single['data']['stock_quantity'] = $stock_quantity;
                } else {
                    $single['data']['in_stock'] = false;
                    $single['data']['stock_quantity'] = $stock_quantity;
                }
            }
            $sold_ind = get_post_meta($itemID, '_sold_individually', true);
            if ($sold_ind == 'yes') {
                $single['data']['stock_quantity'] = 1;
            }
            $sales_price_to = get_post_meta($itemID, '_sale_price_dates_to', true);
            $sales_price_from = get_post_meta($itemID, '_sale_price_dates_from', true);
            $now = time();
            if ($sales_price_from > 0) {
                if ($now > $sales_price_from AND $now < $sales_price_to) {
                    $final['sale_price_dates_to'] = $sales_price_to;
                    $final['sale_price_dates_from'] = $sales_price_from;
                }
            }

            if ($sales_price_to) {
                $index_object = $this->service_container->get(Index::class);
                $single['data']['different'] = $this->index_object->extract_time_with_now($sales_price_to);
            }
            $result = $single;
        }

        return $result;
    }

    public function update_featured_sale_to(&$index) {
        if (isset($index['data']['home'][2]) && "featured" == $index['data']['home'][2]["type"]) {
            foreach ($index['data']['home'][2]['data'] as &$item) {
                $sales_price_to = get_post_meta($item['id'], '_sale_price_dates_to', true);
                $item['different'] = $this->index_object->extract_time_with_now($sales_price_to);
            }
        }
    }

}
