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


function function_core_stream(Parse $parse, Data $data, $command, $attribute=null, $type=null){
    $object = $parse->object();
    $output = [];
    $mode = $object->config('core.execute.mode');
    $object->config('core.execute.mode', 'stream');
    Core::execute($object, $command, $output, $notification, $type);
    if($attribute) {
        if (substr($attribute, 0, 1) === '$') {
            $attribute = substr($attribute, 1);
        }
        $data->data($attribute, $output);
        Event::trigger($object, 'core.stream.output', [
            'output' => $output,
            'command' => $command
        ]);
        if($notification){
            $data->data($attribute . '_notification', $notification);
            Event::trigger($object, 'core.stream.output.notification', [
                'notification' => $notification,
                'command' => $command
            ]);
        } else {
            $data->data('delete', $attribute . '_notification');
        }
    }
    if($mode){
        $object->config('core.execute.mode', $mode);
    } else {
        $object->config('delete', 'core.execute.mode');
    }
}
