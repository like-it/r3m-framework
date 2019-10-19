<?php
/**
 *  (c) 2019 Priya.software
 *
 *  License: MIT
 *
 *  Author: Remco van der Velde
 *  Version: 1.0
 */

namespace R3m\Io\Module;

use stdClass;
use Exception;

class Core {

    const EXCEPTION_MERGE_ARRAY_OBJECT = 'cannot merge an array with an object.';

    const ATTRIBUTE_EXPLODE = [
        '.'
    ];

    public static function array_object($array=array()){
        $object = new stdClass();
        foreach ($array as $key => $value){
            if(is_array($value)){
                $object->{$key} = Core::array_object($value);
            } else {
                $object->{$key} = $value;
            }
        }
        return $object;
    }

    public static function explode_multi($delimiter=array(), $string='', $limit=array()){
        $result = array();
        if(!is_array($limit)){
            $limit = explode(',', $limit);
            $value = reset($limit);
            if(count($delimiter) > count($limit)){
                for($i = count($limit); $i < count($delimiter); $i++){
                    $limit[$i] = $value;
                }
            }
        }
        foreach($delimiter as $nr => $delim){
            if(isset($limit[$nr])){
                $tmp = explode($delim, $string, $limit[$nr]);
            } else {
                $tmp = explode($delim, $string);
            }
            if(count($tmp)==1){
                continue;
            }
            foreach ($tmp as $tmp_value){
                $result[] = $tmp_value;
            }
        }
        if(empty($result)){
            $result[] = $string;
        }
        return $result;
    }

    public static function object_delete($attributeList=array(), $object='', $parent='', $key=null){
        if(is_string($attributeList)){
            $attributeList = Core::explode_multi(array('.', ':', '->'), $attributeList);
        }
        if(is_array($attributeList)){
            $attributeList = Core::object_horizontal($attributeList);
        }
        if(!empty($attributeList) && is_object($attributeList)){
            foreach($attributeList as $key => $attribute){
                if(isset($object->{$key})){
                    return Core::object_delete($attribute, $object->{$key}, $object, $key);
                } else {
                    unset($object->{$key}); //to delete nulls
                    return false;
                }
            }
        } else {
            unset($parent->{$key});    //unset $object won't delete it from the first object (parent) given
            return true;
        }
    }

    public static function object_has($attributeList=array(), $object=''){
        if(Core::object_is_empty($object)){
            if(empty($attributeList)){
                return true;
            }
            return false;
        }
        if(is_string($attributeList)){
            $attributeList = Core::explode_multi(Core::ATTRIBUTE_EXPLODE, $attributeList);
            foreach($attributeList as $nr => $attribute){
                if(empty($attribute)){
                    unset($attributeList[$nr]);
                }
            }
        }
        if(is_array($attributeList)){
            $attributeList = Core::object_horizontal($attributeList);
        }
        if(empty($attributeList)){
            return true;
        }
        foreach($attributeList as $key => $attribute){
            if(empty($key)){
                continue;
            }
            if(property_exists($object,$key)){
                $get = Core::object_has($attributeList->{$key}, $object->{$key});
                if($get === false){
                    return false;
                }
                return true;
            }
        }
        return false;
    }

    public static function object_get($attributeList=array(), $object=''){
        if(Core::object_is_empty($object)){
            if(empty($attributeList)){
                return $object;
            }
            return null;
        }
        if(is_string($attributeList)){
            $attributeList = Core::explode_multi(Core::ATTRIBUTE_EXPLODE, $attributeList);
            foreach($attributeList as $nr => $attribute){
                if(empty($attribute) && $attribute != '0'){
                    unset($attributeList[$nr]);
                }
            }
        }
        if(is_array($attributeList)){
            $attributeList = Core::object_horizontal($attributeList);
        }
        if(empty($attributeList)){
            return $object;
        }
        foreach($attributeList as $key => $attribute){
            if(empty($key) && $key != 0){
                continue;
            }
            if(isset($object->{$key})){
                return Core::object_get($attributeList->{$key}, $object->{$key});
            }
        }
        return null;
    }

