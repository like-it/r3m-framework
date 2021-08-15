<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Dir;

function function_parse_restart(Parse $parse, Data $data){
    $cache_dir = $parse->cache_dir();
    if(File::exist($cache_dir)){
       Dir::remove($cache_dir);
       Dir::create($cache_dir, Dir::CHMOD);
       File::chown($cache_dir, 'www-data', 'www-data', true);
    }
}
