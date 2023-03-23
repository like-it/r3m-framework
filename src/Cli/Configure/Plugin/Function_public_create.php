<?php

use R3m\Io\Config;

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Event;

use Exception;
use R3m\Io\Exception\ObjectException;

/**
 * @throws ObjectException
 * @throws Exception
 */
function function_public_create(Parse $parse, Data $data, $public_html=''){
    $object = $parse->object();
    $write = 0;
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
        Event::trigger($object, 'configure.public.create', [
            'public_html' => $public_html,
            'bytes' => $write,
            'exception' => $exception
        ]);
        return $exception;
    }
    if(empty($public_html)){
        $public_html = $object->config('project.dir.public');
    }
    $dir = $object->config('project.dir.data');
    $url = $dir .
        'Config' .
        $object->config('extension.json')
    ;
    $read = $object->data_read($url);
    if(empty($read)){
        $read = new Data();
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            Dir::create($dir, 0777);
        } else {
            Dir::create($dir, 0750);
        }
        if(empty($id)){
            exec('chown www-data:www-data ' . $dir);
        }
    }
    $source = $read->data('server.public');
    if(strstr($public_html, '/') === false){
        $public_html = $object->config('project.dir.root') . $public_html . $object->config('ds');
    }
    if(!empty($source)){
        try {
            $destination = $public_html;
            if(
                $source != $destination &&
                File::exist($source)
            )
            {
                File::rename($source, $destination);
            }
            if(!File::exist($destination)){
                Dir::create($destination);
            }
            $destination = $public_html . '.htaccess';
            if(!File::exist($destination)){
                $source = $object->config('controller.dir.data') . '.htaccess';
                File::copy($source, $destination);
            }
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $destination);
            } else {
                exec('chmod 640 ' . $destination);
            }
            $destination = $public_html . '.user.ini';
            if(!File::exist($destination)){
                $source = $object->config('controller.dir.data') . '.user.ini';
                File::copy($source, $destination);
            }
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $destination);
            } else {
                exec('chmod 640 ' . $destination);
            }
            $destination = $public_html . 'index.php';
            if(!File::exist($destination)){
                $source = $object->config('controller.dir.data') . 'index.php';
                File::copy($source, $destination);
            }
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $destination);
            } else {
                exec('chmod 640 ' . $destination);
            }
        } catch (Exception $exception){
            Event::trigger($object, 'configure.public.create', [
                'public_html' => $public_html,
                'bytes' => $write,
                'exception' => $exception
            ]);
            return $exception;
        }
    } else {
        Dir::create($public_html);
        $source = $object->config('controller.dir.data') . '.htaccess';
        $destination = $public_html . '.htaccess';
        File::copy($source, $destination);
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 666 ' . $destination);
        } else {
            exec('chmod 640 ' . $destination);
        }
        $source = $object->config('controller.dir.data') . '.user.ini';
        $destination = $public_html . '.user.ini';
        File::copy($source, $destination);
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 666 ' . $destination);
        } else {
            exec('chmod 640 ' . $destination);
        }
        $source = $object->config('controller.dir.data') . 'index.php';
        $destination = $public_html . 'index.php';
        File::copy($source, $destination);
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 666 ' . $destination);
        } else {
            exec('chmod 640 ' . $destination);
        }
    }
    if(empty($id)){
        Core::execute($object, 'chown www-data:www-data ' . $public_html . ' -R');
    }
    $read->data('server.public', $public_html);
    try {
        $write = File::write($url, Core::object($read->data(), Core::OBJECT_JSON));
        $response = 'Bytes written: ' . $write . PHP_EOL;
        Event::trigger($object, 'configure.public.create', [
            'public_html' => $public_html,
            'bytes' => $write
        ]);
        return $response;
    } catch (Exception | ObjectException $exception){
        Event::trigger($object, 'configure.public.create', [
            'public_html' => $public_html,
            'bytes' => $write,
            'exception' => $exception
        ]);
        return $exception;
    }
}