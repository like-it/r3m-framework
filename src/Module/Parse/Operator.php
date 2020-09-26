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

class Operator {

    public static function has($token=array()){
        foreach($token as $nr => $record){
            if(isset($record['is_operator']) && $record['is_operator'] == true){
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
        return $get;
    }

    private static function get_by_type($token=[], $type=''){
        if(empty($type)){
            throw new Exception('Type cannot be empty');
        }
        $operator = [];
        foreach($token as $nr => $record){
            if($record['type'] == $type){
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
        if($statement === false){
            d($token);
            $debug = debug_backtrace(true);
            dd($debug);
        }
        foreach($statement as $nr => $record){
            if($assign_key === false){
                $assign_key = true;
                continue;
            }
            unset($token[$nr]);
        }
        return $token;
    }

    public static function create(Data $storage, $statement=[]){
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
        $left_value = Value::get($storage, $left);
        $right_value = Value::get($storage, $right);
        switch($operator['value']){
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
        return $result;
    }

}