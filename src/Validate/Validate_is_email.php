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
use R3m\Io\Module\Parse\Token;

function validate_is_uuid(R3m\Io\App $object, $field='', $argument=''){
    $string = $object->request($field);
    //format: %s%s-%s-%s-%s-%s%s%s
    $explode = explode('-', $string);
    if(count($explode) !== 5){
        return false;
    }
    if(strlen($explode[0]) !== 8){
        return false;
    }
    if(strlen($explode[1]) !== 4){
        return false;
    }
    if(strlen($explode[2]) !== 4){
        return false;
    }
    if(strlen($explode[3]) !== 4){
        return false;
    }
    if(strlen($explode[4]) !== 12){
        return false;
    }
    return true;
}
