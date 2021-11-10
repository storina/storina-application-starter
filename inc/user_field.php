<?php
add_action( 'user_register', function ( $user_id ) {

	if ( ! empty( sanitize_text_field($_POST['action']) ) ) // یعنی اگه از تو سایت ثبت نام شده بود
	{
		update_user_meta( $user_id, 'userStatus', true );
	}

} , 10, 1 );


add_filter( 'woocommerce_customer_meta_fields', function ( $fields ) {
	$fields['billing']['fields']['billing_lat']   = array(
		'label'       => esc_html__( 'Google map lat', 'storina-application' ),
		'description' => ''
	);
	$fields['billing']['fields']['billing_lng']   = array(
		'label'       => esc_html__( 'Google map lng', 'storina-application' ),
		'description' => ''
	);
	$fields['shipping']['fields']['shipping_lat'] = array(
		'label'       => esc_html__( 'Google map lat', 'storina-application' ),
		'description' => ''
	);
	$fields['shipping']['fields']['shipping_lng'] = array(
		'label'       => esc_html__( 'Google map lng', 'storina-application' ),
		'description' => ''
	);

	return $fields;
} );

add_action( 'show_user_profile', 'storina_app_user_fields' );
add_action( 'edit_user_profile', 'storina_app_user_fields' );

function storina_app_user_fields( $user ) { ?>

<h3><?php echo esc_html__('Extra information','storina-application')?></h3>
<table class="form-table">

    <tr>
        <th><label for="userToken"><?php echo esc_html__('User token','storina-application')?></label></th>

        <td>
            <input readonly type="text" name="userToken" id="userToken" value="<?php echo esc_attr( get_user_meta($user->ID, 'userToken',true  ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php echo esc_html__('Current user token id.','storina-application');?></span>
        </td>
    </tr>

    <tr>
        <th><label for="gender"><?php echo esc_html__('Gender','storina-application');?></label></th>

        <td>
            <input readonly type="text" name="gender" id="gender" value="<?php echo esc_attr( get_user_meta($user->ID, 'gender',true  ) ); ?>" class="regular-text" />
            <select name="gender" id="gender">
                <option value="male">male</option>
                <option value="female">female</option>
            </select>
            <br />
            <span class="description"><?php echo esc_html__('Select your gender','storina-application');?></span>
        </td>
    </tr>
    <tr>
        <th><label for="melliCode"><?php echo esc_html__('National codeٔ','storina-application');?></label></th>

        <td>
            <input type="text" name="melliCode" id="melliCode" value="<?php echo esc_attr( get_user_meta($user->ID, 'melliCode',true  ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php echo esc_html__('National codeٔ','storina-application');?></span>
        </td>
    </tr>
    <tr>
        <th><label for="birthDate"><?php echo esc_html__('Birth date','storina-application');?></label></th>
        <?php
        $birthYear = get_user_meta($user->ID, 'birthYear',true  );
        $birthMonth = get_user_meta($user->ID, 'birthMonth',true  );
        $birthDay = get_user_meta($user->ID, 'birthDay',true  );
        ?>
        <td>
            <select name="birthYear" id="birthYear">
                <?php
                for($i=1350;$i<=1400;$i++){
                    $selected = ($birthYear == $i)?'selected':'';
                    echo "<option $selected value=\"$i\">$i</option>";}
                ?>

            </select>
            <select name="birthMonth" id="birthMonth">
                <?php
                for($i=1;$i<=12;$i++){
                    $selected = ($birthMonth == $i)?'selected':'';
                    echo "<option $selected value=\"$i\">$i</option>";}
                ?>
                <option value="1">1</option>
            </select>
            <select name="birthDay" id="birthDay">
                <?php
                for($i=1;$i<=31;$i++){
                    $selected = ($birthDay == $i)?'selected':'';
                    echo "<option $selected value=\"$i\">$i</option>";}
                ?>
            </select>
            <span class="description"><?php echo esc_html__('Birth date','storina-application');?></span>
        </td>
    </tr>
    
</table>
<?php }

add_action( 'personal_options_update', 'storina_save_user_fields' );
add_action( 'edit_user_profile_update', 'storina_save_user_fields' );

