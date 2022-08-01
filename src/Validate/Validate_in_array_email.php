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

function validate_in_array_email(R3m\Io\App $object, $field='', $argument=''){
    if($object->request('has', 'node.' . $field)){
        $array = $object->request('node.' . $field);
    } else {
        $array = $object->request($field);
    }
    dd($array);
    foreach($array as $nr => $value){
        if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            // invalid address
            return false;
        }
    }
    return true;
}
