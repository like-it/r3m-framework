<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-18
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_cookie(Parse $parse, Data $data, $attribute=null, $value=null, $duration=null){
    $object = $parse->object();
    if(!empty($parse->is_assign())){
        $cookie = $object->cookie($attribute, $value, $duration);
        return Core::object($cookie);
    } else {
        return $object->cookie($attribute, $value, $duration);
    }    
}
