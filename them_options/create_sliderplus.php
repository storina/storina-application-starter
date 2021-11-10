<div class="osa-option-wrapper">
	<p class="osa-option-title"><strong><?php _e("Description",'storina-application'); ?></strong></p>
	<p class="osa-option-description"><?php echo esc_html($page['title']); ?></p>
	<form action="" method="POST" id="on5_form_panel" class="panel_form slider_form">
		<?php
		$slider = $page['slider_name'];
		$tax = $page['taxonomy'];
		$description = $page['title'];
		$image_name = $page['image_name'];
		$link_name = $page['link_name'];
		$typeLink_name = $page['typeLink_name'];
		$title_name = $page['title_name'];
		$caption_name = $page['caption_name'];
		$category_name = $page['category_name'];
		$addresses = storina_get_option($image_name);
		$links = storina_get_option($link_name);
		$typeLinks = storina_get_option($typeLink_name);
		$titles = storina_get_option($title_name);
		$captions = storina_get_option($caption_name);
		$cats = storina_get_option($category_name);

		
		global $product_cats;
		$product_cats     = array();
		$product_cats[-1] = esc_html__("All",'storina-application');
		$product_cats     = storina_hierarchical_category_tree2( 0 , $tax);
		global $osa_autoload;
		$general          = $osa_autoload->service_provider->get(\STORINA\Controllers\General::class);
		$action           = $general->clickEventList();
		if ( function_exists( 'dokan_get_store_info' ) ) {
			$action['VendorPage'] = esc_html__( 'Open vendor page', 'storina-application' );
		}
		?>
		<table class="wp-list-table widefat fixed">
		<thead>
		<th style="width: 20px;"><strong class="sort_elem">|||</strong></th>
		<th><?php echo esc_html__( "Banner address", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Title", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Description", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Link type", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Value", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Show in", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Action", 'storina-application' ); ?></th>
		</thead>
		<tfoot>
		<th style="width: 20px;"><strong class="sort_elem">|||</strong></th>
		<th><?php echo esc_html__( "Banner address", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Title", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Description", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Link type", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Value", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Show in", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Action", 'storina-application' ); ?></th>
		</tfoot>
		<?php 
		if($addresses){
			$i = 0;
			foreach($addresses as $address){

				?>
				<tr >
					<td style="width: 20px; text-align: center;"><strong class="sort_elem">|||</strong></td>
				<td>
				<input class="target_line" type="text" name="<?php echo esc_attr($slider); ?>[address][]" value="<?php echo $addresses[$i]; ?>"/>
				<input type="button" name="upload-btn" class="upload-btn button-secondary" value="<?php echo esc_html__("Upload",'storina-application');?>">
				</td>
				<td><input type="text" name="<?php echo esc_attr($slider); ?>[title][]" value="<?php echo $titles[$i]; ?>"/></td>
				<td><textarea name="<?php echo esc_attr($slider); ?>[text][]" ><?php echo $captions[$i]; ?></textarea></td>
					<td>
						<select class="select_box" name="<?php echo esc_attr($slider); ?>[typeLink][]">
							<?php foreach ($action as $item => $value) { ?>
								<option value="<?php echo$item?>" <?php if($typeLinks[$i] == $item){echo 'selected="selected"';} ?>><?php echo$value?></option>
							<?php } ?>
						</select>
					</td>
				<td><input type="text" name="<?php echo esc_attr($slider); ?>[link][]" value="<?php echo $links[$i]; ?>"/></td>
				<td>
				<select  name="<?php echo esc_attr($slider); ?>[category][]" class="widefat">
					<?php
					foreach($product_cats as $product_cat => $value){
						$select = "";
						if($product_cat == $cats[$i]){ $select = "selected";}
						echo '<option  value="'.$product_cat.'" '.$select.'>'.$value.'</option>';
					}
					?>

				</select>
				</td>
				<td>
					<input title="<?php echo esc_attr($slider); ?>" type="button" class="button-primary delete_row" value="<?php echo esc_html__("Delete",'storina-application');?>">
				</td>
				</tr>
			<?php 
			$i++;
			}
		}else{ ?>
		<tr>
			<td style="width: 20px; text-align: center;"><strong class="sort_elem">|||</strong></td>
		<td>
		<input class="target_line" type="text" name="<?php echo esc_attr($slider); ?>[address][]" />
		<input type="button" name="upload-btn" class="upload-btn button-secondary" value="<?php echo esc_html__("Upload",'storina-application');?>">
		</td>
		<td><input type="text" name="<?php echo esc_attr($slider); ?>[title][]" /></td>
		<td><textarea name="<?php echo $slider; ?>[text][]" ></textarea></td>
			<td>
				<select class="select_box" name="<?php echo $slider; ?>[typeLink][]">
					<?php foreach ($action as $item => $value) { ?>
						<option value="<?php echo$item?>" ><?php echo$value?></option>
					<?php } ?>
				</select>
			</td>
		<td><input type="text" name="<?php echo esc_attr($slider); ?>[link][]" /></td>
		<td>
			<select name="<?php echo esc_attr($slider); ?>[category][]" class="widefat">
				<?php
				foreach($product_cats as $product_cat => $value){
					echo '<option  value="'.$product_cat.'">'.$value.'</option>';
				}
				?>

			</select>
		</td>
		<td>
			<input title="<?php echo esc_attr($slider); ?>" type="button" class="button-primary delete_row" value="<?php echo esc_html__("Delete",'storina-application');?>">
		</td>
		</tr>
		<?php }
		
		//$value = storina_get_option(); ?>
		
		</table>
<div class="osa-submit-wrapper-table">
<input type="hidden" name="apptype_form" value="sliderplus">
		<input type="hidden" name="appname_form" value="<?php echo($pages[$counter-1]['apppagename']); ?>">
		<input type="submit" value="<?php echo esc_html__("Save",'storina-application')?>" name="submit_theme_options" class="button save">
		<button type="button" class="button add_row"><?php echo esc_html__( "Add ", 'storina-application' ) ?></button>
</div>
	</form>
</div>
