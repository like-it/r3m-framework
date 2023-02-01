<?php
/**
 * @author          Remco van der Velde
 * @since           2021-03-31
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_language(Parse $parse, Data $data, $attribute=null){
    $object = $parse->object();
    if($attribute === null){
        $language = $object->session('language');
        if($language === null){
            $language = $object->session('language', $object->config('framework.default.language'));
        }
    } else {
        $language = $object->session('language', $attribute);
    }
    return $language;
}
