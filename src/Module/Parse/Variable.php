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

class Variable {

    public static function assign($build, Data $storage, $token=[], $is_result=false){
        $variable = array_shift($token);
        if(!array_key_exists('variable', $variable)){
            return '';
        }
        if($storage->data('is.debug') === true){
            dd($token);
        }
        if($storage->data('is.debug')){
        }
        $token = Variable::addAssign($token);
        switch($variable['variable']['operator']){
            case '=' :
                $assign = '$this->storage()->data(\'';
                $assign .= $variable['variable']['attribute'] . '\', ';
                $value = Variable::getValue($build, $storage, $token, $is_result);
                if(stristr($value, '"') && stristr($value, '\'') !== false){
//                     d($value);
                }
//                 d($value);
                if(empty($value)){
                    dd($assign);
                } else {
                    $assign .= $value . ')';
                }

                return $assign;
            break;
            case '+=' :
                // use $this->assign_plus_equal()

                $assign = '$this->storage()->data(\'';
                $assign .= $variable['variable']['attribute'] . '\', ';
                $assign .= '$this->assign_plus_equal(' ;
                $assign .= '$this->storage()->data(\'';
                $assign .= $variable['variable']['attribute'] . '\'), ';
                $value = Variable::getValue($build, $storage, $token, $is_result);
                $assign .= $value . '))';
                return $assign;
            break;
            case '-=' :
                // use $this->assign_plus_equal()

                $assign = '$this->storage()->data(\'';
                $assign .= $variable['variable']['attribute'] . '\', ';
                $assign .= '$this->assign_min_equal(' ;
                $assign .= '$this->storage()->data(\'';
                $assign .= $variable['variable']['attribute'] . '\'), ';
                $value = Variable::getValue($build, $storage, $token, $is_result);
                $assign .= $value . '))';
                return $assign;
            break;
            case '.=' :
                // use $this->assign_plus_equal()

                $assign = '$this->storage()->data(\'';
                $assign .= $variable['variable']['attribute'] . '\', ';
                $assign .= '$this->assign_dot_equal(' ;
                $assign .= '$this->storage()->data(\'';
                $assign .= $variable['variable']['attribute'] . '\'), ';
                $value = Variable::getValue($build, $storage, $token, $is_result);
                $assign .= $value . '))';
                return $assign;
            break;
            case '++' :
                // use $this->assign_plus_equal()

                $assign = '$this->storage()->data(\'';
                $assign .= $variable['variable']['attribute'] . '\', ';
                $assign .= '$this->assign_plus_plus(' ;
                $assign .= '$this->storage()->data(\'';
                $assign .= $variable['variable']['attribute'] . '\')';
                $assign .= '))';
                return $assign;
            break;
            case '--' :
                // use $this->assign_plus_equal()

                $assign = '$this->storage()->data(\'';
                $assign .= $variable['variable']['attribute'] . '\', ';
                $assign .= '$this->assign_min_min(' ;
                $assign .= '$this->storage()->data(\'';
                $assign .= $variable['variable']['attribute'] . '\')';
                $assign .= '))';
                return $assign;
            break;
            default: throw new Exception('Variable operator not defined');

        }
    }

    private static function addAssign($token=[]){
        foreach ($token as $nr => $record){
            $record['is_assign'] = true;
            $token[$nr] = $record;
        }
        return $token;
    }

