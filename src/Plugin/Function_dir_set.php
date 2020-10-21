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

function function_dir_set(Parse $parse, Data $data, $directory=''){
    $result = false;
    if(is_dir($directory)){
        $result = chdir($directory);
    }
    return $result;
}
