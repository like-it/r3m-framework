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

function function_array_combine(Parse $parse, Data $data, $keys=[], $values=[]){
    if(count($keys) == count($values)){
        $result = array_combine($keys, $values);
    } else {
        $result = false;
    }
    return $result;
}
