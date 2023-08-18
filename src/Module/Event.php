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
use R3m\Io\Module\Template\Main;

use R3m\Io\Node\Trait\Data;
use R3m\Io\Node\Trait\Role;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\ObjectException;

class Event extends Main {

    use Data;
    use Role;

    const NAME = 'Event';
    const OBJECT = 'Event';
    const CHUNK_SIZE = 4096;

    const LIST = 'list';
    const RECORD = 'record';

    public function __construct(App $object){
        $this->object($object);
    }

    public static function on(App $object, $data, $options=[]): void
    {
        $list = $object->get(App::EVENT)->get(Event::NAME);
        if(empty($list)){
            $list = [];
        }
        if(is_array($data)){
            foreach($data as $node){
                $list[] = $node;
            }
        } else {
            $list[] = $data;
        }
        $object->get(App::EVENT)->set(Event::NAME, $list);
    }

    public static function off(App $object, $action, $options=[]){
        //reinplement this
        /*
        $list = $object->get(App::EVENT)->get('event');
        if(empty($list)){
            return;
        }
        //remove them on the sorted list backwards so sorted on input order
        krsort($list);
        foreach($list as $key => $event){
            if(empty($options)){
                if($event['action'] === $action){
                    unset($list[$key]);
                    break;
                }
            } else {
                if($event['action'] === $action){
                    foreach($options as $options_key => $value){
                        if(
                            $value === true &&
                            is_array($event['options']) &&
                            array_key_exists($options_key, $event['options'])
                        ){
                            unset($list[$key]);
                            break;
                        }
                        if(
                            $value === true &&
                            is_object($event['options']) &&
                            property_exists($event['options'], $options_key)
                        ){
                            unset($list[$key]);
                            break;
                        }
                        elseif(
                            is_array($event['options']) &&
                            array_key_exists($options_key, $event['options']) &&
                            $event['options'][$options_key] === $value
                        ){
                            unset($list[$key]);
                            break;
                        }
                        elseif(
                            is_object($event['options']) &&
                            property_exists($event['options'], $options_key) &&
                            $event['options']->{$options_key} === $value
                        ){
                            unset($list[$key]);
                            break;
                        }
                    }
                }
            }
        }
        $object->get(App::EVENT)->set('event', $list);
        */
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function trigger(App $object, $action, $options=[]){
        $events = $object->get(App::EVENT)->select(Event::NAME, [
            'action' => $action
        ]);
        if(empty($events)){
            return null;
        }
        if(is_array($events)){
            foreach($events as $event){
                if(is_object($event)) {
                    if(
                        property_exists($event, 'options') &&
                        property_exists($event->options, 'command') &&
                        is_array($event->options->command)
                    ){
                        foreach($event->options->command as $command){
                            $command = str_replace('{{binary()}}', Core::binary(), $command);
                            Core::execute($object, $command, $output, $notification);
                        }
                    }
                    if(
                        property_exists($event, 'options') &&
                        property_exists($event->options, 'controller') &&
                        is_array($event->options->controller)
                    ){
                        foreach($event->options->controller as $controller){
                            $route = new stdClass();
                            $route->controller = $controller;
                            $route = Route::controller($route);
                            if(
                                property_exists($route, 'controller') &&
                                property_exists($route, 'function')
                            ){
                                $event = new Storage($event);
                                try {
                                    $route->controller::{$route->function}($object, $event, $options);
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
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     * @throws Exception
     */
    public static function configure(App $object): void
    {
        $start = microtime(true);
        $event = new Event($object);
        $limit = $object->config('event.chunk_size') ?? Event::CHUNK_SIZE;
        $count = $event->count(
            Event::OBJECT,
            $event->role_system(),
            [
                'sort' => [
                    'action' => 'ASC',
                    'options.priority' => 'ASC'
                ]
            ]
        );
        if($count === 0 || $limit === 0){
            return;
        }
        $page_max = ceil($count / $limit);
        for($page = 1; $page <= $page_max; $page++){
            $response = $event->list(
                Event::OBJECT,
                $event->role_system(),
                [
                    'sort' => [
                        'action' => 'ASC',
                        'options.priority' => 'ASC'
                    ],
                    'page' => $page,
                    'limit' => $limit,
                    'ramdisk' => true
                ]
            );
            if(
                $response &&
                array_key_exists('list', $response)
            ){
                Event::on($object, $response['list']);
            }
        }
        $duration = microtime(true) - $start;
        if($object->config('project.log.node')){
            if($duration >= 1){
                $object->logger($object->config('project.log.node'))->info('Event::configure (sec)', [$duration]);
            } else {
                $object->logger($object->config('project.log.node'))->info('Event::configure (msec)', [$duration * 1000]);
            }
        }
        elseif($object->config('project.log.name')){
            if($duration >= 1){
                $object->logger($object->config('project.log.name'))->info('Event::configure (sec)', [$duration]);
            } else {
                $object->logger($object->config('project.log.name'))->info('Event::configure (msec)', [$duration * 1000]);
            }
        }
    }
}
