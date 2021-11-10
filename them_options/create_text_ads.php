<div class="osa-option-wrapper">
	<p class="osa-option-title"><strong><?php _e("Description",'storina-application'); ?></strong></p>
	<p class="osa-option-description"><?php echo esc_html($page['title']); ?></p>
	<form action="" method="POST" id="on5_form_panel" class="panel_form textads_form">
		<?php
		$description = $page['title'];
		$text_ads_name = $page['text_ads_name'];
		$text_ads_titles = $page['text_ads_titles'];
		$text_ads_links = $page['text_ads_links'];
		$text_ads_captions = $page['text_ads_captions'];
		$text_ads_follow = $page['text_ads_follow'];
		$text_ads_expire = $page['text_ads_expire'];
		$text_ads_color = $page['text_ads_color'];
		$titles = storina_get_option($text_ads_titles);
		$links = storina_get_option($text_ads_links);
		$captions = storina_get_option($text_ads_captions);
		$followes = storina_get_option($text_ads_follow);
		$expires = storina_get_option($text_ads_expire);
		$colors = storina_get_option($text_ads_color);
		?>
		<div class="clear"></div>
		<table>
		<tr class="header_th">
		<th>عنوان</th><th>لینک</th><th>متن</th><th>سئو فالو</th><th>انقضاء</th><th>کد رنگ</th><th>کنترل</th>
		</tr>
		<?php 
		if($titles){
			$i = 0;
			foreach($titles as $title){ ?>
				<tr>
				<td><input type="text" name="<?php echo esc_attr($text_ads_name); ?>[title][]" value="<?php echo $titles[$i]; ?>"/></td>
				<td><input type="text" name="<?php echo esc_attr($text_ads_name); ?>[link][]" value="<?php echo $links[$i]; ?>"/></td>
				<td><textarea name="<?php echo esc_attr($text_ads_name); ?>[text][]" ><?php echo $captions[$i]; ?></textarea></td>
				<td><select class="select_box" name="<?php echo esc_attr($text_ads_name); ?>[follow][]">
				<option value="follow" <?php if($followes[$i] == 'follow'){echo 'selected="selected"';} ?>>Follow</option>
				<option value="nofollow" <?php if($followes[$i] == 'nofollow'){echo 'selected="selected"';} ?>>no-Follow</option>
				</select></td>
				<td><input type="text" name="<?php echo esc_attr($text_ads_name); ?>[expire][]" value="<?php echo $expires[$i]; ?>"/></td>
				<td><input class="color" type="text" name="<?php echo esc_attr($text_ads_name); ?>[color][]" value="<?php echo $colors[$i]; ?>"/></td>
				<td>
					<input title="<?php echo esc_attr($text_ads_name); ?>" type="button" class="button-primary delete_row"
							value="حذف">
				</td>
				</tr>
			<?php 
			$i++;
			}
		}else{ ?>
		<tr>
		<td><input type="text" name="<?php echo esc_attr($text_ads_name); ?>[title][]" /></td>
		<td><input type="text" name="<?php echo esc_attr($text_ads_name); ?>[link][]" /></td>
		<td><textarea name="<?php echo esc_attr($text_ads_name); ?>[text][]" ></textarea></td>
		<td><select class="select_box" name="<?php echo esc_attr($text_ads_name); ?>[follow][]">
			<option value="follow">Follow</option>
			<option value="nofollow">no-Follow</option>
		</select></td>
		<td><input type="text" name="<?php echo esc_attr($text_ads_name); ?>[expire][]" value="<?php echo $expires[$i]; ?>" /></td>
		<td><input type="text" class="color" name="<?php echo $text_ads_name; ?>[expire][]" value="<?php echo $colors[$i]; ?>" /></td>
		<td>
			<input title="<?php echo esc_attr($text_ads_name); ?>" type="button" class="button delete_row" value="حذف">
		</td>
		</tr>
		<?php }
		
		//$value = storina_get_option(); ?>
		
		</table>
<div class="osa-submit-wrapper-table">
<input type="hidden" name="apptype_form" value="text_ads">
		<input type="hidden" name="appname_form" value="<?php echo($pages[$counter-1]['apppagename']); ?>">
		<input type="submit" value="<?php _e("save","storina-application"); ?>" name="submit_theme_options" class="button save">
</div>
	</form>
</div>
