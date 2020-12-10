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

function validate_unique_json(R3m\Io\App $object, $field='', $argument=''){    
    $string = strtolower($object->request('node.' . $field));
    if(property_exists($argument, 'url')){
        $url = $argument->url;
    }
    if(property_exists($argument, 'list')){
        $list = $argument->list;
    }
    $data = $object->data(sha1($url));
    $data = false;
    if(empty($data)){
        $data = $object->parse_read($url, sha1($url));
    }
    $is_unique = true;
    if($data){
        foreach($data->data($list) as $uuid => $record){        
            $match = strtolower($data->data($list . '.' . $uuid . '.' . $field));
            if($match == $string){
                $is_unique = false;
                break;
            }        
        }
    }    
    return $is_unique;    
}
