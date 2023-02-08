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

function function_array_reverse(Parse $parse, Data $data, $array=[], $preserve_key=false){
    if(empty($preserve_key)){
        $result = array_reverse($array);
    } else {
        $result = array_reverse($array, true);
    }
    return $result;
}
