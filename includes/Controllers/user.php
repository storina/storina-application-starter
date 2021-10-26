<?php


use \STORINA\Controllers\Yith_Role_Based_Price;
use \STORINA\Controllers\Index;
use \STORINA\Controllers\Terawallet;

defined('ABSPATH') || exit;

class OSA_user {

    public $yith_price_role = false;
    public $user_id = false;
    public $service_container;

    public function __construct($service_container) {
        $this->service_container = $service_container;
        require_once( ABSPATH . "wp-load.php" );
        // fix conflict by google recaptcha
        remove_action('init', 'gglcptch_init');
        remove_action('plugins_loaded', 'gglcptch_plugins_loaded');
        remove_action('login_form', 'gglcptch_login_display');
        remove_action('authenticate', 'gglcptch_login_check', 21, 1);
        remove_action('allow_password_reset', 'gglcptch_lostpassword_check');
        remove_action('pre_comment_on_post', 'gglcptch_commentform_check');
        remove_filter('wp_authenticate_user', 'wp_authenticate_userssss', 10, 2);
        $this->yith_price_role = $this->service_container->get(Yith_Role_Based_Price::class);
    }

    public function resendCode() {
        if (!function_exists('digit_create_otp')) {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 4,
                    'message' => __("Digits plugin not active.", "onlinerShopApp")
                )
            );

