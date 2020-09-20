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

    public static function get($build, $record=[], Data $storage, $is_debug = false){
        if($record['type'] != Token::TYPE_METHOD){
            return $record;
        }
        $attribute = '';
        /*
        if(
            !array_key_exists('attribute', $record['method']) &&
            in_array(
                $record['method']['php_name'],
                [
                    Token::TYPE_FOR,
                    Token::TYPE_BREAK,
                    Token::TYPE_CONTINUE
                ]
            )
        ){
            $record['method']['attribute'] = [];
        }
        */
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
//                 dd($record['method']['attribute'][0]);
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
//                 $token = Token::attribute($token);
//                 d($token);
//                 $token['test']['method'] = $record['method'];
//                 d($record['method']['attribute']);
                $token = $build->require('function', $token);

                if($is_debug){
//                     d($token);
                }

                if($record['method']['php_name'] == Token::TYPE_FOREACH){
//                     $is_debug = 'foreach';
                }

                $value = Variable::getValue($build, $token, $storage, $is_debug);
//                 d($token);
//                 d($value);
                if(substr($value,0,2) == '\'"'){
                    dd($value);
                }

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
//                 dd($attribute);
                $attribute = substr($attribute, 0, -2);
//                 dd($attribute);
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
//                        dd($assign);
                    }
                }
                $build->indent -= 1;
            } else {
                $attribute = substr($attribute, 0, -2);
            }
        } else {
            dd($record);
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
//                         dd($attribute);
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
        } else {
            d($record);
            $debug = debug_backtrace(true);
            dd($debug);
            $record['method']['php_name'] = 'function_' . str_replace('.', '_', $record['value']);
            $result = $record['method']['php_name'] . '($this->parse(), $this->storage(), ' . $attribute . ')';
            $record['value'] = $result;
            $record['type'] = Token::TYPE_CODE;
            dd($record);
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
        dd($token);
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

    public static function create_control($build, $token=[], Data $storage){
        $method = array_shift($token);
        $record = Method::get($build, $method, $storage);
//         d($record);
        if($record['type'] === Token::TYPE_CODE){
            return $record['value'];
        }

        throw new Exception('Method type (' . $record['type'] . ') undefined');
    }

    public static function create($build, $token=[], Data $storage){
        $method = array_shift($token);
        $record = Method::get($build, $method, $storage);
        if($record['type'] === Token::TYPE_CODE){
            return $record['value'];
        }
        d($record);
        throw new Exception('Method type (' . $record['type'] . ') undefined');
    }

    public static function create_capture($build, $token=[], Data $storage){
        $method = array_shift($token);
        foreach($token as $nr => $item){
            $token[$nr]['value'] = str_replace('\'', '\\\'', $item['value']);
        }
        $method['method']['attribute'][] = $token;
//         d($method['method']['attribute']);
        $record = Method::get($build, $method, $storage, true);
//                 d($record);
//                 $debug = debug_backtrace(true);
//                 d($debug[0]);
        if($record['type'] === Token::TYPE_CODE){
            return $record['value'];
        }

        throw new Exception('Method type (' . $record['type'] . ') undefined');
    }

    public static function capture_selection($build, $tree=[], $selection=[], Data $storage){
        $key = key($selection);
        $is_collect = false;
        foreach($tree as $nr => $record){
            if($nr == $key){
                $is_collect = true;
                d($tree);
                dd($nr);
                //need tag_name
            }
            if($is_collect === true){
                if(
                    $record['type'] == Token::TYPE_TAG_CLOSE &&
                    $record['tag']['name'] == '/capture.append'
                ){
                    $is_collect = false;
                    break;
                }
                elseif(
                    in_array(
                        $record['type'],
                        [
                            Token::TYPE_CURLY_CLOSE,
                            Token::TYPE_CURLY_OPEN
                        ]
                    )
                ){
                    continue;
                }
                $selection[$nr] = $record;
            }
        }
        return $selection;
    }

}