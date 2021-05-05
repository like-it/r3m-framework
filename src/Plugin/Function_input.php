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

function function_input(Parse $parse, Data $data, $options=[]){
    $class = '';
    if(array_key_exists('class', $options)){
        $class=' class="'. $options['class'] . '"';
    }
    if(array_key_exists('type', $options)){
        switch($options['type']){
            case 'text' :
                $label = '';
                $input = '';
                if(
                    array_key_exists('name', $options) &&
                    array_key_exists('label', $options)
                ){
                    $label = '<label for="' . $options['name'] .'"'. $class . '>' . $options['label'] . '</label>';
                }
                if(array_key_exists('placeholder', $options)){
                    $placeholder = ' placeholder="' . $options['placeholder'] .'"';
                } else {
                    $placeholder = '';
                }
                if(
                    array_key_exists('name', $options) &&
                    array_key_exists('value', $options)
                ){
                    $input = '<input type="text" id="' . $options['name'] .'"' . $class . ' name="' . $options['name'] .'" value="' . $options['value'] . '"' . $placeholder . '/>';
                }
                elseif(array_key_exists('name', $options)) {
                    $input = '<input type="text" id="' . $options['name'] .'"' . $class . ' name="' . $options['name'] .'" value=""'. $placeholder . '/>';
                }
                return $label . $input;
            break;
        }
    }
}