    public static function define($build, Data $storage, $token=[]){
        $variable = array_shift($token);
        if(!array_key_exists('variable', $variable)){
            return '';
        }

        if($storage->data('is.debug') === true){
            d($storage->data('is.debug'));
            dd($token);
        }

        $define = '$this->storage()->data(\'' . $variable['variable']['attribute'] . '\')';
        $define_modifier = '';

//         $define_placeholder = 'R3M-IO-' . Core::uuid();

        if(
            array_key_exists('has_modifier', $variable['variable']) &&
            $variable['variable']['has_modifier'] === true
        ){
            foreach($variable['variable']['modifier'] as $nr => $modifier_list){
                foreach($modifier_list as $modifier_nr => $modifier){
                    $define_modifier .= '$this->' . $modifier['php_name'] . '($this->parse(), $this->storage(), ' . $define . ', ';
                    if($modifier['has_attribute']){
                        foreach($modifier['attribute'] as $attribute){
                            switch($attribute['type']){
                                case Token::TYPE_METHOD :
                                    dd($attribute);
                                break;
                                case TOKEN::TYPE_VARIABLE:
                                    $temp = [];
                                    $temp[] = $attribute;
                                    $define_modifier .= Variable::define($build, $storage, $temp) . ', ';
                                break;
                                default :
                                    $define_modifier .= Value::get($storage, $attribute) . ', ';
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

    public static function getValue($build, Data $storage, $token=[], $is_result=false){
        $set_max = 1024;
        $set_counter = 0;
        $operator_max = 1024;
        $operator_counter = 0;
        $set = null;
//         d($token);
        //create new token for token + sets;
        while(Set::has($token)){
            $set = Set::get($token);
            while(Operator::has($set)){
                $statement = Operator::get($set);
                $set = Operator::remove($set, $statement);
                $statement = Operator::create($storage, $statement);
                $key = key($statement);
                $set[$key]['value'] = $statement[$key];
                $set[$key]['type'] = Token::TYPE_CODE;
                unset($set[$key]['execute']);
                unset($set[$key]['is_executed']);
                $token[$key] = $set[$key];
                $operator_counter++;
                if($operator_counter > $operator_max){
                    break;
                }
            }
            $target = Set::target($token);
            $token = Set::remove($token);
            $token = Set::replace($token, $set, $target);
            $set_counter++;
            if($set_counter > $set_max){
                break;
            }
        }
        $operator = $token;
        while(Operator::has($operator)){
            $statement = Operator::get($operator);
//             d($operator);
//             $debug = debug_backtrace(true);
//             d($debug[0]);
            $operator = Operator::remove($operator, $statement);
            $statement = Operator::create($storage, $statement);

            if(empty($statement)){
                throw new Exception('Operator error');
            }

            $key = key($statement);
            $operator[$key]['value'] = $statement[$key];
            $operator[$key]['type'] = Token::TYPE_CODE;
            unset($operator[$key]['execute']);
            unset($operator[$key]['is_executed']);
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
        while(count($operator) >= 1){
            $record = array_shift($operator);
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

//                 dd($result);

                $result .= ' . ';
                $is_collect = false;
                $type = null;
                $selection = [];
            }
            elseif($record['type'] == Token::TYPE_BRACKET_SQUARE_OPEN){
                $in_array = true;
            }
            elseif($record['type'] == Token::TYPE_BRACKET_SQUARE_CLOSE){
                $in_array = false;
            }
            elseif($is_collect === false){
                if($record['type'] == 'code'){
//                     dd($record);
                }

                $record = Method::get($build, $storage, $record);
//                 d($record);
                $result .= Value::get($storage, $record);

//                 d($result);

                if(
                    !in_array(
                        $record['type'],
                        [
                            Token::TYPE_EXCLAMATION
                        ]
                    ) &&
                    $in_array === false
                ){
                    $result .= ' . ';
                }

                /*
                if(
                    in_array(
                        $record['type'],
                        [
                            Token::TYPE_STRING ,
                            Token::TYPE_QUOTE_SINGLE_STRING,
                            Token::TYPE_QUOTE_DOUBLE_STRING,
                            //                         Token::TYPE_VARIABLE

                        ]
                    ) &&
                    empty($record['is_foreach']) &&
                    $in_array === false
                ){

                    $result .= ' . ';
                }
                */

                $operator_counter++;
                if($operator_counter > $operator_max){
                    break;
                }
            }
            //this too see below breaks a lot, foreach, but also route.get so disabled

            /*
            if(
                in_array(
                    $record['type'],
                    [
                        Token::TYPE_STRING ,
                        Token::TYPE_QUOTE_SINGLE_STRING,
                        Token::TYPE_QUOTE_DOUBLE_STRING,
                        //                     Token::TYPE_VARIABLE

                    ]
                    ) &&
                $in_array === false
            ){
                if($storage->data('is.debug') == 'select'){
                    d($result);
                }
                $result = substr($result,0, -3);
            }
            */
            if($storage->data('is.debug') == 'string'){
                //             d($result);
            }
                    //         d($result);


                    // d($result)

        }
        $result = substr($result,0, -3);
        return $result;
    }

}