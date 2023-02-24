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
use Exception;
use R3m\Io\App;
use R3m\Io\Config;

class Event {

    public static function on(App $object, $action, $options){
        $event = $object->config('event.' . $action);
        if(empty($event)){
            $event = [];
        }
        $event[] = $options;
        $object->config('event.' . $action, $event);
    }

    public static function off(App $object, $action, $options){

    }

    public static function trigger(App $object, $action, $options){
        $events = $object->config('event.' . $action);
        if(empty($events)){
            return null;
        }
        $list = [];
        foreach($events as $event){
            ddd($event);
        }
    }

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
