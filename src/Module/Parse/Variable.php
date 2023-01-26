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

use Exception;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;

class Variable {

    /**
     * @throws Exception
     */
    public static function count_assign($build, Data $storage, $token=[], $is_result=false): string
    {
        $count = array_shift($token);
        $variable = array_shift($token);
        switch($count['type']){
            case Token::TYPE_IS_MINUS_MINUS :
                $assign = '$this->storage()->set(\'';
                $assign .= $variable['variable']['attribute'] . '\', ';
                $assign .= '$this->min_min_assign(' ;
                $assign .= '$this->storage()->data(\'';
                $assign .= $variable['variable']['attribute'] . '\')';
                $assign .= '))';
                return $assign;
            case Token::TYPE_IS_PLUS_PLUS :
                $assign = '$this->storage()->set(\'';
                $assign .= $variable['variable']['attribute'] . '\', ';
                $assign .= '$this->plus_plus_assign(' ;
                $assign .= '$this->storage()->data(\'';
                $assign .= $variable['variable']['attribute'] . '\')';
                $assign .= '))';
                return $assign;
            default:
                throw new Exception('unknown counter in assign (' . $count['type'] . ')');
        }        
    }

    /**
     * @throws Exception
     */
    private static function getArrayAttribute($variable=[], $build, Data $storage){
        $execute = [];
        if(array_key_exists('array', $variable['variable'])){
            foreach($variable['variable']['array'] as $nr => $record){
                if(array_key_exists('execute', $record)){
                    $execute[] = $record['execute'];
                } else {
                    if(
                        array_key_exists('type', $record) &&
                        $record['type'] === Token::TYPE_VARIABLE &&
                        array_key_exists('variable', $record) &&
                        array_key_exists('attribute', $record['variable'])
                    ){
                        $execute[] = Value::get($build, $storage, $record);
                    }
                }
            }
        }
        $result = '\'' . $variable['variable']['attribute'];
        $quote_add = false;
        foreach($execute as $nr => $record){
            if(substr($record, 0, 1) === '$'){
                if($nr === 0){
                    $result .= $record . ' . ';
                } else {
                    $result .= '.\' . ' . $record . ' . ';
                }
                $quote_add = true;
            } else {
                if($quote_add === true){
                    $result .= '\'.' . $record;
                    $quote_add = false;
                } else {
                    $result .= '.' . $record;
                }
            }
        }
        if(
            !empty($record) &&
            substr($record, 0, 1) === '$'
        ){
            $result = substr($result, 0, -3);
        } else {
            $result .= '\'';
        }
        return $result;
    }

