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

    public static function list($list): Sort
    {
        return new Sort($list);
    }

    public function with($sort=[], $options=[]){
        if(array_key_exists('key', $options)){
            $key = $options['key'];
        } else {
            $key = false;
        }
        if(array_key_exists('key_reset', $options)){
            $key_reset = $options['key_reset'];
        } else {
            $key_reset = false;
        }
        if(array_key_exists('flags', $options)){
            $flags = $options['flags'];
        } else {
            $flags = SORT_NATURAL;
        }
        $list = $this->data();
        if(
            is_array($list) || 
            is_object($list)
        ){
            $result = [];  
            $no_attribute = [];
            $count = count($sort);
            if($count == 1){
                if(
                    is_object($list) &&
                    Core::object_is_empty($list)){
                    return [];
                }
                $attribute = false;
                $sortable_1 = 'ASC';
                foreach($list as $uuid => $node){
                    foreach($sort as $attribute => $record){
                        $value = $this->data($uuid . '.' . $attribute);
                        if(is_scalar($value)) {
                            if(is_array($node)){
                                $result[$value][] = $node;
                            }
                            elseif(is_object($node)){

                                $result[$value][] = $node;
                            }
                        }
                        else if (is_array($value)){
                            $attr = '';
                            foreach($value as $node_attribute){
                                if(is_scalar($node_attribute)){
                                    $attr .= '.' . $node_attribute;
                                }
                            }
                            $attr = substr($attr, 1);
                            $result[$attr][] = $node;
                        } else {
                            $result[''][] = $node;
                        }
                        $sortable_1 = $record;
                        break;
                    }
                }
                unset($sort[$attribute]);                
                if(strtolower($sortable_1) == 'asc'){
                    if($attribute === 'uuid'){
                        usort($result, array($this,"uuid_compare"));
                        ddd('uuid sort');
                    } else {
                        ksort($result, $flags);
                    }

                } else {
                    if($attribute === 'uuid'){
                        ddd('reverseuuid sort');
                    } else {
                        krsort($result, $flags);
                    }
                }
                $list = [];                
                foreach($result as $attribute => $subList){
                    foreach($subList as $nr => $record){
                        if(is_array($record)){
                            if(array_key_exists('uuid', $record)){
                                $list[$record['uuid']] = $record;
                            } else {
                                while(true){
                                    $uuid = Core::uuid();
                                    if(!array_key_exists($uuid, $list)){
                                        $record['uuid'] = $uuid;
                                        break;
                                    }
                                }
                                $list[$uuid] = $record;
                            }
                        } else {
                            if(property_exists($record, 'uuid')){
                                $list[$record->uuid] = $record;
                            } else {
                                while(true){
                                    $uuid = Core::uuid();
                                    if(
                                        !array_key_exists($uuid, $list) &&
                                        is_object($record)
                                    ){
                                        $record->uuid = $uuid;
                                        break;
                                    }
                                }
                                $list[$uuid] = $record;
                            }
                        }
                    }
                }                                
            }
            elseif($count == 2){
                if(
                    is_object($list) &&
                    Core::object_is_empty($list)){
                    return [];
                }
                $attribute = false;
                $sortable_1 = 'ASC';
                $sortable_2 = 'ASC';
                foreach($list as $uuid => $node){
                    foreach($sort as $attribute => $record){
                        $value = $this->data($uuid . '.' . $attribute);
                        if(is_scalar($value)){
                            if(is_array($node)){
                                $result[$value][] = $node;
                            }
                            elseif(is_object($node)){
                                $result[$value][] = $node;
                            }
                        }
                        else if (is_array($value)){
                            $attr = '';
                            foreach($value as $node_attribute){
                                if(is_scalar($node_attribute)){
                                    $attr .= '.' . $node_attribute;
                                }
                            }
                            $attr = substr($attr, 1);
                            $result[$attr][] = $node;
                        } else {
                            $result[''][] = $node;
                        }

                        $sortable_1 = $record;
                        break;
                    }
                }
                unset($sort[$attribute]);
                $data = new Data($result);
                $result = [];
                if(!empty($sort)){
                    foreach($data->data() as $result_key => $list){
                        foreach($list as $list_key => $node) {
                            foreach ($sort as $attribute => $record) {
                                $value = $data->data($result_key . '.' . $attribute);
                                if(is_scalar($value)){
                                    if (is_array($node)) {
                                        $result[$result_key][$value][] = $node;
                                    } elseif (is_object($node)) {
                                        $result[$result_key][$value][] = $node;
                                    }
                                }
                                else if (is_array($value)){
                                    $attr = '';
                                    foreach($value as $node_attribute){
                                        if(is_scalar($node_attribute)){
                                            $attr .= '.' . $node_attribute;
                                        }
                                    }
                                    $attr = substr($attr, 1);
                                    $result[$attr][] = $node;
                                } else {
                                    $result[$result_key][''][] = $node;
                                }
                                $sortable_2 = $record;
                                break;
                            }
                        }
                    }
                    unset($sort[$attribute]);
                    if(strtolower($sortable_1) == 'asc'){
                        ksort($result, $flags);
                    } else {
                        krsort($result, $flags);
                    }                
                    foreach($result as $key => $list){
                        if(strtolower($sortable_2) == 'asc'){
                            ksort($list, $flags);
                        } else {
                            krsort($list, $flags);
                        }
                        $result[$key] = $list;                                                
                    }                                        
                    $list = [];          
                    $has_uuid = false;
                    foreach($result as $result_key => $subList){
                        foreach($subList as $attribute => $subSubList){
                            foreach($subSubList as $nr => $node){
                                if(is_array($node)){
                                    if(array_key_exists('uuid', $node)){
                                        $has_uuid = true;
                                        $list[$node['uuid']] = $node;
                                    } else {
                                        while(true){
                                            $uuid = Core::uuid();
                                            if(!array_key_exists($uuid, $list)){
                                                $node['uuid'] = $uuid;
                                                break;
                                            }
                                        }
                                        $list[$uuid] = $node;
                                    }
                                } else {
                                    if(property_exists($node, 'uuid')){
                                        $has_uuid = true;
                                        $list[$node->uuid] = $node;
                                    } else {
                                        while(true){
                                            $uuid = Core::uuid();
                                            if(
                                                !array_key_exists($uuid, $list) &&
                                                is_object($node)
                                            ){
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
        }
        if($key_reset){
            $result = [];
            foreach($list as $record){
                $result[] = $record;
            }
            return $result;
        }
        return $list;
    }

    public function uuid_compare($a, $b)
    {
        d($a);
        d($b);
        return 1;
    } 
}
