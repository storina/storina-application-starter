<?php

namespace VOS\Models;

use VOS\Libraries\Model;

class SearchExpression extends Model {

    const table = "woap_search_expression";

    public $data = [
        'expression' => '',
        'operator' => 0,
        'count' => 0,
    ];

    public static function create($data){
        global $wpdb;
        $table = $wpdb->prefix . self::table;
        $format = ['%s','%d','%d'];
        $wpdb->insert($table,$data,$format);
        $insert_id = $wpdb->insert_id;
        return $insert_id;
    }

    public static function get($id){}
    
    public static function update($id,$data){
        global $wpdb;
        $table = $wpdb->prefix . self::table;
        $where = ['id' => $id];
        $format = ['%s','%d','%d'];
        $result = $wpdb->update($table,$data,$where,$format);
        return $result;
    }
    
    public static function delete($id){}

    public static function get_search_expression($expression){
        global $wpdb;
        $table = $wpdb->prefix . self::table;
        $sql = "SELECT * FROM {$table} WHERE expression LIKE '%{$expression}%'";
        $expression_rows = $wpdb->get_results($sql,ARRAY_A);
        if(empty($expression_rows)){
            return;
        }
        $expression_row = current($expression_rows);
        $search_expression = new SearchExpression;
        $search_expression->id = $expression_row['id'];
        unset($expression_row['id']);
        $search_expression->set_data($expression_row);
        return $search_expression;
    }

    public static function search_expressions_orderby_count($limit){
        global $wpdb;
        $table = $wpdb->prefix . self::table;
        $sql = "SELECT * FROM {$table} ORDER BY count DESC LIMIT {$limit}";
        $expression_rows = $wpdb->get_results($sql,ARRAY_A);
        if(empty($expression_rows)){
            return;
        }
        foreach($expression_rows as $expression_row){
            $search_expression = new SearchExpression;
            $search_expression->set_data([
                'expression' => $expression_row['expression'],
                'operator' => $expression_row['operator'],
                'count' => $expression_row['count'],
            ]);
            $search_expressions[] = $search_expression;
        }
        return $search_expressions ?? [];
    }

}