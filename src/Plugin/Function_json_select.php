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

function function_json_select(Parse $parse, Data $data, $url, $select=null){    
    if(File::exist($url)){
        $read = File::read($url);
        $read = Core::object($read);
        $read = $parse->compile($read, [], $data);
        $json = new Data();
        $json->data($read);
        return $json->data($select);
    }
    return '';
}
