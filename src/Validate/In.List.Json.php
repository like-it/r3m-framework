<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-18
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

function validate_in_list_json(R3m\Io\App $object, $request=null, $field='', $argument=''){
    if($object->request('has', 'node.' . 'uuid')){
        $original_uuid = $object->request('node.' . 'uuid');
    }
    elseif($object->request('has', 'node_' . 'uuid')){
        $original_uuid = $object->request('node_' . 'uuid');
    }
    else {
        $original_uuid = $object->request('uuid');
    }
    if(is_array($request)){
        $url = false;
        $list = false;
        $attribute = 'name';
        if(property_exists($argument, 'url')){
            $url = $argument->url;
        }
        if(property_exists($argument, 'list')){
            $list = $argument->list;
        }
        if(property_exists($argument, 'attribute')){
            $attribute = $argument->attribute;
        }
        if($url){
            $data = $object->parse_read($url, sha1($url));
            if($data){
                $result = [];
                foreach($data->data($list) as $nr => $record) {
                    if(is_object($record) && property_exists($record, $attribute)) {
                        $result[] = $record->{$attribute};
                    } else {
                        $result[] = $record;
                    }
                }
                foreach($request as $post){
                    if(!in_array($post, $result, true)) {
                        return false;
                    }
                }
                return true;
            }
            return false;
        }
    } else {
        $string = strtolower($request);
        $url = false;
        $list = false;
        if(property_exists($argument, 'url')){
            $url = $argument->url;
        }
        if(property_exists($argument, 'list')){
            $list = $argument->list;
        }
        if($url){
            $data = $object->parse_read($url, sha1($url));
            if($data){
                foreach($data->data($list) as $uuid => $record){
                    if(
                        !empty($string) &&
                        $string == $uuid
                    ){
                        return true;
                    }
                }
            }
            return false;
        }
    }
}
