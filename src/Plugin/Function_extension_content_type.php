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

function function_extension_content_type(Parse $parse, Data $data, $extension=''){
    $object = $parse->object();
    return $object->config('contentType.' . $extension);
}
