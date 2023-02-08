<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-16
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_string_token(Parse $parse, Data $data, $string='', $token=null){
    if($token === null){
        $result = strtok($string);
    } else {
        $result = strtok($string, $token);
    }
    return $result;
}
