<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-14
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_string_pad(Parse $parse, Data $data, $input='',  $pad_length=0, $pad_string=' ', $pad_type=null){
    if(empty($pad_type)){
        $pad_type= STR_PAD_RIGHT;
    } else {
        if(is_string($pad_type)){
            $pad_type = constant(strtoupper($pad_type));
        }
    }
    return str_pad($input, $pad_length, $pad_string, $pad_type);
}
