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
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_textarea(Parse $parse, Data $data, $options=[]){
    $label = '';
    $textarea = '';
    $class = '';
    if(array_key_exists('class', $options)){
        $class=' class="'. $options['class'] . '"';
    }
    if(
        array_key_exists('name', $options) &&
        array_key_exists('label', $options)
    ) {
        $label = '<label for="' . $options['name'] . '"'. $class . '>' . $options['label'] . '</label><br>';
    }
    $rows = '';
    if(array_key_exists('rows', $options)){
        $rows = ' rows="' . $options['rows']. '"';
    }
    $cols = '';
    if(array_key_exists('cols', $options)){
        $cols = ' cols="' . $options['cols']. '"';
    }
    if(
        array_key_exists('name', $options) &&
        array_key_exists('value', $options)
    ){
        if(is_array($options['value'])){
            $textarea = '<textarea id="' . $options['name'] . '"'. $class . $rows . $cols .   ' name="' . $options['name'] . '">' . implode(",\n", $options['value']) . '</textarea>';
        } elseif(is_string($options['value'])) {
            $textarea = '<textarea id="' . $options['name'] . '"'. $class . $rows . $cols . ' name="' . $options['name'] . '">' . $options['value'] . '</textarea>';
        }
    }
    elseif(array_key_exists('name', $options)) {
        $textarea = '<textarea id="' . $options['name'] . '"'. $class . $rows . $cols . ' name="' . $options['name'] .'"></textarea>';
    }
    return $label . $textarea;
}
