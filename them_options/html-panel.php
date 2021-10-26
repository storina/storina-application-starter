<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$stylesheet = ("fa_IR" == get_locale())? "rtl.css" : "style.css";
wp_enqueue_style('osa-rtl-style', trailingslashit(THEMOPTION_OSAU) . 'assets/css/' . $stylesheet);
wp_enqueue_style('woap-iran-yekan',trailingslashit(STORINA_PDU) . "assets/css/iran-yekan.css");
?>
<link rel="stylesheet" type="text/css" href="<?= THEMOPTION_OSAU; ?>css/screen.css">
<?php
wp_enqueue_script('jquery');
if(function_exists('wp_enqueue_media')){
	wp_enqueue_media();}
?>
<link type="text/css" rel="stylesheet" href="<?= THEMOPTION_OSAU; ?>css/jquery-ui.css" />
<link type="text/css" href="<?= THEMOPTION_OSAU; ?>css/ui.multiselect.css" rel="stylesheet" />
<script type="text/javascript" src="<?= THEMOPTION_OSAU; ?>scripts/plugins/localisation/jquery.localisation-min.js"></script>
<script type="text/javascript" src="<?= THEMOPTION_OSAU; ?>scripts/plugins/scrollTo/jquery.scrollTo-min.js"></script>
<script type="text/javascript" src="<?= THEMOPTION_OSAU; ?>scripts/tabmenu.js"></script>
<script type="text/javascript" src="<?= THEMOPTION_OSAU; ?>scripts/rotator.js"></script>
<script type="text/javascript" src="<?= THEMOPTION_OSAU; ?>scripts/jscolor/jscolor.js"></script>
<div class="wrap">
	<div class="osa-settings-wrapper">
		<ul class="osa-setting-tabs">
			<?php
			$counter = 1;
			foreach($pages as $page){ ?>
			<li  <?php if($counter==1){echo 'class="active"';} ?>>
				<a href="#" rel="tab-<?php echo $counter; ?>"><?php echo $page['title']; ?></a>
			</li>
			<?php $counter++; } ?>
			<li>
				<a href="#" rel="tab-import"><?= __( "import","onlinerShopApp" ); ?></a>
			</li>
		</ul>
		<div class="osa-setting-contents">
		<?php
		$counter = 1;
		foreach($options_page as $page){ ?>
		<div <?php if($counter == 1){echo 'style="display:block;"';} ?> id="tab-<?php echo $counter; ?>" class="osa-setting-content-item">
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
				<p class="osa-option-title"><strong><?php _e("Import Settings","onlinerShopApp"); ?></strong></p>
				<p class="osa-option-description"><?php _e("Import default home page settings like slider and banners and product box.note that import action remove all current settings","onlinerShopApp"); ?></p>
				<form action="" method="post">
					<input type="submit" value="<?= __( "Import configuration", 'onlinerShopApp' ) ?>" name="submit_osa_import" class="button save">
					<?php wp_nonce_field('osa_import_configuration_nonce_value','osa_import_configuration_nonce_key'); ?>
				</form>
			</div>
		</div>
		</div><!--.table-cell-->
	</div><!--.table-->
</div>
