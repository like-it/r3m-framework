<?php

use R3m\Io\Config;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;


function function_host_create(Parse $parse, Data $data, $host='', $public_html='', $ip='0.0.0.0', $email=''){
    $object = $parse->object();
    if(empty($public_html)){
        $public_html = $object->config('server.public');
        if(empty($public_html)){
            $public_html = $object->config(Config::DATA_PROJECT_DIR_PUBLIC);
        }
    } else {
        if(strstr($public_html, '/') === false){
            $public_html = $object->config(Config::DATA_PROJECT_DIR_ROOT) . $public_html . $object->config('ds');
        }
    }
    $output = [];
    Core::execute(Core::binary() . ' configure site create ' . $host . ' ' . $public_html, $output);
    $output = [];
    Core::execute(Core::binary() . ' configure host add ' . $ip . ' ' . $host, $output);
    $output = [];
    Core::execute(Core::binary() . ' configure public create ' . $public_html, $output);
    $output = [];
    Core::execute(Core::binary() . ' configure domain add ' . $host, $output);
    $output = [];
    Core::execute(Core::binary() . ' configure site enable ' . $host, $output);
    $output = [];
    Core::execute('a2enmod rewrite', $output);
    $output = [];
    Core::execute('service apache2 restart', $output);
}

