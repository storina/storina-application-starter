<?php

namespace VOS\Models;

use VOS\Libraries\Model;

class Viewer extends Model {

    const table = "woap_viewer";
    const tablemeta = 'woap_viewermeta';

    public $data = [
        'identifier' => 0,
        'session_id' => 0,
        'action_slug' => '',
        'action_id' => 0,
        'authentication' => 0,
        'client_type' => '',
        'expire_time' => 0,
        'notif_token' => '',
        'current_version' => 0,
    ];

    public static function create($data){
        global $wpdb;
        $table = $wpdb->prefix . self::table;
        $format = ['%d','%d','%s','%d','%d','%s','%d'];
        $viewer_data = array_slice($data,0,7);
        $wpdb->insert($table,$viewer_data,$format);
        $viewer_id = $wpdb->insert_id;
        $tablemeta = $wpdb->prefix . self::tablemeta;
        $viewermeta_data = array_slice($data,-2,2);
        foreach($viewermeta_data as $meta_key => $meta_value){
            $meta_data = [
                'viewer_id' => $viewer_id,
                'meta_key' => $meta_key,
                'meta_value' => $meta_value
            ];
            $meta_format = ['%d','%s','%s'];
            $wpdb->insert($tablemeta,$meta_data,$meta_format);
        }
        return $viewer_id;
    }

    public static function update($id,$data){
        global $wpdb;
        $table = $wpdb->prefix . self::table;
        $viewer_data = array_slice($data,0,7);
        $viewermeta_data = array_slice($data,-2,2);
        $where = ['id' => intval($id)];
        $format = ['%d','%d','%s','%d','%d','%s','%d'];
        $wpdb->update($table, $viewer_data, $where, $format);
        $tablemeta = $wpdb->prefix . self::tablemeta;
        foreach($viewermeta_data as $meta_key => $meta_value){
            $meta_data = [
                'meta_value' => $meta_value
            ];
            $meta_where = [
                'viewer_id' => (int) $id,
                'meta_key' => $meta_key,
            ];
            $meta_format = ['%s'];
            $meta_where_format = ['%d','%s'];
            $wpdb->update($tablemeta,$meta_data,$meta_where,$meta_format,$meta_where_format);
        }
    }

    public static function get($id){}
    
    public static function delete($id){}

    public static function get_viewer_by($data_column, $data_value, $data_format){
        global $wpdb;
        $table = $wpdb->prefix . self::table;
        $query = "SELECT * FROM {$table} WHERE {$data_column} = {$data_format}";
        $prepared_query = $wpdb->prepare($query,$data_value);
        $viewer_row = $wpdb->get_row($prepared_query,ARRAY_A);
        if(empty($viewer_row)){
            return false;
        }
        $meta_data = [];
        $tablemeta = $wpdb->prefix . self::tablemeta;
        $query = "SELECT * FROM {$tablemeta} WHERE viewer_id = %d";
        $prepared_query = $wpdb->prepare($query,[$viewer_row['id']]);
        $viewermeta_rows = $wpdb->get_results($prepared_query,ARRAY_A);
        foreach($viewermeta_rows as $viewermeta_row){
            $key = $viewermeta_row['meta_key'];
            $value = $viewermeta_row['meta_value'];
            $meta_data[$key] = $value;
        }
        $viewer = new Viewer;
        $viewer->id = $viewer_row['id'];
        unset($viewer_row['id']);
        $viewer->set_data(array_merge($viewer_row,$meta_data));
        return $viewer;
    }

    public static function get_viewer_by_identifier($data_value){
        $data_column = 'identifier';
        $data_format = '%d';
        return self::get_viewer_by($data_column, $data_value, $data_format);
    }

    public static function get_online_viewers($limit,$offset){
        global $wpdb;
        $table = $wpdb->prefix . self::table;
        $tablemeta = $wpdb->prefix . self::tablemeta;
        $time = time();
        $query = "SELECT ";
        $query .= "viewer.id , viewer.identifier , viewer.session_id , viewer.action_slug , viewer.action_id , viewer.authentication , viewer.client_type , ";
        $query .= "viewer.expire_time , GROUP_CONCAT(viewermeta.meta_key) AS meta_key , GROUP_CONCAT(viewermeta.meta_value) AS meta_value ";
        $query .= "FROM {$table} AS viewer INNER JOIN {$tablemeta} AS viewermeta ";
        $query .= "ON viewermeta.viewer_id = viewer.id ";
        $query .= "WHERE viewer.expire_time > {$time} ";
        $query .= "GROUP BY viewer.id ";
        $query .= "LIMIT {$limit} OFFSET {$offset} ";
        $viewer_rows = $wpdb->get_results($query,ARRAY_A);
        if(empty($viewer_rows)){
            return false;
        }
        foreach($viewer_rows as $viewer_row){
            $viewer = new Viewer;
            $viewer->id = $viewer_row['id'];
            unset($viewer_row['id']);
            $meta_data = [];
            $meta_keys = explode(',',$viewer_row['meta_key']);
            $meta_values = explode(',',$viewer_row['meta_value']);
            for($i=0;$i<count($meta_keys);$i++){
                $meta_data[$meta_keys[$i]] = $meta_values[$i];
            }
            $viewer->set_data(array_merge($viewer_row,$meta_data));
            $viewer_results[] = $viewer;
        }
        return $viewer_results;
    }

    public static function get_online_viewers_count(){
        global $wpdb;
        $table = $wpdb->prefix . self::table;
        $tablemeta = $wpdb->prefix . self::tablemeta;
        $time = time();
        $current_timestamp = time();
        $sql = "SELECT COUNT(*) FROM ( ";
        $sql .= "SELECT viewer.id ";
        $sql .= "FROM {$table} AS viewer ";
        $sql .= "INNER JOIN {$tablemeta} AS viewermeta ";
        $sql .= "ON viewermeta.viewer_id = viewer.id ";
        $sql .= "WHERE viewer.expire_time > {$time} ";
        $sql .= "GROUP BY viewer.id ";
        $sql .= ") AS ONLINE_VIEWERS ";
        return $wpdb->get_var($sql);
    }
}