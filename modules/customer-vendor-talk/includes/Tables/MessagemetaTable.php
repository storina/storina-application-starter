<?php 

namespace CRN\Tables;

use CRN\Tables\MessagesTable as Messages;

class MessagemetaTable {

public $wpdb;

const table = "woap_messagemeta";

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function up() {
        $messages = $this->wpdb->prefix . Messages::table;
        $messagemeta = $this->wpdb->prefix . self::table;
        $sql = "CREATE TABLE IF NOT EXISTS {$messagemeta} ("
                . "id BIGINT(20) NOT NULL AUTO_INCREMENT, "
                . "meta_key varchar(255) DEFAULT NULL,"
                . "meta_value longtext,"
                . "message_id BIGINT(20) NOT NULL,"
                . "PRIMARY KEY (id),"
                . "FOREIGN KEY (message_id) REFERENCES {$messages}(id) ON DELETE CASCADE ON UPDATE CASCADE"
                . ")"
                . "CHARACTER SET utf8 "
                . "COLLATE utf8_general_ci";
        dbDelta($sql);
    }

}