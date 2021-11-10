<div class="wrap">
    <div class="woap-options-wrapper">
        <nav class="woap-options-section-wrapper">
        <?php 
        foreach($sections as $id => $label){
            $section_id = "woap-options-{$id}_section";
            $href = add_query_arg([
                'section' => $id
            ]);
            $active_class = ($id == $active_section_value)? "active" : "";
            ?>
            <a class="<?php echo "woap-option-nav-section {$active_class}"; ?>" id="<?php echo esc_att($section_id); ?>" href="<?php echo $href; ?>"><?php echo $label; ?></a>
            <?php
        }
        ?>
        </nav>
        <!-- .woap-options-section-wrapper -->
        <div class="wrap woap-options-content-wrapper">
            <?php do_action("woap_options_configuration_build_{$active_section_value}_content"); ?>
        </div>
        <!-- .woap-options-content-wrapper -->
    </div>
    <!-- .woap-options-wrapper -->
</div>
<!-- .wrap -->
