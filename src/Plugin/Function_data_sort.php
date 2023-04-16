<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Sort;

function function_data_sort(Parse $parse, Data $data, $list, $sort=[], $options=[]){
    return Sort::list($list)->with($sort, $options);
}
