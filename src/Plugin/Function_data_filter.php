<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Filter;

function function_data_filter(Parse $parse, Data $data, $list, $where=[]){
    return Filter::list($list)->where($where);
}
