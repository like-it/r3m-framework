<?php

use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use Exception;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\FileWriteException;

/**
 * @throws ObjectException
 * @throws FileWriteException
 * @throws Exception
 */
function function_site_create(Parse $parse, Data $data){
    $id = posix_geteuid();
    if(
        !in_array(
            $id,
            [
                0
            ]
        )
    ){
        throw new Exception('Only root can configure site create...');
    }
    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);

    $server = array_shift($attribute);

    if(!empty($server) && is_object($server)){
        $dir = '/etc/apache2/sites-available/';
        $config = $parse->object()->data(App::CONFIG);
        $server->admin = $config->data('server.admin');
        if(empty($server->root)){
            $server->root = substr($config->data(Config::DATA_PROJECT_DIR_PUBLIC), 0, -1);
        } else {
            if(substr($server->root, -1, 1) == '/'){
                $server->root = substr($server->root, 0, -1);
            }
        }
        $server->number = sprintf("%'.03d", File::count($dir));
        $server->url = $dir . $server->number . '-' . str_replace('.', '-', $server->name) . $config->data('extension.conf');

        $url = $config->data(Config::DATA_CONTROLLER_DIR_DATA) . '001-site.conf';
        $read = File::read($url);
        $data = new stdClass();
        $data->server = $server;
        $write = $parse->compile($read, $data, $parse->storage());
        File::write($server->url, $write);
        return $server->url . ' created.' . PHP_EOL;
    } else {
        throw new Exception('Server variable needs to be an object');
    }
}