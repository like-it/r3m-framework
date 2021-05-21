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

function function_session(Parse $parse, Data $data, $attribute=null, $value=null){
    $object = $parse->object();
    if($attribute === 'delete'){
        $object->session($attribute, $value);
        return;
    }
    if(!empty($parse->is_assign())){
        $session = $object->session($attribute, $value); 
        return Core::object($session);
    } else {
        return $object->session($attribute, $value);
    }    
}
