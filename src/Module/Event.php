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

use R3m\Io\Exception\LocateException;
use stdClass;

use R3m\Io\App;

use Exception;

use R3m\Io\Exception\ObjectException;

class Event {
    const DIR = __DIR__ . DIRECTORY_SEPARATOR;
    const NAME = 'Event';

    public static function on(App $object, $action, $options=[]){
        $list = $object->get(App::EVENT)->get('event');
        if(empty($list)){
            $list = [];
        }
        $list[] = [
            'action' => $action,
            'options' => $options
        ];
        $object->get(App::EVENT)->set('event', $list);
    }

    public static function off(App $object, $action, $options=[]){
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
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function trigger(App $object, $action, $options=[]){
        d($options);
        $events = $object->get(App::EVENT)->select('event', [
            'action' => $action
        ]);
        if(empty($events)){
            return null;
        }
        $events = Sort::list($events)->with(['options.priority' => 'DESC']);
        if(is_array($events)){
            foreach($events as $event){
                if(is_array($event)){
                    if(
                        array_key_exists('options', $event) &&
                        property_exists($event['options'], 'command') &&
                        is_array($event['options']->command)
                    ){
                        foreach($event['options']->command as $command){
                            $command = str_replace('{{binary()}}', Core::binary(), $command);
                            Core::execute($object, $command, $output, $notification);
                        }
                    }
                    if(
                        array_key_exists('options', $event) &&
                        property_exists($event['options'], 'controller') &&
                        is_array($event['options']->controller)
                    ){
                        foreach($event['options']->controller as $controller){
                            $route = new stdClass();
                            $route->controller = $controller;
                            $route = Route::controller($route);
                            if(
                                property_exists($route, 'controller') &&
                                property_exists($route, 'function')
                            ){
                                $event = new Data($event);
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
                } elseif(is_object($event)) {
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
                                $event = new Data($event);
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
     */
    public static function configure(App $object){
        $url = $object->config('project.dir.data') . 'Events' . $object->config('extension.json');
        $data = $object->data_read($url);
        if(!$data){
            return;
        }
        foreach($data->get('event') as $event){
            if(
                property_exists($event, 'action') &&
                property_exists($event, 'options')
            )
            Event::on($object, $event->action, $event->options);
        }
        Event::trigger($object, 'event.configure');
    }
}
