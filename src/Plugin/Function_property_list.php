<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-22
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_property_list(Parse $parse, Data $data, $object, $allowed=[]){
    $result = [];
    if(is_object($object)){
        foreach($object as $attribute => $unused){
            if(empty($allowed)){
                $result[] = $attribute;
            }
            elseif(in_array($attribute, $allowed, true)){
                $result[] = $attribute;
            }
        }
    }
    return $result;
}
