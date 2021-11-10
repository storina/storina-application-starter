<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
	<div class="osa-settings-wrapper">
		<ul class="osa-setting-tabs">
			<?php
			$counter = 1;
			foreach($pages as $page){ ?>
			<li  <?php if($counter==1){echo 'class="active"';} ?>>
				<a href="#" rel="tab-<?php echo esc_attr($counter); ?>"><?php echo $page['title']; ?></a>
			</li>
			<?php $counter++; } ?>
			<li>
				<a href="#" rel="tab-import"><?php echo esc_html__( "import","storina-application" ); ?></a>
			</li>
		</ul>
		<div class="osa-setting-contents">
		<?php
		$counter = 1;
		foreach($options_page as $page){ ?>
		<div <?php if($counter == 1){echo 'style="display:block;"';} ?> id="tab-<?php echo esc_attr($counter); ?>" class="osa-setting-content-item">
			<?php
			if(@$page['type'] == 'slider'){ require("create_slider.php"); }
			else if(@$page['type'] == 'sliderplus'){ require("create_sliderplus.php"); }
			else if(@$page['type'] == 'text_adsplus'){ require("create_text_adsplus.php"); }
			else if(@$page['type'] == 'menu'){ require("create_menu.php"); }
			else if(@$page['type'] == 'banner'){ require("create_banner.php"); }
			else if(@$page['type'] == 'text_ads'){ require("create_text_ads.php"); }
			else if(@$page['type'] == 'custom'){ require("create_custompanel.php"); }
			else if(@$page['type'] == 'custom2'){ require("create_custompanel2.php"); }
			else if(@$page['type'] == 'custom5'){ require("create_custompanel5.php"); }
			else if(@$page['type'] == 'custom3'){ require("create_custompanel3.php"); }
			else if(@$page['type'] == 'custom4'){ require("create_custompanel4.php"); }
			else{
				require("create_options.php");
			}
			?>
		</div>
		<?php
		$counter++;
		} ?>
		<div id="tab-import" class="osa-setting-content-item">
			<div class="osa-option-wrapper">
				<p class="osa-option-title"><strong><?php _e("Import Settings","storina-application"); ?></strong></p>
				<p class="osa-option-description"><?php _e("Import default home page settings like slider and banners and product box.note that import action remove all current settings","storina-application"); ?></p>
				<form action="" method="post">
					<input type="submit" value="<?php echo esc_html__( "Import configuration", 'storina-application' ) ?>" name="submit_osa_import" class="button save">
					<?php wp_nonce_field('osa_import_configuration_nonce_value','osa_import_configuration_nonce_key'); ?>
				</form>
			</div>
		</div>
		</div><!--.table-cell-->
	</div><!--.table-->
</div>
