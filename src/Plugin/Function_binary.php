<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-13
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_binary(Parse $parse, Data $data){
    if(array_key_exists('_', $_SERVER)){
        $dirname = \R3m\Io\Module\Dir::name($_SERVER['_']);
        $binary = str_replace($dirname, '', $_SERVER['_']);
        if(
            in_array(
                $binary,
                [
                    'php'
                ]
            )
        ){
            $binary = '';
        }
        return $binary;
    }
}
