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

function function_require(Parse $parse, Data $data, $url=''){
    $read = '';
    if(\R3m\Io\Module\File::exist($url)){
        $read = \R3m\Io\Module\File::read($url);
    } else {
        throw new Exception('Require: file not found: ' . $url);
    }
    return $parse->compile($read, [], $data);
}
