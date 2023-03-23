<?php

use R3m\Io\Config;

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Event;
use R3m\Io\Module\Parse;

use Exception;

/**
 * @throws Exception
 */
function function_host_setup(Parse $parse, Data $data, $host='', $public_html='', $ip='0.0.0.0', $email=''){
    $object = $parse->object();
    $id = $object->config(Config::POSIX_ID);
    if(
        !in_array(
            $id,
            [
                0,
                33
            ],
            true
        )
    ){
        $exception = new Exception('Only root and after that www-data can configure host create...');
        Event::trigger($object, 'configure.host.setup', [
            'host' => $host,
            'public_html' => $public_html,
            'ip' => $ip,
            'email' => $email,
            'exception' => $exception
        ]);
        throw $exception;
    }
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
        $exception = new Exception('Host cannot be empty...');
        Event::trigger($object, 'configure.host.setup', [
            'host' => $host,
            'public_html' => $public_html,
            'ip' => $ip,
            'email' => $email,
            'exception' => $exception
        ]);
        throw $exception;
    }
    $point = substr_count($host, '.');
    if($point < 1 ){
        $exception = new Exception('Invalid host...');
        Event::trigger($object, 'configure.host.setup', [
            'host' => $host,
            'public_html' => $public_html,
            'ip' => $ip,
            'email' => $email,
            'exception' => $exception
        ]);
        throw $exception;
    }
    $host = escapeshellarg($host);
    $public_html = escapeshellarg($public_html);
    $ip = escapeshellarg($ip);
    $email = escapeshellarg($email);
    if(empty($email)){
        $exception = new Exception('Server admin e-mail cannot be empty...');
        Event::trigger($object, 'configure.host.setup', [
            'host' => $host,
            'public_html' => $public_html,
            'ip' => $ip,
            'email' => $email,
            'exception' => $exception
        ]);
        throw $exception;
    }
    Core::execute($object, Core::binary() . ' configure server admin ' . $email);
    Core::execute($object, Core::binary() . ' configure site create ' . $host . ' ' . $public_html);
    if(empty($id)) {
        Core::execute($object, Core::binary() . ' configure host add ' . $ip . ' ' . $host);
    }
    Core::execute($object, Core::binary() . ' configure public create ' . $public_html);
    Core::execute($object, Core::binary() . ' configure domain add ' . $host);
    if(empty($id)){
        Core::execute($object, Core::binary() . ' configure site enable ' . $host);
        Core::execute($object, 'a2enmod rewrite');
        Core::execute($object, 'service apache2 restart');
    }
    Event::trigger($object, 'configure.host.setup', [
        'host' => $host,
        'public_html' => $public_html,
        'ip' => $ip,
        'email' => $email
    ]);
}

