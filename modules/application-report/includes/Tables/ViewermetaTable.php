<?php 

namespace VOS\Tables;

use VOS\Tables\ViewerTable AS Viewer;

class ViewermetaTable {

    public $wpdb;

    public function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    const table = 'woap_viewermeta';

    public function up(){
        $viewermeta = $this->wpdb->prefix . self::table;
        $viewer = $this->wpdb->prefix . Viewer::table;
        $sql = "CREATE TABLE IF NOT EXISTS {$viewermeta} ("
                . "id BIGINT(20) NOT NULL AUTO_INCREMENT, "
                . "meta_key varchar(255) DEFAULT NULL,"
                . "meta_value longtext,"
                . "viewer_id BIGINT(20) NOT NULL,"
                . "PRIMARY KEY (id),"
                . "FOREIGN KEY (viewer_id) REFERENCES {$viewer}(id) ON DELETE CASCADE ON UPDATE CASCADE"
                . ")"
                . "CHARACTER SET utf8 "
                . "COLLATE utf8_general_ci";
        dbDelta($sql);
    }

}