<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */

namespace R3m\Io\Module\Parse;

use Exception;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;

class Method {
    const WHERE_BEFORE = 'before';
    const WHERE_AFTER = 'after';

    public static function get(Build $build, Data $storage, $record=[]){
        if($record['type'] != Token::TYPE_METHOD){
            return $record;
        }
        if($storage->data('is.debug')){
        }
        $attribute = '';
        if(
            !array_key_exists('attribute', $record['method'])
        ){
            $record['method']['attribute'] = [];
        }
        if(array_key_exists('attribute', $record['method'])){
            if($record['method']['php_name'] == Token::TYPE_FOR){
                $record['method']['assign_before'] = Method::getAssign($record['method']['attribute'], Method::WHERE_BEFORE);
                $record['method']['assign_after'] = Method::getAssign($record['method']['attribute'], Method::WHERE_AFTER);
                $record['method']['attribute'] = Method::getAttribute($record['method']['attribute']);
            }
            elseif($record['method']['php_name'] == Token::TYPE_FOREACH){
                $as_is = false;
                $is_key_value = false;
                $has_key = false;
                foreach($record['method']['attribute'][0] as $nr => $item){
                    if(
                        in_array(
                            $item['value'],
                            [
                                '=>'
                            ]
                        )
                    ){
                        $is_key_value = true;
                    }
                    $record['method']['attribute'][0][$nr]['is_foreach'] = true;
                }
                foreach($record['method']['attribute'][0] as $nr => $item){
                    if(
                        in_array(
                            $item['value'],
                            [
                                'as',
                                '=>'
                            ]
                        )
                    ){
                        $as_is = true;
                    }
                    if($as_is === true){
                        $record['method']['attribute'][0][$nr]['value_old'] = $item['value'];
                        $record['method']['attribute'][0][$nr]['type_old'] = $item['type'];
                        if(
                            $is_key_value === true &&
                            $has_key === false &&
                            $item['type'] == Token::TYPE_VARIABLE
                        ){
                            $record['method']['attribute'][0][$nr]['value'] = ' ' . Core::uuid_variable() . ' ';
                            $has_key = true;
                        }
                        elseif(
                            $is_key_value === false &&
                            $has_key === false &&
                            $item['type'] == Token::TYPE_VARIABLE
                            ){
                                $record['method']['attribute'][0][$nr]['value'] = ' ' . Core::uuid_variable() . ' ';
                                $has_key = true;
                        }
                        elseif(
                            $is_key_value === true &&
                            $has_key === true &&
                            $item['type'] == Token::TYPE_VARIABLE
                        ){
                            $record['method']['attribute'][0][$nr]['value'] = ' ' . Core::uuid_variable() . ' ';
                        } else {
                            $record['method']['attribute'][0][$nr]['value'] = ' ' . $item['value'] . ' ';
                        }
                        $record['method']['attribute'][0][$nr]['type'] = Token::TYPE_CODE;
                        $record['method']['attribute'][0][$nr]['is_operator'] = false;
                        $record['method']['attribute'][0][$nr]['is_key_value'] = $is_key_value;
                    }
                }
            }
            foreach($record['method']['attribute'] as $nr => $token){
                $token = Token::define($token);
                $token = Token::method($token);
                $token = $build->require('function', $token);                
                $value = Variable::getValue($build, $storage, $token);
                $attribute .= $value . ', '; //#
            }            
            if($record['method']['php_name'] == Token::TYPE_FOR){
                $assign = [];
                $assign_nr = 0;
                foreach($record['method']['assign_before'] as $nr => $selection){
                    foreach($selection as $selection_nr => $select){
                        if($select['type'] == Token::TYPE_COMMA){
                            $assign_nr++;
                            continue;
                        }
                        $assign[$assign_nr][$selection_nr] = $select;
                    }
                }
                $assign_before = '';
                foreach($assign as $nr => $selection){
                    $assign_before .= Variable::Assign($build, $selection, $storage) . ', ';
                }
                $assign = [];
                $assign_nr = 0;
                foreach($record['method']['assign_after'] as $nr => $selection){
                    foreach($selection as $selection_nr => $select){
                        if($select['type'] == Token::TYPE_COMMA){
                            $assign_nr++;
                            continue;
                        }
                        $assign[$assign_nr][$selection_nr] = $select;
                    }
                }
                $assign_after = '';
                foreach($assign as $nr => $selection){
                    $assign_after .= Variable::Assign($build, $selection, $storage) . ', ';
                }
                $attribute =
                    substr($assign_before, 0, -2) .
                    ';' .
                    substr($attribute, 0, -2) .
                    ';' .
                    substr($assign_after, 0, -2);
            }
            elseif($record['method']['php_name'] == Token::TYPE_FOREACH){
                $attribute = substr($attribute, 0, -2);
                $assign = '';
                $is_assign = false;
                $token = [];
                $build->indent += 1;
                foreach($record['method']['attribute'][0] as $nr => $item){
                    if(
                        array_key_exists('type_old', $item) &&
                        $item['type_old'] == Token::TYPE_VARIABLE
                    ){
                       $assign .= $build->indent() . '$this->storage()->data(\'' . $item['variable']['attribute'] . '\', ' . $item['value'] . ');' . "\n";
                    }
                }
                $build->indent -= 1;
            } else {
                $attribute = substr($attribute, 0, -2);
            }
        }
        if(array_key_exists('php_name', $record['method'])){
            if(
                in_array(
                    $record['method']['php_name'],
                    [
                        'if',
                        'elseif',
                        'else.if',
                        'for',
                        'foreach',
                        'while',
                        'switch',
                        'break',
                        'continue'
                    ]
                )
            ){
                $name = $record['method']['name'];
                $indent = $build->indent;
                if($name == 'for.each'){
                    $name = 'foreach';
                }
                elseif($name === 'elseif'){
                    $indent -= 1; //$build->indent($build->indent-1);
                    $build->indent($indent);
                    $name = '}' . "\n" . $build->indent() . $name;
                }
                elseif($name === 'else.if'){
                    $indent -= 1; //$build->indent($build->indent-1);
                    $build->indent($indent);
                    $name = '}' . "\n" . $build->indent() . 'elseif';
                }
                if(
                    in_array(
                        $name,
                        [
                            'break',
                            'continue'
                        ]
                    )
                ){
                    if(empty($attribute)){
                        $result = $name;
                    } else {
                        $result = $name . ' ' . $attribute;
                    }
                } else {
                    $result = $name . '(' . $attribute . ')';
                    if(!empty($assign)){
                        $result .= '{' . "\n" . $assign;
                    }
                }
            } else {
                if(empty($attribute)){
                    $result = '$this->' . $record['method']['php_name'] . '($this->parse(), $this->storage())';
                } else {
                    $result = '$this->' . $record['method']['php_name'] . '($this->parse(), $this->storage(), ' . $attribute . ')';
                }
            }
            $record['value'] = $result;
            $record['type'] = Token::TYPE_CODE;
        }
        return $record;
    }