    /**
     * @throws Exception
     */
    public static function assign($build, Data $storage, $token=[], $is_result=false): string
    {
        $variable = array_shift($token);
        d($variable);
        if(!array_key_exists('variable', $variable)){
            return '';
        }        
        $token = Variable::addAssign($token);
        if(
            array_key_exists('is_array', $variable['variable']) &&
            $variable['variable']['is_array'] === true &&
            $variable['variable']['operator'] === '=' &&
            array_key_exists('array', $variable['variable'])
        ){
            $attribute = Variable::getArrayAttribute($variable, $build, $storage);
            d($attribute);
            $assign = '$this->storage()->set(';
            $assign .= $attribute . ', ';
            $value = Variable::getValue($build, $storage, $token, $is_result);
            $assign .= $value . ')';
            d($assign);
            return $assign;
        } else {
            switch($variable['variable']['operator']){
                case '=' :
                    $assign = '$this->storage()->set(\'';
                    $assign .= $variable['variable']['attribute'] . '\', ';
                    $value = Variable::getValue($build, $storage, $token, $is_result);
                    $assign .= $value . ')';
                    return $assign;
                case '+=' :
                    $assign = '$this->storage()->set(\'';
                    $assign .= $variable['variable']['attribute'] . '\', ';
                    $assign .= '$this->assign_plus_equal(' ;
                    $assign .= '$this->storage()->data(\'';
                    $assign .= $variable['variable']['attribute'] . '\'), ';
                    $value = Variable::getValue($build, $storage, $token, $is_result);
                    $assign .= $value . '))';
                    return $assign;
                case '-=' :
                    $assign = '$this->storage()->set(\'';
                    $assign .= $variable['variable']['attribute'] . '\', ';
                    $assign .= '$this->assign_min_equal(' ;
                    $assign .= '$this->storage()->data(\'';
                    $assign .= $variable['variable']['attribute'] . '\'), ';
                    $value = Variable::getValue($build, $storage, $token, $is_result);
                    $assign .= $value . '))';
                    return $assign;
                case '.=' :
                    $assign = '$this->storage()->set(\'';
                    $assign .= $variable['variable']['attribute'] . '\', ';
                    $assign .= '$this->assign_dot_equal(' ;
                    $assign .= '$this->storage()->data(\'';
                    $assign .= $variable['variable']['attribute'] . '\'), ';
                    $value = Variable::getValue($build, $storage, $token, $is_result);
                    $assign .= $value . '))';
                    return $assign;
                case '++' :
                    $assign = '$this->storage()->set(\'';
                    $assign .= $variable['variable']['attribute'] . '\', ';
                    $assign .= '$this->assign_plus_plus(' ;
                    $assign .= '$this->storage()->data(\'';
                    $assign .= $variable['variable']['attribute'] . '\')';
                    $assign .= '))';
                    return $assign;
                case '--' :
                    $assign = '$this->storage()->set(\'';
                    $assign .= $variable['variable']['attribute'] . '\', ';
                    $assign .= '$this->assign_min_min(' ;
                    $assign .= '$this->storage()->data(\'';
                    $assign .= $variable['variable']['attribute'] . '\')';
                    $assign .= '))';
                    return $assign;
                default:
                    throw new Exception('Variable operator not defined');

            }
        }
    }

    private static function addAssign($token=[]): array
    {
        foreach ($token as $nr => $record){
            $record['is_assign'] = true;
            $token[$nr] = $record;
        }
        return $token;
    }

    public static function is_count($build, Data $storage, $token=[]): array
    {
        $count = null;
        foreach($token as $nr => $record){
            if($count === null){
                $count = $record;
            } else {
                if(array_key_exists('variable', $record)){
                   $token[$nr]['variable'] ['is_assign'] = true;
                   unset($token[$nr]['parse']);
                }                
            }
        }
        return $token;
    }

    public static function define($build, Data $storage, $token=[]): string
    {
        $variable = array_shift($token);
        if(!array_key_exists('variable', $variable)){
            return '';
        }
        $define = '$this->storage()->data(\'' . $variable['variable']['attribute'] . '\')';
        $define_modifier = '';
        if(
            array_key_exists('has_modifier', $variable['variable']) &&
            $variable['variable']['has_modifier'] === true
        ){
            foreach($variable['variable']['modifier'] as $nr => $modifier_list){
                foreach($modifier_list as $modifier_nr => $modifier){
                    if(!array_key_exists('php_name', $modifier)){
                        continue;
                    }
                    $define_modifier .= '$this->' . $modifier['php_name'] . '($this->parse(), $this->storage(), ' . $define . ', ';
                    if(!empty($modifier['has_attribute'])){
                        foreach($modifier['attribute'] as $attribute){
                            switch($attribute['type']){
                                case Token::TYPE_METHOD :
                                    ddd($attribute);
                                break;
                                case Token::TYPE_VARIABLE:
                                    $temp = [];
                                    $temp[] = $attribute;
                                    $define_modifier .= Variable::define($build, $storage, $temp) . ', ';
                                break;
                                default :
                                    $define_modifier .= Value::get($build, $storage, $attribute) . ', ';
                            }
                        }
                    }
                    $define_modifier = substr($define_modifier, 0, -2) . ')';
                    $define = $define_modifier;
                    $define_modifier = '';
                }
            }
        }
        return $define;
    }

