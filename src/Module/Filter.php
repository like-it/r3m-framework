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

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\ObjectException;
use stdClass;
use Exception;
use R3m\Io\App;
use R3m\Io\Config;

class Filter extends Data{
    const NAME = 'Filter';
    const INPUT = 'input';
    const OUTPUT = 'output';


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
                $data = new Data($node);
                foreach($where as $attribute => $record){
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
                                $value = $data->get($attribute);
                                if(is_scalar($value)){
                                    if($value === $record['value']){
                                        $skip = true;
                                    }
                                }
                                elseif(is_array($value)){
                                    if(in_array($record['value'], $value, true)){
                                        $skip = true;
                                    }
                                }
                            break;
                            case '!==' :
                            case 'not-strictly-exact' :
                                $value = $data->get($attribute);
                                if(is_scalar($value)){
                                    if($value !== $record['value']){
                                        $skip = true;
                                    }
                                }
                                elseif(is_array($value)){
                                    if(!in_array($record['value'], $value, true)){
                                        $skip = true;
                                    }
                                }
                            break;
                            case '==' :
                            case 'exact' :
                                $value = $data->get($attribute);
                                if(is_scalar($value)){
                                    if($value == $record['value']){
                                        $skip = true;
                                    }
                                }
                                elseif(is_array($value)){
                                    if(in_array($record['value'], $value, true)){
                                        $skip = true;
                                    }
                                }
                            break;
                            case '!=' :
                            case 'not-exact' :
                                $value = $data->get($attribute);
                                if(is_scalar($value)){
                                    if($value != $record['value']){
                                        $skip = true;
                                    }
                                }
                                elseif(is_array($value)){
                                    if(!in_array($record['value'], $value, true)){
                                        $skip = true;
                                    }
                                }
                            break;
                            case '>' :
                            case 'gt' :
                                $value = $data->get($attribute);
                                if(is_scalar($value)){
                                    if($value > $record['value']){
                                        $skip = true;
                                    }
                                }
                                elseif(is_array($value)){
                                    $found = false;
                                    foreach($value as $value_key => $value_value){
                                        if($value_value > $record['value']){
                                        } else {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if(!$found){
                                        $skip = true;
                                    }
                                }
                            break;
                            case '>=' :
                            case 'gte' :
                                $value = $data->get($attribute);
                                if(is_scalar($value)){
                                    if($value >= $record['value']){
                                        $skip = true;
                                    }
                                }
                                elseif(is_array($value)){
                                    $found = false;
                                    foreach($value as $value_key => $value_value){
                                        if($value_value >= $record['value']){
                                        } else {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if(!$found){
                                        $skip = true;
                                    }
                                }
                            break;
                            case '<' :
                            case 'lt' :
                                $value = $data->get($attribute);
                                if(is_scalar($value)){
                                    if($value < $record['value']){
                                        $skip = true;
                                    }
                                }
                                elseif(is_array($value)){
                                    $found = false;
                                    foreach($value as $value_key => $value_value){
                                        if($value_value < $record['value']){
                                        } else {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if(!$found){
                                        $skip = true;
                                    }
                                }
                            break;
                            case '<=' :
                            case 'lte' :
                                $value = $data->get($attribute);
                                if(is_scalar($value)){
                                    if($value <= $record['value']){
                                        $skip = true;
                                    }
                                }
                                elseif(is_array($value)){
                                    $found = false;
                                    foreach($value as $value_key => $value_value){
                                        if($value_value <= $record['value']){
                                        } else {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if(!$found){
                                        $skip = true;
                                    }
                                }
                            break;
                            case '> <' :
                            case 'between' :
                                $value = $data->get($attribute);
                                $explode = explode('..', $record['value'], 2);
                                if(array_key_exists(1, $explode)){
                                    if(is_numeric($explode[0])){
                                        $explode[0] += 0;
                                    }
                                    if(is_numeric($explode[1])){
                                        $explode[1] += 0;
                                    }
                                    if(is_array($value)) {
                                        foreach ($value as $value_key => $value_value) {
                                            if (
                                                $value_value > $explode[0] &&
                                                $value_value < $explode[1]
                                            ) {
                                                $skip = true;
                                                break;
                                            }
                                        }
                                    } elseif(
                                        $value > $explode[0] &&
                                        $value < $explode[1]
                                    ){
                                        $skip = true;
                                    }

                                } else {
                                    throw new Exception('Value is range: ?..?');
                                }
                            break;
                            case '>=<' :
                            case 'between-equals' :
                                $value = $data->get($attribute);
                                $explode = explode('..', $record['value'], 2);
                                if(array_key_exists(1, $explode)){
                                    if(is_numeric($explode[0])){
                                        $explode[0] += 0;
                                    }
                                    if(is_numeric($explode[1])){
                                        $explode[1] += 0;
                                    }
                                    if(is_array($value)) {
                                        foreach ($value as $value_key => $value_value) {
                                            if (
                                                $value_value >= $explode[0] &&
                                                $value_value <= $explode[1]
                                            ) {
                                                $skip = true;
                                                break;
                                            }
                                        }
                                    } elseif(
                                        $value >= $explode[0] &&
                                        $value <= $explode[1]
                                    ){
                                        $skip = true;
                                    }

                                } else {
                                    throw new Exception('Value is range: ?..?');
                                }
                            break;
                            case 'before' :
                                $value = $data->get($attribute);
                                if(is_string($value)){
                                    $node_date = strtotime($value);
                                    $record_date = Filter::date($record);
                                }
                                elseif(is_int($value)){
                                    $node_date = $value;
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
                            break;
                            case 'after' :
                                $value = $data->get($attribute);
                                if(is_string($value)){
                                    $node_date = strtotime($value);
                                    $record_date = Filter::date($record);
                                }
                                elseif(is_int($value)){
                                    $node_date = $value;
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
                            break;
                            case 'strictly-before' :
                                $value = $data->get($attribute);
                                if(is_string($value)){
                                    $node_date = strtotime($value);
                                    $record_date = Filter::date($record);
                                }
                                elseif(is_int($value)){
                                    $node_date = $value;
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
                            break;
                            case 'strictly-after' :
                                $value = $data->get($attribute);
                                if(is_string($value)){
                                    $node_date = strtotime($value);
                                    $record_date = Filter::date($record);
                                }
                                elseif(is_int($value)){
                                    $node_date = $value;
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
                            break;
                            case 'partial' :
                                $value = $data->get($attribute);
                                if(
                                    is_string($record['value'])
                                ){
                                    if(is_array($value)){
                                        foreach($value  as $value_key => $value_value){
                                            if(stristr($value_value, $record['value']) !== false) {
                                                $skip = true;
                                                break;
                                            }
                                        }
                                    }
                                    elseif(is_string($value)){
                                        if(stristr($value, $record['value']) !== false) {
                                            $skip = true;
                                        }
                                    }
                                }
                            break;
                            case 'not-partial' :
                                $value = $data->get($attribute);
                                if(
                                    is_string($record['value'])
                                ){
                                    if(is_array($value)){
                                        foreach($value  as $value_key => $value_value){
                                            if(stristr($value_value, $record['value']) === false) {
                                                $skip = true;
                                                break;
                                            }
                                        }
                                    }
                                    elseif(is_string($value)){
                                        if(stristr($value, $record['value']) === false) {
                                            $skip = true;
                                        }
                                    }
                                }
                            break;
                            case 'start' :
                                $value = $data->get($attribute);
                                if(
                                    is_string($record['value'])
                                ){
                                    if(
                                        is_string($value) &&
                                        stristr(
                                            substr(
                                                $value,
                                                0,
                                                strlen($record['value'])
                                            ),
                                            $record['value']
                                        ) !== false
                                    ) {
                                        $skip = true;
                                    }
                                }
                                elseif(is_array($value)){
                                    foreach($value as $value_key => $value_value){
                                        if(
                                            is_string($value_value) &&
                                            stristr(
                                                substr(
                                                    $value_value,
                                                    0,
                                                    strlen($record['value'])
                                                ),
                                                $record['value']
                                            ) !== false
                                        ) {
                                            $skip = true;
                                        }
                                    }
                                }
                            break;
                            case 'not-start' :
                                $value = $data->get($attribute);
                                if(
                                    is_string($record['value'])
                                ){
                                    if(
                                        is_string($value) &&
                                        stristr(
                                            substr(
                                                $value,
                                                0,
                                                strlen($record['value'])
                                            ),
                                            $record['value']
                                        ) === false
                                    ) {
                                        $skip = true;
                                    }
                                    elseif(is_array($value)){
                                        foreach($value as $value_key => $value_value){
                                            if(
                                                is_string($value_value) &&
                                                stristr(
                                                    substr(
                                                        $value_value,
                                                        0,
                                                        strlen($record['value'])
                                                    ),
                                                    $record['value']
                                                ) === false
                                            ) {
                                                $skip = true;
                                            }
                                        }
                                    }
                                }
                            break;
                            case 'end' :
                                $value = $data->get($attribute);
                                if(
                                    is_string($record['value'])
                                ){
                                    $length = strlen($record['value']);
                                    if(is_array($value)){
                                        foreach($value as $value_key => $value_value){
                                            $start = strlen($value_value) - $length;
                                            if(
                                                stristr(
                                                    substr(
                                                        $value_value,
                                                        $start,
                                                        $length
                                                    ),
                                                    $record['value']
                                                ) !== false
                                            ) {
                                                $skip = true;
                                                break;
                                            }
                                        }
                                    }
                                    elseif(is_string($value)){
                                        $start = strlen($value) - $length;
                                        if(
                                            stristr(
                                                substr(
                                                    $value,
                                                    $start,
                                                    $length
                                                ),
                                                $record['value']
                                            ) !== false
                                        ) {
                                            $skip = true;
                                        }
                                    }
                                }
                            break;
                            case 'not-end' :
                                $value = $data->get($attribute);
                                if(
                                    is_string($record['value'])
                                ){
                                    $length = strlen($record['value']);
                                    if(is_array($value)){
                                        foreach($value as $value_key => $value_value){
                                            $start = strlen($value_value) - $length;
                                            if(
                                                stristr(
                                                    substr(
                                                        $value_value,
                                                        $start,
                                                        $length
                                                    ),
                                                    $record['value']
                                                ) === false
                                            ) {
                                                $skip = true;
                                                break;
                                            }
                                        }
                                    }
                                    elseif(is_string($value)){
                                        $start = strlen($value) - $length;
                                        if(
                                            stristr(
                                                substr(
                                                    $value,
                                                    $start,
                                                    $length
                                                ),
                                                $record['value']
                                            ) === false
                                        ) {
                                            $skip = true;
                                        }
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
                        $where = [];
                        $where[$attribute] = [
                            'operator' => 'partial',
                            'value' => $record
                        ];
                        $list = Filter::list($list)->where($where);
                    }
                }
            }
        }
        return $list;
    }

    public static function on(App $object, $filter){
        $action = $filter->get('action');
        $options = $filter->get('options');
        $list = $object->get(App::FILTER)->get(Filter::NAME);
        if(empty($list)){
            $list = [];
        }
        $list[] = $filter->data();
        $object->get(App::FILTER)->set(Filter::NAME, $list);
    }

    public static function off(App $object, $filter){
        $action = $filter->get('action');
        $options = $filter->get('options');
        $list = $object->get(App::FILTER)->get(Filter::NAME);
        if(empty($list)){
            return;
        }
        //remove them on the sorted list backwards so sorted on input order
        krsort($list);
        foreach($list as $key => $node){
            if(empty($options)){
                if($node['action'] === $action){
                    unset($list[$key]);
                    break;
                }
            } else {
                if($node['action'] === $action){
                    foreach($options as $options_key => $value){
                        if(
                            $value === true &&
                            is_array($node['options']) &&
                            array_key_exists($options_key, $node['options'])
                        ){
                            unset($list[$key]);
                            break;
                        }
                        if(
                            $value === true &&
                            is_object($node['options']) &&
                            property_exists($node['options'], $options_key)
                        ){
                            unset($list[$key]);
                            break;
                        }
                        elseif(
                            is_array($node['options']) &&
                            array_key_exists($options_key, $node['options']) &&
                            $node['options'][$options_key] === $value
                        ){
                            unset($list[$key]);
                            break;
                        }
                        elseif(
                            is_object($node['options']) &&
                            property_exists($node['options'], $options_key) &&
                            $node['options']->{$options_key} === $value
                        ){
                            unset($list[$key]);
                            break;
                        }
                    }
                }
            }
        }
        $object->get(App::FILTER)->set(Filter::NAME, $list);
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function trigger(App $object, $action, $options=[]){
        $filters = $object->get(App::FILTER)->select(Filter::NAME, [
            'action' => $action
        ]);
        $response = null;
        if(empty($filters)){
            if(
                array_key_exists('type', $options) &&
                $options['type'] === Filter::OUTPUT &&
                array_key_exists('response', $options)
        ){
                return $options['response'];
            }
            elseif(
                array_key_exists('type', $options) &&
                $options['type'] === Filter::INPUT &&
                array_key_exists('route', $options)
            ){
                return $options['route'];
            }
            return null;
        }
        $filters = Sort::list($filters)->with(['options.priority' => 'DESC']);
        if(is_array($filters) || is_object($filters)){
            foreach($filters as $filter){
                if(is_object($filter)) {
                    if(
                        property_exists($filter, 'options') &&
                        property_exists($filter->options, 'controller') &&
                        is_array($filter->options->controller)
                    ){
                        foreach($filter->options->controller as $controller){
                            $route = new stdClass();
                            $route->controller = $controller;
                            $route = Route::controller($route);
                            if(
                                property_exists($route, 'controller') &&
                                property_exists($route, 'function')
                            ){
                                $filter = new Data($filter);
                                try {
                                    $response = $route->controller::{$route->function}($object, $filter, $options);
                                    if($filter->get('stopPropagation')){
                                        break 2;
                                    }
                                }
                                catch (LocateException $exception){
                                    if($object->config('project.log.error')){
                                        $object->logger($object->config('project.log.error'))->error('LocateException', [ $route, (string) $exception ]);
                                    }
                                    elseif($object->config('project.log.name')){
                                        $object->logger($object->config('project.log.name'))->error('LocateException', [ $route, (string) $exception ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if(array_key_exists('type', $options)){
            switch($options['type']){
                case 'input' :
                    if(array_key_exists('route', $options)){
                        if($response){
                            return $response;
                        }
                        return $options['route'];
                    }
                    break;
                case 'output' :
                    if(array_key_exists('response', $options)){
                        if($response){
                            return $response;
                        }
                        return $options['response'];
                    }
                    break;
            }
        }
    }

    /**
     * @throws ObjectException
     */
    public static function configure(App $object){
        $url = $object->config('project.dir.data') .
            'Node' .
            $object->config('ds') .
            'Filter' .
            $object->config('ds') .
            'Data' .
            $object->config('extension.json')
        ;
        $data = $object->data_read($url);
        if(!$data){
            return;
        }
        if($data->has(Filter::NAME)){
            foreach($data->get(Filter::NAME) as $filter){
                if(
                    property_exists($filter, 'action') &&
                    property_exists($filter, 'options')
                )
                    $filter = new Data($filter);
                    Filter::on($object, $filter);
            }
        }

    }
}
