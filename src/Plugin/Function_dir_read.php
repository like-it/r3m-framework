<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_dir_read(Parse $parse, Data $data, $url=''){
    if(\R3m\Io\Module\File::exist($url)){
        $dir = new \R3m\Io\Module\Dir();
        $read = $dir->read($url);
        return $read;
    }
    return [];
}
