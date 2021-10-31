<?php

namespace VOS\Controllers\Web;

use VOS\Models\SearchExpression;

class SearchExpressionController {

    public $service_provider;

    public function __construct($service_provider){
        $this->service_provider = $service_provider;
        add_action('osa_archive_search_action_init',[$this,'update_search_expression'],10,2);
    }

    public function update_search_expression($expression_value,$user_id){
        $google_id = sanitize_text_field($_POST['googleID']) ?? 0;
        $operator = $user_id ?: $google_id;
        $expression = (isset(sanitize_text_field($_POST['s'])))? sanitize_text_field($_POST['s']) : $expression_value;
        if(empty($expression)){
            return;
        }
        $search_expression = SearchExpression::get_search_expression($expression);
        if(empty($search_expression)){
            $search_expression = new SearchExpression;
            $data = [
                'expression' => $expression,
                'operator' => $operator,
                'count' => 1
            ];
            $search_expression->set_data($data)->save();
        }elseif($search_expression instanceof SearchExpression){
            if($search_expression->get_data('operator') == $operator){
                return;
            }
            $count = $search_expression->get_data('count');
            $data = [
                'count' => $count+1,
                'operator' => $operator,
            ];
            $search_expression->set_data($data)->save();
        }
    }

}
