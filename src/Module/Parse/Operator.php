<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */

namespace R3m\Io\Module\Parse;

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
//         $get = Operator::get_code($token);
//         d($get);
        $get = false;
        if($get === false){
            $get = Operator::get_multiply($token);
        }
        if($get === false){
            $get = Operator::get_divide($token);
        }
        if($get === false){
            $get = Operator::get_modulo($token);
        }
        if($get === false){
            $get = Operator::get_plus($token);
        }
        if($get === false){
            $get = Operator::get_minus($token);
        }
        return $get;
    }

    private static function get_code($token=[]){
        $operator = [];
        $previous = null;
        $previous_previous = null;
        $previous_nr = null;
        $previous_previous_nr = null;
        foreach($token as $nr => $record){
            if($record['type'] == Token::TYPE_CODE){
                dd($token);
                $operator[$previous_previous_nr] = $previous_previous;
                $operator[$previous_nr] = $previous;
                $operator[$nr] = $record;
            }
            $previous_previous = $previous;
            $previous_previous_nr = $previous_nr;
            $previous_nr = $nr;
            $previous = $record;

        }
        return false;
    }

    private static function get_multiply($token=[]){
        $operator = [];
        foreach($token as $nr => $record){
            if($record['type'] == Token::TYPE_IS_MULTIPLY){
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

    private static function get_divide($token=[]){
        $operator = [];
        foreach($token as $nr => $record){
            if($record['type'] == Token::TYPE_IS_DIVIDE){
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

    private static function get_modulo($token=[]){
        $operator = [];
        foreach($token as $nr => $record){
            if($record['type'] == Token::TYPE_IS_MODULO){
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

    private static function get_plus($token=[]){
        $operator = [];
        foreach($token as $nr => $record){
            if($record['type'] == Token::TYPE_IS_PLUS){
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

    private static function get_minus($token=[]){
        $operator = [];
        foreach($token as $nr => $record){
            if($record['type'] == Token::TYPE_IS_MINUS){
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
        foreach($statement as $nr => $record){
            if($assign_key === false){
                $assign_key = true;
                continue;
            }
            unset($token[$nr]);
        }
        return $token;
    }

    public static function create($statement=[]){
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
        $left_value = Value::get($left);
        $right_value = Value::get($right);
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
        }
        return $result;
    }

}