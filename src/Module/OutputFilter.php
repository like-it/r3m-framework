<?php
/**
 * @author          Remco van der Velde
 * @since           18-12-2020
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module;

use R3m\Io\Exception\FileWriteException;
use stdClass;

use R3m\Io\App;

use R3m\Io\Module\Data as Storage;

use R3m\Io\Node\Trait\Data;
use R3m\Io\Node\Trait\Role;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\ObjectException;

class OutputFilter {

    use Data;
    use Role;

    const NAME = 'OutputFilter';
    const CHUNK_SIZE = 4096;

    public static function on(App $object, $filter){
        $action = $filter->get('action');
        $options = $filter->get('options');
        $list = $object->get(App::FILTER)->get(OutputFilter::NAME);
        if(empty($list)){
            $list = [];
        }
        $list[] = $filter->data();
        $object->get(App::FILTER)->set(OutputFilter::NAME, $list);
    }

    public static function off(App $object, $filter){
        $action = $filter->get('action');
        $options = $filter->get('options');
        $list = $object->get(App::FILTER)->get(OutputFilter::NAME);
        if(empty($list)){
            return;
        }
        //remove them on the sorted list backwards so sorted on input order
        krsort($list);
        foreach($list as $key => $node){
            if(empty($options)){
                if($node['action'] === $action){
                    unset($list[$key]);
                    break;
                }
            } else {
                if($node['action'] === $action){
                    foreach($options as $options_key => $value){
                        if(
                            $value === true &&
                            is_array($node['options']) &&
                            array_key_exists($options_key, $node['options'])
                        ){
                            unset($list[$key]);
                            break;
                        }
                        if(
                            $value === true &&
                            is_object($node['options']) &&
                            property_exists($node['options'], $options_key)
                        ){
                            unset($list[$key]);
                            break;
                        }
                        elseif(
                            is_array($node['options']) &&
                            array_key_exists($options_key, $node['options']) &&
                            $node['options'][$options_key] === $value
                        ){
                            unset($list[$key]);
                            break;
                        }
                        elseif(
                            is_object($node['options']) &&
                            property_exists($node['options'], $options_key) &&
                            $node['options']->{$options_key} === $value
                        ){
                            unset($list[$key]);
                            break;
                        }
                    }
                }
            }
        }
        $object->get(App::FILTER)->set(OutputFilter::NAME, $list);
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function trigger(App $object, $action, $options=[]){
        $filters = $object->get(App::FILTER)->select(OutputFilter::NAME, [
            'action' => $action
        ]);
        $response = null;
        if(empty($filters)){
            if(
                array_key_exists('type', $options) &&
                $options['type'] === OutputFilter::OUTPUT &&
                array_key_exists('response', $options)
        ){
                return $options['response'];
            }
            elseif(
                array_key_exists('type', $options) &&
                $options['type'] === OutputFilter::INPUT &&
                array_key_exists('route', $options)
            ){
                return $options['route'];
            }
            return null;
        }
        $filters = Sort::list($filters)->with(['options.priority' => 'DESC']);
        if(is_array($filters) || is_object($filters)){
            foreach($filters as $filter){
                if(is_object($filter)) {
                    if(
                        property_exists($filter, 'options') &&
                        property_exists($filter->options, 'controller') &&
                        is_array($filter->options->controller)
                    ){
                        foreach($filter->options->controller as $controller){
                            $route = new stdClass();
                            $route->controller = $controller;
                            $route = Route::controller($route);
                            if(
                                property_exists($route, 'controller') &&
                                property_exists($route, 'function')
                            ){
                                $filter = new Storage($filter);
                                try {
                                    $response = $route->controller::{$route->function}($object, $filter, $options);
                                    if($filter->get('stopPropagation')){
                                        break 2;
                                    }
                                }
                                catch (LocateException $exception){
                                    if($object->config('project.log.error')){
                                        $object->logger($object->config('project.log.error'))->error('LocateException', [ $route, (string) $exception ]);
                                    }
                                    elseif($object->config('project.log.name')){
                                        $object->logger($object->config('project.log.name'))->error('LocateException', [ $route, (string) $exception ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if(array_key_exists('type', $options)){
            switch($options['type']){
                case 'input' :
                    if(array_key_exists('route', $options)){
                        if($response){
                            return $response;
                        }
                        return $options['route'];
                    }
                    break;
                case 'output' :
                    if(array_key_exists('response', $options)){
                        if($response){
                            return $response;
                        }
                        return $options['response'];
                    }
                    break;
            }
        }
        return null;
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     */
    public static function configure(App $object): void
    {
        $outputFilter = new OutputFilter($object);
        $limit = $object->config('output.filter.chunk_size') ?? OutputFilter::CHUNK_SIZE;
        $count = $outputFilter->count(
            Middleware::NAME,
            $outputFilter->role_system(),
            [
                'sort' => [
                    'options.priority' => 'ASC'
                ]
            ]
        );
        $page_max = ceil($count / $limit);
        for($page = 1; $page <= $page_max; $page++){
            $response = $outputFilter->list(
                OutputFilter::NAME,
                $outputFilter->role_system(),
                [
                    'sort' => [
                        'action' => 'ASC',
                        'options.priority' => 'ASC'
                    ],
                    'page' => $page,
                    'limit' => $limit
                ]
            );
            if(
                $response &&
                array_key_exists('list', $response)
            ){
                foreach($response['list'] as $record){
                    $record = new Storage($record);
                    OutputFilter::on($object, $record);
                }
            }
        }
    }
}
