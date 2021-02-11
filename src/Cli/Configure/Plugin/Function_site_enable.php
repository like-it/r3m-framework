<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_site_enable(Parse $parse, Data $data){

    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);

    $server = array_shift($attribute);

    if(!empty($server) && is_object($server)){
        $url = '/etc/apache2/sites-available/';
        $url2 = '/etc/apache2/sites-enabled/';
        $dir = new \R3m\Io\Module\Dir();
        $read = $dir->read($url);
        foreach($read as $file){
            if($file->type != \R3m\Io\Module\File::TYPE){
                continue;
            }
            if(stristr($file->name, str_replace('.', '-', $server->name)) !== false){
                $exec = 'ln -s ' . $file->url . ' '  . $url2 . $file->name;
                $output = [];
                \R3m\Io\Module\Core::execute($exec, $output);
                echo 'Site: ' . $server->name . ' enabled.' . "\n";
            }
        }
    } else {
        throw new \Exception('Server variable needs to be an object');
    }









}

