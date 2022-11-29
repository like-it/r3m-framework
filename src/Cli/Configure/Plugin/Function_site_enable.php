<?php

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;


/**
 * @throws Exception
 */
function function_site_enable(Parse $parse, Data $data, $server=null){
    $id = posix_geteuid();
    if(
        !in_array(
            $id,
            [
                0
            ]
        )
    ){
        throw new Exception('Only root can configure site enable...');
    }
    if(!empty($server) && is_object($server)){
        $url = '/etc/apache2/sites-available/';
        $url2 = '/etc/apache2/sites-enabled/';
        $dir = new Dir();
        $read = $dir->read($url);
        foreach($read as $file){
            if($file->type != File::TYPE){
                continue;
            }
            if(File::exist($url2 . $file->name)){
                continue;
            }
            if(stristr($file->name, str_replace('.', '-', $server->name)) !== false){
                $exec = 'ln -s ' . $file->url . ' '  . $url2 . $file->name;
                $output = [];
                Core::execute($exec, $output);
                echo 'Site: ' . $server->name . ' enabled.' . "\n";
            }
        }
    } else {
        throw new Exception('Server variable needs to be an object');
    }









}

