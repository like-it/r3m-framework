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

function function_array_count(Parse $parse, Data $data, $array=[], $mode=COUNT_NORMAL){
    if(is_string($mode)){
        $mode = constant($mode);
    }
    return count($array, $mode);
}
