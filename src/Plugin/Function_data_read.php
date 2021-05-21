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

function function_data_read(Parse $parse, Data $data, $url=''){
    if(File::exist($url)){
        $read = File::read($url);
        $read = Core::object($read);
        $data->data(Core::object_merge($data->data(), $read));
        return $read;
    } else {
        throw new Exception('Error: url:' . $url . ' not found');
    }
    return '';
}
