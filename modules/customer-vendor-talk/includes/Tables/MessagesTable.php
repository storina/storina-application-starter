<?php

namespace CRN\Tables;

class MessagesTable {

    public $wpdb;

    const table = "woap_messages";

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function up() {
        $messages = $this->wpdb->prefix . self::table;
        $sql = "CREATE TABLE IF NOT EXISTS {$messages} ("
                . "id BIGINT(20) NOT NULL AUTO_INCREMENT, "
                . "content TEXT, "
                . "customer_id BIGINT(20) UNSIGNED, "
                . "vendor_id BIGINT(20) UNSIGNED, "
                . "product_id BIGINT(20) UNSIGNED, "
                . "updated_at INT(32) NOT NULL, "
                . "PRIMARY KEY (id) "
                . ")"
                . "CHARACTER SET utf8 "
                . "COLLATE utf8_general_ci";
        dbDelta($sql);
    }

}