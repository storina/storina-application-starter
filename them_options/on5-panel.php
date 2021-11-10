<?php
/**
 * Plugin Name: theme options
 * Plugin URI: http://on5.ir
 * Description: This plugin can manage theme options.
 * Version: 1.0.0
 * Author: on5 corporation
 * Author URI: http://on5.ir
 * License: GPL2
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function storina_update_option2() {
	$import_nonce_value = (!empty(sanitize_text_field($_POST['osa_import_configuration_nonce_key'])))? sanitize_text_field($_POST['osa_import_configuration_nonce_key']) : null;
	$import_verify_nonce = wp_verify_nonce($import_nonce_value,'osa_import_configuration_nonce_value');
	if ( !empty( sanitize_text_field($_POST['submit_osa_import'])) && !empty($import_nonce_value) && false != $import_verify_nonce ) {

		//app option
		$title                 = storina_get_option( 'app_title' );
		$app_masterColor       = storina_get_option( 'app_masterColor' );
		$app_secondColor       = storina_get_option( 'app_secondColor' );
		$Archive_product_count = storina_get_option( 'Archive_product_count' );
		
		$zeroPriceText         = storina_get_option( 'zeroPriceText' );
		$appArchiveType        = storina_get_option( 'appArchiveType' );
		$viewCounterField      = storina_get_option( 'viewCounterField' );
		$payType               = storina_get_option( 'payType' );
		$variation_priceType   = storina_get_option( 'variation_priceType' );
		$app_callNumber        = storina_get_option( 'app_callNumber' );
		$VendorAvatar          = storina_get_option( 'VendorAvatar' );

		if ( ! $title ) {
			storina_update_option( 'app_title', 'اپلیکیشن اندروید' );
		}
		if ( ! $app_masterColor ) {
			storina_update_option( 'app_masterColor', 'F21D2B' );
		}
		if ( ! $app_secondColor ) {
			storina_update_option( 'app_secondColor', 'CF1111' );
		}
		if ( ! $Archive_product_count ) {
			storina_update_option( 'Archive_product_count', 8 );
		}

		if ( ! $zeroPriceText ) {
			storina_update_option( 'zeroPriceText', 'zero' );
		}
		if ( ! $appArchiveType ) {
			storina_update_option( 'appArchiveType', 'sub' );
		}
		if ( ! $viewCounterField ) {
			storina_update_option( 'viewCounterField', 'post-views' );
		}
		if ( ! $payType ) {
			storina_update_option( 'payType', 'inAppPay' );
		}
		if ( ! $variation_priceType ) {
			storina_update_option( 'variation_priceType', 1 );
		}
		if ( ! $app_callNumber ) {
			storina_update_option( 'app_callNumber', '09355240891' );
		}
		if ( ! $VendorAvatar ) {
			storina_update_option( 'VendorAvatar', 'hidden' );
		}

		// about option
		$app_slogan          = storina_get_option( 'app_slogan' );
		$app_Email           = storina_get_option( 'app_Email' );
		$app_telegramID      = storina_get_option( 'app_telegramID' );
		$app_phone           = storina_get_option( 'app_phone' );
		$app_copyright       = storina_get_option( 'app_copyright' );
		$app_privacyLink     = storina_get_option( 'app_privacyLink' );
		$app_termsLink       = storina_get_option( 'app_termsLink' );
		$app_aboutLink       = storina_get_option( 'app_aboutLink' );
		$app_aboutButtonText = storina_get_option( 'app_aboutButtonText' );
		$app_aboutButtonLink = storina_get_option( 'app_aboutButtonLink' );
		if ( ! $app_slogan ) {
			storina_update_option( 'app_slogan', 'بخر بفروش و آنلاین باش' );
		}
		if ( ! $app_Email ) {
			storina_update_option( 'app_Email', 'app@x.ir' );
		}
		if ( ! $app_telegramID ) {
			storina_update_option( 'app_telegramID', '@ali_akherati' );
		}
		if ( ! $app_phone ) {
			storina_update_option( 'app_phone', '09355600355' );
		}
		if ( ! $app_copyright ) {
			storina_update_option( 'app_copyright', 'کلیه حقوق مادی و معنوی این اپلیکیشن متعلق به شرکت ایکس است.' );
		}
		if ( ! $app_privacyLink ) {
			storina_update_option( 'app_privacyLink', 'https://person.bilpay.ir/privacy-policy/' );
		}
		if ( ! $app_termsLink ) {
			storina_update_option( 'app_termsLink', 'https://person.bilpay.ir/tos/' );
		}
		if ( ! $app_aboutLink ) {
			storina_update_option( 'app_aboutLink', 'https://person.bilpay.ir/about-person/' );
		}
		if ( ! $app_aboutButtonText ) {
			storina_update_option( 'app_aboutButtonText', 'مشاهده سایت' );
		}
		if ( ! $app_aboutButtonLink ) {
			storina_update_option( 'app_aboutButtonLink', 'https://storina.com' );
		}

		// index option

		$index_types = storina_get_option( 'appindex_element' );
		$index_IDs   = storina_get_option( 'appindex_ID' );

		if ( empty( $index_types ) OR ! $index_types ) {
			$index_types = array(
				'sliderItems',
				'categories',
				'featured',
				'productBox',
				'oneColADV',
				'categories',
				'productBox'
			);
			$index_IDs   = array(
				'slide_1',
				'cat_1',
				'feature_1',
				'productBox_1',
				'oneColADV_1',
				'categories_2',
				'productBox_2'
			);
			storina_update_option( 'appindex_element', $index_types );
			storina_update_option( 'appindex_ID', $index_IDs );
		}
		//slider

		$top_slider_imagesslide_1 = storina_get_option( 'top_slider_imagesslide_1' );
		if ( empty( $top_slider_imagesslide_1 ) OR ! $top_slider_imagesslide_1  || false) {
			$banners = array();
			storina_update_option( 'top_slider_imagesslide_1', $banners );
		}

		// cat
		$product_cat_ids   = get_terms( array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'fields'     => 'ids',
		) );
		$indexAppCatscat_1 = storina_get_option( 'indexAppCatscat_1' );
		if ( empty( $indexAppCatscat_1 ) OR ! $indexAppCatscat_1 ) {
			storina_update_option( 'indexAppCatscat_1', $product_cat_ids );
			storina_update_option( 'indexAppCatTypecat_1', 'Thumbnail' );
		}

		//featured
		$indexAppFeaturesfeature_1 = storina_get_option( 'indexAppFeaturesfeature_1' );
		if ( empty( $indexAppFeaturesfeature_1 ) OR ! $indexAppFeaturesfeature_1 ) {
			storina_update_option( 'indexAppFeaturesfeature_1', $product_cat_ids );
			storina_update_option( 'indexAppFeaturesCountfeature_1', 4 );
		}

		// product box

		$indexAppBoxproductBox_1 = storina_get_option( 'indexAppBoxproductBox_1' );
		if ( empty( $indexAppBoxproductBox_1 ) OR ! $indexAppBoxproductBox_1 ) {
			storina_update_option( 'indexAppBoxproductBox_1', 0 );
			storina_update_option( 'indexAppBoxTitleproductBox_1', 'جدیدترین محصولات' );
		}

		//oneColADV_1
		$Hbanner_banner1oneColADV_1 = storina_get_option( 'Hbanner_banner1oneColADV_1' );
		if ( empty( $Hbanner_banner1oneColADV_1 ) OR ! $Hbanner_banner1oneColADV_1 ) {
			$banners = array();
			storina_update_option( 'Hbanner_banner1oneColADV_1', $banners );
		}
		// cat
		$indexAppCatscategories_2 = storina_get_option( 'indexAppCatscategories_2' );
		if ( empty( $indexAppCatscategories_2 ) OR ! $indexAppCatscategories_2 ) {
			storina_update_option( 'indexAppCatscategories_2', $product_cat_ids );
			storina_update_option( 'indexAppCatTypecategories_2', 'scrollButtons' );
		}
		// product box

		$indexAppBoxproductBox_2 = storina_get_option( 'indexAppBoxproductBox_2' );
		if ( empty( $indexAppBoxproductBox_2 ) OR ! $indexAppBoxproductBox_2 ) {
			storina_update_option( 'indexAppBoxproductBox_2', 0 );
			storina_update_option( 'indexAppBoxTitleproductBox_2', 'تازه ترین محصولات' );
		}
		storina_update_option( "dig_otp_size", 6 );
	}
	if ( !empty( sanitize_text_field($_POST['submit_theme_options']) ) ) {
	$apptype_form = sanitize_text_field($_POST['apptype_form']);
	$appname_form = sanitize_text_field($_POST['appname_form']);
	$count = 0;
	global $options_page;
	global $pages;

	foreach($options_page as $page){

		$str1 = $pages[ $count ]['apppagename'];
		$str2 = $appname_form;
		if(strcmp($str1,$str2)== 0){
			switch($apptype_form){
				case 'slider':
					$slider_name = $page['slider_name'];
					$image_name = $page['image_name'];
					$link_name = $page['link_name'];
					$title_name = $page['title_name'];
					$caption_name = $page['caption_name'];
					$slider = sanitize_text_field($_POST[$slider_name]);
					$address = $slider['address'];
					$title = $slider['title'];
					$text = $slider['text'];
					$link = $slider['link'];
					storina_update_option($image_name,$address);
					storina_update_option($link_name,$link);
					storina_update_option($title_name,$title);
					storina_update_option($caption_name,$text);
					break;
				case 'sliderplus':
					$slider_name   = $page['slider_name'];
					$image_name    = $page['image_name'];
					$link_name     = $page['link_name'];
					$typeLink_name = $page['typeLink_name'];
					$title_name    = $page['title_name'];
					$caption_name  = $page['caption_name'];
					$category_name = $page['category_name'];
					$slider        = sanitize_text_field($_POST[ $slider_name ]);
					$address       = $slider['address'];
					$title         = $slider['title'];
					$text          = $slider['text'];
					$link          = $slider['link'];
					$typeLink      = $slider['typeLink'];
					$category      = $slider['category'];
					storina_update_option( $image_name, $address );
					storina_update_option( $link_name, $link );
					storina_update_option( $typeLink_name, $typeLink );
					storina_update_option( $title_name, $title );
					storina_update_option( $caption_name, $text );
					storina_update_option( $category_name, $category );
					break;
				case 'options':
					$counter = 0;
					foreach ( $page as $item ) {
						$h = @$page[ $counter ];
						switch ( $h['type'] ) {
							case 'textarea':
							case 'codearea':
								{
									$tmp_name     = $h['id'];
									$tmp_textarea = stripslashes( sanitize_text_field($_POST[ $tmp_name ]) );
									storina_update_option( $tmp_name, $tmp_textarea );
									break;
								}
							case 'text':
							case 'radio':
							case 'select':
							case 'picture':
								{
									$tmp_name = $h['id'];
									storina_update_option( $tmp_name, sanitize_text_field($_POST[ $tmp_name ]) );
									break;
								}
							case 'color':
								$tmp_name = $h['id'];
								storina_update_option( $tmp_name, sanitize_text_field($_POST[ $tmp_name ]) );
								break;
							case 'listbox':
								{
									$tmp_name = $h['id'];
									storina_update_option( $tmp_name, sanitize_text_field($_POST[ $tmp_name ]) );
								}
							case 'checkbox':
								{
									$count = count( $h['id'] );
									for ( $i = 0; $i < $count; $i ++ ) {
										$tmp_name = $h['id'][ $i ];
										storina_update_option( $tmp_name, sanitize_text_field($_POST[ $tmp_name ]) );
									}

									break;
								}
						}//end switch
						$counter ++;
					}//end foreach
					break;
				case 'banner':
					$banner           = $page['banner_name'];
					$banner_addresses = $page['banner_addresses'];
					$banner_links     = $page['banner_links'];
					$banner_titles    = $page['banner_titles'];
					$banner_captions  = $page['banner_captions'];
					$widths_banner    = $page['banner_widths'];
					$heights_banner   = $page['banner_heights'];
					$relations_banner = $page['banner_relation'];
					$expires_banner   = $page['banner_expire'];
					$banners          = sanitize_text_field($_POST[ $banner ]);
					$address_banner   = $banners['address'];
					$title_banner     = $banners['title'];
					$text_banner      = $banners['text'];
					$link_banner      = $banners['link'];
					$width_banner     = $banners['width'];
					$height_banner    = $banners['height'];
					$relation_banner  = $banners['banner_relation'];
					$expire_banner    = $banners['banner_expire'];
					storina_update_option( $banner_addresses, $address_banner );
					storina_update_option( $banner_links, $link_banner );
					storina_update_option( $banner_titles, $title_banner );
					storina_update_option( $banner_captions, $text_banner );
					storina_update_option( $widths_banner, $width_banner );
					storina_update_option( $heights_banner, $height_banner );
					storina_update_option( $relations_banner, $relation_banner );
					storina_update_option( $expires_banner, $expire_banner );
					break;
				case 'text_ads':
					$text_ads_name     = $page['text_ads_name'];
					$text_ads_titles   = $page['text_ads_titles'];
					$text_ads_links    = $page['text_ads_links'];
					$text_ads_captions = $page['text_ads_captions'];
					$text_ads_follow   = $page['text_ads_follow'];
					$types_banner      = $page['banner_type'];
					$text_ads_expire   = $page['text_ads_expire'];
					$text_ads_color    = $page['text_ads_color'];
					$text_ads          = sanitize_text_field($_POST[ $text_ads_name ]);
					$title_text_ads    = $text_ads['title'];
					$link_text_ads     = $text_ads['link'];
					$text_text_ads     = $text_ads['text'];
					$follow_text_ads   = $text_ads['follow'];
					$expire_text_ads   = $text_ads['expire'];
					$color_text_ads    = $text_ads['color'];
					storina_update_option( $text_ads_titles, $title_text_ads );
					storina_update_option( $text_ads_links, $link_text_ads );
					storina_update_option( $text_ads_captions, $text_text_ads );
					storina_update_option( $text_ads_follow, $follow_text_ads );
					storina_update_option( $text_ads_expire, $expire_text_ads );
					storina_update_option( $text_ads_color, $color_text_ads );
					break;
				case 'text_adsplus':

					$slider_name   = $page['slider_name'];
					$link_name     = $page['link_name'];
					$typeLink_name = $page['typeLink_name'];
					$title_name    = $page['title_name'];
					$caption_name  = $page['caption_name'];

					$slider = sanitize_text_field($_POST[ $slider_name ]);
					$title    = $slider['title'];
					$text     = $slider['text'];
					$link     = $slider['link'];
					$typeLink = $slider['typeLink'];
					storina_update_option( $link_name, $link );
					storina_update_option( $typeLink_name, $typeLink );
					storina_update_option( $title_name, $title );
					storina_update_option( $caption_name, $text );
					break;
				case 'custom':
				case 'custom2':
					$custom_option_name = $page['custom_option_name'];
					$custom_option2     = $page['custom_option2'];
					$custom_option3     = $page['custom_option3'];
					$custom_option4     = $page['custom_option4'];
					$custom_option5     = $page['custom_option5'];
					$custom_option6     = $page['custom_option6'];
					$custom_option7     = $page['custom_option7'];
					$custom_option8     = $page['custom_option8'];
					$custom_option9     = $page['custom_option9'];

					$custom_option        = sanitize_text_field($_POST[ $custom_option_name ]);
					$custom_option2_value = $custom_option['option2'];
					@$custom_option3_value = @$custom_option['option3'];
					$custom_option4_value = $custom_option['option4'];
					$custom_option5_value = $custom_option['option5'];
					$custom_option6_value = $custom_option['option6'];
					$custom_option7_value = $custom_option['option7'];
					$custom_option8_value = $custom_option['option8'];
					$custom_option9_value = $custom_option['option9'];
				if ( ! $custom_option2_value ) {
					$custom_option2_value = array();
				}
				for ( $i = 0; $i < count( $custom_option3_value ); $i ++ ) {
					if ( empty( $custom_option2_value[ $i ] ) OR ! $custom_option2_value[ $i ] ) {
						$custom_option2_value[ $i ] = 'ID_' . rand( 1, 999999 );
					}
				}
					storina_update_option( $custom_option2, $custom_option2_value );
					storina_update_option( $custom_option3, $custom_option3_value );
					storina_update_option( $custom_option4, $custom_option4_value );
					storina_update_option( $custom_option5, $custom_option5_value );
					storina_update_option( $custom_option6, $custom_option6_value );
					storina_update_option( $custom_option7, $custom_option7_value );
					storina_update_option( $custom_option8, $custom_option8_value );
					storina_update_option( $custom_option9, $custom_option9_value );
					break;
					case 'woap-home-adc':
						foreach($page['option-names'] as $option_name){
							$option_value = sanitize_text_field($_POST[$option_name]);
							update_option($option_name,$option_value);
						}
						break;

			}
		}//end if
		$count++;
	}// end foreach
	storina_delete_index_cache();
	echo '<div class="updated"><p>'.esc_html__('Saved changes.','storina-application').'</p></div>';
}//end if
}

/** Step 1. */
add_action( 'admin_menu', function () {
	add_menu_page( 'onliner app options', esc_html__('Application settings','storina-application'), 'manage_options', 'ONLINER_options', 'storina_app_options' );
	add_submenu_page( 'ONLINER_options', esc_html__('Send notification','storina-application'), esc_html__('Send notification','storina-application'), 'manage_options', 'onliner-send-notification', 'storina_send_notif');
        do_action("osa_admin_menu_application_main");
} );

