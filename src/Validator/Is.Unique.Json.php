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

use R3m\Io\App;
use R3m\Io\Config;

/**
 * @throws \R3m\Io\Exception\ObjectException
 * @throws Exception
 */
function validate_is_unique_json(App $object, $string='', $field='', $argument=''): bool
{
    if($object->request('has', 'node.' . 'uuid')){
        $original_uuid = $object->request('node.' . 'uuid');
    }
    elseif($object->request('has', 'node_' . 'uuid')) {
        $original_uuid = $object->request('node_' . 'uuid');
    }
    else {
        $original_uuid = $object->request('uuid');
    }
    $url = false;
    $list = null;
    if(property_exists($argument, 'url')){
        $url = $argument->url;
        $parameters =[];
        $parameters[] = $url;
        $parameters = Config::parameters($object, $parameters);
        $url = $parameters[0];
    }
    if(property_exists($argument, 'list')){
        $list = $argument->list;
    }
    $is_unique = true;
    if($url){
        $data = $object->data_read($url, sha1($url));
        if($data){
            $result = $data->data($list);
            if(is_array($result) || is_object($result)){
                foreach($result as $nr => $record){
                    $uuid = false;
                    if(
                        is_array($record) &&
                        array_key_exists('uuid', $record)
                    ){
                        $uuid = $record['uuid'];
                    }
                    elseif(
                        is_object($record) &&
                        property_exists($record, 'uuid')
                    ){
                        $uuid = $record->uuid;
                    }
                    if(
                        !empty($original_uuid) &&
                        $original_uuid === $uuid
                    ){
                        continue;
                    }
                    if(empty($list)){
                        $match = strtolower($data->data($nr . '.' . $field));
                    } else {
                        $match = strtolower($data->data($list . '.' . $nr . '.' . $field));
                    }
                    if(empty($match)){
                        continue;
                    }
                    if($match == strtolower($string)){
                        $is_unique = false;
                        break;
                    }
                }
            }
        }
        return $is_unique;
    }
    return false;
}
