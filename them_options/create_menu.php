<div class="osa-option-wrapper">
	<p class="osa-option-title"><strong><?php _e("Description",'onlinerShopApp'); ?></strong></p>
	<p class="osa-option-description"><?php echo $page['title']; ?></p>
	<form action="" method="POST" id="on5_form_panel" class="panel_form slider_form">
	<?php
	$FAQ = $page['page_name'];
	$description = $page['title'];
	$menu_names = $page['menu_name'];
	$link_names = $page['link_name'];
	$cat1_names = $page['cat1_name'];
	$cat2_names = $page['cat2_name'];
	$cat3_names = $page['cat3_name'];
	$menu_namess = storina_get_option($menu_names);
	$link_namess = storina_get_option($link_names);
	$cat1_namess = storina_get_option($cat1_names);
	$cat2_namess = storina_get_option($cat2_names);
	$cat3_namess = storina_get_option($cat3_names);

	?>
	<div class="clear"></div>
	<table id="menu_table">
	<tr class="header_th">
	<th><?php echo esc_html__("Title",'onlinerShopApp')?></th><th><?php echo esc_html__("Link",'onlinerShopApp')?></th><th>دسته شاخص</th><th>دسته دوم</th><th>دسته سوم</th><th>کنترل</th>
	</tr>
	<?php 
	if($menu_namess){
		$i = 0;
		foreach($menu_namess as $menu_name){  ?>
			<tr >
			<td><input type="text" name="<?php echo $FAQ; ?>[question][]" value="<?php echo $menu_namess[$i]; ?>"/></td>
			<td><input type="text" name="<?php echo $FAQ; ?>[link][]" value="<?php echo $link_namess[$i]; ?>"/></td>
			<td>
			<select name="<?php echo $FAQ; ?>[username][]" class="select_box">
			<?php $count = count($wp_cats);
			$value = $cat1_namess[$i];
			for($ii=0;$ii<$count;$ii++){
				echo '<option value="'.$wp_cats[$ii].'"';
				if($value == $wp_cats[$ii]){echo 'selected="selected"';}
				echo '>'.$wp_cats[$ii].'</option>';
			} ?>
			</select>
			</td>
			<td>
			<select name="<?php echo $FAQ; ?>[answer][]" class="select_box">
			<?php $count = count($wp_cats);
			$value = $cat2_namess[$i];
			for($ii=0;$ii<$count;$ii++){
				echo '<option value="'.$wp_cats[$ii].'"';
				if($value == $wp_cats[$ii]){echo 'selected="selected"';}
				echo '>'.$wp_cats[$ii].'</option>';
			} ?>
			</select>
			</td>
			<td>
			<select name="<?php echo $FAQ; ?>[date][]" class="select_box">
			<?php $count = count($wp_cats);
			$value = $cat3_namess[$i];
			for($ii=0;$ii<$count;$ii++){
				echo '<option value="'.$wp_cats[$ii].'"';
				if($value == $wp_cats[$ii]){echo 'selected="selected"';}
				echo '>'.$wp_cats[$ii].'</option>';
			} ?>
			</select>
			</td>
			<td>
				<input title="<?php echo $FAQ; ?>" type="button" class="button-primary delete_row" value="حذف">
			</td>
			</tr>
		<?php 
		$i++;
		}
	}else{ ?>
	<tr>
	<td><input type="text" name="<?php echo $FAQ; ?>[question][]" /></td>
	<td><input type="text" name="<?php echo $FAQ; ?>[link][]" /></td>
	<td>
	<select name="<?php echo $FAQ; ?>[username][]" class="select_box">
	<?php $count = count($wp_cats);
	$value = $cat1_namess[$i];
	for($ii=0;$ii<$count;$ii++){
		echo '<option value="'.$wp_cats[$ii].'" >'.$wp_cats[$ii].'</option>';
	} ?>
	</select>
	</td>
	<td>
	<select name="<?php echo $FAQ; ?>[answer][]" class="select_box">
	<?php $count = count($wp_cats);
	$value = $cat2_namess[$i];
	for($ii=0;$ii<$count;$ii++){
		echo '<option value="'.$wp_cats[$ii].'" >'.$wp_cats[$ii].'</option>';
	} ?>
	</select></td>
	<td>
	<select name="<?php echo $FAQ; ?>[date][]" class="select_box">
	<?php $count = count($wp_cats);
	$value = $cat3_namess[$i];
	for($ii=0;$ii<$count;$ii++){
		echo '<option value="'.$wp_cats[$ii].'" >'.$wp_cats[$ii].'</option>';
	} ?>
	</select></td>
	<td>
		<input title="<?php echo $FAQ; ?>" type="button" class="button-primary delete_row" value="حذف">
	</td>
	</tr>
	<?php }
	//$value = storina_get_option(); ?>
	</table>
	<div class="osa-submit-wrapper-table">
		<input type="hidden" name="apptype_form" value="FAQ">
		<input type="hidden" name="appname_form" value="<?php echo($pages[$counter-1]['apppagename']); ?>">
		<input type="submit" value="ذخیره" name="submit_theme_options" class="button save">
		<button type="button" class="button add_row"><?php echo esc_html__( "Add ", 'onlinerShopApp' ) ?></button>
	</div>
	</form>
</div>
