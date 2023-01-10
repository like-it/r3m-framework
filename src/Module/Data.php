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

use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;
use stdClass;
use Exception;

class Data {

    private $data;
    private $do_not_nest_key;


    private $copy;

    public function __construct($data=null){
        $this->data($data);
    }

    /**
     * @example
     *
     * cli: r3m test test2 test.csv
     * Data::parameter($object->data('request.input'), 'test2', -1)
     * App::parameter(App $object, 'test2', -1)
     *
     * @param object $data
     * @param string $parameter
     * @param number $offset
     * @return NULL|boolean|string
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
                $param = $data->{$parameter};
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
                        if(substr($param, 0, 2) === '-'){
                            continue;
                        }
                        $param = rtrim($param);
                        $tmp = explode('=', $param);
                        if(count($tmp) > 1){
                            $param = array_shift($tmp);
                            $value = implode('=', $tmp);
                        }
                        if(strtolower($param) == strtolower($parameter)){
                            if($offset !== 0){
                                if(property_exists($data, ($key + $offset))){
                                    $value = trim($data->{($key + $offset)});
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

    public static function flags($data): array
    {
        $flags = [];
        foreach($data as $nr => $parameter){
            if(substr($parameter, 0, 2) === '--'){
                $parameter = substr($parameter, 2);
                $tmp = explode('=', $parameter);
                if(count($tmp) > 1){
                    $parameter = array_shift($tmp);
                    $value = implode('=', $tmp);
                } else {
                    $value = true;
                }
                $flags[$parameter] = $value;
            }
        }
        return $flags;
    }

    public static function options($data): array
    {
        $options = [];
        foreach($data as $nr => $parameter){
            if(
                substr($parameter, 0, 2) !== '--' &&
                substr($parameter, 0, 1) === '-'
            ){
                $parameter = substr($parameter, 1);
                $tmp = explode('=', $parameter);
                if(count($tmp) > 1){
                    $parameter = array_shift($tmp);
                    $value = implode('=', $tmp);
                } else {
                    $value = true;
                }
                $options[$parameter] = $value;
            }
        }
        return $options;
    }

    public function get($attribute=''){
        return $this->data('get', $attribute);
    }

    public function set($attribute='', $value=null){
        return $this->data('set', $attribute, $value);
    }

    public function delete($attribute=''){
        return $this->data('delete', $attribute);
    }

    public function has($attribute=''){
        return Core::object_has($attribute, $this->data());
    }

    public function data($attribute=null, $value=null, $type=null){
        if($attribute !== null){
            if($attribute == 'set'){
                if(
                    $value === null &&
                    $type === null
                ){
                    $this->data = null;
                } else {
                    $do_not_nest_key = $this->do_not_nest_key();
                    if($do_not_nest_key){
                        $this->data->{$value} = $type;
                        return $this->data->{$value};
                    } else {
                        Core::object_delete($value, $this->data()); //for sorting an object
                        Core::object_set($value, $type, $this->data());
                        return Core::object_get($value, $this->data());
                    }
                }
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
                }
                elseif(is_object($attribute) && get_class($attribute) === Data::class){
                    $this->setData($attribute->data());
                    return $this->getData();
                }
                else {
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

    public function is_empty(){
        $data = $this->data();
        if(empty($data) && is_array($data)){
            return true;
        }
        elseif(Core::object_is_empty($data)){
            return true;
        }
        return false;
    }

    public function clear(){
        $data = $this->data();
        foreach($data as $key => $unused){
            $this->data('delete', $key);
        }
    }

    public function copy(){
        $data = $this->data();
        if(is_array($data)){
            $this->copy = $data;
        } else {
            $this->copy = Core::deep_clone($data);
        }
    }

    public function reset(){
        $this->clear();
        if($this->copy){
            $this->data($this->copy);
        }
    }

    public function do_not_nest_key($do_not_nest_key=null){
        if($do_not_nest_key !== null){
            $this->do_not_nest_key = $do_not_nest_key;
        }
        return $this->do_not_nest_key;
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     */
    public function write($url=''){
        $dir = Dir::name($url);
        Dir::create($dir);
        return File::write($url, Core::object($this->data(), Core::OBJECT_JSON));
    }
}