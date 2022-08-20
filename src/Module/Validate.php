<?php
/**
 * @author          Remco van der Velde
 * @since           16-12-2020
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module;

use stdClass;
use Exception;
use R3m\Io\App;
use R3m\Io\Config;

class Validate {

    public static function check($validate, $attribute=''){
        $is_valid = true;
        if(
            !empty($validate) &&
            is_object($validate) &&
            property_exists($validate, 'test') &&
            array_key_exists($attribute, $validate->test)
        ){
            foreach($validate->test[$attribute] as $type => $status_list){
                foreach ($status_list as $nr => $status){
                    if($status === false){
                        $is_valid = false;
                        break 2;
                    }
                }
            }
        }
        return $is_valid;
    }

    /**
     * @throws Exception
     */
    public static function validate(App $object, $validate){
        $extension = $object->config('extension.php');  
        $test = [];
        foreach($validate as $field => $list){
            $is_optional = false;
            if($field == 'test'){
                continue;
            }
            if(substr($field, 0, 1) === '?'){
                $field = substr($field, 1);
                $is_optional = true;
            }
            $test[$field] = [];
            if(is_object($list)){
                $validate->{$field} = Validate::validate($object, $list);
                if(property_exists($validate->{$field}, 'test')){
                    $validate->test[$field] = $validate->{$field}->test;                    
                }
            } 
            elseif(is_array($list)){
                $field_request = str_replace('[]', '', $field);
                d($field_request);
                dd($object->request());
                if($object->request('has', 'node.' . $field_request)){
                    $value = $object->request('node.' . $field_request);
                }
                elseif($object->request('has', 'node_' . $field_request)) {
                    $value = $object->request('node_' . $field_request);
                }
                else {
                    $value = $object->request($field_request);
                }
                if(
                    is_string($value) &&
                    substr($value, 0, 1) === '[' &&
                    substr($value, -1, 1) === ']'
                ){
                    $value = Core::object($value, Core::OBJECT_ARRAY);
                }
                if($is_optional && empty($value)){
                    $function = 'optional';
                    if(empty($test[$field][$function])){
                        $test[$field][$function] = [];
                    }
                    $test[$field][$function][] = true;
                } else {
                    foreach($list as $nr => $record){
                        foreach($record as $key => $argument){
                            $key = 'validate' . '.' . $key;
                            $url = $object->config('framework.dir.validate') . ucfirst(str_replace('.', '_', $key) . $extension);
                            if(File::exist($url)){
                                require_once $url;
                                $function = str_replace('.', '_', $key);
                                if(empty($test[$field][$function])){
                                    $test[$field][$function] = [];
                                }
                                $test[$field][$function][] = $function($object, $value, $field, $argument);
                            } else {
                                throw new Exception('validator (' . $url . ') not found');
                            }
                        }
                    }
                }
            }
        }        
        if(property_exists($validate, 'test')){            
            $validate->test = array_merge($test, $validate->test);
        } else {
            $validate->test = $test;
        }
        foreach($validate as $field => $value) {
            if (
                is_object($value) &&
                property_exists($value, 'success') &&
                $value->success === false
            ) {
                $validate->success = $value->success;
            }
        }
        if(
            property_exists($validate, 'success') &&
            $validate->success===false
        ){
            return $validate;
        } else {
            $validate->success = true;
            foreach($test as $field => $list){
                foreach($list as $key => $subList){
                    foreach($subList as $nr => $status){
                        if(empty($status)){
                            $validate->success = false;
                        }
                    }
                }
            }        
            return $validate;
        }        
    }
}