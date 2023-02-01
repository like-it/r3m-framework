<?php
/**
 * @author          Remco van der Velde
 * @since           2022-03-17
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_image_extensions(Parse $parse, Data $data){
    $object = $parse->object();
    $contentType = $object->config('contentType');
    $list = [];
    if(
        is_array($contentType) ||
        is_object($contentType)
    ){
        foreach($contentType as $key => $value){
            if(stristr($value, 'image/') !== false){
               $list[] = $key;
            }
        }
    }
    return $list;
}
