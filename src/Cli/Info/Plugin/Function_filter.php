<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Config;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;

function function_filter(Parse $parse, Data $data, $list, $options){
    d($list);
    d($options);
    return $list;
}
