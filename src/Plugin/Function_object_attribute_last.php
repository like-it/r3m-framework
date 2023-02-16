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

function function_object_attribute_last(Parse $parse, Data $data, $object){
    if(is_object($object)){
        $attribute = false;
        foreach($object as $attribute => $unused){
            //intentionally
        }
        return $attribute;
    }
    return false;
}
