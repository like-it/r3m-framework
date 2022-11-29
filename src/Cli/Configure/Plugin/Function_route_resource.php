<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;
use Exception;

/**
 * @throws Exception
 */
function function_route_resource(Parse $parse, Data $data, $resource=''){
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
        throw new Exception('Only root and www-data can configure route add...');
    }
    $object = $parse->object();
    $url = $object->config('project.dir.data') . 'Route' . $object->config('extension.json');
    $read = $object->data_read($url);
    $has_route = false;
    if(!$read){
        $read = new Data();
    }
    foreach($read->data() as $key => $route){
        if(
            property_exists($route, 'resource') &&
            stristr($route->resource, $resource) !== false
        ){
            $has_route = $route;
            break;
        }
    }
    if(!$has_route){
        $read->data(Core::uuid() . '.resource', $resource);
        $read->write($url);
        if($id === 0){
            File::chmod($url, 0666);
            $project_dir_data = $object->config('project.dir.data');
            Core::execute('chown www-data:www-data -R ' . $project_dir_data);
            if(File::exist($project_dir_data . 'Cache/0/')){
                Core::execute('chown root:root -R ' . $project_dir_data . 'Cache/0/');
            }
            if(File::exist($project_dir_data . 'Compile/0/')){
                Core::execute('chown root:root -R ' . $project_dir_data . 'Compile/0/');
            }
            if(File::exist($project_dir_data . 'Cache/1000/')){
                Core::execute('chown 1000:1000 -R ' . $project_dir_data . 'Cache/1000/');
            }
            if(File::exist($project_dir_data . 'Compile/1000/')){
                Core::execute('chown 1000:1000 -R ' . $project_dir_data . 'Compile/1000/');
            }
        }
        return 'Route resource: ' . $resource . ' added' . PHP_EOL;
    }

}

