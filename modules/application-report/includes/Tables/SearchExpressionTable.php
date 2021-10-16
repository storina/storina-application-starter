<?php

namespace VOS\Tables;

class SearchExpressionTable {

    public $wpdb;

    const table = "woap_search_expression";

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function up() {
        $search = $this->wpdb->prefix . self::table;
        $sql = "CREATE TABLE IF NOT EXISTS {$search} ("
                . "id BIGINT(20) NOT NULL AUTO_INCREMENT, "
                . "expression VARCHAR(512) , "
                . "operator BIGINT(20) UNSIGNED, "
                . "count INT(30) UNSIGNED DEFAULT 1, "
                . "updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, "
                . "PRIMARY KEY (id) "
                . ")"
                . "CHARACTER SET utf8 "
                . "COLLATE utf8_general_ci";
        dbDelta($sql);
    }

}
