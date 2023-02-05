<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-18
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */

use R3m\Io\App;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_autoload_prefix_add(Parse $parse, Data $data, $prefix='',$directory='', $extension=''){
    $object = $parse->object();
    $autoload = $object->data(App::AUTOLOAD_R3M);
    ddd($autoload);
    $autoload->addPrefix($prefix, $directory, $extension);
}
