<?php

use R3m\Io\Config;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;


function function_host_create(Parse $parse, Data $data, $host='', $public_html='', $ip='0.0.0.0', $email=''){
    $object = $parse->object();
    if(empty($public_html)){
        $public_html = $object->config(Config::DATA_PROJECT_DIR_PUBLIC);
    } else {
        if(strstr($public_html, '/') === false){
            $public_html = $object->config(Config::DATA_PROJECT_DIR_ROOT) . $public_html . $object->config('ds');
        }
    }
    $output = [];
    //Core::execute(Core::binary() . ' configure site create ' . $host . ' ' . $public_html, $output);
    $output = [];
    //Core::execute(Core::binary() . ' configure host add ' . $ip . ' ' . $host, $output);
    $output = [];
    //Core::execute(Core::binary() . ' configure site enable ' . $host, $output);
    $output = [];


    Dir::create($public_html);
    $source = $object->config('controller.dir.data') . '.htaccess';
    $destination = $public_html . '.htaccess';
    File::copy($source, $destination);
    $source = $object->config('controller.dir.data') . 'index.php';
    $destination = $public_html . 'index.php';
    File::copy($source, $destination);

    




    //need public html
    //need host creation
    //default site
//    Core::execute('a2enmod rewrite');
    $output = [];
//    Core::execute('service apache2 restart');

}

