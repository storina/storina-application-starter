<div class="osa-option-wrapper">
	<p class="osa-option-title"><strong><?php _e("Description",'storina-application'); ?></strong></p>
	<p class="osa-option-description"><?php echo $page['title']; ?></p>
	<form action="" method="POST" id="on5_form_panel" class="panel_form slider_form">
		<?php
		$slider = $page['slider_name'];
		$description = $page['title'];
		$image_name = $page['image_name'];
		$link_name = $page['link_name'];
		$title_name = $page['title_name'];
		$caption_name = $page['caption_name'];
		$addresses = storina_get_option($image_name);
		$links = storina_get_option($link_name);
		$titles = storina_get_option($title_name);
		
		$captions = storina_get_option($caption_name);
		
		?>
		<table class="wp-list-table widefat fixed">
		<thead>
		<th style="width: 20px;"><?php echo esc_html__( "Sort", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Banner address", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Title", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Description", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Link", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Action", 'storina-application' ); ?></th>
		</thead>
		<tfoot>
		<th style="width: 20px;"><?php echo esc_html__( "Sort", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Banner address", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Title", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Description", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Link", 'storina-application' ); ?></th>
		<th><?php echo esc_html__( "Action", 'storina-application' ); ?></th>
		</tfoot>
		<?php 
		if($addresses){
			$i = 0;
			foreach($addresses as $address){ ?>
				<tr >
					<td style="width: 20px; text-align: center;"><strong class="sort_elem">|||</strong></td>
				<td>
				<input class="target_line" type="text" name="<?php echo $slider; ?>[address][]" value="<?php echo $addresses[$i]; ?>"/>
				<input type="button" name="upload-btn" class="upload-btn button-secondary" value="<?php echo esc_html__("Upload",'storina-application');?>">
				</td>
				<td><input type="text" name="<?php echo $slider; ?>[title][]" value="<?php echo $titles[$i]; ?>"/></td>
				<td><textarea name="<?php echo $slider; ?>[text][]" ><?php echo $captions[$i]; ?></textarea></td>
				<td><input type="text" name="<?php echo $slider; ?>[link][]" value="<?php echo $links[$i]; ?>"/></td>
				<td>
					<input title="<?php echo $slider; ?>" type="button" class="button-primary delete_row" value="<?php echo esc_html__("Delete",'storina-application');?>">
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
		<input type="button" name="upload-btn" class="upload-btn button-secondary" value="<?php echo esc_html__("Upload",'storina-application');?>">
		</td>
		<td><input type="text" name="<?php echo $slider; ?>[title][]" /></td>
		<td><textarea name="<?php echo $slider; ?>[text][]" ></textarea></td>
		<td><input type="text" name="<?php echo $slider; ?>[link][]" /></td>
		<td>
			<input title="<?php echo $slider; ?>" type="button" class="button-primary delete_row" value="<?php echo esc_html__("Delete",'storina-application');?>">
		</td>
		</tr>
		<?php }
		
		//$value = storina_get_option(); ?>
		
		</table>
		<div class="osa-submit-wrapper-table">
			<input type="hidden" name="apptype_form" value="slider">
			<input type="hidden" name="appname_form" value="<?php echo($pages[$counter-1]['apppagename']); ?>">
			<input type="submit" value="<?php echo esc_html__("Save",'storina-application')?>" name="submit_theme_options" class="button save">
			<button type="button" class="button add_row"><?php echo esc_html__( "Add ", 'storina-application' ) ?></button>
		</div>
	</form>
</div>
