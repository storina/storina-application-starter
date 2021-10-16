<?php
add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs2', 98 );
function woo_remove_product_tabs2( $tabs ) {
    //unset( $tabs['description'] );      	// Remove the description tab
    //unset( $tabs['additional_information'] );  	// Remove the additional information tab
    //unset( $tabs['seller'] );  	// Remove the additional information tab
    unset( $tabs['more_seller_product'] );  	// Remove the additional information tab
    return $tabs;
}

// Display Fields
add_action( 'woocommerce_product_options_general_product_data', 'woo_add_custom_general_fields2' );

// Save Fields
add_action( 'woocommerce_process_product_meta', 'woo_add_custom_general_fields_save2' );
function woo_add_custom_general_fields2() {

    global $woocommerce, $post;

    echo '<div class="options_group">';

    // Text Field
    woocommerce_wp_text_input(
        array(
            'id'          => '_subtitle',
            'label'       => __('secondary title for show in app','onlinerShopApp'),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => ''
        )
    );

    echo '</div>';

}
function woo_add_custom_general_fields_save2( $post_id ){

// Text Field
    $woocommerce_text_field = $_POST['_subtitle'];
    if( !empty( $woocommerce_text_field ) )
        update_post_meta( $post_id, '_subtitle', esc_attr( $woocommerce_text_field ) );
}

