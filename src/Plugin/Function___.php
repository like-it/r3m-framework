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

function function___(Parse $parse, Data $data, $attribute=null){
    $object = $parse->object();
    $language = $object->session('language');
    if($language === null){
        $language = $object->session('language', $object->config('framework.default.language'));
    }
    $test = $object->data('translation');
    if(empty($test)){
        return '{import.translation()} missing or corrupted translation file...' . PHP_EOL;
    }
    $translation = $object->data('translation.' . $language);
    if(property_exists($translation, $attribute)){
        return $translation->{$attribute};
    } else {
        $translation = $object->data('translation.' . $object->config('framework.default.language'));
        if(property_exists($translation, $attribute)){
            return $translation->{$attribute};
        } else {
            return $attribute;
        }
    }
}
