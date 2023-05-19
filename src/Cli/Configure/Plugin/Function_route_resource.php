<?php

use R3m\Io\Config;

use R3m\Io\Module\Event;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;

use R3m\Io\Exception\RouteExistException;

/**
 * @throws Exception
 */
function function_route_resource(Parse $parse, Data $data, $resource='')
{
    $object = $parse->object();
    $id = $object->config(Config::POSIX_ID);
    if (
        !in_array(
            $id,
            [
                0,
                33
            ],
            true
        )
    ) {
        $exception = new Exception('Only root and www-data can configure route resource...');
        Event::trigger($object, 'cli.configure.route.resource', [
            'resource' => $resource,
            'route' => false,
            'exception' => $exception
        ]);
        throw $exception;
    }
    $url = $object->config('app.route.url');
    $read = $object->data_read($url);
    $has_route = false;
    if (!$read) {
        $read = new Data();
    }
    foreach ($read->data() as $key => $route) {
        if (
            property_exists($route, 'resource') &&
            stristr($route->resource, $resource) !== false
        ) {
            $has_route = $route;
            break;
        }
    }
    $response = null;
    if (!$has_route) {
        $read->data(Core::uuid() . '.resource', $resource);
        $read->write($url);
        if(empty(($id))){
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $url);
            } else {
                exec('chmod 640 ' . $url);
            }
            if(empty($id)){
                exec('chown www-data:www-data ' . $url);
            }
        }
        $response = 'Route resource: ' . $resource . ' added' . PHP_EOL;
    }
    if($response){
        Event::trigger($object, 'cli.configure.route.resource', [
            'resource' => $resource,
            'route' => $has_route
        ]);
        return $response;
    } else {
        $exception = new RouteExistException('Route resource already exists...');
        Event::trigger($object, 'cli.configure.route.resource', [
            'resource' => $resource,
            'route' => $has_route,
            'exception' => $exception
        ]);
        throw $exception;
    }
}
