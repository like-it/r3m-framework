<?php
/**
 * @author          Remco van der Velde
 * @since           18-12-2020
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

class Filter extends Data{

    public static function list($list): Filter
    {
        return new Filter($list);
    }

    public function where($where=[]){
        $list = $this->data();
        if(
            is_array($list) || 
            is_object($list)
        ){
            if(
                is_object($list) &&
                Core::object_is_empty($list)){
                return [];
            }
            ddd($where);
            foreach($list as $uuid => $node){
                foreach($where as $attribute => $record){
                    if(array_key_exists('exist', $record)){
                        if(!empty($record['exist'])){
                            if(is_object($node) && !property_exists($node, $attribute)){
                                $this->data('delete', $uuid);
                                unset($list->$uuid);
                            }
                        } else {
                            if(property_exists($node, $attribute)){
                                $this->data('delete', $uuid);
                                unset($list->$uuid);
                            }
                        }
                    } 
                    if(array_key_exists('exists', $record)){
                        if(!empty($record['exists'])){
                            if(!property_exists($node, $attribute)){
                                $this->data('delete', $uuid);
                                unset($list->$uuid);
                            }
                        } else {
                            if(property_exists($node, $attribute)){
                                $this->data('delete', $uuid);
                                unset($list->$uuid);
                            }
                        }
                    }
                    if(
                        array_key_exists('operator', $record) && 
                        array_key_exists('value', $record)                     
                    ){
                        $skip = false;
                        switch($record['operator']){
                            case '===' :
                                if(
                                    property_exists($node, $attribute) &&
                                    $node->$attribute === $record['value']
                                ){
                                    $skip = true;
                                }
                            break;
                            case '!==' :
                                if(
                                    property_exists($node, $attribute) &&
                                    $node->$attribute !== $record['value']
                                ){
                                    $skip = true;
                                }
                            break;
                            case '==' :
                                if(
                                    property_exists($node, $attribute) && 
                                    $node->$attribute == $record['value']
                                ){
                                    $skip = true;
                                }
                            break;
                            case '!=' :
                                if(
                                    property_exists($node, $attribute) && 
                                    $node->$attribute != $record['value']
                                ){
                                    $skip = true;
                                }                                
                            break;
                            case '>' :
                                if(
                                    property_exists($node, $attribute) && 
                                    $node->$attribute > $record['value']
                                ){
                                    $skip = true;
                                }                                
                            break;
                            case '>=' :
                                if(
                                    property_exists($node, $attribute) && 
                                    $node->$attribute >= $record['value']
                                ){
                                    $skip = true;
                                }                                
                            break;
                            case '<' :
                                if(
                                    property_exists($node, $attribute) && 
                                    $node->$attribute < $record['value']
                                ){
                                    $skip = true;
                                }                                
                            break;
                            case '<=' :
                                if(
                                    property_exists($node, $attribute) && 
                                    $node->$attribute <= $record['value']
                                ){
                                    $skip = true;
                                }                                
                            break;
                        }
                        if($skip === false){
                            $this->data('delete', $uuid);
                            if(is_array($list)){
                                unset($list[$uuid]);
                            } else {
                                unset($list->$uuid);
                            }
                        }
                    }
                }
            }
        }
        return $list;
    }
}
