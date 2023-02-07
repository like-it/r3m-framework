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

    /**
     * @throws Exception
     */
    private static function date($record=[]){
        if(array_key_exists('value', $record)){
            if(is_string($record['value'])){
                $record_date = strtotime($record['value']);
            }
            elseif(is_int($record['value'])){
                $record_date = $record['value'];
            } else {
                throw new Exception('Not a date.');
            }
            return $record_date;
        }
        throw new Exception('Date: no value.');
    }

    /**
     * @throws Exception
     */
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
            foreach($list as $uuid => $node){
                foreach($where as $attribute => $record){
                    d($record);
                    d($attribute);
                    if(
                        is_array($record) &&
                        array_key_exists('exist', $record)
                    ){
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
                    if(
                        is_array($record) &&
                        array_key_exists('exists', $record)
                    ){
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
                        is_array($record) &&
                        array_key_exists('operator', $record) && 
                        array_key_exists('value', $record)                     
                    ){
                        $skip = false;
                        switch($record['operator']){
                            case '===' :
                            case 'strictly-exact' :
                                if(
                                    property_exists($node, $attribute) &&
                                    $node->$attribute === $record['value']
                                ){
                                    $skip = true;
                                }
                            break;
                            case '!==' :
                            case 'not-strictly-exact' :
                                if(
                                    property_exists($node, $attribute) &&
                                    $node->$attribute !== $record['value']
                                ){
                                    $skip = true;
                                }
                            break;
                            case '==' :
                            case 'exact' :
                                if(
                                    property_exists($node, $attribute) && 
                                    $node->$attribute == $record['value']
                                ){
                                    $skip = true;
                                }
                            break;
                            case '!=' :
                            case 'not-exact' :
                                if(
                                    property_exists($node, $attribute) && 
                                    $node->$attribute != $record['value']
                                ){
                                    $skip = true;
                                }                                
                            break;
                            case '>' :
                            case 'gt' :
                                if(
                                    property_exists($node, $attribute) && 
                                    $node->$attribute > $record['value']
                                ){
                                    $skip = true;
                                }                                
                            break;
                            case '>=' :
                            case 'gte' :
                                if(
                                    property_exists($node, $attribute) && 
                                    $node->$attribute >= $record['value']
                                ){
                                    $skip = true;
                                }                                
                            break;
                            case '<' :
                            case 'lt' :
                                if(
                                    property_exists($node, $attribute) && 
                                    $node->$attribute < $record['value']
                                ){
                                    $skip = true;
                                }                                
                            break;
                            case '<=' :
                            case 'lte' :
                                if(
                                    property_exists($node, $attribute) && 
                                    $node->$attribute <= $record['value']
                                ){
                                    $skip = true;
                                }                                
                            break;
                            case '> <' :
                            case 'between' :
                                if(
                                    property_exists($node, $attribute)
                                ){
                                    $explode = explode('..', $record['value'], 2);
                                    if(array_key_exists(1, $explode)){
                                        if(is_numeric($explode[0])){
                                            $explode[0] += 0;
                                        }
                                        if(is_numeric($explode[1])){
                                            $explode[1] += 0;
                                        }
                                        if(
                                            $node->$attribute > $explode[0] &&
                                            $node->$attribute < $explode[1]
                                        ){
                                            $skip = true;
                                        }
                                    } else {
                                        throw new Exception('Value is range: ?..?');
                                    }
                                }
                            break;
                            case '>=<' :
                            case 'between-equals' :
                                if(
                                    property_exists($node, $attribute)
                                ){
                                    $explode = explode('..', $record['value'], 2);
                                    if(array_key_exists(1, $explode)){
                                        if(is_numeric($explode[0])){
                                            $explode[0] += 0;
                                        }
                                        if(is_numeric($explode[1])){
                                            $explode[1] += 0;
                                        }
                                        if(
                                            $node->$attribute >= $explode[0] &&
                                            $node->$attribute <= $explode[1]
                                        ){
                                            $skip = true;
                                        }
                                    } else {
                                        throw new Exception('Value is range: ?..?');
                                    }
                                }
                            break;
                            case 'before' :
                                if(property_exists($node, $attribute)){
                                    if(is_string($node->$attribute)){
                                        $node_date = strtotime($node->$attribute);
                                        $record_date = Filter::date($record);
                                    }
                                    elseif(is_int($node->$attribute)){
                                        $node_date = $node->$attribute;
                                        $record_date = Filter::date($record);
                                    } else {
                                        throw new Exception('Cannot calculate: before');
                                    }
                                    if(
                                        $node_date <=
                                        $record_date
                                    ){
                                        $skip = true;
                                    }
                                }
                            break;
                            case 'after' :
                                if(property_exists($node, $attribute)){
                                    if(is_string($node->$attribute)){
                                        $node_date = strtotime($node->$attribute);
                                        $record_date = Filter::date($record);
                                    }
                                    elseif(is_int($node->$attribute)){
                                        $node_date = $node->$attribute;
                                        $record_date = Filter::date($record);
                                    } else {
                                        throw new Exception('Cannot calculate: before');
                                    }
                                    if(
                                        $node_date >=
                                        $record_date
                                    ){
                                        $skip = true;
                                    }
                                }
                            break;
                            case 'strictly-before' :
                                if(property_exists($node, $attribute)){
                                    if(is_string($node->$attribute)){
                                        $node_date = strtotime($node->$attribute);
                                        $record_date = Filter::date($record);
                                    }
                                    elseif(is_int($node->$attribute)){
                                        $node_date = $node->$attribute;
                                        $record_date = Filter::date($record);
                                    } else {
                                        throw new Exception('Cannot calculate: before');
                                    }
                                    if(
                                        $node_date <
                                        $record_date
                                    ){
                                        $skip = true;
                                    }
                                }
                            break;
                            case 'strictly-after' :
                                if(property_exists($node, $attribute)){
                                    if(is_string($node->$attribute)){
                                        $node_date = strtotime($node->$attribute);
                                        $record_date = Filter::date($record);

                                    }
                                    elseif(is_int($node->$attribute)){
                                        $node_date = $node->$attribute;
                                        $record_date = Filter::date($record);
                                    } else {
                                        throw new Exception('Cannot calculate: before');
                                    }
                                    if(
                                        $node_date >
                                        $record_date
                                    ){
                                        $skip = true;
                                    }
                                }
                            break;
                            case 'partial' :
                                if(
                                    property_exists($node, $attribute) &&
                                    is_string($node->$attribute) &&
                                    is_string($record['value'])
                                ){
                                    if(stristr($node->$attribute, $record['value']) !== false) {
                                        $skip = true;
                                    }
                                }
                            break;
                            case 'not-partial' :
                                if(
                                    property_exists($node, $attribute) &&
                                    is_string($node->$attribute) &&
                                    is_string($record['value'])
                                ){
                                    if(stristr($node->$attribute, $record['value']) === false) {
                                        $skip = true;
                                    }
                                }
                            break;
                            case 'start' :
                                if(
                                    property_exists($node, $attribute) &&
                                    is_string($node->$attribute) &&
                                    is_string($record['value'])
                                ){
                                    if(
                                        stristr(
                                            substr(
                                                $node->$attribute,
                                                0,
                                                strlen($record['value'])
                                            ),
                                            $record['value']
                                        ) !== false
                                    ) {
                                        $skip = true;
                                    }
                                }
                            break;
                            case 'not-start' :
                                if(
                                    property_exists($node, $attribute) &&
                                    is_string($node->$attribute) &&
                                    is_string($record['value'])
                                ){
                                    if(
                                        stristr(
                                            substr(
                                                $node->$attribute,
                                                0,
                                                strlen($record['value'])
                                            ),
                                            $record['value']
                                        ) === false
                                    ) {
                                        $skip = true;
                                    }
                                }
                            break;
                            case 'end' :
                                if(
                                    property_exists($node, $attribute) &&
                                    is_string($node->$attribute) &&
                                    is_string($record['value'])
                                ){
                                    $length = strlen($record['value']);
                                    $start = strlen($node->$attribute) - $length;
                                    if(
                                        stristr(
                                            substr(
                                                $node->$attribute,
                                                $start,
                                                $length
                                            ),
                                            $record['value']
                                        ) !== false
                                    ) {
                                        $skip = true;
                                    }
                                }
                            break;
                            case 'not-end' :
                                if(
                                    property_exists($node, $attribute) &&
                                    is_string($node->$attribute) &&
                                    is_string($record['value'])
                                ){
                                    $length = strlen($record['value']);
                                    $start = strlen($node->$attribute) - $length;
                                    if(
                                        stristr(
                                            substr(
                                                $node->$attribute,
                                                $start,
                                                $length
                                            ),
                                            $record['value']
                                        ) === false
                                    ) {
                                        $skip = true;
                                    }
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
                    } elseif(is_array($record)) {
                        foreach($record as $key => $value){
                            $where = [];
                            $where[$attribute] = $value;
                            $list = Filter::list($list)->where($where);
                        }
                    } else {
                        ddd($record);
                    }
                }
            }
        }
        return $list;
    }
}
