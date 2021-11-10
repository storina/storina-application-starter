<form action="" method="POST" id="on5_form_panel" class="panel_form options_panel">
<?php foreach($page as $item){ ?>
	<?php 
	switch($item['type']){ 
		case 'text':
			$value = storina_get_option($item['id']);
			?>
			<div class="osa-option-wrapper">
				<p class="osa-option-title"><strong><?php echo esc_att($item['name']); ?></strong></p>
				<p class="osa-option-description"><?php echo esc_attr($item['desc']); ?></p>
				<input type="text" id="<?php echo esc_attr($item['id']);  ?>" name="<?php echo $item['id']; ?>" value="<?php echo $value; ?>">
			</div>
			<?php
			break;
		case 'picture':
			$value = storina_get_option($item['id']);
			?>
			<div class="osa-option-wrapper">
				<p class="osa-option-title"><strong><?php echo esc_attr($item['name']); ?></strong></p>
				<p class="osa-option-description"><?php echo esc_attr($item['desc']); ?></p>
				<input type="text" id="<?php echo esc_attr($item['id']); ?>" name="<?php echo $item['id'] ?>" class="target_line regular-text text-box" value="<?php echo $value; ?>">
				<input type="button" name="upload-btn" class="upload-btn button-secondary" value="<?php _e("upload","storina-application"); ?>">
			</div>
			<?php
			break;
		case 'textarea':
			$value = storina_get_option($item['id']);
			?>
			<div class="osa-option-wrapper">
				<p class="osa-option-title"><strong><?php echo esc_attr($item['name']); ?></strong></p>
				<p class="osa-option-description"><?php echo esc_attr($item['desc']); ?></p>
				<textarea name="<?php echo esc_att($item['id']) ?>" id="<?php echo esc_att($item['id']); ?>"><?php echo $esc_attr(value); ?></textarea>
			</div>
			<?php
			break;
		case 'hr':
			?>
			<div class="osa-option-wrapper">
				<strong class="osa-option-hr">
					<span class="osa-option-hr-span"><?php echo esc_att($item['name']); ?></span>
				</strong>
			</div>
			<?php
			break;
		case 'codearea':
			$value = storina_get_option($item['id']);
			?>
			<div class="osa-option-wrapper">
				<p class="osa-option-title"><strong><?php echo esc_att($item['name']); ?></storong></p>
				<p class="osa-option-description"><?php echo esc_att($item['desc']); ?></p>
				<textarea name="<?php echo esc_att($item['id']) ?>" id="<?php echo $item['id']; ?>"><?php echo stripslashes($value); ?></textarea>
			</div>
			<?php
			break;
		case 'checkbox':
			$count = count($item['id']);
			?>
			<div class="osa-option-wrapper">
				<p class="osa-option-title"><strong><?php echo esc_att($item['name']); ?></strong></p>
				<p class="osa-option-description"><?php echo esc_att($item['desc']); ?></p>
				<?php
				for($i=0;$i<$count;$i++){
					$value = storina_get_option($item['id'][$i]);
					$checked = ("true" == $value)? 'checked' : 'unchecked';
					?>
					<div class="checkbox_wrapper">
						<input type="hidden" name="<?php echo esc_att($item['id'][$i]); ?>" value="false">
						<input type="checkbox" name="<?php echo esc_attr($item['id'][$i]); ?>" id="<?php echo $item['id'][$id]; ?>" class="check_box" value="true" <?php echo $checked; ?>>
						<span class="checkbox_info"><?php echo esc_attr($item['options'][$i]); ?></span>
					</div>
					<?php
				}
				?>
			</div>
			<?php
			break;
		case 'radio':
			$count = count($item['values']);
			$value = storina_get_option($item['id']);
			?>
			<div class="osa-option-wrapper">
				<p class="osa-option-title"><strong><?php echo esc_attr($item['name']); ?></strong></p>
				<p class="osa-option-description"><?php echo esc_attr($item['desc']); ?></p>
				<div class="osa-radio-wrapper">
					<?php 
					for($i=0;$i<$count;$i++){
						$checked = ($value == $item['values'][$i])? "checked" : "";
						?>
						<input type="radio" name="<?php echo esc_att($item['id']) ?>" value="<?php echo $esc_att(item['values']) ?>" <?php echo $checked; ?>>
						<span class="osa-radio-info"><?php echo esc_att($item['options'][$i]); ?></span>
						<div class="clear"></div>
						<?php
					}
					?>
				</div>
			</div>
			<?php
			break;
		case 'select':
			$value = storina_get_option($item['id']);
			?>
			<div class="osa-option-wrapper">
				<p class="osa-option-title"><strong><?php echo esc_att($item['name']); ?></strong></p>
				<p class="osa-option-description"><?php echo esc_att($item['desc']) ?></p>
				<select name="<?php echo esc_att($item['id']) ?>" id="<?php echo $item['id']; ?>">
					<?php 
					foreach($item['options'] as $option_value => $option_label){
						$selected = ($option_value == $value)? "selected" : "";
						echo "<option value='{$option_value}' {$selected}>{$option_label}</option>";
					}
					?>
				</select>
			</div>
			<?php
			if ( $item['is_toggle'] == true ){
			?>
			<script type="text/javascript">
				jQuery(document).ready(function ($) {
					jQuery(document).on('change', '#<?php echo$item['id'];?>', function (e) {
						e.preventDefault();
						var allelm = jQuery('.<?php echo$item['id'];?>');
						var elm = jQuery('#' + this.value);
						allelm.hide();
						elm.show();
					});
				});

			</script>
			<?php
			}
			break;
		case 'listbox':
			$values_arr = storina_get_option($item['id']);
			?>
			<div class="osa-option-wrapper">
				<p class="osa-option-title"><strong><?php echo esc_att($item['name']) ?></strong></p>
				<p class="osa-option-description"><?php echo esc_att($item['desc']) ?></p>
				<select name="<?php echo esc_att($item['id']) ?>[]" id="<?php echo $item['id'] ?>" multiple="">
					<?php 
					foreach($item['options'] as $option_value => $option_label){
						$selected = (in_array($option_value,$values_arr))? "selected" : "";
						echo "<option {$selected} value='{$option_value}'>{$option_label}</option>";
					}
					?>
				</select>
			</div>
			<?php
			break;
		case 'color':
			$value = storina_get_option($item['id']);
			?>
			<div class="osa-option-wrapper">
				<p class="osa-option-title"><strong><?php echo esc_attr($item['name']); ?></strong></p>
				<p class="osa-option-description"><?php echo esc_att($item['desc']) ?></p>
				<input type="text" name="<?php echo esc_attr($item['id']); ?>" id="<?php echo $item['id'] ?>" class="color" value="<?php echo $value; ?>">
			</div>
			<?php
	}//end switch
	?>
<?php } ?>
<input type="hidden" name="apptype_form" value="options">
<input type="hidden" name="appname_form" value="<?php echo($pages[$counter-1]['apppagename']); ?>">
<div class="osa-submit-wrapper">
	<input type="submit" value="<?php _e("Save",'storina-application')?>" name="submit_theme_options" class="button save button-primary button-large">
</div>
</form>
