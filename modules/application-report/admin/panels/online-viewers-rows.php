<?php
foreach($viewers_data as $viewer){
    echo '<tr>';
        echo '<td>' . $viewer['display_name'] . '</td>';
        echo '<td>' . $viewer['identifier'] . '</td>';
        echo '<td>' . $viewer['authentication'] . '</td>';
        echo '<td>' . $viewer['client_type'] . '</td>';
        echo '<td>' . $viewer['current_version'] . '</td>';
        ?>
        <td>
            <!-- Trigger-modal -->
            <a 
            href="#" 
            data-modal="osa-modal-content-<?php echo $viewer['identifier']; ?>" 
            class="button button-primary osa-modal-button"><?php _e("send notification","onlinerShopApp") ?></a>
            <!-- The Modal -->
            <div id="osa-modal-content-<?php echo $viewer['identifier']; ?>"  class="osa-modal-content">
                <!-- Modal content -->
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="modal-close">&times;</span>
                        <h2><?php _e("Send Notification","onlinerShopApp"); ?></h2>
                    </div>
                    <div class="modal-body"><?php require trailingslashit( __DIR__ ) . 'online-viewers-rows-notif.php'; ?></div>
                </div>
                <!-- .modal-content -->
            </div>
            <!-- .modal -->
        </td>
        <?php
    echo '</tr>';
}