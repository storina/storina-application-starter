<?php

namespace VOS\Tables;

class ViewerTable {

    public $wpdb;

    const table = "woap_viewer";
    const tablemeta = 'woap_viewermeta';

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function up() {
        $viewer = $this->wpdb->prefix . self::table;
        $sql = "CREATE TABLE IF NOT EXISTS {$viewer} ("
                . "id BIGINT(20) NOT NULL AUTO_INCREMENT, "
                . "identifier BIGINT(20) UNSIGNED, "
                . "session_id BIGINT(20) UNSIGNED, "
                . "action_slug VARCHAR(128) DEFAULT 'index', "
                . "action_id BIGINT(20), "
                . "authentication BOOLEAN DEFAULT 0, "
                . "client_type VARCHAR(32) NOT NULL, "
                . "expire_time BIGINT(20), "
                . "updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, "
                . "PRIMARY KEY (id) "
                . ")"
                . "CHARACTER SET utf8 "
                . "COLLATE utf8_general_ci";
        dbDelta($sql);
    }

}
