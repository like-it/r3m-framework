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

function validate_is_unique_json(R3m\Io\App $object, $field='', $argument=''){
    if($object->request('has', 'node.' . 'uuid')){
        $original_uuid = $object->request('node.' . 'uuid');
    }
    elseif($object->request('has', 'node_' . 'uuid')) {
        $original_uuid = $object->request('node_' . 'uuid');
    }
    else {
        $original_uuid = $object->request('uuid');
    }
    if($object->request('has', 'node.' . $field)){
        $string = strtolower($object->request('node.' . $field));
    }
    elseif($object->request('has', 'node_' . $field)) {
        $string = $object->request('node_' . $field);
    } else {
        $string = strtolower($object->request($field));
    }
    $url = false;
    $list = false;
    if(property_exists($argument, 'url')){
        $url = $argument->url;
    }
    if(property_exists($argument, 'list')){
        $list = $argument->list;
    }
    $is_unique = true;
    if($url){
        $data = $object->parse_read($url, sha1($url));
        if($data){
            foreach($data->data($list) as $uuid => $record){
                if(
                    !empty($original_uuid) &&
                    $original_uuid == $uuid
                ){
                    continue;
                }
                if(empty($list)){
                    $match = strtolower($data->data($uuid . '.' . $field));
                } else {
                    $match = strtolower($data->data($list . '.' . $uuid . '.' . $field));
                }
                if(empty($match)){
                    continue;
                }
                if($match == $string){
                    $is_unique = false;
                    break;
                }
            }
        }
        return $is_unique;
    }
}
