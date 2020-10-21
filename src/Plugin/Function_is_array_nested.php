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
use R3m\Io\Module\Core;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_is_array_nested(Parse $parse, Data $data, $array=null){
    $result = Core::is_array_nested($array);
    return $result;
}
