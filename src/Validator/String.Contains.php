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
use R3m\Io\App;

function validate_string_contains(App $object, $string='', $field='', $argument=''): bool
{
    if(empty($string)){
        return true;
    }
    if(is_object($argument)){
        if(property_exists($argument, 'regex')){
            $matches = [];
            preg_match(
                $argument->regex,
                $string,
            $matches
            );
            if(array_key_exists(0, $matches)){
                if($string != $matches[0]){
                    return false;
                }
                return true;
            } else {
                return false;
            }
        }
    }

    if(is_string($argument)){
        if(stristr($string, $argument) !== false){
            return true;
        }
    }
    return false;
}
