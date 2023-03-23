<?php

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Event;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

use Exception;
/**
 * @throws Exception
 */
function function_route_add(Parse $parse, Data $data, $add=''){
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
        $exception = new Exception('Only root and www-data can configure route add...');
        Event::trigger($object, 'configure.route.add', [
            'add' => $add,
            'route' => false,
            'exception' => $exception
        ]);
        throw $exception;
    }
    $object = $parse->object();
    $read = $object->get(App::ROUTE);
    $has_route = false;
    if($read){
        foreach($read->data() as $key => $route){
            if(
                property_exists($route, 'resource') &&
                property_exists($add, 'resource') &&
                stristr($route->resource, $add->resource) !== false
            ){
                $has_route = $route;
                break;
            }
        }
        if($has_route){
            $read = $object->data_read($has_route->resource);
            $key = $add->name;
            unset($add->resource);
            unset($add->name);
            $read->data($key, $add);
            $read->write($has_route->resource);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $has_route->resource);
            } else {
                exec('chmod 640 ' . $has_route->resource);
            }
            if(empty($id)){
                exec('chown www-data:www-data ' . $has_route->resource);
            }
            Event::trigger($object, 'route.add', [
                'add' => $add,
                'route' => $has_route
            ]);
            return 'Route: ' . $key . ' added' . PHP_EOL;
        } else {
            $error[] = 'Resource not found, available resources:';
            $is_resource = false;
            foreach($read->data() as $key => $route){
                if(property_exists($route, 'resource')){
                    $error[] = 'resource: ' . $route->resource;
                    $is_resource = true;
                }
            }
            if($is_resource === false){
                $error[] = 'No resources found, is Route corrupt?';
            }
            $exception = new Exception(implode(PHP_EOL, $error));
            Event::trigger($object, 'configure.route.add', [
                'add' => $add,
                'route' => $has_route,
                'error' => $error,
                'exception' => $exception
            ]);
            throw $exception;
        }
    }
}

