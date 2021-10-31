<?php

namespace STORINA\Controllers;

use \STORINA\Controllers\User;
use \STORINA\Controllers\Cart;
use \STORINA\Controllers\General;

defined('ABSPATH') || exit;

class Terawallet {

    public $user;
    public $cart;
    public $wpdb;
    public $service_container;

    const id = 'wallet';
    const cart_table = "OSA_cart";
    const cartmeta_table = "OSA_cartmeta";
    const meta_key = "OSA_apply_terawallet";

    public function __construct($service_container) {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->service_container = $service_container;
        $this->user = $this->service_container->get(User::class);
        $this->cart = $this->service_container->get(Cart::class);
        add_filter("osa_user_dologin_result", array($this,"update_user_wallet_login"),10,2);
        add_filter("osa_user_verify_result",array($this,"update_user_wallet_verify"),10,2);
        add_filter('woap_order_process_handle',[$this,'apply_wallet_order_process'],10,3);
    }

    public function update_user_wallet_login($result, $user_id){
        $woo_ballance = (function_exists("woo_wallet")) ? $this->get_wallet_ballance($user_id) : null;
        if(isset($woo_ballance)){
            $result['data']['user']['woo_ballance'] = $woo_ballance;
        }
        return $result;
    }

    public function update_user_wallet_verify($result,$user_id){
        $woo_ballance = (function_exists("woo_wallet")) ? $this->get_wallet_ballance($user_id) : null;
        if(isset($woo_ballance)){
            $result['user']['woo_ballance'] = $woo_ballance;
        }
        return $result;
    }

