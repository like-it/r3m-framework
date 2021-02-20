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

    public static function list($list){
        $filter = new Filter($list);
        return $filter;
    }

    public function where($where=[]){
        $list = $this->data();
        if(
            is_array($list) || 
            is_object($list)
        ){
            foreach($list as $uuid => $node){
                foreach($where as $attribute => $record){
                    if(array_key_exists('exist', $record)){
                        if(!empty($record['exist'])){
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
                            unset($list->$uuid);
                        }
                    }
                }
            }
        }
        return $list;
    }
}
