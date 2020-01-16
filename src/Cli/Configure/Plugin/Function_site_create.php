<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_site_create(Parse $parse, Data $data){

    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);

    $server = array_shift($attribute);

    if(!empty($server) && is_object($server)){
        $dir = '/etc/apache2/sites-available/';
        $config = $parse->object()->data(\R3m\Io\App::CONFIG);
        $server->admin = $config->data('server.admin');
        $server->root = substr($config->data(\R3m\Io\Config::DATA_PROJECT_DIR_PUBLIC), 0, -1);
        $server->number = sprintf("%'.03d", \R3m\Io\Module\File::count($dir));
        $server->url = $dir . $server->number . '-' . str_replace('.', '-', $server->name) . $config->data('extension.conf');

        $url = $config->data(\R3m\Io\Config::DATA_CONTROLLER_DIR_DATA) . '001-site.conf';
        $read = \R3m\Io\Module\File::read($url);
        $data = new \stdClass();
        $data->server = $server;
        $write = $parse->compile($read, $data, $parse->storage(), 'string');
        \R3m\Io\Module\File::write($server->url, $write);
    } else {
        throw new \Exception('Server variable needs to be an object');
    }









}

