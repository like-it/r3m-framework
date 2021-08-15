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

class Sort extends Data{

    public static function list($list){
        $sort = new Sort($list);
        return $sort;
    }

    public function with($sort=[]){
        $list = $this->data();
        if(
            is_array($list) || 
            is_object($list)
        ){
            $result = [];  
            $no_attribute = [];
            $count = count($sort);
            if($count == 1){
                foreach($list as $uuid => $node){
                    foreach($sort as $attribute => $record){                    
                        if(property_exists($node, $attribute)){
                            $result[$node->$attribute][] = $node;
                        } else {
                            $result[''][] = $node;                            
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
                        if(property_exists($record, 'uuid')){
                            $list[$record->uuid] = $record;
                        } else {
                            while(true){
                                $uuid = Core::uuid();
                                if(!array_key_exists($uuid, $list)){
                                    $record->uuid = $uuid;
                                    break;  
                                }
                            }
                            $list[$uuid] = $record;
                        }                        
                    }
                }                                
            }
            elseif($count == 2){
                if(Core::object_is_empty($list)){
                    return $list;
                }
                foreach($list as $uuid => $node){
                    foreach($sort as $attribute => $record){                    
                        if(property_exists($node, $attribute)){
                            $result[$node->$attribute][] = $node;
                        } else {
                            $result[''][] = $node;                            
                        }
                        $sortable_1 = $sort[$attribute];                    
                        break;
                    }                
                }                    
                unset($sort[$attribute]);                                
                if(!empty($sort) && is_array($result)){                
                    $list = [];                
                    foreach($result as $key => $subList){
                        foreach($subList as $nr => $node){
                            foreach($sort as $attribute => $record){                            
                                if(property_exists($node, $attribute)){
                                    $list[$key][$node->$attribute][] = $node;
                                } else {
                                    $list[$key][''][] = $node;                                    
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
                    foreach($result as $key => $list){
                        if(strtolower($sortable_2) == 'asc'){
                            ksort($list, SORT_NATURAL);                            
                        } else {
                            krsort($list, SORT_NATURAL);
                        }
                        $result[$key] = $list;                                                
                    }                                        
                    $list = [];          
                    $has_uuid = false;      
                    foreach($result as $key => $subList){
                        foreach($subList as $attribute => $subSubList){
                            foreach($subSubList as $nr => $node){
                                if(property_exists($node, 'uuid')){
                                    $has_uuid = true;
                                    $list[$node->uuid] = $node;
                                } else {
                                    while(true){
                                        $uuid = Core::uuid();
                                        if(!array_key_exists($uuid, $list)){
                                            $node->uuid = $uuid;
                                            break;
                                        }
                                    }
                                    $list[$uuid] = $node;
                                }
                                
                            }
                        }
                    }                                      
                }  
            }                   
        }
        return $list;
    }
}