    /**
     * increase wallet charge transaction
     * @return JSON
     */
    public function increase_ballance() {
        $wallet_balance = (int) sanitize_text_field($_POST['woo_wallet_balance_to_add']);
        $paymentMethod = sanitize_text_field($_POST['paymentMethod']);
        $user_token = sanitize_text_field($_POST['userToken']);

        $user_id = $this->user->get_userID_byToken($user_token);
        if (!$user_id) {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => __('Token is invalid', 'onlinerShopApp')
                )
            );
            return ( $result );
        }

        if (isset($wallet_balance) && !empty($wallet_balance)) {

            $is_valid = $this->is_valid_wallet_recharge_amount($wallet_balance);
            if (!$is_valid['is_valid']) {
                $result = array(
                    'status' => false,
                    'error' => array(
                        'errorCode' => - 14,
                        'message' => $is_valid['message'],
                    )
                );

	            return ( $result );
            }
        }

        $product = get_wallet_rechargeable_product();
        if (!$product) {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 16,
                    'message' => "کیف پول یافت نشد",
                )
            );

	        return ( $result );
            return;
        }
        $product->set_price($wallet_balance);

        $order = wc_create_order();
        $order->add_product($product);
        $order->set_customer_id($user_id);
        update_post_meta($order->id, 'purchase_type', 'app');

        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

        update_post_meta($order->id, '_payment_method', $paymentMethod);
        update_post_meta($order->id, '_payment_method_title', $available_gateways[$paymentMethod]->title);
        WC()->session->order_awaiting_payment = $order->id;

        $order->calculate_totals();
        $order->update_status('pending');
        $pending_result = $available_gateways[$paymentMethod]->process_payment($order->id);
        $data = WC()->checkout->get_posted_data();
        do_action('woocommerce_checkout_create_order', $order, $data);
        $order_id = $order->save();
        do_action('woocommerce_checkout_update_order_meta', $order_id, $data);
        do_action('woocommerce_before_checkout_process');
        do_action('woocommerce_checkout_process');
        if (class_exists('WoocommerceIR_SMS_Orders')) {
            $sms_order = new WoocommerceIR_SMS_Orders();
            $sms_order->sendOrderSms($order_id);
        }

        if ($pending_result['result'] !== 'success') {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 18,
                    'message' => __('something wrong happen.', 'onlinerShopApp')
                )
            );

	        return ( $result );
            return;
        }

        update_post_meta($order->id, 'pay_link', $pending_result['redirect']);
        WC()->session->destroy_session();

        $result = array(
            'status' => true,
            'data' => array(
                'status' => $pending_result['result'],
                'orderID' => intval($order->id),
                'redirect' => $pending_result['redirect'],
            )
        );

	    return ( $result );
    }

    /**
     * check wallet charge amount using plugin options
     * @param int $amount
     * @return JSON
     */
    public function is_valid_wallet_recharge_amount($amount = 0) {
        $response = array('is_valid' => true);

        $min_topup_amount = (int) woo_wallet()->settings_api->get_option('min_topup_amount', '_wallet_settings_general', 0);
        $max_topup_amount = (int) woo_wallet()->settings_api->get_option('max_topup_amount', '_wallet_settings_general', 0);
        if ($min_topup_amount && $amount < $min_topup_amount) {
            $currency = get_woocommerce_currency_symbol();
            $response = array(
                'is_valid' => false,
                'message' => sprintf(__('The minimum amount needed for wallet top up is %s', 'woo-wallet'), "{$min_topup_amount} {$currency}"),
            );
        }
        if ($max_topup_amount && $amount > $max_topup_amount) {
            $currency = get_woocommerce_currency_symbol();
            $response = array(
                'is_valid' => false,
                'message' => sprintf(__('Wallet top up amount should be less than %s', 'woo-wallet'), "{$max_topup_amount} {$currency}")
            );
        }
        if ($min_topup_amount && $max_topup_amount && ( $amount < $min_topup_amount || $amount > $max_topup_amount )) {
            $currency = get_woocommerce_currency_symbol();
            $response = array(
                'is_valid' => false,
                'message' => sprintf(__('Wallet top up amount should be between %s and %s', 'woo-wallet'), "{$min_topup_amount} {$currency}", "{$max_topup_amount} {$currency}")
            );
        }

        return apply_filters('woo_wallet_is_valid_wallet_recharge_amount', $response, $amount);
    }

    /**
     * get cart meta value
     * @param int $cart_id
     * @param string $meta_key
     * @return string
     */
    function get_cart_meta($cart_id, $meta_key = false) {
        if (isset($meta_key)) {
            $meta_key = self::meta_key;
        }
        $cartmeta = $this->wpdb->prefix . self::cartmeta_table;
        return $this->wpdb->get_var($this->wpdb->prepare("SELECT meta_value FROM {$cartmeta} WHERE cart_id=%d AND meta_key=%s", $cart_id, $meta_key));
    }

    public function delete_cart_meta($cart_id, $meta_key = false) {
        if (!isset($meta_key) or empty($meta_key)) {
            $meta_key = self::meta_key;
        }
        $cartmeta = $this->wpdb->prefix . self::cartmeta_table;
        $where = array("cart_id" => (int) $cart_id, "meta_key" => (string) $meta_key);
        $where_format = array("%d", "%s");
        $result = $this->wpdb->delete($cartmeta, $where, $where_format);
        return $result;
    }

    /**
     * update cart meta
     * @param int $cart_id
     * @param string $meta_key
     * @param string $meta_value
     * @return Object
     */
    function update_cart_meta($cart_id, $meta_key, $meta_value) {
        $cartmeta = $this->wpdb->prefix . self::cartmeta_table;
        $count = $this->wpdb->get_var($this->wpdb->prepare("SELECT COUNT(*) FROM {$cartmeta} WHERE cart_id = %d AND meta_key = %s", $cart_id, $meta_key));
        if (!empty($count)) {
            $data = array("meta_value" => $meta_value);
            $where = array('cart_id' => $cart_id, 'meta_key' => $meta_key);
            $data_format = array('%s');
            $where_format = array('%d', '%s');
            return $this->wpdb->update($cartmeta, $data, $where, $data_format, $where_format);
        }

        $data = array('cart_id' => $cart_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value);
        $format = array('%d', '%s', '%s');
        return $this->wpdb->insert($cartmeta, $data, $format);
    }

    /**
     * get cart id using google ID
     * @param string $google_id
     * @return int
     */
    public function get_cart_id_by_googleID($google_id) {
        $cart = $this->wpdb->prefix . self::cart_table;
        return $this->wpdb->get_var($this->wpdb->prepare("SELECT id from {$cart} where googleID = %s", $google_id));
    }

    /**
     * get ballance of wallet
     * @param int $user_id
     */
    public function get_wallet_ballance($user_id) {
        return (int) str_replace(",", "", woo_wallet()->wallet->get_wallet_balance($user_id, false));
    }

    /**
     * set meta key for cart named apply charge and return applyed charge cart total
     * @return JSON
     */
    public function trigger_charge($type) {
        $google_id = sanitize_text_field($_POST['googleID']);
        $user_token = sanitize_text_field($_POST['userToken']);
        //get cart id
        $cart_id = $this->get_cart_id_by_googleID($google_id);
        if (!$cart_id) {
	        return ( array(
                'status' => false,
                'message' => 'کارت یافت نشد.'
            ));
            return;
        }
        //get user id
        $user_id = $this->user->get_userID_byToken($user_token);
        if (!$user_id) {
	        return ( array(
                'status' => false,
                'message' => 'کاربر یافت نشد.',
            ));
            return;
        }
        //get wallet balance
        $wallet_ballance = $this->get_wallet_ballance($user_id);
        $cart = $this->cart->get_cart();
        $cart_total = $cart['total']['total'];

        if ($wallet_ballance <= 0) {
	        return ( array(
                'status' => false,
                'message' => 'کیف پول خالی است',
            ));
            return;
        }
        if ($type == "apply") {
            $this->update_cart_meta($cart_id, self::meta_key, 1);
            $applyed_cart_subtotal = (int) intval($cart_total) - intval($wallet_ballance);
            $applyed_cart = (int) (intval($cart_total) - intval($wallet_ballance)) > 0 ? $wallet_ballance : $cart_total;

            $result = array(
                'status' => true,
                'cart_total' => max($applyed_cart_subtotal, 0),
                'applyed_ballance' => $applyed_cart,
            );

	        return ( $result );
        }
        $this->update_cart_meta($cart_id, self::meta_key, 0);
        $result = array(
            'status' => true,
            'cart_total' => $cart_total,
            'applyed_ballance' => 0,
        );

	    return ( $result );
    }

    /**
     * ballance (increase or decrease) wallet programmatically
     * @param array $params
     * @return int
     */
    public function wallet_ballance($params) {
        $params['details'] = isset($params['details']) ? $params['details'] : '';
        $transaction_id = false;
        if ('credit' === $params['type']) {
            $transaction_id = woo_wallet()->wallet->credit($params['id'], $params['amount'], $params['details']);
        } else if ('debit' === $params['type']) {
            $transaction_id = woo_wallet()->wallet->debit($params['id'], $params['amount'], $params['details']);
        }
        return $transaction_id;
    }

    /**
     * Add a discount to an Orders programmatically
     * (Using the FEE API - A negative fee)
     * @param type $order_id
     * @param type $title
     * @param type $amount
     */
    public function wc_order_add_discount($order, $title, $amount) {
        if (is_numeric($order)) {
            $order = wc_get_order($order);
        }
        $total = $order->get_total();
        $item = new WC_Order_Item_Fee();

        $discount = (float) str_replace(' ', '', $amount);
        $discount = (float) str_replace(',', '', $amount);
        $discount = intval($discount > $total ? -$total : -$discount);

        $item->set_name($title);
        $item->set_amount($discount);
        $item->set_total($discount);
        $item->save();

        $order->add_item($item);
        $order->calculate_totals();
        $order->save();
    }

    /**
     * check wallet apply meta value
     * @param type $trigger_wallet
     * @return boolean
     */
    public function check_wallet_key($trigger_wallet) {
        return $trigger_wallet !== 0 and ! empty($trigger_wallet) and isset($trigger_wallet);
    }

    /**
     * apply wallet on order total and set apply meta key to ordermeta
     * @param string $google_id
     * @param int $user_id
     * @param object $order
     * @return void
     */
    public function apply_wallet_order_process($order, $user_id, $google_id) {
        if(!function_exists("woo_wallet")){
            return $order;
        }
        $cart_id = $this->get_cart_id_by_googleID($google_id);
        $wallet_balance = $this->get_wallet_ballance($user_id);
        $gateways = WC()->payment_gateways->payment_gateways();
        $wallet_gateway = ($gateways[self::id]) ?? null;
        if(!$this->validate_wallet_payment($cart_id,$user_id,$wallet_balance,$wallet_gateway)){
            return $order;
        }
        if('complate' == $this->check_wallet_payment_type($order,$wallet_balance)){
            //$this->delete_cart_meta($cart_id);
            return $this->order_process_payment_wallet($user_id,$order,$wallet_gateway);
        }
        $order->calculate_totals();
        $order_total_before_discount = $order->get_total();
        $order_id = $order->get_id();
        $wallet_debit_description = __( 'For order payment #', 'woo-wallet' ) . $order->get_order_number();
        $this->wc_order_add_discount($order, $wallet_debit_description, $wallet_balance);
        update_post_meta($order_id, self::meta_key, $wallet_balance);
        return $order;
    }

    public function validate_wallet_payment($cart_id,$user_id,$wallet_balance,$wallet_gateway){
        $trigger_wallet = $this->get_cart_meta($cart_id) ?: null;
        $wallet_enabled = ($wallet_gateway instanceof WC_Payment_Gateway)? $wallet_gateway->enabled : 'no';
        return ($trigger_wallet > 0) && ($wallet_balance > 0) && ('yes' == $wallet_enabled);
    }

    public function check_wallet_payment_type($order,$wallet_balance){
        $order->calculate_totals();
        $order_total = $order->get_total();
        return ($wallet_balance < $order_total)? 'partial' : 'complate';
    }

    public function order_process_payment_wallet($user_id,$order,$wallet_gateway){
        $order_id = $order->get_id();
        $wallet_debit_description = __( 'For order payment #', 'woo-wallet' ) . $order->get_order_number();
        $wallet_response = woo_wallet()->wallet->debit( $user_id, $order->get_total( 'edit' ), $wallet_debit_description);
 
        if ( !$wallet_response ) {
            return $order;
        }
        $order->payment_complete($wallet_response);
        $order->set_payment_method(self::id);
        $order->set_payment_method_title($wallet_gateway->get_method_title());
        wc_reduce_stock_levels( $order_id );
        WC()->cart->empty_cart();
        delete_post_meta($order_id,self::meta_key);
        $order->save();
        return $order;
    }

}