    private static function getAssign($token=[], $where=''){
        if(empty($where)){
            $where = Method::WHERE_BEFORE;
        }
        switch($where){
            case Method::WHERE_BEFORE :
                $data = [];
                if(isset($token[0])){
                    $data[0] = $token[0];
                } else {
                    $data[0] = [];
                }

                return $data;
            break;
            case Method::WHERE_AFTER :
                $data = [];
                if(isset($token[2])){
                    $data[0] = $token[2];
                } else {
                    $data[0] = [];
                }
                return $data;
            break;
        }
    }

    private static function getAttribute($token=[]){
        $data = [];
        if(isset($token[1])){
            $data[0] = $token[1];
        } else {
            $data[0] = [];
        }
        return $data;
    }

    public static function create_control(Build $build, Data $storage, $token=[]){
        $method = array_shift($token);
        $record = Method::get($build, $storage, $method);
        if($record['type'] === Token::TYPE_CODE){
            return $record['value'];
        }

        throw new Exception('Method type (' . $record['type'] . ') undefined');
    }

    public static function create(Build $build, Data $storage, $token=[]){
        $method = array_shift($token);
        $record = Method::get($build, $storage, $method);
        if($record['type'] === Token::TYPE_CODE){
            return $record['value'];
        }
        throw new Exception('Method type (' . $record['type'] . ') undefined');
    }

    public static function create_capture(Build $build, Data $storage, $token=[]){
        $method = array_shift($token);
        $method['method']['attribute'][] = $token;
        $record = Method::get($build, $storage, $method);        
        if($record['type'] === Token::TYPE_CODE){        
            if(
                in_array(
                    $record['method']['name'], 
                    [
                        'capture.append',
                        'capture.prepend'
                    ]
                )
            ){
                $attribute = current($record['method']['attribute'][0]);
                if(array_key_exists('execute', $attribute)){                    
                    $record['value'] = '$this->storage()->data(\''. $record['method']['name'] .'\', \'' . $attribute['execute'] . '\');' . 
                        "\n" . 
                        $record['value'] . 
                        ';' . "\n" . '$this->storage()->data(\'delete\',\'' . $record['method']['name'] . '\')';               }
                
                
            }

            return $record['value'];
        }
        throw new Exception('Method type (' . $record['type'] . ') undefined');
    }

    public static function capture_selection(Build $build, Data $storage, $tree=[], $selection=[]){
        $key = key($selection);
        $is_collect = false;
        foreach($tree as $nr => $record){
            if($nr == $key){
                $is_collect = true;
                $is_curly_close = false;
            }
            if($is_collect === true){
                if(
                    $record['type'] == Token::TYPE_CURLY_CLOSE &&
                    $is_curly_close === false
                ){
                    $is_curly_close = true;
                    continue;
                }
                if(
                    $record['type'] == Token::TYPE_TAG_CLOSE &&
                    $record['tag']['name'] == '/capture.append'
                ){
                    $is_collect = false;
                    array_pop($selection);
                    break;
                }
                $selection[$nr] = $record;
            }
        }
        return $selection;
    }
}