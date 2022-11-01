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

function function_file_put(Parse $parse, Data $data, $url='', $content='', $flags=LOCK_EX){
    try {
        if(is_string($flags)){
            $flags = constant($flags);
        }
        return File::put($url, $content, $flags);
    } catch (Exception $e){
        return false;
    }
}