/** Step 3. */
function storina_app_options() {

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( esc_html__('You can not change options on site.','storina-application') );
	}
	require_once("create_list.php");
	global $options_page;
	global $pages;
	$options_page = $pages = array();
	require("include.php");
	storina_update_option2();
	global $pages;
	$pages        = array();
	$options_page = array();
	require("include.php");
	require_once("html-panel.php");
}

function storina_pippin_get_image_id( $image_url ) {
	global $wpdb;
	$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ) );

	return $attachment[0];
}

function storina_upload_from_url( $postID, $url, $title = '' ) {
	$tmp = download_url( $url );
	if ( strlen( $title ) < 1 ) {
		$title = basename( $url );
	}
	$file_array = array();

	// Set variables for storage
	// fix file filename for query strings
	preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches );
	$file_array['name']     = basename( $url );
	$file_array['tmp_name'] = $tmp;

	// If error storing temporarily, unlink
	if ( is_wp_error( $tmp ) ) {
		@unlink( $file_array['tmp_name'] );
		$file_array['tmp_name'] = '';
	}
	// do the validation and storage stuff
	$id = media_handle_sideload( $file_array, $postID, $title );


	// If error storing permanently, unlink
	if ( is_wp_error( $id ) ) {
		@unlink( $file_array['tmp_name'] );

		return $id;
	}

	return $id;
}

