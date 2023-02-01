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
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_array_key_exist(Parse $parse, Data $data, $key='', $array=[]){
    $result = array_key_exists($key, $array);
    return $result;
}