function storina_save_user_fields( $user_id ) {

    if ( !current_user_can( 'edit_user', $user_id ) )
        return false;

    /* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
    //update_usermeta( $user_id, 'userToken', sanitize_text_field($_POST['userToken']) );
	update_usermeta( $user_id, 'gender', sanitize_text_field($_POST['gender']) );
	update_usermeta( $user_id, 'melliCode', sanitize_text_field($_POST['melliCode']) );
	update_usermeta( $user_id, 'birthYear', sanitize_text_field($_POST['birthYear']) );
	update_usermeta( $user_id, 'birthMonth', sanitize_text_field($_POST['birthMonth']) );
	update_usermeta( $user_id, 'birthDay', sanitize_text_field($_POST['birthDay']) );
	if ( !empty( sanitize_text_field($_POST['billing_address_1']) ) AND sanitize_text_field($_POST['billing_address_1']) != '' ) {
		update_usermeta( $user_id, 'billing_status', 'active' );
	}
	if ( !empty( sanitize_text_field($_POST['shipping_address_1']) ) AND sanitize_text_field($_POST['shipping_address_1']) != '' ) {
		update_usermeta( $user_id, 'shipping_status', 'active' );
	}

}


// Hook in
add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
    if(is_admin()){
	    $fields['billing']['billing_mobile'] = array(
		    'type' => 'text',
		    'label' => esc_html__('Emergency phone', 'storina-application'),
		    'placeholder' => _x('Emergency phone', 'placeholder', 'storina-application')
	    );
	    $fields['billing']['billing_email'] = array(
		    'type' => 'text',
		    'label' => esc_html__('Email', 'storina-application'),
		    'placeholder' => _x('Email', 'placeholder', 'storina-application')
	    );

	    $fields['shipping']['shipping_phone'] = array(
		    'type' => 'text',
		    'label' => esc_html__('Phone number', 'storina-application'),
		    'placeholder' => _x('Phone number', 'placeholder', 'storina-application')
	    );
	    $fields['shipping']['shipping_mobile'] = array(
		    'type' => 'text',
		    'label' => esc_html__('Mobile', 'storina-application'),
		    'placeholder' => _x('Mobile', 'placeholder', 'storina-application')
	    );
	    $fields['shipping']['shipping_email'] = array(
		    'type' => 'text',
		    'label' => esc_html__('Email', 'storina-application'),
		    'placeholder' => _x('Email', 'placeholder', 'storina-application')
	    );
    }

    return $fields;
});


/**
 * Display field value on the order edit page
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {
	$mobile = get_post_meta( $order->id, 'billing_mobile', true );
	if ( $mobile ) {
		echo '<p><strong>' . esc_html__( 'Mobile :', 'storina-application' ) . '</strong> <br/>' . $mobile . '</p>';
	}
	$phone = get_post_meta( $order->id, 'shipping_phone', true );
	if ( $phone ) {
		echo '<p><strong>' . esc_html__( 'Shipping phone :', 'storina-application' ) . '</strong> <br/>' . $phone . '</p>';
	}
	$mobile = get_post_meta( $order->id, 'shipping_mobile', true );
	if ( $mobile)
    echo '<p><strong>'.esc_html__('Shipping mobile :', 'storina-application').':</strong> <br/>' . get_post_meta( $order->id, 'shipping_mobile', true ) . '</p>';
	$timestamp = get_post_meta( $order->id, 'time4SendTimestamp', true );
	$valid_timestamp = (strlen($timestamp) > 10)? intval($timestamp/1000) : $timestamp;
	$date = (is_rtl())? STORINA\Libraries\JDate::jdate("Y-m-d H:i",$valid_timestamp) : date("Y-m-d H:i",$valid_timestamp);
		echo '<p><strong>' . esc_html__('Send box time and date :','storina-application') . '</strong> <br/>' . $date . '</p>';

} , 10, 1 );


add_action('dokan_order_detail_after_order_items', function ($order){
	$billing_mobile = get_post_meta( $order->id, 'billing_mobile', true );
	$shipping_mobile = get_post_meta( $order->id, 'shipping_mobile', true );
	?>
    <script>
        $(document).ready(function(){
            var mobile1;
            var mobile2;
			<?php if(!empty($billing_mobile)){ ?>
            mobile1 = '<li><a href="#">' + <?php echo esc_html__('Mobile 1 :','storina-application');?> + ' <span class="tab"><?php echo esc_html($billing_mobile); ?></span></a></li>';
			<?php }
			if(!empty($shipping_mobile)){
			?>
            mobile2 = '<li><a href="#">' + <?php echo esc_html__('Mobile 2 :','storina-application');?> +'<span class="tab"><?php echo esc_html($shipping_mobile); ?></span></a></li>';
			<?php } 
			$timestamp = get_post_meta( $order->id, 'time4SendTimestamp', true );
			$valid_timestamp = (strlen($timestamp) > 10)? intval($timestamp/1000) : $timestamp;
			$date = (is_rtl())? STORINA\Libraries\JDate::jdate("Y-m-d H:i",$valid_timestamp) : date("Y-m-d H:i",$valid_timestamp);
			if(!empty($timestamp)){
			?>
            timestamp = '<li><a href="#">' + <?php echo esc_html__('Send box time and date :','storina-application');?> + '<span class="tab"><?php echo esc_html($date); ?></span></a></li>';
			<?php } ?>
            jQuery("ul.customer-details").append(mobile1 + mobile2 + timestamp);
		});
        //$('.customer-details').append('<?php echo $billing_mobile.$shipping_mobile; ?>');
    </script>
	<?php
} ,5,1);


add_action( "woocommerce_email_after_order_table", function ( $order ) {
	$timestamp = get_post_meta( $order->id, 'time4SendTimestamp', true );
	$valid_timestamp = (strlen($timestamp) > 10)? intval($timestamp/1000) : $timestamp;
	$time = (is_rtl())? STORINA\Libraries\JDate::jdate("Y-m-d H:i",$valid_timestamp) : date("Y-m-d H:i",$valid_timestamp);
	if($timestamp)
		echo '<p><strong>'.esc_html__('Send box time and date :','storina-application').'</strong>'. $time .'</p>';

} , 10, 1 );