    public static function object_merge(){
        $objects = func_get_args();
        $main = array_shift($objects);
        if(empty($main) && !is_array($main)){
            $main = new stdClass();
        }
        foreach($objects as $nr => $object){
            if(is_array($object)){
                foreach($object as $key => $value){
                    if(is_object($main)){
                        throw new Exception(Core::EXCEPTION_MERGE_ARRAY_OBJECT);
                    }
                    if(!isset($main[$key])){
                        $main[$key] = $value;
                    } else {
                        if(is_array($value) && is_array($main[$key])){
                            $main[$key] = Core::object_merge($main[$key], $value);
                        } else {
                            $main[$key] = $value;
                        }
                    }
                }
            }
            elseif(is_object($object)){
                foreach($object as $key => $value){
                    if((!isset($main->{$key}))){
                        $main->{$key} = $value;
                    } else {
                        if(is_object($value) && is_object($main->{$key})){
                            $main->{$key} = Core::object_merge(clone $main->{$key}, clone $value);
                        } else {
                            $main->{$key} = $value;
                        }
                    }
                }
            }
        }
        return $main;
    }

    public static function object_set($attributeList=array(), $value=null, $object='', $return='child'){
        if(empty($object)){
            return;
        }
        if(is_string($return) && $return != 'child'){
            if($return == 'root'){
                $return = $object;
            } else {
                $return = Core::object_get($return, $object);
            }
        }
        if(is_string($attributeList)){
            $attributeList = Core::explode_multi(Core::ATTRIBUTE_EXPLODE, $attributeList);
        }
        if(is_array($attributeList)){
            $attributeList = Core::object_horizontal($attributeList);
        }
        if(!empty($attributeList)){
            foreach($attributeList as $key => $attribute){
                if(isset($object->{$key}) && is_object($object->{$key})){
                    if(empty($attribute) && is_object($value)){
                        foreach($value as $value_key => $value_value){
                            if(isset($object->$key->$value_key)){
                                //                                 unset($object->$key->$value_key);   //so sort will happen, request will tak forever and apache2 crashes needs reboot apache2
                            }
                            $object->{$key}->{$value_key} = $value_value;
                        }
                        return $object->{$key};
                    }
                    return Core::object_set($attribute, $value, $object->{$key}, $return);
                }
                elseif(is_object($attribute)){
                    $object->{$key} = new stdClass();
                    return Core::object_set($attribute, $value, $object->{$key}, $return);
                } else {
                    $object->{$key} = $value;
                }
            }
        }
        if($return == 'child'){
            return $value;
        }
        return $return;
    }

    public static function object_is_empty($object=null){
        if(!is_object($object)){
            return true;
        }
        $is_empty = true;
        foreach ($object as $value){
            $is_empty = false;
            break;
        }
        return $is_empty;
    }

    public static function is_cli(){
        if(isset($_SERVER['HTTP_HOST'])){
            $domain = $_SERVER['HTTP_HOST'];
        }
        elseif(isset($_SERVER['SERVER_NAME'])){
            $domain = $_SERVER['SERVER_NAME'];
        } else {
            $domain = '';
        }
        if(empty($domain)){
            if(!defined('IS_CLI')){
                define('IS_CLI', true);
                return true;
            }
        } else {
            return false;
        }
    }

    public static function object_horizontal($verticalArray=array(), $value=null, $return='object'){
        if(empty($verticalArray)){
            return false;
        }
        $object = new stdClass();
        if(is_object($verticalArray)){
            $attributeList = get_object_vars($verticalArray);
            $list = array_keys($attributeList);
            $last = array_pop($list);
            if($value===null){
                $value = $verticalArray->$last;
            }
            $verticalArray = $list;
        } else {
            $last = array_pop($verticalArray);
        }
        if(empty($last) && $last != '0'){
            return false;
        }
        foreach($verticalArray as $attribute){
            if(empty($attribute)){
                continue;
            }
            if(!isset($deep)){
                $object->{$attribute} = new stdClass();
                $deep = $object->{$attribute};
            } else {
                $deep->{$attribute} = new stdClass();
                $deep = $deep->{$attribute};
            }
        }
        if(!isset($deep)){
            $object->$last = $value;
        } else {
            $deep->$last = $value;
        }
        if($return=='array'){
            $json = json_encode($object);
            return json_decode($json,true);
        } else {
            return $object;
        }
    }
}