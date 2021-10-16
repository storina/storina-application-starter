<div class="osa-report-sub-section-wrapper">
        <h2 class="osa-report-sub-heading"><?php _e("Product Most viewed","onlinerShopApp"); ?></h2>
        <p class="osa-report-sub-description"><?php _e("the product most was viewed from application","onlinerShopApp"); ?></p>
        <div class="osa-report-most-views-products-wrapper osa-report-sub-content-wrapper">
        <?php 
        if(!empty($most_views_products)){
            ?>
            <ul class="osa-report-most-views-products">
                <?php 
                foreach($most_views_products as $product){
                    echo '<li class="osa-report-most-views-products-item">' . $product['name'] . ' (' . $product['count'] . ')</li>';
                }
                ?>
            </ul>
            <?php
        }
        ?>
        </div>
    </div>