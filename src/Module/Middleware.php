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

use stdClass;

use R3m\Io\App;

use R3m\Io\Module\Data as Storage;
use R3m\Io\Module\Template\Main;

use R3m\Io\Node\Trait\Data;
use R3m\Io\Node\Trait\Role;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\ObjectException;
class Middleware extends Main {

    use Data;
    use Role;

    const NAME = 'Middleware';

    public function __construct(App $object){
        $this->object($object);
    }


    public static function on(App $object, $filter){
        $action = $filter->get('action');
        $options = $filter->get('options');
        $list = $object->get(App::MIDDLEWARE)->get(Middleware::NAME);
        if(empty($list)){
            $list = [];
        }
        $list[] = $filter->data();
        $object->get(App::FILTER)->set(Middleware::NAME, $list);
    }

    public static function off(App $object, $filter){
        $action = $filter->get('action');
        $options = $filter->get('options');
        $list = $object->get(App::MIDDLEWARE)->get(Middleware::NAME);
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
        $object->get(App::FILTER)->set(Middleware::NAME, $list);
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function trigger(App $object, $action, $options=[]){
        $filters = $object->get(App::MIDDLEWARE)->select(Middleware::NAME, [
            'action' => $action
        ]);
        $response = null;
        if(empty($filters)){
            if(
                array_key_exists('type', $options) &&
                $options['type'] === Middleware::OUTPUT &&
                array_key_exists('response', $options)
        ){
                return $options['response'];
            }
            elseif(
                array_key_exists('type', $options) &&
                $options['type'] === Middleware::INPUT &&
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
     */
    public static function configure(App $object){
        /**
         {{$response = R3m.Io.Node:Data:list(
        'Event',
        R3m.Io.Node:Role:role.system(),
        [
        'sort' => [
        'action' => 'ASC',
        'options.priority' => 'ASC'
        ],
        'limit' => (int) $options.limit,
        'page' => (int) $options.page
        ])}}
         */


        $middleware = new Middleware($object);
        $limit = $object->config('middleware.limit') ?? 2;
        $page = 1;
        $count = $middleware->count(
            'Event',
            $middleware->role_system(),
            [
                'sort' => [
                    'action' => 'ASC',
                    'options.priority' => 'ASC'
                ],
                'limit' => $limit,
                'page' => $page,
                /*
                'where' => [
                    '(',
                    [
                        'attribute' => 'options.priority',
                        'value' => 10,
                        'operator' => '>'
                    ],
                    ')'
                ]
                */
            ]
        );
        $page_max = ceil($count / $limit);
        d($page_max);
        for($page = 1; $page <= $page_max; $page++){
            $response = $middleware->list(
                'Event',
                $middleware->role_system(),
                [
                    'sort' => [
                        'action' => 'ASC',
                        'options.priority' => 'ASC'
                    ],
                    'limit' => $limit,
                    'page' => $page,
                ]
            );
            ddd($response);
        }


        ddd($count);


        while(true){
            $response = $middleware->list(
                'Middleware',
                $middleware->role_system(),
                [
                    'sort' => [
                        'options.priority' => 'ASC'
                    ],
                    'limit' => $limit,
                    'page' => $page
                ]
            );
            ddd($response);
            $page++;
        }



        /*
        $url = $object->config('project.dir.data') .
            'Node' .
            $object->config('ds') .
            'Filter' .
            $object->config('ds') .
            'Data' .
            $object->config('extension.json')
        ;
        $data = $object->data_read($url);
        if(!$data){
            return;
        }
        if($data->has(Middleware::NAME)){
            foreach($data->get(Middleware::NAME) as $middleware){
                if(
                    property_exists($middleware, 'action') &&
                    property_exists($middleware, 'options')
                )
                    $middleware = new Storage($middleware);
                    Middleware::on($object, $middleware);
            }
        }
        */

    }
}