function storina_send_notif(){
        global $osa_autoload;
        $general          = $osa_autoload->service_provider->get(\STORINA\Controllers\General::class);
	//error_reporting(E_ALL);
	wp_enqueue_script('jquery');
	if(function_exists('wp_enqueue_media')){
		wp_enqueue_media();
	}
	if ( !empty( sanitize_text_field($_POST['send_notif']) ) ) {
		$title     = sanitize_text_field($_POST['notif_title']);
		$desc      = sanitize_text_field($_POST['notif_desc']);
		$icon      = sanitize_text_field($_POST['notif_icon']);
		$linkType  = sanitize_text_field($_POST['notif_linkType']);
		$linkValue = sanitize_text_field($_POST['notif_linkValue']);
		if ( isset( $title ) AND isset( $desc ) AND isset( $icon ) AND isset( $linkType ) AND isset( $linkValue ) ) {

			$onClickModel = $general->clickEvent( $linkType, $linkValue );
			$result = $general->sendNotif( $title, $desc, $icon, $onClickModel );
			if ( $result->message_id > 0 ) {
				$args          = array(
					'post_title'     => $title,
					'post_type'      => 'Announcements',
					'post_status'    => 'publish',
					'comment_status' => 'closed',   // if you prefer
					'ping_status'    => 'closed',      // if you prefer
					'post_content'   => $desc
				);
				$post_id       = wp_insert_post( $args );
				$attachment_id = storina_pippin_get_image_id( $icon );
				if ( $attachment_id > 0 ) {
					$attach_id = $attachment_id;
				} else {
					$attach_id = storina_upload_from_url( $post_id, $icon, $title );
				}
				if ( $post_id ) {
					set_post_thumbnail( $post_id, $attach_id );
					add_post_meta( $post_id, 'onClickModel', json_encode( $onClickModel ) );
				}
				echo '<div class="updated"><p>' . esc_html__( 'Notification sent.', 'storina-application' ) . ' </p></div>';

			} else {
				echo '<div class="update-nag"><p>' . esc_html__( 'Notification not send.', 'storina-application' ) . '  </p></div>';
			}
		} else {
			echo '<div class="update-nag"><p>' . esc_html__( 'Please fill all reqired fields.', 'storina-application' ) . '</p></div>';
		}

	}

	?>
    <div class="wrap"><div id="icon-options-general" class="icon32"><br></div> <h2><?php echo esc_html__('Send notification','storina-application')?></h2>
        <form action="" method="post">
            <table class="form-table">
                <tr>
                    <th><label for="notif_title"><?php echo esc_html__( 'Title', 'storina-application' ) ?></label></th>
                    <td><input required id="notif_title" name="notif_title" type="text" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="notif_desc"><?php echo esc_html__( 'Text', 'storina-application' ) ?></label></th>
                    <td><textarea required name="notif_desc" id="notif_desc" cols="30" rows="4"></textarea></td>
                </tr>
                <tr>
                    <th><label for="notif_icon"><?php echo esc_html__( 'Icon', 'storina-application' ) ?></label></th>
                    <td><input id="notif_icon" name="notif_icon" class="target_line regular-text text-box" value=""
                               type="text">
                        <input name="upload-btn" class="upload-btn button-secondary" value="آپلود تصویر" type="button">
                        <p class="description"><?php echo esc_html__( 'Select notification icon 512*512 px', 'storina-application' ) ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="notif_linkType"><?php echo esc_html__( 'Link type', 'storina-application' ) ?></label></th>
                    <td>
                        <select required name="notif_linkType" class="select_box">
	                        <?php $actions = $general->clickEventList();
	                        foreach ( $actions as $index => $action ) {
		                        ?>
                                <option value="<?php echo $index ?>"><?php echo $action ?></option>
		                        <?php
	                        }
	                        if ( function_exists( 'dokan_get_store_info' ) ) { ?>
                                <option value="VendorPage"><?php echo esc_html__( 'Open vendor page', 'storina-application' ) ?></option>
	                        <?php } ?>
                        </select>
                        <p class="description"><?php echo esc_html__( 'What do you want then touch on notification?', 'storina-application' ) ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="notif_linkValue"><?php echo esc_html__( 'Link value', 'storina-application' ) ?></label></th>
                    <td>
                        <input required id="notif_linkValue" name="notif_linkValue" class="regular-text text-box"
                               value="" type="text">
                        <p class="description"><?php echo esc_html__( 'Enter the desired value according to the link type field.', 'storina-application' ) ?></p>
                    </td>
                </tr>
                <tr>
                    <th><input value="<?php echo esc_html__( 'Send', 'storina-application' ) ?>" name="send_notif"
                               class="button save button-primary button-large" type="submit">
                    </th>
                    <td></td>
                </tr>

            </table>

        </form>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                jQuery(document).on('click', '.upload-btn', function (e) {
                    var elm = jQuery(this).parent().find('.target_line');
                    e.preventDefault();
                    var image = wp.media({
                        title: 'Upload Image',
                        // mutiple: true if you want to upload multiple files at once
                        multiple: false
                    }).open()
                        .on('select', function (e) {
                            // This will return the selected image from the Media Uploader, the result is an object
                            var uploaded_image = image.state().get('selection').first();
                            // We convert uploaded_image to a JSON object to make accessing it easier
                            // Output to the console uploaded_image
                            console.log(uploaded_image);
                            var image_url = uploaded_image.toJSON().url;
                            // Let's assign the url value to the input field
                            elm.val(image_url);
                        });
                });
            });
        </script>
    </div>
<?php }