            return ( $result );
        }
        $userToken = $_POST['userToken'];
        $user_id = $this->get_userID_byToken($userToken);
        $user = get_user_by('ID', $user_id);
        $session_data = $this->get_digits_details($userToken);
        $username = ($user instanceof WP_User)? $user->user_login : $session_data['username'];
        if (empty($username)) {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 2,
                    'message' => __("This user is not exist. please review or sign up first.", "onlinerShopApp")
                )
            );
        } else {
            $result = $this->sendVerificationCode($username);
        }

        return ( $result );
    }

    public function get_userID_byToken($token) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'usermeta';
        $query = "SELECT *
		FROM $table_name
		WHERE meta_key = 'userToken' AND meta_value = '$token'";
        $check_exist = $wpdb->get_row($query);
        if (isset($check_exist->user_id)) {
            return $check_exist->user_id;
        } else {
            return false;
        }
    }

    private function sendVerificationCode($to) {
        if (function_exists('digit_create_otp')) {
            $countrycode = getUserCountryCode();
            $countrycode = ( $countrycode == '+' ) ? $countrycode . '98' : $countrycode;
            $validate_phone = apply_filters( "osa_user_verification_otp", $to, $countrycode );
            $res_code = digit_create_otp($countrycode, $validate_phone);
            if ($res_code == 1) {
                $result = array(
                    'status' => true,
                    'data' => array(
                        'verify' => true,
                        'message' => __("Password sent.", "onlinerShopApp")
                    )
                );
            } else {
                $result = array(
                    'status' => false,
                    'error' => array(
                        'errorCode' => - 1,
                        'message' => 'خطا در ارسال کد'
                    )
                );
            }
        }
        return $result;
    }
    
    private function digits_is_active($send_json) {
        if (!function_exists('digit_create_otp')) {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 4,
                    'message' => __("Digits plugin not active.", "onlinerShopApp")
                )
            );
            if ($send_json) {
                return ( $result );
            } else {
                return true;
            }
        }
    }
    
    public function get_digits_phone_no($digits_phone_no){
        $meta_key = 'digits_phone_no';
        $meta_value = (string) ltrim($digits_phone_no,'0');
        global $wpdb;
        $user_meta = $wpdb->prefix . 'usermeta';
        $meta_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$user_meta} WHERE meta_key = %s AND meta_value=%s",$meta_key,$meta_value),ARRAY_A);
        return (!empty($meta_row))? $meta_row['user_id'] : false;
    }

    public function verify() {
        $this->digits_is_active(true);
        $otp = $_POST['code'];
        $userToken = $user_token = $_POST['userToken'];
        $session_data = $this->get_digits_details($userToken);
        if(empty($session_data) || !isset($session_data['username'])){
            return array(
                "status" => false,
                "message" => "session data not set"
            );
        }
		$digits_phone_no = get_user_meta($session_data['user_id'],'digits_phone_no',true);
		$phone_no = (strlen($digits_phone_no) > 5 && is_numeric($digits_phone_no))? $digits_phone_no : $session_data['username'];
        $validate_phone = apply_filters( "osa_user_verify_phone_gateways", $phone_no, $session_data['digits_country_code'] );
        $verify_otp = verifyOTP($session_data['digits_country_code'], "0" . ltrim($validate_phone,'0'), $otp, false);
        if(false == $verify_otp){
            return array(
                "status" => false,
				'error' => [
					"message" => __("Entered cod is invalid.", "onlinerShopApp")
				]
            );
        }
        $user_id = ($session_data['user_id'] > 0)? $session_data['user_id'] : $this->digits_register_core($session_data);
        if(is_wp_error( $user_id )){
            return array(
                "status" => false,
                "message" => $user_id->get_error_message()
            );
        }
        $user_status = get_user_meta($user_id,"userStatus");
        $user = get_user_by( 'id', $user_id );
        $result = array(
            'status' => true,
            'user' => array(
                'id' => $user_id,
                'name' => $user->display_name,
                'username' => $user->username,
                'email' => $user->user_email,
                'registerDate' => $session_data['register_date'],
                'verifyStatus' => $user_status,
                'userToken' => $user_token,
            ),
            'data' => array(
                'message' => __("Verify successfully.", "onlinerShopApp")
            ),
        );
        return apply_filters("osa_user_verify_result",$result ,$user_id);
    }

    public function register() {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $checkMail = $this->checkEmail($email);
        $registerType = osa_get_option('registerType');
        if ($registerType == 'email') {
            if (!$checkMail) {
                $result = array(
                    'status' => false,
                    'error' => array(
                        'errorCode' => - 2,
                        'message' => __("Entered email is invalid.", "onlinerShopApp")
                    )
                );
            } else {
                $result = $this->EMAILRegister($email, $password);
            }
        } elseif ($registerType == 'mobile') {
            // do somthing

            $result = $this->SMSRegister($email, $password);
        } else {
            if ($checkMail) {
                $result = $this->EMAILRegister($email, $password);
            } else {

                $result = $this->SMSRegister($email, $password);
            }
        }

        return ( $result );
    }

    public function checkEmail($email) {
        $find1 = strpos($email, '@');

        return ( $find1 !== false ) ? true : false;
    }

    public function EMAILRegister($email, $password) {
        $parts = explode("@", $email);
        $username = $parts[0] . rand(1000, 9999);
        $user_id = email_exists($email);
        $fullname = ( isset($_POST['name']) ) ? $_POST['name'] : $parts[0];
        if (!$user_id) {
            $result = $this->registerCore($username, $password, $email, $fullname);
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'message' => __("This email is exist. please login.", "onlinerShopApp")
                )
            );
        }

        return $result;
    }

    public function registerCore($username, $password, $email, $name) {
        $user_id = wp_create_user($username, $password);
        $billing_state = ( isset($_POST['state']) ) ? $_POST['state'] : "";
        if (!empty($billing_state) and ! empty($user_id)) {
            update_user_meta($user_id, "billing_state", $billing_state);
        }
        if (!is_wp_error($user_id)) {
            $result = wp_update_user(
                    array(
                        'ID' => intval($user_id),
                        'role' => apply_filters("osa_user_register_user_role",osa_get_option('default_role')),
                        'user_email' => $email,
                        'display_name' => $name,
                        'first_name' => $name
                    )
            );
            $info = array();
            $info['user_login'] = $username;
            $info['user_password'] = $password;
            //$userValid = wp_authenticate($info['user_login'], $pass);
            wp_set_auth_cookie($info['user_login'], false);


            $user_signon = wp_signon($info, false);
            wp_set_current_user($user_signon->ID, $username);
            $userToken = implode('-', str_split(substr(strtolower(md5(microtime() . rand(1000, 9999))), 0, 30), 6));
            update_user_meta($user_signon->ID, 'userToken', $userToken);
            global $wpdb, $googleID;
            $table = $wpdb->prefix . 'OSA_cart';
            $wpdb->update(
                    $table,
                    array(
                        'googleID' => $user_signon->user_login
                    ),
                    array('googleID' => $googleID),
                    array(
                        '%s'
                    ),
                    array('%s')
            );

            $table = $wpdb->prefix . 'OSA_view_log';
            $wpdb->update(
                    $table,
                    array(
                        'googleID' => $user_signon->user_login
                    ),
                    array('googleID' => $googleID),
                    array(
                        '%s'
                    ),
                    array('%s')
            );
            update_user_meta($user_id, 'userStatus', true);

            $countries_obj = new WC_Countries();
            //$countries   = $countries_obj->__get('countries');
            $default_country = $countries_obj->get_base_country();
            $default_county_states = $countries_obj->get_states($default_country);
            foreach ($default_county_states as $index => $default_county_state) {
                $states[] = array('fa' => $default_county_state, 'EN' => $index);
            }
            $OSA_avatar = get_user_meta(intval($user_signon->ID), 'OSA_avatar', true);
            $this->parvankalaCustomize(intval($user_signon->ID)); // if state is sent

            $result = array(
                'status' => true,
                'data' => array(
                    'user' => array(
                        'id' => intval($user_signon->ID),
                        'name' => $user_signon->display_name,
                        'username' => $user_signon->user_login,
                        'email' => $user_signon->user_email,
                        'registerDate' => $user_signon->user_registered,
                        'userToken' => $userToken,
                        'OSA_avatar' => $OSA_avatar,
                    ),
                    'verify' => false,
                    'message' => __("Register successfully.", "onlinerShopApp"),
                )
            );
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'message' => $user_id->get_error_message()
                )
            );
        }
        do_action('osa_user_register_complate',$user_id);
        return $result;
    }

    private function parvankalaCustomize($user_id) {
        if (isset($_POST['state'])) {
            $state = $_POST['state'];
            update_user_meta($user_id, 'billing_state', $state);
        }
    }

    public function SMSRegister($username, $password) {
        $this->digits_is_active(true);
        $validMobile = $this->validMobile($username);
        if (!$validMobile) {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 4,
                    'message' => __("Mobile number is invalid.", "onlinerShopApp")
                )
            );

            return $result;
        }

        $validMobile = (0 != substr($validMobile, 0, 1)) ? "0{$validMobile}" : $validMobile;
        $user_id1 = username_exists($validMobile);
        $user_id2 = username_exists(ltrim($validMobile, '0'));
        $fullname = ( isset($_POST['name']) ) ? $_POST['name'] : $validMobile;
        if ($user_id1 || $user_id2) {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 3,
                    'message' => __("This mobile number is exist. please login.", "onlinerShopApp")
                )
            );
        } else {
            $password = ( isset($_POST['password']) ) ? $_POST['password'] : wp_generate_password();
            //$validMobile = ltrim( $validMobile, '0' );
            $country_code = getUserCountryCode();
            $result = $this->digits_register_request($validMobile, $password, '', $fullname, $country_code, ltrim($validMobile,'0'));
            $this->sendVerificationCode($validMobile);
        }

        return $result;
    }

    private function validMobile($number) {
        $result = str_replace('+۹۸', '', $number);
        $result = str_replace('+98', '', $result);
        $result = str_replace('۱', '1', $result);
        $result = str_replace('۲', '2', $result);
        $result = str_replace('۳', '3', $result);
        $result = str_replace('۴', '4', $result);
        $result = str_replace('۵', '5', $result);
        $result = str_replace('۶', '6', $result);
        $result = str_replace('۷', '7', $result);
        $result = str_replace('۸', '8', $result);
        $result = str_replace('۹', '9', $result);
        $result = str_replace('۰', '0', $result);
        $len_str = strlen($result);
        if ($len_str > 11 OR $len_str < 10 OR ! is_numeric($result)) {
            $result = false;
        }

        return $result;
    }

    private function doLogin($creds, $user, $userStatus) {
        $pass = $_POST['password'];
        $username = $user->user_login;
        $userValid = wp_authenticate($username, $pass);

        if (is_wp_error($userValid)) {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 4,
                    'message' => __("Wrong information.", "onlinerShopApp")
                /* html_entity_decode(strip_tags($userValid->get_error_message())) */
                )
            );

            return $result;
        }
        $validation_error = new WP_Error();
        $validation_error = apply_filters('woocommerce_process_login_errors', $validation_error, $username, $pass);
        if ($validation_error->get_error_code()) {
            $message = ( __("Error:", 'woocommerce') . $validation_error->get_error_message() );
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 4,
                    'message' => $message
                )
            );

            return $result;
        }
        if (empty($username)) {
            $message = ( __("Error:", 'woocommerce') . __("Username is required.", 'woocommerce') );
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 4,
                    'message' => $message
                )
            );

            return $result;
        }
        if (is_email($username) && apply_filters('woocommerce_get_username_from_email', true)) {
            $user = get_user_by('email', $username);

            if (!$user) {
                $user = get_user_by('login', $username);
            }

            if (isset($user->user_login)) {
                $creds['user_login'] = $user->user_login;
            } else {
                $message = ( __("Error:", 'woocommerce') . __("A user could not be found with this email address.", 'woocommerce') );
                $result = array(
                    'status' => false,
                    'error' => array(
                        'errorCode' => - 4,
                        'message' => $message
                    )
                );

                return $result;
            }
        } else {
            $creds['user_login'] = $username;
        }
        $user = get_user_by('login', $creds['user_login']);
        wp_set_auth_cookie($creds['user_login'], true);
        $user_signon = wp_signon($creds, is_ssl());
        wp_set_current_user($user_signon->ID, $creds['user_login']);

        if (!is_wp_error($user)) {
            $result = $this->login_core($user, false);
        } else {
            $message = $user->get_error_message();
            $message = str_replace('<strong>' . esc_html($creds['user_login']) . '</strong>', esc_html($username), $message);
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 4,
                    'message' => $message
                )
            );
        }

        return $result;
    }
	#login action code for current user that request login whithout any condition
    private function login_core($user, $verify) {
        global $wpdb, $googleID;
        $table = $wpdb->prefix . 'OSA_cart';
        //$wpdb->delete( $table, array( 'googleID' => $creds['user_login'] ) );
        $wpdb->update(
                $table,
                array(
                    'googleID' => $user->user_login
                ),
                array('googleID' => $googleID),
                array(
                    '%s'
                ),
                array('%s')
        );
        /* echo $wpdb->show_errors();
          $wpdb->print_error(); */

        $userToken = implode('-', str_split(substr(strtolower(md5(microtime() . rand(1000, 9999))), 0, 30), 6));
        update_user_meta($user->ID, 'userToken', $userToken);

        wp_set_current_user($user->ID, $user->user_login);
        wp_set_auth_cookie($user->ID, true);
        update_user_meta($user->ID, 'userStatus', true);
        $OSA_avatar = get_user_meta($user->ID, 'OSA_avatar', true);
        $userStatus = get_user_meta($user->ID, 'userStatus', true);
        $userToken = get_user_meta($user->ID, 'userToken', true);
        $countries_obj = new WC_Countries();
        //$countries   = $countries_obj->__get('countries');
        $default_country = $countries_obj->get_base_country();
        $default_county_states = $countries_obj->get_states($default_country);
        foreach ($default_county_states as $index => $default_county_state) {
            $states[] = array('fa' => $default_county_state, 'EN' => $index);
        }
        if ($verify) {
            $username = $user->user_login;
            $valid_mobile = (is_numeric($user->user_login))? $user->user_login : get_user_meta($user->ID,'digits_phone_no',true);
            $result = $this->sendVerificationCode($valid_mobile);
            $result = array(
                'status' => false,
                'data' => array(
                    'user' => array(
                        'name' => $user->display_name,
                        'username' => $valid_mobile,
                        'email' => $user->user_email,
                        'registerDate' => $user->user_registered,
                        'verifyStatus' => $userStatus,
                        'userToken' => $userToken,
                    ),
                ),
                'error' => array(
                    'errorCode' => - 60,
                    'goVerify' => true,
                    'message' => __("Before login need to verify mobile number. please first verify that.", "onlinerShopApp")
                )
            );
        } else {
            $result = array(
                'status' => true,
                'data' => array(
                    'user' => array(
                        'name' => $user->display_name,
                        'username' => $user->user_login,
                        'email' => $user->user_email,
                        'registerDate' => $user->user_registered,
                        'verifyStatus' => $userStatus,
                        'userToken' => $userToken,
                        'OSA_avatar' => $OSA_avatar,
                    ),
                    'message' => __("Login successfully.", "onlinerShopApp"),
                    'states' => $states,
                ),
            );
            update_user_meta($user->ID, 'userStatus', true);
        }

        return apply_filters("osa_user_dologin_result", $result, $user->ID);
    }

    private function sms_login($creds, $user_by_id, $userStatus) {
        $user_id = $user_by_id->ID;
        if ($user_id) {
            $user = get_user_by('id', $user_id);
            $app_loginVerifyType = osa_get_option('app_loginVerifyType');

            if ($app_loginVerifyType == 'password') {
                $result = $this->doLogin($creds, $user, 1);
            } else {
                $this->digits_is_active(true);
                $result = $this->login_core($user, true);
            }
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 15,
                    'message' => __("This user is not exist.", "onlinerShopApp")
                )
            );
        }

        return $result;
    }

    public function login() {
        $email = trim($_POST['email']);
        $is_mobile = $this->validMobile($email);
        $user_id = username_exists($is_mobile);
        if (strlen($is_mobile) == 11 AND ! $user_id) {
            $is_mobile = ltrim($is_mobile, '0');
            $user_id = $this->get_digits_phone_no($is_mobile);
            
        }
        $creds = array(
            'user_password' => $_POST['password'],
            'remember' => isset($_POST['rememberme']),
        );
        if ($is_mobile) { // if is mobile number
            $user_by_id = get_user_by('ID', $user_id);
            $userStatus = get_user_meta($user_by_id->ID, 'userStatus', true);
            $result = $this->sms_login($creds, $user_by_id, $userStatus);
        } else { // if is email or username
            $user_by_email = get_user_by('email', $email);
            $user_by_login = get_user_by('login', $email);
            if ($user_by_email) {
                $userStatus = get_user_meta($user_by_email->ID, 'userStatus', true);
                $result = $this->doLogin($creds, $user_by_email, $userStatus);
            } elseif ($user_by_login) {
                $userStatus = get_user_meta($user_by_login->ID, 'userStatus', true);
                $result = $this->doLogin($creds, $user_by_login, $userStatus);
            }
        }
        if (!$result) {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 5,
                    'message' => __("This user is not exist.", "onlinerShopApp")
                )
            );
        }

        return ( $result );
    }

    public function logout() {
        wp_logout();
        $result = array(
            'status' => true,
            'data' => array(
                'message' => __("Logout successfully.", "onlinerShopApp")
            ),
        );

        return ( $result );
    }

    public function setPassword() {
        $password = $_POST['key'];
        $userToken = $_POST['userToken'];
        $user_id = $this->get_userID_byToken($userToken);
        wp_set_password($password, $user_id);
        $result = array(
            'status' => true,
            'data' => array(
                'message' => __("Password reset successfully.", "onlinerShopApp")
            )
        );

        return ( $result );
    }

    public function forgetPass() {
        $email = ( $_POST['email'] );
        $userToken = $_POST['userToken'];

        $checkMail = $this->checkEmail($email);
        $registerType = osa_get_option('registerType');
        $user_id = email_exists($email);
        if ($registerType == 'email') {

            if ($user_id) {

                if ($this->retrieve_password($user_id)) {
                    $result = array(
                        'status' => true,
                        'data' => array(
                            'message' => __("Reset password link sent to your email.", "onlinerShopApp")
                        )
                    );
                } else {
                    $result = array(
                        'status' => false,
                        'error' => array(
                            'message' => __("Reset password link not sent.", "onlinerShopApp")
                        )
                    );
                }
            } else {
                $result = array(
                    'status' => false,
                    'error' => array(
                        'message' => __("Entered email is not exist.", "onlinerShopApp")
                    )
                );
            }
        } else {
            if ($checkMail) {
                if ($this->retrieve_password($user_id)) {
                    $result = array(
                        'status' => true,
                        'data' => array(
                            'message' => __("Reset password link sent to your email.", "onlinerShopApp")
                        )
                    );
                } else {
                    $result = array(
                        'status' => false,
                        'error' => array(
                            'message' => __("Reset password link not sent.", "onlinerShopApp")
                        )
                    );
                }
            } else {
                $mobile = $this->validMobile($email);
                if (!function_exists('digit_create_otp')) {
                    $result = array(
                        'status' => false,
                        'error' => array(
                            'errorCode' => - 4,
                            'message' => __("Digits plugin not active.", "onlinerShopApp")
                        )
                    );

                    return ( $result );
                }
                if (!isValidMobile($mobile)) {
                    $result = array(
                        'status' => false,
                        'error' => array(
                            'errorCode' => - 4,
                            'message' => __("Entered mobile number invalid.", "onlinerShopApp")
                        )
                    );

                    return ( $result );
                }

                if (!username_exists($mobile)) {
                    $result = array(
                        'status' => false,
                        'error' => array(
                            'errorCode' => - 4,
                            'message' => __("Entered mobile number is not exist.", "onlinerShopApp")
                        )
                    );

                    return ( $result );
                }

                $result = $this->sendVerificationCode($mobile);
                //$user_id = $this->get_userID_byToken($userToken);
                $user = get_user_by('login', $mobile);
                $userStatus = get_user_meta($user->ID, 'userStatus', true);
                $userToken = get_user_meta($user->ID, 'userToken', true);
                $result['data']['user'] = array(
                    'name' => $user->display_name,
                    'username' => $user->user_login,
                    'email' => $user->user_email,
                    'registerDate' => $user->user_registered,
                    'verifyStatus' => $userStatus,
                    'userToken' => $userToken,
                );
            }
        }

        return ( $result );
    }

    private function retrieve_password($user_id) {
        global $wpdb, $current_site;

        if (empty($user_id)) {
            return false;
        } else {
            $user_data = get_user_by('id', $user_id);
        }

        do_action('lostpassword_post');


        if (!$user_data) {
            return false;
        }

        // redefining user_login ensures we return the right case in the email
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;

        do_action('retreive_password', $user_login);  // Misspelled and deprecated
        do_action('retrieve_password', $user_login);

        $allow = apply_filters('allow_password_reset', true, $user_data->ID);

        if (!$allow) {
            return false;
        } else if (is_wp_error($allow)) {
            return false;
        }

        $_POST['user_login'] = $user_login;
        if (class_exists('WC_Shortcode_My_Account')) {
            $success = WC_Shortcode_My_Account::retrieve_password();
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    public function disable_offline_methods(&$methods) {
        $map = ["cod", "cheque", "bacs"];
        foreach ($map as $id) {
            if (isset($methods[$id])) {
                unset($methods[$id]);
            }
        }
    }

    public function getProfile() {
        $userToken = $_POST['userToken'];
        $user_id = $this->get_userID_byToken($userToken);
        if ($user_id) {
            $user = get_userdata($user_id);
            $gateway_list = array();
            $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
            $this->disable_offline_methods($available_gateways);
            foreach ($available_gateways as $available_gateway) {
                $tmp = null;
                if ('yes' == $available_gateway->enabled) {
                    $tmp['id'] = $available_gateway->id;
                    $tmp['title'] = strip_tags($available_gateway->title);
                    $tmp['description'] = $available_gateway->description;
                    $tmp['icon'] = $available_gateway->icon;
                    $gateway_list[] = $tmp;
                }
            }
            $OSA_avatar = get_user_meta($user_id, 'OSA_avatar', true);
            $woo_wallet = $this->service_container->get(Terawallet::class);
            $woo_ballance = ( function_exists("woo_wallet") ) ? $woo_wallet->get_wallet_ballance($user_id) : -1;
            $min_topup_amount = ( function_exists("woo_wallet") ) ? (int) woo_wallet()->settings_api->get_option('min_topup_amount', '_wallet_settings_general', 0) : -1;
            $max_topup_amount = ( function_exists("woo_wallet") ) ? (int) woo_wallet()->settings_api->get_option('max_topup_amount', '_wallet_settings_general', 0) : -1;
            $result = array(
                'status' => true,
                'data' => array(
                    'userData' => array(
                        'username' => $user->user_login,
                        'name' => $user->display_name,
                        'email' => $user->user_email,
                        'woo_ballance' => $woo_ballance,
                        'min_topup_amount' => $min_topup_amount,
                        'max_topup_amount' => $max_topup_amount,
                        'gateway_list' => $gateway_list,
                        'password' => '',
                        'verify' => '',
                        'OSA_avatar' => $OSA_avatar,
                    )
                ),
            );
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => __("Token is invalid", "onlinerShopApp")
                )
            );
        }

        return ( $result );
    }

    public function setProfile() {
        $userToken = $_POST['userToken'];
        $user_id = $this->get_userID_byToken($userToken);
        if ($user_id) {

            $display_name = $_POST['name'];
            $email = $_POST['email'];
            $pass = $_POST['pass'];
            $verifyPass = $_POST['verifyPass'];

            $avatar = $_POST['avatar'];


            //$path = "uploads/".$_POST['name'].".png";
            //$actualpath = $path;
            //file_put_contents($path,base64_decode($avatar));
            //echo base64_decode($avatar);
            //$uploadDirs = ( wp_upload_dir() );
            $dir = ABSPATH . "/uploads";
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            //$target_path1 = $uploadDirs['basedir'].'/OSA_avatars/'. basename( $_FILES['avatar']['name']);
            /* $target_path1 = $uploadDirs['basedir'] . '/OSA_avatars/' . basename( $_FILES['avatar']['name'] ); */

            $time = time() . '_';
            $filePath = $dir . "/" . $time . ".jpg";
            file_put_contents($filePath, base64_decode($avatar));
            //move_uploaded_file( $_FILES['avatar']['tmp_name'], $uploadDirs['basedir'] . '/OSA_avatars/' . $time . basename( $_FILES['avatar']['name'] ) );
            $url = home_url();


            $target_url = $url . "/uploads/" . $time . ".jpg";


            $user_id = wp_update_user(
                    array(
                        'ID' => $user_id,
                        'user_email' => $email,
                        'display_name' => $display_name,
                        'user_pass' => ( $pass == $verifyPass AND $pass != '' ) ? $pass : '',
            ));
            update_user_meta($user_id, 'OSA_avatar', $target_url);

            $result = array(
                'status' => true,
                'data' => array(
                    'name' => $display_name,
                    'avatar' => '',
                    'avatarFile' => '',
                ),
            );
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => __("Token is invalid", "onlinerShopApp")
                )
            );
        }

        return ( $result );
    }

    public function addToWishlist() {
        $userToken = $_POST['userToken'];
        $product_id = $_POST['product_id'];
        $user_id = $this->get_userID_byToken($userToken);
        if ($user_id) {
            if (function_exists('YITH_WCWL')) {
                global $wpdb;
                $wcwl_table = $wpdb->prefix.'yith_wcwl';
                $data = array(
                    'prod_id' => $product_id,
                    'quantity' => 1,
                    'user_id' => $user_id,
                    'wishlist_id' => 0,
                );
                $format = array('%d','%d','%d','%d');
                $wpdb->insert($wcwl_table, $data, $format);
                $result = array(
                    'status' => true,
                    'data' => array()
                );
            } else {
                $result = array(
                    'status' => false,
                    'error' => array(
                        'errorCode' => - 19,
                        'message' => __("yith-woocommerce-wishlist plugin is not active. please first active that.", "onlinerShopApp")
                    )
                );
            }
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => __("Token is invalid", "onlinerShopApp")
                )
            );
        }

        return ( $result );
    }

    public function removeFromWishlist() {
        $userToken = $_POST['userToken'];
        $product_id = $_POST['product_id'];

        $user_id = $this->get_userID_byToken($userToken);
        if ($user_id) {
            if (function_exists('YITH_WCWL')) {
                global $wpdb;
                $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "yith_wcwl WHERE prod_id = %d AND user_id = %d", $product_id, $user_id));
                if ($result) {
                    $wpdb->delete($wpdb->prefix . 'yith_wcwl', array(
                        'prod_id' => $product_id,
                        'user_id' => $user_id
                    ));
                }


                $result = array(
                    'status' => true,
                    'data' => array()
                );
            } else {
                $result = array(
                    'status' => false,
                    'error' => array(
                        'errorCode' => - 19,
                        'message' => __("yith-woocommerce-wishlist plugin is not active. please first active that.", "onlinerShopApp")
                    )
                );
            }
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => __("Token is invalid", "onlinerShopApp")
                )
            );
        }

        return ( $result );
    }

    public function Wishlist() {
        $userToken = $_POST['userToken'];
        $user_id = $this->get_userID_byToken($userToken);
        $this->user_id = $user_id;
        if ($user_id) {
            if (function_exists('YITH_WCWL')) {
                global $wpdb;
                $result = $wpdb->get_results($wpdb->prepare("SELECT prod_id FROM " . $wpdb->prefix . "yith_wcwl WHERE user_id = %d", $user_id),ARRAY_N);
                $products = array();
                if (!empty($result)) {
                    $product_ids = array();
                    foreach($result as $product_id){
                        $product_ids[] = current($product_id);
                    }
                    $product_ids = array_unique($product_ids);
                    foreach ($product_ids as $item) {
                        $product = wc_get_product($item);
                        if ($product) {
                            if (has_post_thumbnail($item)) {
                                $img_id = get_post_thumbnail_id($item);
                                $thumb = wp_get_attachment_image_src($img_id, 'medium')[0];
                            } else {
                                $thumb = $img = OSA_PLUGIN_URL . "/assets/images/notp.png";
                            }
                            $tmp['id'] = intval($item);
                            $tmp['title'] = get_the_title($item);
                            $enTitle = get_post_meta($item, '_subtitle', true);
                            $tmp['EN_title'] = ( $enTitle ) ? $enTitle : get_post_meta($item, '_ENtitle', true);
                            $tmp['thumbnail'] = $thumb;


                            if ($product->get_type() == 'variable') {
                                $tmp['regular_price'] = get_post_meta($item, '_price', true);
                            } else {
                                $tmp['regular_price'] = get_post_meta($item, '_regular_price', true);
                            }
                            if (function_exists("YITH_Role_Based_Type")) {
                                $tmp['regular_price'] = $this->yith_price_role->get_compute_price_render($product, $this->user_id);
                            }
                            $tmp['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
                            $products[] = $tmp;
                        }
                    }
                }
                $result = array(
                    'status' => true,
                    'data' => array(
                        'wishlist' => $products
                    )
                );
            } else {
                $result = array(
                    'status' => false,
                    'error' => array(
                        'errorCode' => - 19,
                        'message' => __("yith-woocommerce-wishlist plugin is not active. please first active that.", "onlinerShopApp")
                    )
                );
            }
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => __("Token is invalid", "onlinerShopApp")
                )
            );
        }

        return ( $result );
    }

    public function commentLike() {
        $userToken = $_POST['userToken'];
        $user_id = $this->get_userID_byToken($userToken);
        if ($user_id) {
            $commentId = $_POST['commentId'];
            $likeAction = $_POST['likeAction'];
            $like_count = get_comment_meta($commentId, 'cld_like_count', true);
            $dislike_count = get_comment_meta($commentId, 'cld_dislike_count', true);
            global $wpdb;
            $row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}commentLikes WHERE user_id = {$user_id} AND comment_id = {$commentId}");
            if (null == $row) {
                $wpdb->insert(
                        $wpdb->prefix . 'commentLikes',
                        array(
                            'user_id' => $user_id,
                            'comment_id' => $commentId,
                            'action' => $likeAction,
                        ),
                        array(
                            '%d',
                            '%d',
                            '%s'
                        )
                );
                if ($likeAction == 'like') {
                    update_comment_meta($commentId, 'cld_like_count', $like_count + 1);
                } else {
                    update_comment_meta($commentId, 'cld_dislike_count', $dislike_count + 1);
                }
                $like_count = get_comment_meta($commentId, 'cld_like_count', true);
                $dislike_count = get_comment_meta($commentId, 'cld_dislike_count', true);
            } else {
                $wpdb->update(
                        $wpdb->prefix . 'commentLikes',
                        array(
                            'action' => $likeAction
                        ),
                        array(
                            'id' => $row->id
                        ),
                        array('%s'),
                        array(
                            '%d'
                        )
                );
                if ($row->action == 'like') {
                    if ($likeAction == 'dislike') {
                        update_comment_meta($commentId, 'cld_dislike_count', intval($dislike_count) + 1);
                        update_comment_meta($commentId, 'cld_like_count', intval($like_count) - 1);
                    } elseif ($likeAction == 'like') {
                        update_comment_meta($commentId, 'cld_like_count', intval($like_count) - 1);
                    }
                } elseif ($row->action == 'dislike') {
                    if ($likeAction == 'like') {
                        update_comment_meta($commentId, 'cld_dislike_count', intval($dislike_count) - 1);
                        update_comment_meta($commentId, 'cld_like_count', intval($like_count) + 1);
                    } elseif ($likeAction == 'dislike') {
                        update_comment_meta($commentId, 'cld_dislike_count', intval($dislike_count) - 1);
                    }
                } else {
                    if ($likeAction == 'like') {
                        update_comment_meta($commentId, 'cld_like_count', intval($like_count) + 1);
                    } elseif ($likeAction == 'dislike') {
                        update_comment_meta($commentId, 'cld_dislike_count', intval($dislike_count) + 1);
                    }
                }

                $like_count = get_comment_meta($commentId, 'cld_like_count', true);
                $dislike_count = get_comment_meta($commentId, 'cld_dislike_count', true);
            }
            $result = array(
                'status' => true,
                'data' => array(
                    'comment_result' => array(
                        'likeCount' => intval($like_count),
                        'dislikeCount' => intval($dislike_count),
                        'message' => __("Your vote saved.", "onlinerShopApp")
                    )
                )
            );
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => __("Token is invalid", "onlinerShopApp")
                )
            );
        }

        return ( $result );
    }

    public function get_addresses() {
        $userToken = $_POST['userToken'];
        $user_id = $this->get_userID_byToken($userToken);
        if ($user_id) {
            $data = array();
            $addressType = 'billing';
            $billing['first_name'] = get_user_meta($user_id, $addressType . '_first_name', true);
            $billing['last_name'] = get_user_meta($user_id, $addressType . '_last_name', true);
            $billing['company'] = get_user_meta($user_id, $addressType . '_company', true);
            $billing['address_1'] = get_user_meta($user_id, $addressType . '_address_1', true);
            $billing['city'] = get_user_meta($user_id, $addressType . '_city', true);
            $billing['state'] = get_user_meta($user_id, $addressType . '_state', true);
            $billing['postcode'] = get_user_meta($user_id, $addressType . '_postcode', true);
            $billing['country'] = get_user_meta($user_id, $addressType . '_country', true);
            $billing['email'] = get_user_meta($user_id, $addressType . '_email', true);
            $billing['mobile'] = get_user_meta($user_id, $addressType . '_mobile', true);
            $billing['phone'] = get_user_meta($user_id, $addressType . '_phone', true);
            $billing['lat'] = get_user_meta($user_id, $addressType . '_lat', true);
            $billing['lng'] = get_user_meta($user_id, $addressType . '_lng', true);
            $addressType = 'shipping';
            $shipping['first_name'] = get_user_meta($user_id, $addressType . '_first_name', true);
            $shipping['last_name'] = get_user_meta($user_id, $addressType . '_last_name', true);
            $shipping['company'] = get_user_meta($user_id, $addressType . '_company', true);
            $shipping['address_1'] = get_user_meta($user_id, $addressType . '_address_1', true);
            $shipping['city'] = get_user_meta($user_id, $addressType . '_city', true);
            $shipping['state'] = get_user_meta($user_id, $addressType . '_state', true);
            $shipping['postcode'] = get_user_meta($user_id, $addressType . '_postcode', true);
            $shipping['country'] = get_user_meta($user_id, $addressType . '_country', true);
            $shipping['mobile'] = get_user_meta($user_id, $addressType . '_mobile', true);
            $shipping['phone'] = get_user_meta($user_id, $addressType . '_phone', true);
            $shipping['lat'] = get_user_meta($user_id, $addressType . '_lat', true);
            $shipping['lng'] = get_user_meta($user_id, $addressType . '_lng', true);


            $billing_status = get_user_meta($user_id, 'billing_status', true);
            if ($billing_status == 'active') {
                $data['billing'] = $billing;
                unset($data['billing']['address_2']);
                $data['billing']['mobile'] = get_user_meta($user_id, 'billing_mobile', true);
                $data['billing']['phone'] = get_user_meta($user_id, 'billing_phone', true);
            } else {
                unset($data['billing']);
            }
            $shipping_status = get_user_meta($user_id, 'shipping_status', true);
            if ($shipping_status == 'active') {
                $data['shipping'] = $shipping;
                unset($data['shipping']['address_2']);
                $data['shipping']['mobile'] = get_user_meta($user_id, 'shipping_mobile', true);
                $data['shipping']['phone'] = get_user_meta($user_id, 'shipping_phone', true);
            } else {
                unset($data['shipping']);
            }

            $result = array(
                'status' => true,
                'data' => $data,
            );
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'errorCode' => - 12,
                    'message' => __("Token is invalid", "onlinerShopApp")
                )
            );
        }

        return ( $result );
    }

    public function edit_address() {
        $userToken = $_POST['userToken'];
        $addressType = $_POST['addressType'];
        $user_id = $this->get_userID_byToken($userToken);
        if (!is_numeric($user_id)) {
            return array(
                'status' => false,
                'error' => array(
                    'message' => __("Token is invalid", "onlinerShopApp")
                )
            );
        }
        $fields = json_decode(stripcslashes($_POST['fields']), true);
        foreach($fields as $key => $value){
            if(strpos($key, "email") && !is_email($value) && !empty($value)){
                $errors[$key] = __("Email is invalid","onlinerShopApp");
            }
        }
        if(!empty($errors) && is_array($errors)){
            return array(
                "status" => false,
                'error_fields' => $errors
            );
        }
        foreach ($fields as $key => $value) {
            update_user_meta($user_id, $key, $value);
        }
        $status = $_POST[$addressType . '_status'];
        update_user_meta($user_id, $addressType . '_status', $status);
        $result = array(
            'status' => true,
            'data' => $fields,
        );
        return ( $result );
    }

    public function digits_register_request($username,$password,$email,$fullname,$country_code,$phone_no){
        $user_token = $this->make_user_token();
        $register_date = date('Y-m-d h:i:s');
        $result = array(
            "status" => true,
            "data" => array(
                'user' =>  array(
                    "name" => $fullname,
                    "username" => $username,
                    "email" => $email,
                    "registerDate" => date('Y-m-d h:i:s'),
                    "userToken" => $user_token
                ),
                "verify" => true,
                "message" => "digits register request is successfully"
            ),
        );
        $session_data = array(
            "digits_country_code" => $country_code,
            "digits_phone_no" => $phone_no,
            "digits_phone" => $country_code.$phone_no,
            "register_date" => $register_date,
            "userToken" => $user_token,
            "username" => $username,
            "password" => $password,
            "email" => $email,
            "fullname" => $fullname,
            'googleID' => $_POST['googleID'],
            "role" => apply_filters("osa_user_register_user_role",osa_get_option('default_role')),
        );
        update_option($user_token,$session_data);
        return $result;
    }

    public function digits_register_core($session_data){
        $username = $session_data['username'];
        $passowrd = $session_data['password'];
        $email = $session_data['email'];
        $fullname = $session_data['fullname'];
        $role = $session_data['role'];
        $user_id = wp_create_user($username,$password);
        if(is_wp_error($user_id)){
            return $user_id;
        }
        $user_data = array(
            "ID" => (int) $user_id, 
            "user_email" => $email,
            "display_name" => $fullname,
            "first_name" => $fullname,
            "role" => $role,
        );
        wp_update_user($user_data);
        $meta_data = array(
            "userToken" => $session_data['userToken'],
            "digits_country_code" => $session_data['digits_country_code'],
            "digits_phone_no" => $session_data['digits_phone_no'],
            "digits_phone" => $session_data['digits_phone'],
            "register_date" => $session_data['register_date'],
            "userStatus" => true
        );
        foreach($meta_data as $meta_key => $meta_value){
            update_user_meta($user_id,$meta_key,$meta_value);
        }
        global $wpdb;
        $google_id = $session_data['googleID'];
        $table = $wpdb->prefix . "OSA_cart";
        $data_update = array('googleID'=>$username);
        $data_where = array('googleID'=>$google_id);
        $data_format = $data_where_format  = array('%s');
        $wpdb->update($table,$data_update,$data_where,$data_format,$data_where_format);
        $table = $wpdb->prefix . "OSA_view_log";
        $wpdb->update($table,$data_update,$data_where,$data_format,$data_where_format);
        do_action('osa_user_register_complate',$user_id);
        return $user_id;
    }
	#set user information on wp_option session
    public function get_digits_details($user_token){
        $user_id = $this->get_userID_byToken($user_token);
        if(is_numeric($user_id) && false != $user_id){
            $user = get_user_by('id',$user_id);
            $countrycode = sanitize_text_field(getUserCountryCode());
            $countrycode = ( $countrycode == '+' ) ? $countrycode . '98' : $countrycode;
            return array(
                'user_id' => $user->ID,
                'digits_country_code' => $countrycode,
                'username' => $user->user_login
            );
        }
        $session_data = get_option($user_token);
        if(!empty($session_data) || isset($session_data['username'])){
            return $session_data;
        }
        return false;
    }

    public function make_user_token(){
        return implode('-', str_split(substr(strtolower(md5(microtime() . rand(1000, 9999))), 0, 30), 6));
    }

    public function vendorProduct(){
        $index = $this->service_container->get(Index::class);
        $user_id = $this->get_userID_byToken($_POST['userToken']);
        $paged = (isset($_POST['paged']))? $_POST['paged'] : 1;
        if(empty($user_id)){
            wp_send_json(array(
                "status" => false,
                "message" => __("user not founded","crn")
            ));
        }
        $args = array(
            'posts_per_page' => 10,
            'paged'          => $paged,
            'author'         => $user_id,
            'post_type'      => array('product'),
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => apply_filters( 'dokan_product_listing_exclude_type', array() ),
                    'operator' => 'NOT IN',
                ),
            ),
        );
        $args_filtered = apply_filters("crn_vendor_products_filters",$args);
        $wp_query = new WP_Query($args_filtered);
        if($wp_query->have_posts()){
            while($wp_query->have_posts()){
                $wp_query->the_post();
                $product = wc_get_product(get_the_ID());
                //INDEX
                $product_info['id'] = get_the_ID();
                $product_info['title'] = html_entity_decode(get_the_title());
                $product_info['thumbnail'] = (has_post_thumbnail()) ? current(wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'medium')) : OSA_PLUGIN_URL . "/assets/images/notp.png";
                $product_info['type'] = ( $product->get_type() == 'grouped' OR $product->get_type() == 'simple_catalogue' ) ? 'simple_catalogue' : $product->get_type();
                $prices = $index->filter_prices($product);
                $product_info['regular_price'] = trim($prices['regular_price']);
                $product_info['sale_price'] = trim($prices['sale_price']);
                $product_info['stock_status'] = $product->get_stock_status();
                $product_info['currency'] = html_entity_decode(get_woocommerce_currency_symbol());
                //INDEX
                $data['products'][] = $product_info;
            }
        }else{
            $data['products'] = array();
        }
        wp_reset_postdata();
        return(array(
            "status" => true,
            "data" => $data
        ));
    }

}
