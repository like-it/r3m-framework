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
use R3m\Io\Module\Core;

function function_request(Parse $parse, Data $data, $attribute=null, $value=null, $type=null){
    $object = $parse->object();
    if(
        is_string($attribute) &&
        substr($attribute, 0, 1) === '{' &&
        substr($attribute, -1, 1) === '}'
    ){
        $attribute = str_replace(['{$ldelim}', '{$rdelim}'], ['{', '}'], $attribute);
        $attribute = Core::object($attribute, Core::OBJECT_OBJECT);
    }
    if(!empty($parse->is_assign())){
        return $object->request($attribute, $value, $type);
    } else {
        if($attribute !== null){
            if($value === null){
                return $object->request($attribute);
            }
            elseif($type === null) {
                $object->request($attribute, $value);
            } else {
                return $object->request($attribute, $value, $type);
            }
        } else {
            return $object->request();
        }        
    }
}
