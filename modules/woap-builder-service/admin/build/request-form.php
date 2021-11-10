<div class="woap-form-wrapper">
	<form action="" type="post" id="woap-build-form">
		<div class="woap-field-wrapper">
			<label for="">نام اپلیکیشن</label>
			<input type="text" name="title" >
		</div>
		<!-- .woap-field-wrapper -->
		<div class="woap-field-wrapper">
			<label for="">شماره نسخه</label>
			<input type="number" name="version_number" >
		</div>
		<!-- .woap-field-wrapper -->
		<div class="woap-field-wrapper">
			<label for="">پروتکل وب سایت</label>
			<select disabled class="website-protocol" name="protocol">
				<option <?php echo ('http' == $protocol)? 'selected' : ''; ?> value="http://">http</option>
				<option <?php echo ('https' == $protocol)? 'selected' : ''; ?> value="https://">https</option>
			</select>
		</div>
		<!-- .woap-field-wrapper -->
		<div class="woap-field-wrapper">
			<label for="">مسیر نصب وردپرس</label>
			<input type="text" disabled class="wp-installation-url" name="wp_installation_url" dir="ltr" value="<?php echo esc_html($wp_installation_url); ?>">
		</div>
		<!-- .woap-field-wrapper -->
		<div class="woap-field-wrapper">
			<label for="">آیکن اپلیکیشن</label>
			<input type="text" name="icon" class="target-line">
			<input class="button upload-button" type="button" value="<?php _e("upload","onlinerShopApp"); ?>">
		</div>
		<div class="woap-field-wrapper">
			<label for="">آیکن صفحه اسپلش</label>
			<input type="text" name="splash_screen_icon" class="target-line">
			<input class="button upload-button" type="button" value="<?php _e("upload","onlinerShopApp"); ?>">
		</div>
		<div class="woap-field-wrapper">
			<label for="">رنگ صفحه آغازین اپلیکیشن</label>
			<input type="text" class="wp-color-picker" name="splash_screen_color">
		</div>
		<!-- .woap-field-wrapper -->
		<div class="woap-field-wrapper">
			<label for="">کلید گوگل مپ</label>
			<input type="text" name="map_id" >
		</div>
		<!-- .woap-field-wrapper -->
		<div class="woap-field-wrapper">
			<label for="">مجوز استفاده از دوربین</label>
			<input type="checkbox" name="camera_manifest" value="on" >
		</div>
		<!-- .woap-field-wrapper -->
		<div class="woap-field-wrapper">
			<label for="">مجوز استفاده از میکروفون</label>
			<input type="checkbox" name="voice_manifest" value="on" >
		</div>
		<!-- .woap-field-wrapper 
		<div class="woap-field-wrapper">
			<label for="">جهت اپلیکیشن</label>
			<select name="direction">
				<option value="rtl">راست به چپ</option>
				<option value="ltr">چپ به راست</option>
			</select>
		</div>-->
		<div class="woap-field-wrapper">
			<a href="#" title="" data-modal="app-strings" class="modal-button button">برای تغییر کلمات اپلیکیشن کلیک کنید</a>
		</div>
		<!-- .woap-field-wrapper -->
		<div class="woap-field-wrapper">
			<input type="submit" class="button button-primary" value="شروع ساخت اپلیکیشن">
		</div>
		<!-- .woap-field-wrapper -->
		<div id="app-strings" class="modal-wrapper">
			<div class="modal-content">
				<div class="modal-header">
					<span class="modal-close">&times;</span>
					<h2>تغییر رشته های اپلیکیشن</h2>
				</div>
				<div class="modal-body">
					<table>
					<?php
					foreach($strings as $key => $value):
					?>
					
						<tr>
							<td>
								<label for="<?php echo esc_html($key); ?>"><?php echo esc_html($value); ?></label>
							</td>
							<td>
								<div class="woap-field-wrapper">
									<input type="text" id="<?php echo esc_html($key); ?>" name="strings[<?php echo esc_attr($key); ?>]" value="<?php echo $value ?>">
								</div>
							</td>
						</tr>
					
					<?php
					endforeach;
					?>
					</table>
				</div>
				<div class="modal-footer">
					<h3>Footer</h3>
				</div>
			</div>
			<!-- .modal-content -->
		</div>
		<!-- .modal-wrapper -->
	</form>
	<!-- #woap-main-form -->
</div>
<!-- .woap-form-wrapper -->
<div class="request-message"></div>
<div class="woap-respons-list">
	<p class="respons-status"></p>
	<p class="respons-apk"></p>
	<p class="respons-icon"></p>
</div>
<!-- .woap-respons-wrapper -->

