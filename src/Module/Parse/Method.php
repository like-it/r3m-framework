<?php
/**
 * @author          Remco van der Velde
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module\Parse;

use stdClass;
use Exception;
use R3m\Io\App;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;
use R3m\Io\Module\Parse;

class Method {
    const WHERE_BEFORE = 'before';
    const WHERE_AFTER = 'after';

    public static function get(Build $build, Data $storage, $record=[], $is_debug=false){        
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
            $multi_line = Build::getPluginMultiline($build->object());                     
            if(
                in_array(
                    $record['method']['name'],
                    $multi_line                    
                )
            ){                
                $list = [];                
                foreach($record['method']['attribute'] as $nr => $token){
                    if(!array_key_exists($nr, $list)){
                        $list[$nr] = '';
                    }
                    foreach($token as $token_key => $token_value){
                        if(array_key_exists('parse', $token_value)){
                            $list[$nr] .= $token_value['parse'];
                        } else {
                            $list[$nr] .= $token_value['value'];
                        }                        
                    }
                    $list[$nr] = str_replace(
                        [
                            '{{',
                            '}}',
                            '\\\'',
                            '\'',
                        ],
                        [
                            '{',
                            '}',
                            '\\\\\'',
                            '\\\''
                        ],
                        $list[$nr]
                    );
                }
                foreach($list as $nr => $value){
                    if(substr($value, 0, 2) == '\\\'' && substr($value, -2, 2) == '\\\''){
                        $value = substr($value, 2, -2);
                    }
                    if(is_string($value)){
                        $value = '$this->parse()->compile(\'' . $value .'\', [], $this->storage())';
                    }
                    $attribute .= $value . ', ';
                }                
            } else {                
                foreach($record['method']['attribute'] as $nr => $token){                                        
                    $token = $build->require('function', $token);
                    $value = Variable::getValue($build, $storage, $token);
                    $attribute .= $value . ', ';
                }
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
                    $assign_before .= Variable::Assign($build, $storage, $selection) . ', ';
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
                    $assign_after .= Variable::Assign($build, $storage, $selection) . ', ';
                }
                $attribute =
                    substr($assign_before, 0, -2) .
                    ';' .
                    substr($attribute, 0, -2) .
                    ';' .
                    substr($assign_after, 0, -2);
                $assign = '';
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

    public static function get_trait(Build $build, Data $storage, $record=[], $is_debug=false){
        if($record['type'] != Token::TYPE_METHOD){
            return $record;
        }
        $attribute = [];
        if(
            !array_key_exists('attribute', $record['method'])
        ){
            $record['method']['attribute'] = [];
        }
        if(array_key_exists('attribute', $record['method'])){
            $multi_line = Build::getPluginMultiline($build->object());
            if(
                in_array(
                    $record['method']['name'],
                    $multi_line
                )
            ){
                $list = [];
                foreach($record['method']['attribute'] as $nr => $token){
                    if(!array_key_exists($nr, $list)){
                        $list[$nr] = '';
                    }
                    foreach($token as $token_key => $token_value){
                        if(array_key_exists('parse', $token_value)){
                            $list[$nr] .= $token_value['parse'];
                        } else {
                            $list[$nr] .= $token_value['value'];
                        }
                    }
                    $list[$nr] = str_replace(
                        [
                            '{{',
                            '}}',
                            '\\\'',
                            '\'',
                        ],
                        [
                            '{',
                            '}',
                            '\\\\\'',
                            '\\\''
                        ],
                        $list[$nr]
                    );
                }
                foreach($list as $nr => $value){
                    if(substr($value, 0, 2) == '\\\'' && substr($value, -2, 2) == '\\\''){
                        $value = substr($value, 2, -2);
                    }
                    /*
                    if(is_string($value)){
                        $value = '$this->parse()->compile(\'' . $value .'\', [], $this->storage())';
                    }
                    */
                    $attribute[]  = $value;
                }
            } else {
                foreach($record['method']['attribute'] as $nr => $token){
                    $token = $build->require('function', $token);
                    $value = Variable::getValue($build, $storage, $token);
                    $attribute[] = $value;
                }
            }
        }
        $result = end($attribute); //'$this->' . $record['method']['php_name'] . '($this->parse(), $this->storage(), ' . $attribute . ')';
        $record['value'] = $result;
        $record['type'] = Token::TYPE_CODE;
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
            case Method::WHERE_AFTER :
                $data = [];
                if(isset($token[2])){
                    $data[0] = $token[2];
                } else {
                    $data[0] = [];
                }
                return $data;
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

    /**
     * @throws Exception
     */
    public static function create_control(Build $build, Data $storage, $token=[]){
        $method = array_shift($token);
        $record = Method::get($build, $storage, $method);
        if($record['type'] === Token::TYPE_CODE){
            return $record['value'];
        }
        throw new Exception('Method type (' . $record['type'] . ') undefined');
    }

    /**
     * @throws Exception
     */
    public static function create(Build $build, Data $storage, $token=[]){
        $method = array_shift($token);
        $record = Method::get($build, $storage, $method);
        if($record['type'] === Token::TYPE_CODE){
            return $record['value'];
        }
        throw new Exception('Method type (' . $record['type'] . ') undefined');
    }

    /**
     * @throws Exception
     */
    public static function create_trait(Build $build, Data $storage, $token=[], $is_debug=false){
        $method = array_shift($token);
        $method['method']['attribute'][] = $token;
        $record = Method::get_trait($build, $storage, $method, $is_debug);
        if($record['type'] === Token::TYPE_CODE){
            if(
                in_array(
                    $record['method']['name'],
                    [
                        'trait'
                    ]
                )
            ){
                $attribute = current($record['method']['attribute'][0]);
                if(
                    array_key_exists('value', $attribute) &&
                    array_key_exists('type', $attribute) &&
                    $attribute['type'] === Token::TYPE_QUOTE_DOUBLE_STRING
                ){
                    $attribute['execute'] = trim($attribute['value'], '"');
                }
                $explode = explode(':', $attribute['execute']);
                $namespace = false;
                if(array_key_exists(1, $explode)){
                    $namespace = $explode[0];
                    $name = $explode[1];
                } else {
                    $name = $explode[0];
                }
                $trait = [];
                $trait['name'] = $name;
                $trait['namespace'] = $namespace;
                $trait['value'] = $record['value'];
                return $trait;
            }
        }
        throw new Exception('Method type (' . $record['type'] . ') undefined');
    }

    /**
     * @throws Exception
     */
    public static function create_capture(Build $build, Data $storage, $token=[], $is_debug=false){
        $method = array_shift($token);
        $method['method']['attribute'][] = $token;
        $record = Method::get($build, $storage, $method, $is_debug);
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
                        $build->indent() . $record['value'] .
                        ';' . "\n" . $build->indent() . '$this->storage()->data(\'delete\',\'' . $record['method']['name'] . '\')';
                }
            }
            return $record['value'];
        }
        throw new Exception('Method type (' . $record['type'] . ') undefined');
    }

    public static function capture_selection(Build $build, Data $storage, $tree=[], $selection=[]){
        $key = key($selection);
        $is_collect = false;
        $break = '';
        $tag = '';
        $depth = 0;
        foreach($tree as $nr => $record){
            if($nr == $key){
                $is_collect = true;
                $tag = $record['value'];
                $break = '/' . $tag;
                $is_curly_close = false;
                $depth = 1;
            }
            if($is_collect === true){
                if(
                    $record['type'] == Token::TYPE_METHOD &&
                    $record['value'] == $tag &&
                    $nr <> $key
                ){
                    $depth++;
                }
                elseif(
                    $record['type'] == Token::TYPE_CURLY_CLOSE &&
                    $is_curly_close === false
                ){
                    $is_curly_close = true;
                    continue;
                }
                elseif(
                    $record['type'] == Token::TYPE_TAG_CLOSE &&
                    $record['tag']['name'] == $break &&
                    $depth == 1
                ){
                    $is_collect = false;
                    array_pop($selection);
                    break;
                }
                elseif(
                    $record['type'] == Token::TYPE_TAG_CLOSE &&
                    $record['tag']['name'] == $break &&
                    $depth > 1
                ){
                    $depth--;
                }
                $selection[$nr] = $record;
            }
        }
        return $selection;
    }
}