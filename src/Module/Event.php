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

    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function trigger(App $object, $action, $options=[]){
//        $notifications = $object->get(App::EVENT)->get($action . '.notification');
        $events = $object->get(App::EVENT)->select('event', [
            'action' => $action
        ]);
        if(empty($events)){
            return null;
        }
        $events = Sort::list($events)->with(['priority' => 'DESC'], false , 'options');
        if(is_array($events)){
            foreach($events as $event){
                ddd($event);
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
