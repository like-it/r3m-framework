<?php

use R3m\Io\Config;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;

use Exception;

/**
 * @throws Exception
 */
function function_host_create(Parse $parse, Data $data, $host='', $public_html='', $ip='0.0.0.0', $email=''){
    $id = posix_geteuid();
    if(
        !in_array(
            $id,
            [
                0,
                33
            ]
        )
    ){
        throw new Exception('Only root and after that www-data can configure host create...');
    }
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
    if(empty($host)){
        throw new Exception('Host cannot be empty...');
    }
    $host = escapeshellarg($host);
    $public_html = escapeshellarg($public_html);
    $ip = escapeshellarg($ip);
    $email = escapeshellarg($email);
    if(empty($email)){
        throw new Exception('Server admin e-mail cannot be empty...');
    }
    $output = [];
    Core::execute(Core::binary() . ' configure server admin ' . $email, $output);
    $output = [];
    Core::execute(Core::binary() . ' configure site create ' . $host . ' ' . $public_html, $output);
    if($id === 0) {
        $output = [];
        Core::execute(Core::binary() . ' configure host add ' . $ip . ' ' . $host, $output);
    }
    $output = [];
    Core::execute(Core::binary() . ' configure public create ' . $public_html, $output);
    $output = [];
    $error = [];
    Core::execute(Core::binary() . ' configure domain add ' . $host, $output, $error);
    if($id === 0){
        $output = [];
        Core::execute(Core::binary() . ' configure site enable ' . $host, $output);
        $output = [];
        Core::execute('a2enmod rewrite', $output);
        $output = [];
        $host_dir_root = $object->config(Config::DATA_PROJECT_DIR_ROOT) . 'Host' . $object->config('ds');
        Core::execute('chown www-data:www-data -R ' . $host_dir_root);
        Core::execute('chmod 777 -R ' . $host_dir_root);
        Core::execute('service apache2 restart', $output);
    }


}

