<?php
if (!current_user_can('manage_options')) {
    return;
}
wp_enqueue_style('woap-iran-yekan',trailingslashit(WOAP_PDU) . "assets/css/iran-yekan.css");
wp_enqueue_style('osa-report-admin-style',trailingslashit( VOS_PDU ) . 'admin/assets/css/style.css');
wp_enqueue_script('osa-report-chartjs-library', trailingslashit( VOS_PDU ) . 'admin/assets/js/chart.js', ['jquery'], true, 1);
wp_enqueue_script('osa-report-main-script', trailingslashit( VOS_PDU ) . 'admin/assets/js/script.js', ['jquery','osa-report-chartjs-library'], true, 1);
wp_localize_script('osa-report-main-script','ReportOBJ',[
    'singleViewLabel' => __("Product Views Count","onlinerShopApp"),
    'viewsPeriod' => $views_period,
    'viewsCount' => $views_count,
    'adminAjax' => $admin_ajax,
    'viewerAction' => $viewer_action,
    'notificationAction' => $notification_action
]);
?>
<div class="wrapper vos-report-panel-wrapper">
    <h1 class="osa-report-main-page-title"><?php echo get_admin_page_title(); ?></h1>
    <p class="osa-report-main-page-description"><?php _e("Application products view chart , Online user status , Most search expressions","onlinerShopApp") ?></p>
    <?php 
    require_once trailingslashit( VOS_ADM ) . 'panels/viewes-chart.php';
    require_once trailingslashit( VOS_ADM ) . 'panels/most-viewes-products.php';
    require_once trailingslashit( VOS_ADM ) . 'panels/online-viewers.php';
    require_once trailingslashit( VOS_ADM ) . 'panels/search-expressions.php';
    ?>
</div>