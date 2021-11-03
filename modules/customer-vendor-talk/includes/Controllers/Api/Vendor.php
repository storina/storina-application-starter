<?php 

namespace CRN\Controllers\Api;

use WP_Query;
use CRN\Controllers\Web\UserController;
use \STORINA\Controllers\Index;

class Vendor {

    public $user_controller;
    public $service_provider;

    const product_per_page = 10;

    public function __construct($service_provider){
        $this->service_provider = $service_provider;
        $this->user_controller = $this->service_provider->get(UserController::class);
    }

    public function products(){
        global $osa_autoload;
        $index = $osa_autoload->service_provider->get(Index::class);
        $user_id = $this->user_controller->get_user_by_token(sanitize_text_field($_POST['userToken']));
        $paged = (!empty(sanitize_text_field($_POST['paged'])))? sanitize_text_field($_POST['paged']) : 1;
        if(empty($user_id)){
            return(array(
                "status" => false,
                "message" => esc_html__("user not founded","crn")
            ));
        }
        $args = array(
            'posts_per_page' => self::product_per_page,
            'paged'          => $paged,
            'author'         => $user_id,
            'post_type'      => array('product'),
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => apply_filters( 'dokan_product_listing_exclude_type', array() ),
                    'operator' => 'NOT IN',
                ),
            ),
        );
        $args_filtered = apply_filters("crn_vendor_products_filters",$args);
        $wp_query = new WP_Query($args_filtered);
        if($wp_query->have_posts()){
            while($wp_query->have_posts()){
                $wp_query->the_post();
                $product = wc_get_product(get_the_ID());
                //INDEX
                $product_info['id'] = get_the_ID();
                $product_info['title'] = html_entity_decode(get_the_title());
                $product_info['thumbnail'] = (has_post_thumbnail()) ? current(wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'medium')) : STORINA_PLUGIN_URL . "/assets/images/notp.png";
                $product_info['type'] = ( $product->get_type() == 'grouped' OR $product->get_type() == 'simple_catalogue' ) ? 'simple_catalogue' : $product->get_type();
                $prices = $index->filter_prices($product);
                $product_info['regular_price'] = trim($prices['regular_price']);
                $product_info['sale_price'] = trim($prices['sale_price']);
                $product_info['stock_status'] = $product->get_stock_status();
                $product_info['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
                //INDEX
                $data['products'][] = $product_info;
            }
        }else{
            $data['products'] = array();
        }
        wp_reset_postdata();
        return(array(
            "status" => true,
            "data" => $data
        ));
    }
}
