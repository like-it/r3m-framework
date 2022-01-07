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

function validate_in_list_json(R3m\Io\App $object, $field='', $argument=''){
    $original_uuid = $object->request('node.' . 'uuid');
    if(empty($original_uuid)){
        $original_uuid = $object->request('uuid');
    }
    $string = strtolower($object->request('node.' . $field));
    if(empty($string)){
        $string = strtolower($object->request($field));
    }
    dd($string);
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
