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

function function_array_diff(Parse $parse, Data $data, $array1=[], $array2=[], $array3=null, $array4=null, $array5=null){
    if($array5 !== null){
        $result = array_diff($array1, $array2, $array3, $array4, $array5);
    }
    elseif($array4 !== null){
        $result = array_diff($array1, $array2, $array3, $array4);
    }
    elseif($array3 !== null){
        $result = array_diff($array1, $array2, $array3);
    } else {
        $result = array_diff($array1, $array2);
    }
    return $result;
}
