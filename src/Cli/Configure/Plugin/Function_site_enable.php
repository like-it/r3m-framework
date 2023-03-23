<?php

use R3m\Io\Config;

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;

use Exception;

/**
 * @throws Exception
 */
function function_site_enable(Parse $parse, Data $data, $server=null){
    $object = $parse->object();
    $id = $object->config(Config::POSIX_ID);
    if(
        !in_array(
            $id,
            [
                0
            ],
            true
        )
    ){
        $exception = new Exception('Only root can configure site enable...');
        Event::trigger($object, 'configure.site.disable', [
            'server' => $server,
            'exception' => $exception
        ]);
        throw $exception;
    }
    if(!empty($server) && is_object($server)){
        $url = '/etc/apache2/sites-available/';
        $url2 = '/etc/apache2/sites-enabled/';
        $dir = new Dir();
        $read = $dir->read($url);
        $object = $parse->object();
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
                Core::execute($object, $exec, $output);
                echo 'Site: ' . $server->name . ' enabled.' . "\n";
            }
        }
        Event::trigger($object, 'configure.site.disable', [
            'server' => $server,
        ]);
    } else {
        $exception = new Exception('Server variable needs to be an object');
        Event::trigger($object, 'configure.site.disable', [
            'server' => $server,
            'exception' => $exception
        ]);
        throw $exception;
    }
}