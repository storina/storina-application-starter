<?php
add_filter( 'woocommerce_product_tabs', function ( $tabs ) {
    unset( $tabs['more_seller_product'] );  	// Remove the additional information tab
    return $tabs;
} , 98 );

// Display Fields
add_action( 'woocommerce_product_options_general_product_data', function () {

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

} );

// Save Fields
add_action( 'woocommerce_process_product_meta', function ( $post_id ){
    $woocommerce_text_field = sanitize_text_field($_POST['_subtitle']);
    if( !empty( $woocommerce_text_field ) )
        update_post_meta( $post_id, '_subtitle', esc_attr( $woocommerce_text_field ) );
} );

