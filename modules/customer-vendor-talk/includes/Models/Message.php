<?php

namespace CRN\Models;

use WP_Error;
use Libraries\Model;

class Message extends Model{

    protected $data = array(
        "content" => "",
        "vendor_id" => 0,
        "customer_id" => 0,
        "owner_id" => 0,
        "attachment_slug" => "",
        "product_id" => 0,
        "updated_at" => 0,
        "seen" => 0
    );

    const table = "woap_messages";
    const table_meta = "woap_messagemeta";

    public static function create($data=null){
        global $wpdb;
        $table = $wpdb->prefix . self::table;
        $table_meta = $wpdb->prefix . self::table_meta;
        $values = array(
            'content' => $data['content'],
            'vendor_id' => $data['vendor_id'],
            'customer_id' => $data['customer_id'],
            'product_id' => $data['product_id'],
            'updated_at' => $data['updated_at']
        );
        $wpdb->insert($table,$values);
        $message_id = $wpdb->insert_id;
        $message_metas = [
            [
                "meta_key" => "attachment_slug",
                "meta_value" => !empty($data['attachment_slug'])? $data['attachment_slug'] : 0,
                "message_id" => $message_id
            ],
            [
                "meta_key" => "owner_id",
                "meta_value" => isset($data["owner_id"])? $data["owner_id"] : 0,
                "message_id" => $message_id
            ],
            [
                "meta_key" => "seen",
                "meta_value" => isset($data["seen"])? $data["seen"] : 0,
                "message_id" => $message_id
            ],
        ];
        $format = array("%s","%s","%d");
        foreach($message_metas as $message_meta){
            $wpdb->insert($table_meta,$message_meta,$format);
        }
        return (is_numeric($message_id))? $message_id : new WP_Error(500,"message insert fail");
    }

    public static function get($id){
        global $wpdb;
        $table = $wpdb->prefix . self::table;
        $table_meta = $wpdb->prefix . self::table_meta;
        $message_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d",$id),ARRAY_A);
        if(empty($message_row)){
            return false;
        }
        $attachmet_id = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$table_meta} WHERE message_id=%d AND meta_key=%s",$message_row['id'],"attachment_slug"));
        $owner_id = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$table_meta} WHERE message_id=%d AND meta_key=%s",$message_row['id'],"owner_id"));
        $seen = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$table_meta} WHERE message_id=%d AND meta_key=%s",$message_row['id'],"seen"));
        $data = array(
            "content" => $message_row["content"],
            "attachment_slug" => $attachmet_id,
            "vendor_id" => $message_row["vendor_id"],
            "customer_id" => $message_row["customer_id"],
            'product_id' => $message_row['product_id'],
            "updated_at" => $message_row["updated_at"],
            "owner_id" => $owner_id,
            "seen" => $seen,
        );
        $message = new Message($data);
        $message->id = $message_row['id'];
        return $message;
    }

    public static function update($id,$data){}

    public static function delete($id){}

    public static function get_message_list($customer_id,$vendor_id,$product_id,$start,$end){
        global $wpdb;
        $table = $wpdb->prefix . self::table;
        $table_meta = $wpdb->prefix . self::table_meta;
        $query = "SELECT ";
        $query .= "message.id ,message.content ,message.customer_id ,message.vendor_id, message.product_id, message.updated_at ,GROUP_CONCAT(message_meta.meta_value) as meta_values ,GROUP_CONCAT(message_meta.meta_key) as meta_keys ";
        $query .= "FROM ";
        $query .= "{$table} AS message LEFT JOIN {$table_meta} AS message_meta ";
        $query .= "ON ";
        $query .= "message.id = message_meta.message_id ";
        $query .= "WHERE ";
        $query .= "message.customer_id = {$customer_id} AND message.vendor_id = {$vendor_id} AND product_id = {$product_id} AND (message_meta.meta_key = 'owner_id' OR message_meta.meta_key = 'attachment_slug' OR message_meta.meta_key = 'seen') ";
        $query .= "GROUP BY message_meta.message_id ORDER BY message.id DESC LIMIT {$start},{$end}";
        $message_rows = $wpdb->get_results($query,ARRAY_A);
        if(empty($message_rows)){
            return false;
        }
        foreach($message_rows as $message_row){
            $meta_keys = explode(",",$message_row['meta_keys']);
            $meta_values = explode(",",$message_row['meta_values']);
            $meta_data = [];
            for($i=0;$i<count($meta_keys);$i++){
                $meta_data[$meta_keys[$i]] = $meta_values[$i];
            }
            $message = new Message();
            $message->id = $message_row['id'];
            unset($message_row['meta_keys'],$message_row['meta_values'],$message_row['id']);
            $data = array_merge($message_row,$meta_data);
            $message->set_data($data);
            $messages[] = $message;
        }
        return $messages;
    }

    public static function get_user_message_list($user_id,$start,$end){
        global $wpdb;
        $table = $wpdb->prefix . self::table;
        $query = "SELECT GROUP_CONCAT(id) AS message_ids, customer_id, vendor_id, product_id FROM {$table} ";
        $query .= "WHERE customer_id = {$user_id} OR vendor_id = {$user_id} ";
        $query .= "GROUP BY product_id,customer_id ORDER BY id DESC LIMIT {$start},{$end}";
        $message_rows = $wpdb->get_results($query,ARRAY_A);
        return $message_rows;
    }

    public static function seen_messages($message_ids){
        global $wpdb;
        $table = $wpdb->prefix . self::table_meta;
        $message_ids_implode = implode(",",$message_ids);
        $message_ids_string = "($message_ids_implode)";
        $sql = "UPDATE {$table} SET meta_value=1 WHERE meta_key = 'seen' AND message_id IN $message_ids_string";
        return $wpdb->query($sql);
    }

    public static function get_unseen_messages_count($user_info){
        global $wpdb;
        $table = $wpdb->prefix . self::table;
        $table_meta = $wpdb->prefix . self::table_meta;
        $column_name = current(array_keys($user_info));
        $column_value = current(array_values($user_info));
        $sql = "SELECT COUNT(message.id) ";
        $sql .= "FROM {$table} as message , {$table_meta} AS messagemeta_i , {$table_meta} AS messagemeta_ii ";
        $sql .= "WHERE ";
        $sql .= "message.{$column_name} = {$column_value} AND ";
        $sql .= "message.id = messagemeta_i.message_id AND message.id = messagemeta_ii.message_id AND ";
        $sql .= "messagemeta_i.meta_key = 'seen' AND messagemeta_i.meta_value = 0 AND ";
        $sql .= "messagemeta_ii.meta_key = 'owner_id' AND messagemeta_ii.meta_value <> {$column_value} ";
        return $wpdb->get_var($sql);
    }

}