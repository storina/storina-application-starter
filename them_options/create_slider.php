<div class="osa-option-wrapper">
	<p class="osa-option-title"><strong><?php _e("Description",'onlinerShopApp'); ?></strong></p>
	<p class="osa-option-description"><?php echo $page['title']; ?></p>
	<form action="" method="POST" id="on5_form_panel" class="panel_form slider_form">
		<?php
		$slider = $page['slider_name'];
		$description = $page['title'];
		$image_name = $page['image_name'];
		$link_name = $page['link_name'];
		$title_name = $page['title_name'];
		$caption_name = $page['caption_name'];
		$addresses = osa_get_option($image_name);
		$links = osa_get_option($link_name);
		$titles = osa_get_option($title_name);
		
		$captions = osa_get_option($caption_name);
		
		?>
		<table class="wp-list-table widefat fixed">
		<thead>
		<th style="width: 20px;"><?= __( "Sort", 'onlinerShopApp' ); ?></th>
		<th><?= __( "Banner address", 'onlinerShopApp' ); ?></th>
		<th><?= __( "Title", 'onlinerShopApp' ); ?></th>
		<th><?= __( "Description", 'onlinerShopApp' ); ?></th>
		<th><?= __( "Link", 'onlinerShopApp' ); ?></th>
		<th><?= __( "Action", 'onlinerShopApp' ); ?></th>
		</thead>
		<tfoot>
		<th style="width: 20px;"><?= __( "Sort", 'onlinerShopApp' ); ?></th>
		<th><?= __( "Banner address", 'onlinerShopApp' ); ?></th>
		<th><?= __( "Title", 'onlinerShopApp' ); ?></th>
		<th><?= __( "Description", 'onlinerShopApp' ); ?></th>
		<th><?= __( "Link", 'onlinerShopApp' ); ?></th>
		<th><?= __( "Action", 'onlinerShopApp' ); ?></th>
		</tfoot>
		<?php 
		if($addresses){
			$i = 0;
			foreach($addresses as $address){ ?>
				<tr >
					<td style="width: 20px; text-align: center;"><strong class="sort_elem">|||</strong></td>
				<td>
				<input class="target_line" type="text" name="<?php echo $slider; ?>[address][]" value="<?php echo $addresses[$i]; ?>"/>
				<input type="button" name="upload-btn" class="upload-btn button-secondary" value="<?=__("Upload",'onlinerShopApp');?>">
				</td>
				<td><input type="text" name="<?php echo $slider; ?>[title][]" value="<?php echo $titles[$i]; ?>"/></td>
				<td><textarea name="<?php echo $slider; ?>[text][]" ><?php echo $captions[$i]; ?></textarea></td>
				<td><input type="text" name="<?php echo $slider; ?>[link][]" value="<?php echo $links[$i]; ?>"/></td>
				<td>
					<input title="<?php echo $slider; ?>" type="button" class="button-primary delete_row" value="<?=__("Delete",'onlinerShopApp');?>">
				</td>
				</tr>
			<?php 
			$i++;
			}
		}else{ ?>
		<tr>
			<td style="width: 20px; text-align: center;"><strong class="sort_elem">|||</strong></td>
		<td>
		<input class="target_line" type="text" name="<?php echo $slider; ?>[address][]" />
		<input type="button" name="upload-btn" class="upload-btn button-secondary" value="<?=__("Upload",'onlinerShopApp');?>">
		</td>
		<td><input type="text" name="<?php echo $slider; ?>[title][]" /></td>
		<td><textarea name="<?php echo $slider; ?>[text][]" ></textarea></td>
		<td><input type="text" name="<?php echo $slider; ?>[link][]" /></td>
		<td>
			<input title="<?php echo $slider; ?>" type="button" class="button-primary delete_row" value="<?=__("Delete",'onlinerShopApp');?>">
		</td>
		</tr>
		<?php }
		
		//$value = osa_get_option(); ?>
		
		</table>
		<div class="osa-submit-wrapper-table">
			<input type="hidden" name="apptype_form" value="slider">
			<input type="hidden" name="appname_form" value="<?php echo($pages[$counter-1]['apppagename']); ?>">
			<input type="submit" value="<?=__("Save",'onlinerShopApp')?>" name="submit_theme_options" class="button save">
			<button type="button" class="button add_row"><?= __( "Add ", 'onlinerShopApp' ) ?></button>
		</div>
	</form>
</div>