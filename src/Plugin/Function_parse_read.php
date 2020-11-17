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

function function_parse_read(Parse $parse, Data $data, $url=''){
    if(\R3m\Io\Module\File::exist($url)){
        $read = \R3m\Io\Module\File::read($url);
        $read = \R3m\Io\Module\Core::object($read);        
        $read = $parse->compile($read, [], $data);
        $data->data(\R3m\Io\Module\Core::object_merge($data->data(), $read));
        return $read;
    } else {
        throw new Exception('Error: url=' . $url . ' not found');
    }
    return '';
}
