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

function function_dir_read(Parse $parse, Data $data, $url=''){
    if(\R3m\Io\Module\File::exist($url)){
        $dir = new \R3m\Io\Module\Dir();
        $read = $dir->read($url);
        return $read;
    }
    return [];
}
