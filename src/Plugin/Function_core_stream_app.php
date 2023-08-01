<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-13
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use stdClass;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;
use R3m\Io\Module\Event;


function function_core_stream_app(Parse $parse, Data $data, $command, $attribute=null, $type=null){
    $object = $parse->object();
    $output = [];
    $init = $object->config('core.execute.stream.init');
    $object->config('core.execute.stream.init', true);
    Core::execute($object, $command, $output, $notification, $type);
    if($attribute) {
        if (substr($attribute, 0, 1) === '$') {
            $attribute = substr($attribute, 1);
        }
        $data->data($attribute, $output);
        Event::trigger($object, 'core.stream.app.output', [
            'output' => $output,
            'command' => $command
        ]);
        if($notification){
            $data->data($attribute . '_notification', $notification);
            Event::trigger($object, 'core.stream.app.output.notification', [
                'notification' => $notification,
                'command' => $command
            ]);
        } else {
            $data->data('delete', $attribute . '_notification');
        }
    }
    if($init){
        $object->config('core.execute.stream.init', $init);
    } else {
        $object->config('delete', 'core.execute.stream.init');
    }
}
