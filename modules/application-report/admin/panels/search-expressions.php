<div class="osa-report-sub-section-wrapper">
    <h2 class="osa-report-sub-heading"><?php _e("Search Expression","onlinerShopApp"); ?></h2>
    <p class="osa-report-sub-description"><?php _e("Most search expressions was searched by users","onlinerShopApp"); ?></p>
    <div class="osa-report-search-expressions-wrapper osa-report-sub-content-wrapper">
    <?php 
    if(!empty($expressions)){
        ?>
        <ul class="osa-report-search-expressions">
            <?php 
            foreach($expressions as $expression){
                echo '<li class="osa-report-search-expressions-item">' . $expression['expression'] . ' (' . $expression['count'] . ')</li>';
            }
            ?>
        </ul>
        <?php
    }
    ?>
    </div>
</div>