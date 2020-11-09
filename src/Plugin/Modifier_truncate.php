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

function modifier_truncate(Parse $parse, Data $data, $value, $length=80, $replacement='...'){
    $replacement_length = strlen($replacement);
    $length = $length - $replacement_length;
    $value_length = strlen($value);
    if($value_length > $length){
        $value = substr($value, 0, $length) . $replacement;
    }
    return $value;
}
