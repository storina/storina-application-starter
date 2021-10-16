<div class="osa-report-sub-section-wrapper">
    <h2 class="osa-report-sub-heading"><?php _e("Online Viewers","onlinerShopApp"); ?></h2>
    <p class="osa-report-sub-description"><?php _e("Online Viewers on applications","onlinerShopApp"); ?></p>
    <div class="osa-report-sub-content-wrapper">
    <div class="osa-preloader"><span class="osa-spinner"></span></div>
        <table class="osa-report-online-viewers-table">
            <thead>
                <tr>
                    <th><?php _e("Displey name","onlinerShopApp") ?></th>
                    <th><?php _e("identifier","onlinerShopApp") ?></th>
                    <th><?php _e("Authentication","onlinerShopApp") ?></th>
                    <th><?php _e("Client Type","onlinerShopApp") ?></th>
                    <th><?php _e("Current Version","onlinerShopApp") ?></th>
                    <th><?php _e("Send Notif","onlinerShopApp") ?></th>
                </tr>
            </thead>
            <tbody id="osa-report-viewer-rows">
                <?php 
                if(!empty($viewers_data)){
                    require_once trailingslashit( __DIR__ ) . 'online-viewers-rows.php';
                }else{
                    ?>
                    <tr>
                        <td colspan="5"><?php _e("No Viewer has online","onlinerShopApp"); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <div class="osa-report-viewers-pagination-wrapper">
            <?php
            $from = $pagination['from'];
            $to = $pagination['to'];
            $current = $pagination['current'];
            if($to > $from) {
                echo '<ul class="osa-report-viewers-pagination">';
                for($i=$from;$i<=$to;$i++){
                    $disabled = ($i == $current)? 'disabled' : "";
                    ?>
                        <li>
                            <a href="#" class="osa-report-viewers-pagination-item <?php echo $disabled; ?>" data-paged="<?php echo $i; ?>" ><?php echo $i; ?></a>
                        </li>
                    <?php
                }
                echo '</ul>';
            }
            ?>
        </div>
    </div>
</div>