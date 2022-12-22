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
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Handler;
function function_request_reset(Parse $parse, Data $data){
    $object = $parse->object();
    $request = $object->request();
    $config = $object->config('request');
    foreach($request as $key => $value){
        $object->request('delete', $key);
    }
    $object->request($config);
    d($object->request());
}
