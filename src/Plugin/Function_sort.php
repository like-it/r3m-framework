<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Config;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Filter;

function function_sort(Parse $parse, Data $data, $list, $options){
    return Sort::list($list)->with($options);
}
