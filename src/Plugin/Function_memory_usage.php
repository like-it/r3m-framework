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

function function_memory_usage(Parse $parse, Data $data, $format=''){
    $usage = memory_get_peak_usage(true);
    switch(strtoupper($format)){
        case 'B' :
            $result = $usage . ' B';
        break;
        case 'KB' :
            $result = round($usage / 1024, 2) . ' KB';
        break;
        case 'MB' :
            $result = round($usage / 1024 / 1024, 2) . ' MB';
        break;
        case 'GB' :
            $result = round($usage / 1024 / 1024 / 1024, 2) . ' GB';
        break;
        case 'TB' :
            $result = round($usage / 1024 / 1024 / 1024 / 1024, 2) . ' TB';
        break;
        case 'PB' :
            $result = round($usage / 1024 / 1024 / 1024 / 1024 / 1024, 2) . ' PB';
        break;
        default :
            $result = $usage;
        break;
    }
    return $result;
}
