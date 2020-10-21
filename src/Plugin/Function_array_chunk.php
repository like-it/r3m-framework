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

function function_array_chunk(Parse $parse, Data $data, $array=[], $size=1, $preserve_key=false){
    if(empty($preserve_key)){
        $result = array_chunk($array, $size);
    } else {
        $result = array_chunk($array, $size, true);
    }
    return $result;
}
