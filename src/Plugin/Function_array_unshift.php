<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-19
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_array_unshift(Parse $parse, Data $data, $selector='', ...$value){
    if(substr($selector, 0, 1) == '$'){
        $selector = substr($selector, 1);
    }
    $array = $data->data($selector);
    $result = array_unshift($array, ...$value);
    $data->data($selector, $array);
    return $result;
}
