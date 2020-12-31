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

class Sort extends data{

    public static function list($list){
        $filter = new Sort($list);
        return $filter;
    }

    public function with($sort=[]){
        $list = $this->data();
        if(
            is_array($list) || 
            is_object($list)
        ){
            $result = [];  
            $count = count($sort);
            if($count == 1){
                foreach($list as $uuid => $node){
                    foreach($sort as $attribute => $record){                    
                        if(property_exists($node, $attribute)){
                            $result[$node->$attribute][] = $node;
                        } else {
                            dd($node);
                        }
                        $sortable_1 = $sort[$attribute];                    
                        break;
                    }                
                }
                unset($sort[$attribute]);                
                if(strtolower($sortable_1) == 'asc'){
                    ksort($result, SORT_NATURAL);
                } else {
                    krsort($result, SORT_NATURAL);
                } 
                $list = [];                
                foreach($result as $attribute => $subList){
                    foreach($subList as $nr => $record){
                        $list[$record->uuid] = $record;
                    }
                }  
                return $list;
            }
            elseif($count == 2){
                if(Core::object_is_empty($list)){
                    return $list;
                }
                foreach($list as $uuid => $node){
                    foreach($sort as $attribute => $record){                    
                        if(property_exists($node, $attribute)){
                            $result[$node->$attribute][] = $node;
                        } else {}
                        $sortable_1 = $sort[$attribute];                    
                        break;
                    }                
                }    
                if(!empty($attribute)){
                    unset($sort[$attribute]);
                } else {
                    d($list);
                    dd($sort);

                }                
                if(!empty($sort) && is_array($result)){                
                    $list = [];                
                    foreach($result as $key => $subList){
                        foreach($subList as $nr => $node){
                            foreach($sort as $attribute => $record){                            
                                if(property_exists($node, $attribute)){
                                    $list[$key][$node->$attribute][] = $node;
                                } 
                                $sortable_2 = $sort[$attribute];                            
                                break;
                            }
                        }
                    }
                    unset($sort[$attribute]);
                    $result = $list;                
                    if(strtolower($sortable_1) == 'asc'){
                        ksort($result, SORT_NATURAL);
                    } else {
                        krsort($result, SORT_NATURAL);
                    }                
                    foreach($result as $key => $subList){
                        foreach($subList as $attribute => $list){
                            if(strtolower($sortable_2) == 'asc'){
                                sort($list, SORT_NATURAL);
                            } else {
                                rsort($list, SORT_NATURAL);
                            }
                            $result[$key][$attribute] = $list;                         
                        }
                    }
                    $list = [];                
                    foreach($result as $key => $subList){
                        foreach($subList as $attribute => $subSubList){
                            foreach($subSubList as $nr => $node){
                                $list[$node->uuid] = $node;
                            }
                        }
                    }
                }  
            }                   
        }
        return $list;
    }
}
