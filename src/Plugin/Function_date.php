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

function function_date(Parse $parse, Data $data, $format, $timestamp=null){
    if(empty($format)){
        $format = 'Y-m-d H:i:s';
    }
    elseif($format === true){
        $format = 'Y-m-d H:i:s P';
    }
    elseif(defined($format)){
        $format = constant($format);
    }
    if($timestamp === null){
        $timestamp = time();
    }
    return date($format, $timestamp);
}
