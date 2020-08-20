<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */

namespace R3m\Io\Module\Parse;

use R3m\Io\Module\Data;
use Exception;

class Value {
    const TYPE_CAST_BOOLEAN = 'bool';
    const TYPE_CAST_INT = 'int';
    const TYPE_CAST_FLOAT = 'float';
    const TYPE_CAST_STRING = 'string';

    public static function get($record=[], $is_assign=false){
        switch($record['type']){
            case Token::TYPE_INT :
            case Token::TYPE_FLOAT :
                return $record['execute'];
            break;
            case Token::TYPE_BOOLEAN :
            case Token::TYPE_NULL :
            case Token::TYPE_COMMA  :
            case Token::TYPE_EXCLAMATION :
            case Token::TYPE_BRACKET_SQUARE_OPEN :
            case Token::TYPE_BRACKET_SQUARE_CLOSE :
            case Token::TYPE_CODE :
            case Token::TYPE_PARENTHESE_OPEN :
            case Token::TYPE_PARENTHESE_CLOSE :
            case Token::TYPE_QUOTE_SINGLE_STRING :
            case Token::TYPE_BACKSLASH :
            case Token::TYPE_QUOTE_SINGLE :
//             case Token::TYPE_WHITESPACE :
                return $record['value'];
            break;
            case Token::TYPE_STRING :
                return '\'' . $record['value'] . '\''; //might need str_replace on quote_single (') to (\')
            break;
            case Token::TYPE_QUOTE_DOUBLE_STRING :
                if(stristr($record['value'], '{') === false){
                    return $record['value'];
                }
                if($record['depth'] == 0){
                    if($is_assign === true){
                        return '$this->parse()->compile(\'' . substr($record['value'], 1, -1) . '\', [], $this->storage())';
                    } else {
                        return '$this->parse()->compile(\'' . $record['value'] . '\', [], $this->storage())';
                    }
                }
                else {
                    return '$this->parse()->compile(\'' . substr($record['value'], 1, -1) . '\', [], $this->storage())';

                }



//                 $record['value'] = str_replace('{$', '{\$', $record['value']);
//                 return '$this->parse()->compile(' . $record['value'] . ', [], $this->storage())';
                /*
                $record['value'] = '\'' . substr($record['value'], 1, -1) . '\''; // variables in " strings arent possible

                if($record['depth'] == 0){
//                     return '"' . '$this->parse()->compile(' . $record['value'] . ', [], $this->storage())' . '"';
                    return '$this->parse()->compile(' . $record['value'] . ', [], $this->storage())';
                } else {
                    return '$this->parse()->compile(' . $record['value'] . ', [], $this->storage())';
                }
                */


                /*
                d($record['value']);
//                 $record['value'] = str_replace('\\\'', '\'', $record['value']);
//                 $record['value'] = str_replace('\'', '\\\'', $record['value']);
//                 $debug = debug_backtrace(true);
//                 dd($debug);
//                                 d($record);
//                 return '$this->parse()->compile(\'' . substr($record['value'], 1, -1) . '\', [], $this->storage())';
                if($record['depth'] == 0){
                    if($is_assign === true){
                        return '$this->parse()->compile(\'' . substr($record['value'], 1, -1) . '\', [], $this->storage())';
                    } else {
                        return '$this->parse()->compile(\'' . $record['value'] . '\', [], $this->storage())';
                    }

                }
                else {

//                     return '$this->parse()->compile(\'' . str_replace('\'', '\\\'' ,substr($record['value'], 1, -1)) . '\', [], $this->storage())';

                    return '$this->parse()->compile(\'' . substr($record['value'], 1, -1) . '\', [], $this->storage())';

                }
//                 return '"\' . ' . 'str_replace([\'\n\', \'\t\'], ["\n", "\t"], $this->parse()->compile(\'' . substr($record['value'], 1, -1) . '\', [], $this->storage()))' . ' . "\'';
//                 return '\'"\' . ' . 'str_replace([\'\n\', \'\t\'], ["\n", "\t"], $this->parse()->compile(\'' . substr($record['value'], 1, -1) . '\', [], $this->storage()))' . ' . \'"\'';
 *              */

            break;
            case Token::TYPE_CAST :
                return Value::getCast($record);
            break;
            case Token::TYPE_VARIABLE :
                //missing storage from document
                return '$this->storage()->data(\'' . $record['variable']['attribute'] .'\')';
            break;
            case Token::TYPE_METHOD :
                return '$this->' . $record['method']['php_name'] . '($this->parse(), $this->storage())';
            break;
            case Token::TYPE_WHITESPACE :
                return;
            break;
            default:
                $debug = debug_backtrace(true);
                d($record);
                dd($debug);
                throw new Exception('Variable value type ' .  $record['type'] . ' not defined');
            break;
        }
    }

    private function getCast($record=[]){
        switch(strtolower($record['value'])){
            case 'bool':
            case 'boolean':
                $result = Value::TYPE_CAST_BOOLEAN;
            break;
            case 'int':
            case 'integer':
                $result = Value::TYPE_CAST_INT;
            break;
            case 'float':
            case 'double':
                $result = Value::TYPE_CAST_FLOAT;
            break;
            case 'string':
                $result = Value::TYPE_CAST_STRING;
            break;
            default:
                throw new Exception('could not create cast: ' . $record['value']);
        }
        return '(' . $result . ')';
    }


}