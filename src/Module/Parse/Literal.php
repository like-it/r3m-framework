<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */

namespace R3m\Io\Module\Parse;

use R3m\Io\Module\Data;
use R3m\Io\Module\Core;

class Literal {

    public static function apply($string='', Data $data){
        $explode = explode('{literal}', $string, 2);

        $key = $data->data('r3m.parse.literal.key');
        if(empty($key)){
            $uuid = Core::uuid();
            $data->data('r3m.parse.literal.key', $uuid);
        }
        if(isset($explode[1])){
            $temp = explode('{/literal}', $explode[1], 2);
            $uuid = Core::uuid();
            $data->data('r3m.parse.literal.' . $uuid, $temp[0]);
            $temp[1] = 'literal-' . $data->data('r3m.parse.literal.key') . '-' . $uuid . $temp[1];
            $explode[1] = $temp[1];
            $string = implode('', $explode);
            $explode = explode('{literal}', $string, 2);
            if(isset($explode[1])){
                return Literal::apply($string, $data);
            }
        }
        return $string;
    }

    public static function restore($string='', Data $data){
        if(is_object($string)){
            foreach($string as $key => $value){
                $string->{$key} = Literal::restore($value, $data);
            }
        }
        elseif(is_array($string)){
            foreach($string as $key => $value){
                $string[$key] = Literal::restore($value, $data);
            }
        } else {
            $tag = 'literal-' . $data->data('r3m.parse.literal.key') . '-';
            $explode = explode($tag, $string, 2);
            if(isset($explode[1])){
                $key = substr($explode[1], 0, 36);
                $string =  str_replace($tag . $key, $data->data('r3m.parse.literal.' . $key), $string);
                $explode = explode($tag, $string, 2);
                if(isset($explode[1])){
                    return Literal::restore($string, $data);
                }
            }
        }
        return $string;
    }


}