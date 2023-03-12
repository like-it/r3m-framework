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
    const NAME = 'Event';

    public static function on(App $object, $action, $options=[]){
        $event = $object->get(App::EVENT)->get($action);
        if(empty($event)){
            $event = [];
        }
        $event[] = $options;
        $object->get(App::EVENT)->set($action, $event);
    }

    public static function off(App $object, $action, $options=[]){

    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function trigger(App $object, $action, $options=[]){
        $notifications = $object->get(App::EVENT)->get($action . '.notification');
        $events = $object->get(App::EVENT)->get($action);
        unset($events['notification']);
        if(empty($events) && empty($notifications)){
            return null;
        }
        $notifications = Sort::list($notifications)->with(['priority' => 'DESC']);
        $events = Sort::list($events)->with(['priority' => 'DESC']);
        if(is_array($notifications)){
            foreach($notifications as $record){
                if(
                    property_exists($record, 'command') &&
                    is_array($record->command)
                ){
                    foreach($record->command as $command){
                        $command = str_replace('{{binary()}}', Core::binary(), $command);
                        Core::execute($object, $command, $output, $notification);
                    }
                }
                if(
                    property_exists($record, 'controller') &&
                    is_array($record->controller)
                ){
                    foreach($record->controller as $controller){
                        $route = new stdClass();
                        $route->controller = $controller;
                        $route = Route::controller($route);
                        if(
                            property_exists($route, 'controller') &&
                            property_exists($route, 'function')
                        ){
                            $route->controller::{$route->function}($object, $record, $action, $options);
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
                        Core::execute($object, $command, $output, $notification);
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

                            $event = new Data($event);
                            try {
                                $route->controller::{$route->function}($object, $event, $action, $options);
                            }
                            catch (LocateException $exception){
                                ddd('found');
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
