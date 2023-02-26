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

use R3m\Io\Exception\ObjectException;

class Event {

    public static function on(App $object, $action, $options=[]){
        $event = $object->config('event.' . $action);
        if(empty($event)){
            $event = [];
        }
        $event[] = $options;
        $object->config('event.' . $action, $event);
    }

    public static function off(App $object, $action, $options=[]){

    }

    /**
     * @throws ObjectException
     */
    public static function trigger(App $object, $action, $options=[]){
        $errors = $object->config('event.' . $action . '.error');
        $events = $object->config('event.' . $action);
        unset($events['error']);
        if(empty($events) && empty($errors)){
            return null;
        }
        $errors = Sort::list($errors)->with(['priority' => 'DESC']);
        $events = Sort::list($events)->with(['priority' => 'DESC']);
        if(is_array($errors)){
            foreach($errors as $error){
                if(
                    property_exists($error, 'command') &&
                    is_array($error->command)
                ){
                    foreach($error->command as $command){
                        $command = str_replace('{{binary()}}', Core::binary(), $command);
                        Core::execute($object, $command, $output, $error);
                    }
                }
                if(
                    property_exists($error, 'controller') &&
                    is_array($error->controller)
                ){
                    foreach($error->controller as $controller){
                        $route = new stdClass();
                        $route->controller = $controller;
                        $route = Route::controller($route);
                        if(
                            property_exists($route, 'controller') &&
                            property_exists($route, 'function')
                        ){
                            $route->controller::{$route->function}($object, $error, $action, $options);
                        }
                    }
                }
            }
        }
        if(is_array($events)){
            foreach($events as $event){
                if(
                    property_exists($event, 'command') &&
                    is_array($event->command)
                ){
                    foreach($event->command as $command){
                        $command = str_replace('{{binary()}}', Core::binary(), $command);
                        Core::execute($object, $command, $output, $error);
                        ddd($object->config('project.log.error'));
                        $object->logger($object->config('project.log.error'))->error($command, [ $output, $error]);
                    }
                }
                if(
                    property_exists($event, 'controller') &&
                    is_array($event->controller)
                ){
                    foreach($event->controller as $controller){
                        $route = new stdClass();
                        $route->controller = $controller;
                        $route = Route::controller($route);
                        if(
                            property_exists($route, 'controller') &&
                            property_exists($route, 'function')
                        ){
                            $route->controller::{$route->function}($object, $event, $action, $options);
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
