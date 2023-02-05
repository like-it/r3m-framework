<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Limit;

function function_data_limit(Parse $parse, Data $data, $list, $limit=[]){
    return Limit::list($list)->with($limit);
}
