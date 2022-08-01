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

function validate_is_email(R3m\Io\App $object, $field='', $argument=''){
    if($object->request('has', 'node.' . $field)){
        $string = $object->request('node.' . $field);
    }
    elseif($object->request('has', 'node_' . $field)) {
        $string = $object->request('node_' . $field);
    }
    else {
        $string = $object->request($field);
    }
    if(filter_var($string, FILTER_VALIDATE_EMAIL)) {
        // valid address
        if($argument === false){
            return false;
        } else {
            return true;
        }
    }
    else {
        // invalid address
        if($argument === false){
            return true;
        } else {
            return false;
        }
    }
}
