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

function function_html_input(Parse $parse, Data $data, $options=[]){
    $object= $parse->object();
    $class = '';
    if(array_key_exists('class', $options)){
        $class=' class="'. $options['class'] . '"';
    }
    if(array_key_exists('type', $options)){
        switch($options['type']){
            case 'hidden' :
                $input = '';
                if(!array_key_exists('id', $options)){
                    if(array_key_exists('name', $options)){
                        $options['id'] = 'node-' . $options['name'];
                    }
                }
                if(array_key_exists('name', $options)){
                    if(
                        !array_key_exists('value', $options) ||
                        empty($options['value'])
                    ){
                        if(is_string($object->request('node.' . $options['name']))){
                            $input = '<input type="hidden" id="' . $options['id'] .'" name="node.' . $options['name'] .'" value="' . $object->request('node.' . $options['name']).'"/>';
                        }
                    } else {
                        $input = '<input type="hidden" id="' . $options['id'] .'" name="node.' . $options['name'] .'" value="' . $options['value'] . '"/>';
                    }
                }
                return $input;
            case 'text' :
                $label = '';
                $input = '';
                $name = false;
                if(array_key_exists('name', $options)){
                    if(array_key_exists('node', $options)){
                        if(empty($options['node'])){
                            $name = $options['name'];
                        } else {
                            $name = $options['node'] . '.' . $options['name'];
                        }
                    } else {
                        $name = 'node.' . $options['name'];
                    }
                }
                $id = '';
                if(array_key_exists('id', $options)){
                    $id = $options['id'];
                } else {
                    $id = $name;
                }
                if(
                    $id &&
                    array_key_exists('label', $options)
                ){
                    $label = '<label for="' . $id .'"'. $class . '>' . $options['label'] . '</label>';
                }
                if(array_key_exists('placeholder', $options)){
                    $placeholder = ' placeholder="' . $options['placeholder'] .'"';
                } else {
                    $placeholder = '';
                }
                if(array_key_exists('autocorrect', $options)){
                    $autocorrect = ' autocorrect="' . $options['autocorrect'] .'"';
                } else {
                    $autocorrect = '';
                }
                if(array_key_exists('autocapitalize', $options)){
                    $autocapitalize = ' autocapitalize="' . $options['autocapitalize'] .'"';
                } else {
                    $autocapitalize = '';
                }
                if(array_key_exists('spellcheck', $options)){
                    $spellcheck = ' spellcheck="' . $options['spellcheck'] .'"';
                } else {
                    $spellcheck = '';
                }
                if(
                    $id &&
                    $name &&
                    array_key_exists('value', $options)
                ){
                    $input =
                        '<input type="text" id="' .
                        $id .
                        '"' .
                        $class .
                        ' name="' .
                        $name .
                        '" value="' .
                        $options['value'] .
                        '"' .
                        $placeholder .
                        $autocorrect .
                        $autocapitalize .
                        $spellcheck .
                        '/>';
                }
                elseif(
                    $id &&
                    $name
                ) {
                    $input =
                        '<input type="text" id="' .
                        $id .
                        '"' .
                        $class .
                        ' name="' .
                        $name.
                        '" value=""'.
                        $placeholder .
                        $autocorrect .
                        $autocapitalize .
                        $spellcheck .
                        '/>';
                }
                return $label . $input;
        }
    }
}
