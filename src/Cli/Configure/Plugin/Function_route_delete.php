<?php

use R3m\Io\Config;

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Event;

use Exception;

/**
 * @throws Exception
 */
function function_route_delete(Parse $parse, Data $data, $resource=''){
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
        $exception = new Exception('Only root and www-data can configure route delete...');
        Event::trigger($object, 'cli.configure.route.delete', [
            'resource' => $resource,
            'has_deleted' => false,
            'exception' => $exception
        ]);
        throw $exception;
    }
    $url = $object->config('project.dir.data') . 'Route' . $object->config('extension.json');
    $read = $object->data_read($url);
    $has_deleted = false;
    if($read){
        foreach($read->data() as $key => $route){
            if(
                property_exists($route, 'resource') &&
                stristr($route->resource, $resource) !== false
            ){
                $read->data('delete', $key);
                $has_deleted = true;
                echo 'Route delete: deleting resource: ' . $route->resource . PHP_EOL;
            }
        }
        $read->write($url);
        if(empty($id)){
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $url);
            } else {
                exec('chmod 640 ' . $url);
            }
            if(empty($id)){
                exec('chown www-data:www-data ' . $url);
            }
        }
        if($has_deleted === false){
            $exception = new Exception('Couldn\'t find resource: ' . $resource);
            Event::trigger($object, 'cli.configure.route.delete', [
                'resource' => $resource,
                'has_deleted' => $has_deleted,
                'exception' => $exception
            ]);
            throw $exception;
        } else {
            Event::trigger($object, 'cli.configure.route.delete', [
                'resource' => $resource,
                'has_deleted' => $has_deleted
            ]);
        }
    }
}
