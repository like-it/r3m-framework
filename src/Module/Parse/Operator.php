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

class Operator {

    public static function has($token=array()){
        foreach($token as $nr => $record){
            if(                
                isset($record['is_operator']) && 
                $record['is_operator'] == true
            ){
                return true;
            }
        }
        return false;
    }

    public static function get($token=[]){
        $get = false;
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_IS_MULTIPLY);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_IS_DIVIDE);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_IS_MODULO);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_IS_PLUS);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_IS_MINUS);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_IS_SMALLER);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_IS_SMALLER_EQUAL);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_IS_SMALLER_SMALLER);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_IS_GREATER);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_IS_GREATER_EQUAL);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_IS_GREATER_GREATER);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_IS_EQUAL);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_IS_ARRAY_OPERATOR);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_IS_IDENTICAL);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_IS_NOT_EQUAL);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_IS_NOT_IDENTICAL);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_BOOLEAN_AND);
        }
        if($get === false){
            $get = Operator::get_by_type($token, Token::TYPE_BOOLEAN_OR);
        }
        if($get === false){
            $get = Operator::get_by_type_2($token, Token::TYPE_IS_PLUS_PLUS);            
        }
        if($get === false){
            $get = Operator::get_by_type_2($token, Token::TYPE_IS_MINUS_MINUS);            
        }
        if($get === false){
            $get = Operator::get_by_type_3($token, Token::TYPE_IS_PLUS_PLUS);            
        }
        if($get === false){
            $get = Operator::get_by_type_3($token, Token::TYPE_IS_MINUS_MINUS);                        
        }        
        return $get;
    }

    /**
     * @throws Exception
     */
    private static function get_by_type_3($token=[], $type=''){
        if(empty($type)){
            throw new Exception('Type cannot be empty');
        }
        $operator = [];        
        foreach($token as $nr => $record){
            if(
                $record['type'] == $type                
            ){                
                $operator[$nr] = $record;
            }
            elseif(!empty($operator)){
                $operator[$nr] = $record;
                return $operator;
            }            
        }        
        return false;        
    }

    /**
     * @throws Exception
     */
    private static function get_by_type_2($token=[], $type=''){
        if(empty($type)){
            throw new Exception('Type cannot be empty');
        }
        $operator = [];
        $previous_nr = null;
        foreach($token as $nr => $record){
            if(
                $record['type'] == $type && 
                $previous_nr !== null
            ){
                $operator[$previous_nr] = $previous;
                $operator[$nr] = $record;
            }
            elseif(!empty($operator)){
                $operator[$nr] = $record;
                return $operator;
            }
            $previous_nr = $nr;
            $previous = $record;
        }
        if(!empty($operator)){
            return $operator;
        } else {
            return false;
        }
    }

    /**
     * @throws Exception
     */
    private static function get_by_type($token=[], $type=''){
        if(empty($type)){
            throw new Exception('Type cannot be empty');
        }
        $operator = [];
        $previous_nr = null;
        foreach($token as $nr => $record){
            if(
                $record['type'] == $type &&
                $previous_nr !== null
            ){
                $operator[$previous_nr] = $previous;
                $operator[$nr] = $record;
            }
            elseif(!empty($operator)){
                $operator[$nr] = $record;
                return $operator;
            }
            $previous_nr = $nr;
            $previous = $record;
        }        
        return false;        
    }

    public static function remove($token=[], $statement=[]){
        $assign_key = false;
        if(is_array($statement)){
            foreach($statement as $nr => $record){
                if($assign_key === false){
                    $assign_key = true;
                    continue;
                }
                unset($token[$nr]);
            }
        } else {
            d($token);
            // $debug = debug_backtrace(true);
            // dd($debug);
            dd($statement);
        }
        
        return $token;
    }

    /**
     * @throws Exception
     */
    public static function create($build, Data $storage, $statement=[]){
        $assign_key = null;
        $left = null;
        $operator = null;
        $right = null;
        foreach($statement as $key => $record){
            if($left === null){
                $assign_key = $key;
                $left = $record;
            }
            elseif($operator === null){
                $operator = $record;
            }
            elseif($right === null){
                $right = $record;
            }
        }
        $result = [];        
        if(
            in_array(
                $left['value'],
                [
                    '++',
                    '--'
                ]
            ) && 
            $operator !== null
        ){            
            $right_value = Value::get($build, $storage, $operator);            
            $operator = $left;
            switch($operator['value']){
                case '++' :
                    $result[$assign_key] = '$this->plus_plus_value(' . $right_value . ')';
                break;
                case '--' :
                    $result[$assign_key] = '$this->min_min_value(' . $right_value . ')';                    
                break;
            }            
        } else {
            $left_value = Value::get($build, $storage, $left);
            if(
                in_array(
                    $operator['value'],
                    [
                        '++',
                        '--'
                    ]
                )
            ){
                switch($operator['value']){
                    case '++' :
                        $result[$assign_key] = '$this->value_plus_plus(' . $left_value . ')';
                    break;
                    case '--' :
                        $result[$assign_key] = '$this->value_min_min(' . $left_value . ')';                    
                    break;
                }            
            } else {                
                $right_value = Value::get($build, $storage, $right);                
                switch($operator['value']){
                    case '&&' :
                        $result[$assign_key] = $left_value . ' && ' . $right_value;
                    break;
                    case '||' :
                        $result[$assign_key] = $left_value . ' || ' . $right_value;
                    break;
                    case '*' :
                        $result[$assign_key] = '$this->value_multiply(' . $left_value . ', ' . $right_value . ')';
                    break;
                    case '/' :
                        $result[$assign_key] = '$this->value_divide(' . $left_value . ', ' . $right_value . ')';
                    break;
                    case '%' :
                        $result[$assign_key] = '$this->value_modulo(' . $left_value . ', ' . $right_value . ')';
                    break;
                    case '+' :
                        $result[$assign_key] = '$this->value_plus(' . $left_value . ', ' . $right_value . ')';
                    break;
                    case '-' :
                        $result[$assign_key] = '$this->value_minus(' . $left_value . ', ' . $right_value . ')';
                    break;
                    case '<' :
                        $result[$assign_key] = '$this->value_smaller(' . $left_value . ', ' . $right_value . ')';
                    break;
                    case '<=' :
                        $result[$assign_key] = '$this->value_smaller_equal(' . $left_value . ', ' . $right_value . ')';
                    break;
                    case '<<' :
                        $result[$assign_key] = '$this->value_smaller_smaller(' . $left_value . ', ' . $right_value . ')';
                    break;
                    case '>' :
                        $result[$assign_key] = '$this->value_greater(' . $left_value . ', ' . $right_value . ')';
                    break;
                    case '>=' :
                        $result[$assign_key] = '$this->value_greater_equal(' . $left_value . ', ' . $right_value . ')';
                    break;
                    case '>>' :
                        $result[$assign_key] = '$this->value_greater_greater(' . $left_value . ', ' . $right_value . ')';
                    break;
                    case '!=' :
                        $result[$assign_key] = '$this->value_not_equal(' . $left_value . ', ' . $right_value . ')';
                    break;
                    case '!==' :
                        $result[$assign_key] = '$this->value_not_identical(' . $left_value . ', ' . $right_value . ')';
                    break;
                    case '==' :
                        $result[$assign_key] = '$this->value_equal(' . $left_value . ', ' . $right_value . ')';
                    break;
                    case '===' :
                        $result[$assign_key] = '$this->value_identical(' . $left_value . ', ' . $right_value . ')';
                    break;
                    case '=>' :
                        $result[$assign_key] = $left_value . ' => ' . $right_value;
                        break;
                    default :
                        throw new Exception('Unknown operator (' . $operator['value'] .')');
                }
            }  
        }                          
        return $result;
    }
}