    /**
     * @throws Exception
     */
    public static function getValue($build, Data $storage, $token=[], $is_result=false): string
    {
        $set_max = 1024;
        $set_counter = 0;
        $operator_max = 1024;
        $operator_counter = 0;
        $set = null;
        while(Set::has($token)){
            $set = Set::get($token);
            while(Operator::has($set)){
                $statement = Operator::get($set);
                $set = Operator::remove($set, $statement);
                $statement = Operator::create($build, $storage, $statement, $depth);
                $key = key($statement);
                $set[$key]['value'] = $statement[$key];
                $set[$key]['type'] = Token::TYPE_CODE;
                $set[$key]['depth'] = $depth;
                unset($set[$key]['execute']);
                unset($set[$key]['is_executed']);
                $token[$key] = $set[$key];
                $operator_counter++;
                if($operator_counter > $operator_max){
                    break;
                }
            }
            $target = Set::target($token);
            $token = Set::pre_remove($token);
            $token = Set::replace($token, $set, $target);
            $token = Set::remove($token);
            $set_counter++;
            if($set_counter > $set_max){
                break;
            }

        }
        $operator = $token;
        while(Operator::has($operator)){            
            $statement = Operator::get($operator);
            if($statement === false){
                $debug = debug_backtrace(true);
                ddd($debug);
                ddd($operator);
            }
            $operator = Operator::remove($operator, $statement);
            $statement = Operator::create($build, $storage, $statement);
            if(empty($statement)){
                throw new Exception('Operator error');
            }
            $key = key($statement);
            $operator[$key]['value'] = $statement[$key];
            $operator[$key]['type'] = Token::TYPE_CODE;
            unset($operator[$key]['execute']);
            unset($operator[$key]['is_executed']);
            unset($operator[$key]['is_operator']);
            $operator_counter++;
            if($operator_counter > $operator_max){
                break;
            }
        }
        $operator_counter = 0;
        $result = '';
        $in_array = false;
        $is_collect = false;
        $type = null;
        $count = 0;
        $selection = [];
        while(count($operator) >= 1){
            $record = array_shift($operator);
            if(is_bool($record) && $record === false){
                if(substr($result, -3) == ' . '){
                    $result = substr($result,0, -3);
                }
                return $result;
            }
            if(
                $is_collect === true &&
                $record['type'] != Token::TYPE_CURLY_CLOSE
            ){
                if($type === null){
                    $type = Build::getType($build->object(), $record);
                }
                $selection[] = $record;
            }
            if($record['type'] == Token::TYPE_CURLY_OPEN){
                $selection = [];
                $is_collect = true;
                continue;
            }
            elseif($record['type'] == Token::TYPE_CURLY_CLOSE){
                $result .= Code::result($build, $storage, $type, $selection);
                $result .= ' . ';
                $is_collect = false;
                $type = null;
                $selection = [];
            }
            elseif($record['type'] == Token::TYPE_BRACKET_SQUARE_OPEN){
                $in_array = true;
                if(substr($result, -3, 3) === ' . '){
                    $result = substr($result, 0, -3);
                }
                $result .= '[';
            }
            elseif($record['type'] == Token::TYPE_BRACKET_SQUARE_CLOSE){
                $in_array = false;
                $result .= ']';
            }
            elseif($is_collect === false){                                
                $record = Method::get($build, $storage, $record);            
                $result .= Value::get($build, $storage, $record);
                if(
                    !in_array(
                        $record['type'],
                        [
                            Token::TYPE_EXCLAMATION,
                            Token::TYPE_CAST
                        ]
                    )
                ){
                    if(
                        $in_array === false &&
                        empty($record['is_foreach'])
                    ){
                        if(
                            in_array(
                                $record['type'],
                                [
                                    Token::TYPE_CODE
                                ]
                            ) &&
                            substr($record['value'], -1, 1) == '!'
                        ){

                        } else {
                            $result .= ' . ';
                        }
                    }
                }
                $operator_counter++;
                if($operator_counter > $operator_max){
                    break;
                }
            }
        }
        if(substr($result, -3) == ' . '){
            $result = substr($result,0, -3);
        }
        return $result;
    }
}