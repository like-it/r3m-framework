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

function function_in_array(Parse $parse, Data $data, $needle='', $haystack=[], $strict=false){
    if(!empty($strict)){
        $result = in_array($needle, $haystack, true);
    } else {
        $result = in_array($needle, $haystack, false);
    }
    return $result;
}
