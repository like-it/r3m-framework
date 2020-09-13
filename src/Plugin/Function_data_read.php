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

function function_data_read(Parse $parse, Data $data, $url=''){
    if(\R3m\Io\Module\File::exist($url)){
        $read = \R3m\Io\Module\File::read($url);
        $read = \R3m\Io\Module\Core::object($read);
        $data->data(\R3m\Io\Module\Core::object_merge($data->data(),$read));
        $read = $parse->compile($data->data(), [], $data, 'css');
        return $read;
    } else {
        throw new Exception('Error: url=' . $url . ' not found');
    }
    return '';
}
