<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-13
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Core;
use R3m\Io\Exception\ObjectException;

function function_parse_read(Parse $parse, Data $data, $url=''){
    if(File::exist($url)){
        $object = $parse->object();
        $read = $object->parse_read($url, sha1($url));
        if($read){
            try {
                $data->data(Core::object_merge($data->data(), $read->data()));
            } catch (ObjectException $e) {
            }
            return $read->data();
        }
    } else {
        throw new Exception('Error: url=' . $url . ' not found');
    }
    return '';
}
