<?php
/**
 * @author          Remco van der Velde
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module\Parse;

use R3m\Io\Module\Data;
use R3m\Io\Module\Core;

class Literal {

    public static function apply(Data $data, $string=''){
        $explode = explode('{literal}', $string, 2);
        $key = $data->data('r3m.io.parse.literal.key');
        if(empty($key)){
            $uuid = sha1('{literal}');
            $data->data('r3m.io.parse.literal.key', $uuid);
        }
        if(isset($explode[1])){
            $temp = explode('{/literal}', $explode[1], 2);
            $uuid = sha1($temp[0]);
            $data->data('r3m.io.parse.literal.' . $uuid, $temp[0]);
            $temp[1] = 'literal-' . $data->data('r3m.io.parse.literal.key') . '-' . $uuid . $temp[1];
            $explode[1] = $temp[1];
            $string = implode('', $explode);
            $explode = explode('{literal}', $string, 2);
            if(isset($explode[1])){
                return Literal::apply($data, $string);
            }
        }
        return $string;
    }

    public static function restore(Data $data, $string=''){
        if(is_object($string)){
            foreach($string as $key => $value){
                $string->{$key} = Literal::restore($data, $value);
            }
        }
        elseif(is_array($string)){
            foreach($string as $key => $value){
                $string[$key] = Literal::restore($data, $value);
            }
        } else {
            $tag = 'literal-' . $data->data('r3m.io.parse.literal.key') . '-';
            $explode = explode($tag, $string, 2);
            if(isset($explode[1])){
                $key = substr($explode[1], 0, 40);
                $string =  str_replace($tag . $key, $data->data('r3m.io.parse.literal.' . $key), $string);
                $explode = explode($tag, $string, 2);
                if(isset($explode[1])){
                    return Literal::restore($data, $string);
                }
            }
        }
        return $string;
    }
}