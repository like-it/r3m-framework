<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_autoload_restart(Parse $parse, Data $data){
    $object = $parse->object();
    $autoload = $object->data(\R3m\Io\App::AUTOLOAD_R3M);
    $cache_dir = $autoload->cache_dir();
    if(\R3m\Io\Module\File::exist($cache_dir)){
        \R3m\Io\Module\Dir::remove($cache_dir);
    }
}
