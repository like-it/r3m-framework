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

    public static function get($record=[]){
        switch($record['type']){
            case Token::TYPE_INT :
            case Token::TYPE_FLOAT :
                return $record['execute'];
            break;
            case Token::TYPE_BOOLEAN :
                return $record['value'];
            break;
            case Token::TYPE_STRING :
                return '\'' . $record['value'] . '\'';
            break;
            case Token::TYPE_CODE :
            case Token::TYPE_QUOTE_SINGLE_STRING :
                return $record['value'];
            break;
            case Token::TYPE_QUOTE_DOUBLE_STRING :
                return '$this->parse()->compile(' . substr($record['value'], 1, -1) . ', $this->storage()->data())';
            break;
            default:
                $debug = debug_backtrace(true);
                dd($debug);
                d($record);
                throw new Exception('Variable value type ' .  $record['type'] . ' not defined');
        }
    }


}