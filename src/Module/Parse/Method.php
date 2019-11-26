<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */

namespace R3m\Io\Module\Parse;

use Exception;
use R3m\Io\Module\Data;

class Method {

    public static function get($build, $record=[], Data $storage){
        if($record['type'] != Token::TYPE_METHOD){
            return $record;
        }
        $attribute = '';
        if(array_key_exists('attribute', $record['method'])){
            foreach($record['method']['attribute'] as $nr => $token){
                $count = count($token);
                $token = Token::define($token);
                $token = Token::method($token);
                $token = $build->require('function', $token);
                $value = Variable::getValue($build, $token, $storage);
                $attribute .= $value . ', ';
            }
            $attribute = substr($attribute, 0, -2);
        }
        if(array_key_exists('php_name', $record['method'])){
            $result = $record['method']['php_name'] . '($this->parse(), $this->storage(), ' . $attribute . ')';
            $record['value'] = $result;
            $record['type'] = Token::TYPE_CODE;
        } else {
            d($record);
            $debug = debug_backtrace(true);
            dd($debug);
            $record['method']['php_name'] = 'function_' . str_replace('.', '_', $record['value']);
            $result = $record['method']['php_name'] . '($this->parse(), $this->storage(), ' . $attribute . ')';
            $record['value'] = $result;
            $record['type'] = Token::TYPE_CODE;
            dd($record);
        }
        return $record;
    }

    public static function create($build, $token=[], Data $storage){
        $method = array_shift($token);
        $record = Method::get($build, $method, $storage);
        if($record['type'] === Token::TYPE_CODE){
            return $record['value'];
        }
        d($record);
        throw new Exception('Method type (' . $record['type'] . ') undefined');
    }

}