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
namespace R3m\Io\Module;

use stdClass;
use Exception;

class Data {
    private $data;

    public function __construct($data=null){
        $this->data($data);
    }


    /**
     * @example
     *
     * cli: r3m test test2 test.csv
     * $object->parameter($object->data('request.input'), 'test2', -1)
     *
     * @param object $data
     * @param string $parameter
     * @param number $offset
     * @return NULL|boolean|string|
     */
    public static function parameter($data, $parameter, $offset=0){
        $result = null;
        $value = null;
        if(is_string($parameter) && stristr($parameter, '\\')){
            //classname adjustment
            $parameter = basename(str_replace('\\', '//', $parameter));
        }
        if(is_numeric($parameter) && is_object($data)){
            if(property_exists($data, $parameter)){
                $param = ltrim($data->{$parameter}, '-');
                $result = $param;
            } else {
                $result = null;
            }
        } else {
            if(
                is_array($data) ||
                is_object($data)
            ){
                foreach($data as $key => $param){
                    if(is_numeric($key)){
                        $param = ltrim($param, '-');
                        $param = rtrim($param);
                        $tmp = explode('=', $param);
                        if(count($tmp) > 1){
                            $param = array_shift($tmp);
                            $value = implode('=', $tmp);
                        }
                        if(strtolower($param) == strtolower($parameter)){
                            if($offset !== 0){
                                if(property_exists($data, ($key + $offset))){
                                    $value = rtrim(ltrim($data->{($key + $offset)}, '-'));
                                } else {
                                    $result = null;
                                    break;
                                }
                            }
                            if(isset($value) && $value !== null){
                                $result = $value;
                            } else {
                                $result = true;
                                return $result;
                            }
                            break;
                        }
                        $value = null;
                    }
                    elseif($key == $parameter){
                        if($offset < 0){
                            while($offset < 0){
                                $param = prev($data);
                                $offset++;
                            }
                            return $param;
                        }
                        elseif($offset == 0){
                            return $param;
                        } else {
                            while($offset > 0){
                                $param = next($data);
                                $offset--;
                            }
                            return $param;
                        }
                    }
                    $pointer = next($data);
                }
            }
        }
        if($result === null || is_bool($result)){
            return $result;
        }
        return trim($result);
    }

    public function data($attribute=null, $value=null, $type=null){
        if($attribute !== null){
            if($attribute == 'set'){
                Core::object_delete($value, $this->data()); //for sorting an object
                Core::object_set($value, $type, $this->data());
                return Core::object_get($value, $this->data());
            }
            elseif($attribute == 'get'){
                return Core::object_get($value, $this->data());
            }
            elseif($attribute == 'has'){
                return Core::object_has($value, $this->data());
            }
            if($value !== null){
                if(
                    in_array(
                        $attribute,
                        [
                            'delete',
                            'remove'
                        ]
                    )
                ){
                    return $this->deleteData($value);
                } else {
                    Core::object_delete($attribute, $this->data()); //for sorting an object
                    Core::object_set($attribute, $value, $this->data());
                    return;
                }
            } else {
                if(is_string($attribute)){
                    return Core::object_get($attribute, $this->data());
                } else {
                    $this->setData($attribute);
                    return $this->getData();
                }
            }
        }
        return $this->getData();
    }
    private function setData($attribute='', $value=null){
        if(is_array($attribute) || is_object($attribute)){
            if(is_object($this->data)){
                foreach($attribute as $key => $value){
                    $this->data->{$key} = $value;
                }
            }
            elseif(is_array($this->data)){
                foreach($attribute as $key => $value){
                    $this->data[$key] = $value;
                }
            } else {
                $this->data = $attribute;
            }
        } else {
            if(is_object($this->data)){
                $this->data->{$attribute} = $value;
            }
            elseif(is_array($this->data)) {
                $this->data[$attribute] = $value;
            }
        }
    }

    protected function getData($attribute=null){
        if($attribute === null){
            if(is_null($this->data)){
                $this->data = new stdClass();
            }
            return $this->data;
        }
        if(isset($this->data[$attribute])){
            return $this->data[$attribute];
        } else {
            return false;
        }
    }

    private function deleteData($attribute=null){
        return Core::object_delete($attribute, $this->data());
    }
}