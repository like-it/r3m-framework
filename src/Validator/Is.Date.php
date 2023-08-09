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
use R3m\Io\App;

function validate_is_date(App $object, $string='', $field='', $argument=''): bool
{
    if(is_object($argument) && property_exists($argument, 'format')){
        $format = $argument->format;
    } else {
        $format = 'Y-m-d';
    }
    switch(strtolower($format)){
        case 'y':
            $explode = explode('-', $string);
            if(count($explode) !== 1){
                return false;
            }
            $string = strtotime($string . ' years');
            break;
        case 'y-m':
            $explode = explode('-', $string);
            if(count($explode) !== 2){
                return false;
            }
            $explode[0] = $explode[0] + 0;
            $explode[1] = $explode[1] + 0;
            if($explode[1] < 1 || $explode[1] > 12){
                return false;
            }
            $string = strtotime($string . '-01');
        case 'y-m-d':
            $explode = explode('-', $string);
            if(count($explode) !== 3){
                return false;
            }
            $explode[0] = $explode[0] + 0;
            $explode[1] = $explode[1] + 0;
            $explode[2] = $explode[2] + 0;
            if($explode[1] < 1 || $explode[1] > 12){
                return false;
            }
            if($explode[2] < 1 || $explode[2] > 31){
                return false;
            }
            $string = strtotime($string);
            break;
        case 'y-m-d h':
            $explode_date = explode(' ', $string);
            $explode_date[1] = $explode_date[1] + 0;
            if($explode_date[1] < 0 || $explode_date[1] > 23){
                return false;
            }
            $explode = explode('-', $explode_date[0]);
            if(count($explode) !== 3){
                return false;
            }
            $explode[0] = $explode[0] + 0;
            $explode[1] = $explode[1] + 0;
            $explode[2] = $explode[2] + 0;
            if($explode[1] < 1 || $explode[1] > 12){
                return false;
            }
            if($explode[2] < 1 || $explode[2] > 31){
                return false;
            }
            $string = strtotime($string . ':00:00');
            break;
        case 'y-m-d h:i':
            $explode_date = explode(' ', $string);
            $temp = explode(':', $explode_date[1]);
            $temp[0] = $temp[0] + 0;
            $temp[1] = $temp[1] + 0;
            if($temp[0] < 0 || $temp[0] > 23){
                return false;
            }
            if($temp[1] < 0 || $temp[1] > 59){
                return false;
            }
            $explode = explode('-', $explode_date[0]);
            if(count($explode) !== 3){
                return false;
            }
            $explode[0] = $explode[0] + 0;
            $explode[1] = $explode[1] + 0;
            $explode[2] = $explode[2] + 0;
            if($explode[1] < 1 || $explode[1] > 12){
                return false;
            }
            if($explode[2] < 1 || $explode[2] > 31){
                return false;
            }
            $string = strtotime($string . ':00');
            break;
        case 'y-m-d h:i:s':
            $explode_date = explode(' ', $string);
            $temp = explode(':', $explode_date[1]);
            $temp[0] = $temp[0] + 0;
            $temp[1] = $temp[1] + 0;
            $temp[2] = $temp[2] + 0;
            if($temp[0] < 0 || $temp[0] > 23){
                return false;
            }
            if($temp[1] < 0 || $temp[1] > 59){
                return false;
            }
            if($temp[2] < 0 || $temp[2] > 59){
                return false;
            }
            $explode = explode('-', $explode_date[0]);
            if(count($explode) !== 3){
                return false;
            }
            $explode[0] = $explode[0] + 0;
            $explode[1] = $explode[1] + 0;
            $explode[2] = $explode[2] + 0;
            if($explode[1] < 1 || $explode[1] > 12){
                return false;
            }
            if($explode[2] < 1 || $explode[2] > 31){
                return false;
            }
            $string = strtotime($string);
            break;
    }
    if(property_exists($argument, 'range')){
        if(property_exists($argument->range, 'min')){
            if($string < $argument->range->min){
                return false;
            }
        }
        if(property_exists($argument->range, 'max')){
            if($string > $argument->range->max){
                return false;
            }
        }
    }
    return true;
}
