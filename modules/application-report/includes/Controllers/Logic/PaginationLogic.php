<?php

namespace VOS\Controllers\Logic;

class PaginationLogic {

    public $per_page = 5;

    public function get_pagination_params($paged,$founded){
        $from = 1;
        $to = ceil($founded/$this->per_page);
        return [
            'from' => $from,
            'to' => intval($to),
            'current' => $paged,
        ];
    }

    public function get_pagination_args($paged){
        $offset = ($paged - 1) * $this->per_page;
        return [
            'offset' => $offset,
            'limit' => $this->per_page
        ];
    }

}