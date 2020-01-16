<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_parse_restart(Parse $parse, Data $data){

    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);

    $cache_dir = $parse->cache_dir();
    if(\R3m\Io\Module\File::exist($cache_dir)){
        \R3m\Io\Module\Dir::remove($cache_dir);
    }
}
