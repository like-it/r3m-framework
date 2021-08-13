<?php

use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;

function function_autoload_restart(Parse $parse, Data $data){
    $object = $parse->object();
    $autoload = $object->data(\R3m\Io\App::AUTOLOAD_R3M);
    $cache_dir = $autoload->cache_dir();
    $url = Dir::name($cache_dir);
    if(File::exist($url)){
        $dir = new Dir();
        $read = $dir->read($url, true);
        if($read){
            foreach($read as $nr => $file){
                if($file->type === File::TYPE){
                    File::delete($file->url);
                }
            }
        }
    }
}
