<?php

use R3m\Io\Config;

use R3m\Io\Module\Event;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;

use Exception;

/**
 * @throws Exception
 */
function function_site_delete(Parse $parse, Data $data, $server=null){
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
        $exception = new Exception('Only root can configure site delete...');
        Event::trigger($object, 'cli.configure.site.delete', [
            'server' => $server,
            'exception' => $exception
        ]);
        throw $exception;
    }
    if(!empty($server) && is_object($server)){
        $url = '/etc/apache2/sites-available/';
        $dir = new Dir();
        $read = $dir->read($url);
        foreach($read as $file){
            if($file->type != File::TYPE){
                continue;
            }
            if(stristr($file->name, str_replace('.', '-', $server->name)) !== false){
                File::delete($file->url);
            }
        }
        Event::trigger($object, 'cli.configure.site.delete', [
            'server' => $server,
        ]);
    } else {
        $exception = new Exception('Server variable needs to be an object');
        Event::trigger($object, 'cli.configure.site.delete', [
            'server' => $server,
            'exception' => $exception
        ]);
        throw $exception;
    }









}

