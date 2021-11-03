<div class="osa-option-wrapper">
	<p class="osa-option-title"><strong><?php _e("Description",'onlinerShopApp'); ?></strong></p>
	<p class="osa-option-description"><?php echo $page['title']; ?></p>
	<form action="" method="POST" id="on5_form_panel" class="panel_form banner_panel header_th">
		<?php
		$description = $page['title'];
		$banner = $page['banner_name'];
		$banner_addresses = $page['banner_addresses'];
		$banner_links = $page['banner_links'];
		$banner_titles = $page['banner_titles'];
		$banner_captions = $page['banner_captions'];
		$banner_width = $page['banner_widths'];
		$banner_height = $page['banner_heights'];
		$banner_relation = $page['banner_relation'];
		$banner_expire = $page['banner_expire'];
		$addresses = storina_get_option($banner_addresses);
		$links = storina_get_option($banner_links);
		$titles = storina_get_option($banner_titles);
		$captions = storina_get_option($banner_captions);
		$widths = storina_get_option($banner_width);
		$heights = storina_get_option($banner_height);
		$relations = storina_get_option($banner_relation);
		$expires = storina_get_option($banner_expire);
		?>
		<div class="clear"></div>
		<table class="wp-list-table widefat fixed">
		<thead>
		<th><?php echo__("Banner address",'onlinerShopApp');?></th><th><?php echo__("Title",'onlinerShopApp');?></th><th><?php echo__("Description",'onlinerShopApp');?></th><th><?php echo__("Link",'onlinerShopApp');?></th><th><?php echo__("Width px",'onlinerShopApp');?></th><th><?php echo__("Height px",'onlinerShopApp');?></th><th><?php echo__("Category",'onlinerShopApp');?></th><th><?php echo__("Expire",'onlinerShopApp');?></th><th><?php echo__("Action",'onlinerShopApp');?></th>
		</thead>
		<tfoot>
		<th><?php echo__("Banner address",'onlinerShopApp');?></th><th><?php echo__("Title",'onlinerShopApp');?></th><th><?php echo__("Description",'onlinerShopApp');?></th><th><?php echo__("Link",'onlinerShopApp');?></th><th><?php echo__("Width px",'onlinerShopApp');?></th><th><?php echo__("Height px",'onlinerShopApp');?></th><th><?php echo__("Category",'onlinerShopApp');?></th><th><?php echo__("Expire",'onlinerShopApp');?></th><th><?php echo__("Action",'onlinerShopApp');?></th>
		</tfoot>
			<?php
			global $product_cats;
			?>
		<?php 
		if($addresses){
			$i = 0;
			foreach($addresses as $address){ ?>
				<tr>
				<td>
				<input class="target_line" type="text" name="<?php echo $banner; ?>[address][]" value="<?php echo $addresses[$i]; ?>"/>
				<input type="button" name="upload-btn" class="upload-btn button-secondary" value="<?php echo__("Upload",'onlinerShopApp');?>">
				</td>
				<td><input type="text" name="<?php echo $banner; ?>[title][]" value="<?php echo $titles[$i]; ?>"/></td>
				<td>
				
		
				<textarea name="<?php echo $banner; ?>[text][]" ><?php echo $captions[$i]; ?></textarea>
				</td>
				<td><input type="text" name="<?php echo $banner; ?>[link][]" value="<?php echo $links[$i]; ?>"/></td>
				<td>
					<input placeholder="50px" type="text" name="<?php echo $banner; ?>[width][]" value="<?php echo $widths[$i]; ?>"/>
				</td>
					<td>
						<input placeholder="50px" type="text" name="<?php echo $banner; ?>[height][]" value="<?php echo $heights[$i]; ?>"/>
					</td>
				<td>

					<select class="select_box" name="<?php echo $banner; ?>[banner_relation][]">
					<?php
					foreach ($product_cats as $product_cat => $value) {
						$selected = ($product_cat == $relations[$i])?'selected':'';
						echo "<option $selected value='$product_cat'>$value</option>";
					}
					?>
				</select></td>
				<td><input type="text" name="<?php echo $banner; ?>[banner_expire][]" value="<?php echo $expires[$i]; ?>"  id="datepicker"/></td>
				<td>
					<input title="<?php echo $banner; ?>" type="button" class="button-primary delete_row"
							value="<?php echo esc_html__( "Delete", 'onlinerShopApp' ); ?>">
				</td>
				</tr>
			<?php 
			$i++;
			}
		}else{ ?>
		<tr>
		<td>
		<input class="target_line" type="text" name="<?php echo $banner; ?>[address][]" />
		<input type="button" name="upload-btn" class="upload-btn button-secondary" value="<?php echo__("Upload",'onlinerShopApp');?>">
		</td>
		<td><input type="text" name="<?php echo $banner; ?>[title][]" /></td>
		<td><textarea name="<?php echo $banner; ?>[text][]" ></textarea></td>
		<td><input type="text" name="<?php echo $banner; ?>[link][]" /></td>
			<td>
				<input placeholder="50px" type="text" name="<?php echo $banner; ?>[width][]"/>
			</td>
			<td>
				<input placeholder="50px" type="text" name="<?php echo $banner; ?>[height][]"/>
			</td>
		<td><select class="select_box" name="<?php echo $banner; ?>[banner_relation][]">
				<?php
				foreach ($product_cats as $product_cat => $value) {
					echo "<option value='$product_cat'>$value</option>";
				}
				?>
			</select></td>
		<td><input type="text" name="<?php echo $banner; ?>[banner_expire][]" value="<?php echo $expires[$i]; ?>" id="datepicker" /></td>
		<td>
			<input title="<?php echo $banner; ?>" type="button" class="button-primary delete_row"
					value="<?php echo esc_html__( "Delete", 'onlinerShopApp' ); ?>">
		</td>
		</tr>
		<?php }
		//$value = storina_get_option(); ?>
		
		</table>
		<div class="osa-submit-wrapper-table">
			<input type="hidden" name="apptype_form" value="banner">
			<input type="hidden" name="appname_form" value="<?php echo($pages[$counter-1]['apppagename']); ?>">
			<input type="submit" value="<?php echo__("Save",'onlinerShopApp')?>" name="submit_theme_options" class="button save">
			<button type="button" class="button add_row"><?php echo esc_html__( "Add ", 'onlinerShopApp' ) ?></button>
		</div>
	</form>
</div>
