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

function function_request(Parse $parse, Data $data, $attribute=null, $value=null){
    $object = $parse->object();
    if($attribute === null){
        return $object->data('R3m\\Io.Request.Input')->data();
    }
    elseif($value === null){
        return $object->data('R3m\\Io.Request.Input')->data($attribute);
    } else {
        $object->data('R3m\\Io.Request.Input')->data($attribute, $value);
    }
}
