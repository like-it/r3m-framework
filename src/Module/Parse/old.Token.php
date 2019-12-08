<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */

namespace R3m\Io\Module\Parse;

class old.Token {
    public const TYPE_NULL = 'null';
    public const TYPE_STRING = 'string';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_BOOLEAN_AND = 'boolean-and';
    public const TYPE_BOOLEAN_OR = 'boolean-or';
    public const TYPE_INT = 'integer';
    public const TYPE_OCT = 'octal';
    public const TYPE_HEX = 'hexadecimal';
    public const TYPE_FLOAT = 'float';
    public const TYPE_ARRAY = 'array';
    public const TYPE_OBJECT = 'object';
    public const TYPE_VARIABLE = 'variable';
    public const TYPE_OPERATOR = 'operator';
    public const TYPE_DOT = 'dot';
    public const TYPE_COLON = 'colon';
    public const TYPE_DOUBLE_COLON = 'double-colon';
    public const TYPE_DOUBLE_ARROW = 'double-arrow';
    public const TYPE_AS = 'as';
    public const TYPE_SEMI_COLON = 'semi-colon';
    public const TYPE_COMMA = 'comma';
    public const TYPE_MIXED = 'mixed';
    public const TYPE_WHITESPACE = 'whitespace';
    public const TYPE_STATEMENT = 'statement';
    public const TYPE_PARENTHESE = 'parenthese';
    public const TYPE_BRACKET = 'bracket';
    public const TYPE_NUMBER = 'number';
    public const TYPE_SET = 'set';
    public const TYPE_METHOD = 'method';
    public const TYPE_FUNCTION = 'function';
    public const TYPE_MODIFIER = 'modifier';
    public const TYPE_CLASS = 'class';
    public const TYPE_TRAIT = 'trait';
    public const TYPE_EXCLAMATION = 'exclamation';
    public const TYPE_CONTROL = 'control';
    public const TYPE_WHILE = 'while';
    public const TYPE_QUOTE_SINGLE_STRING = 'quote-single-string';
    public const TYPE_QUOTE_DOUBLE_STRING = 'quote-double-string';
    public const TYPE_QUOTE_SINGLE = 'quote-single';
    public const TYPE_QUOTE_DOUBLE = 'quote-double';
    public const TYPE_BACKSLASH = 'backslash';
    public const TYPE_BRACKET_SQUARE_OPEN = 'bracket-square-open';
    public const TYPE_BRACKET_SQUARE_CLOSE = 'bracket-square-close';
    public const TYPE_CURLY_OPEN = 'curly-open';
    public const TYPE_CURLY_CLOSE = 'curly-close';
    public const TYPE_PARENTHESE_OPEN = 'parenthese-open';
    public const TYPE_PARENTHESE_CLOSE = 'parenthese-close';
    public const TYPE_COMMENT_OPEN = 'comment-open';
    public const TYPE_COMMENT_CLOSE = 'comment-close';
    public const TYPE_DOC_COMMENT_OPEN = 'doc-comment-open';
    public const TYPE_COMMENT_SINGLE_LINE = 'comment-single-line';
    public const TYPE_COMMENT = 'comment';
    public const TYPE_DOC_COMMENT = 'doc-comment';
    public const TYPE_AMPERSAND = 'ampersand';
    public const TYPE_QUESTION = 'question';
    public const TYPE_PIPE = 'pipe';
    public const TYPE_LITERAL = 'tag-literal';
    public const TYPE_IS_OBJECT_OPERATOR = 'is-object-operator';
    public const TYPE_IS_ARRAY_OPERATOR = 'is-array-operator';
    public const TYPE_IS_EQUAL = 'is-equal';
    public const TYPE_IS_NOT_EQUAL = 'is-not-equal';
    public const TYPE_IS_GREATER_EQUAL = 'is-greater-equal';
    public const TYPE_IS_SMALLER_EQUAL = 'is-smaller-equal';
    public const TYPE_IS_GREATER = 'is-greater';
    public const TYPE_IS_SMALLER = 'is-smaller';
    public const TYPE_IS_IDENTICAL = 'is-identical';
    public const TYPE_IS_NOT_IDENTICAL = 'is-not-identical';
    public const TYPE_IS_GREATER_GREATER = 'is-greater-greater';
    public const TYPE_IS_SMALLER_SMALLER = 'is-smaller-smaller';
    public const TYPE_IS = 'is';
    public const TYPE_IS_PLUS_EQUAL = 'is-plus-equal';
    public const TYPE_IS_MINUS_EQUAL = 'is-minus-equal';
    public const TYPE_IS_MULTIPLY_EQUAL = 'is-multiply-equal';
    public const TYPE_IS_DIVIDE_EQUAL = 'is-divide-equal';
    public const TYPE_IS_OR_EQUAL = 'is-or-equal';
    public const TYPE_IS_MODULO_EQUAL = 'is-modulo-equal';
    public const TYPE_IS_POWER_EQUAL = 'is-power-equal';
    public const TYPE_IS_XOR_EQUAL = 'is-xor-equal';
    public const TYPE_IS_AND_EQUAL = 'is-and-equal';
    public const TYPE_IS_PLUS = 'is-plus';
    public const TYPE_IS_MINUS = 'is-minus';
    public const TYPE_IS_MULTIPLY = 'is-multiply';
    public const TYPE_IS_DIVIDE = 'is-divide';
    public const TYPE_IS_MODULO = 'is-modulo';
    public const TYPE_IS_PLUS_PLUS = 'is-plus-plus';
    public const TYPE_IS_MINUS_MINUS = 'is-minus-minus';
    public const TYPE_IS_SPACESHIP = 'is-spaceship';
    public const TYPE_IS_POWER = 'is-power';
    public const TYPE_IS_COALESCE = 'is-coalesce';
    public const TYPE_R3M = 'r3m';
    public const TYPE_CAST = 'cast';
    public const LITERAL_OPEN = '{literal}';
    public const LITERAL_CLOSE = '{/literal}';
    public const TYPE_TAG_CLOSE = 'tag-close';
    
    public static function split($string='', $length=1, $encoding='UTF-8') {
        $array = [];
        if(is_array($string)){
            $debug = debug_backtrace(true);
            dd($debug);
            
        }
        $strlen = mb_strlen($string);
        for($i=0; $i<$strlen; $i=$i+$length){
            $array[] = mb_substr($string, $i, $length, $encoding);
        }
        return $array;
    }
    
    public static function create($data=[], $count=0){
        $is_variable = false;
        $is_quote_single = false;
        $is_quote_double = false;
        $is_set = false;
        $is_method = false;
        $method = [];
        $variable = '';  
        $quote_single = '';
        $quote_double = '';
        $token = [];
        $previous_nr = null;
        $next = null;
        $next_next = null;        
        $skip = 0;
        foreach($data as $nr => $char){
            if($skip > 0){
                $skip--;
                $previous_nr = $nr;
                continue;
            }
            if(array_key_exists($nr + 1, $data)){
                $next = $nr + 1;
            } else {
                $next = null;                
            }
            if(array_key_exists($nr + 2, $data)){
                $next_next = $nr + 2;
            } else {
                $next_next = null;
            }
            if(
                $is_quote_single === false &&
                $is_quote_double === false &&
                $is_method === false && 
                $char == '$'                
            ){
                $is_variable = true;
                $variable = '$';
                continue;
            }                                    
            if(
                $is_quote_single === false &&
                $is_quote_double === false &&
                $is_method === false && 
                $is_variable
            ){
                if(in_array($char, [
                    ' ',
                    ',',
                    ')'
                ])){
                    $is_variable = false;
                    $record = [];
                    $record['type'] = old.Token::TYPE_VARIABLE;
                    $record['value'] = $variable;
                    $token[] = $record;
                    $variable = '';
                }                
                elseif(in_array($char, [ 
                    '!',
                    '+',
                    '-',
                    '/',
                    '*',
                    '=',
                    '%'
                ])){                    
                    $is_variable = false;
                    $record = [];
                    $record['type'] = old.Token::TYPE_VARIABLE;
                    $record['value'] = $variable;
                    $record['variable']['name'] = substr($variable, 1);
                    
                    $token[] = $record;
                    $variable = '';   
                    
                    $operator = $char;
//                     d($operator);
                    if(
                        $next_next !== null &&
                        $next !== null
                        ){
                            $operator_3 = $operator;
                            $operator_3 .= $data[$next] . $data[$next_next];
                            if(
                                in_array(
                                    $operator_3,
                                    [
                                        '===',
                                        '!==',
                                        '<=>'
                                    ]
                                    )
                                ){
                                    $record = [];
                                    $record['type'] = old.Token::TYPE_OPERATOR;
                                    $record['value'] = $operator_3;
                                    $token[] = $record;
                                    $skip = 2;
                            }
                            $operator_2 = $operator;
                            $operator_2 .= $data[$next];
                            if(
                                in_array(
                                    $operator_2,
                                    [
                                        '==',
                                        '!=',
                                        '<=',
                                        '>=',
                                        '<>',
                                        '--',
                                        '++',
                                        '**',
                                        '//',
                                        '+=',
                                        '-=',
                                        '*=',
                                        '/=',
                                        '%%',
                                        '%='
                                    ]
                                    )
                                ){
                                    $record = [];
                                    $record['type'] = old.Token::TYPE_OPERATOR;
                                    $record['value'] = $operator_2;
                                    $token[] = $record;
                                    $skip = 1;
                            }
                            elseif(
                                in_array(
                                    $operator,
                                    [
                                        '=',
                                        '+',
                                        '-',
                                        '/',
                                        '*',
                                        '%',
                                    ]
                                    )
                                ){
                                    $record = [];
                                    $record['type'] = old.Token::TYPE_OPERATOR;
                                    $record['value'] = $operator;
                                    $token[] = $record;
                                    $skip = 1;
                            }
                    }                                                                                                                                           
                }                                
                $variable .= $char;
            }
            elseif(
                $is_quote_single === false &&
                $is_quote_double === false &&                 
                in_array($char, [
                '!',
                '+',
                '-',
                '/',
                '*',
                '=',
                '%',
            ])){
                $operator = $char;                
                if(
                    $next_next !== null && 
                    $next !== null                                      
                ){
                    $operator_3 = $operator;
                    $operator_3 .= $data[$next] . $data[$next_next];                   
                    if(
                        in_array(
                            $operator_3, 
                            [
                                '===',
                                '!==',
                                '<=>'
                            ]
                        )
                    ){
                        $record = [];
                        $record['type'] = old.Token::TYPE_OPERATOR;
                        $record['value'] = $operator_3;
                        $token[] = $record;
                        $skip = 2;
                    }
                    $operator_2 = $operator;
                    $operator_2 .= $data[$next];
                    if(
                        in_array(
                            $operator_2,
                            [
                                '==',
                                '!=',
                                '<=',
                                '>=',
                                '<>',
                                '--',
                                '++',
                                '**',
                                '//',
                                '+=',
                                '-=',
                                '*=',
                                '/=',
                                '%%',
                                '%='
                            ]
                        )
                    ){
                        $record = [];
                        $record['type'] = old.Token::TYPE_OPERATOR;
                        $record['value'] = $operator_2;
                        $token[] = $record;
                        $skip = 1;
                    }
                    elseif(
                        in_array(
                            $operator,
                            [
                                '=',
                                '+',
                                '-',
                                '/',
                                '*',
                                '%',                                
                            ]
                            )
                        ){
                            $record = [];
                            $record['type'] = old.Token::TYPE_OPERATOR;
                            $record['value'] = $operator;
                            $token[] = $record;
                            $skip = 1;
                    }     
                }                                           
            }
            elseif(
                $is_quote_single === false &&
                $is_quote_double === false &&
                $char == '('
            ){
                for($i=$nr - 1; $i >= 0; $i--){                    
                    $temp = $data[$i];
                    if(
                        in_array(
                            $temp, 
                            [
                                '=',
                                '+',
                                '-',
                                '/',
                                '%',
                                '(',
                                '{'
                            ]
                        )
                    ){                        
                        break;
                        
                    } else {                        
                        $method[] = $temp;                                             
                    }                    
                }
                if(empty($method)){
                    dd('set found');
                } else {
                    $name = ltrim(implode('', array_reverse($method)));
                    $set_depth = 0;
                    $content = $data[$nr];
                    for($i = $nr + 1; $i < $count; $i++){
                        if($data[$i] == '('){
                            $set_depth++;
                        }
                        elseif($data[$i] == ')'){
                            if($set_depth == 0){
                                break;
                            }
                            $set_depth--;
                        }
                        $content .= $data[$i];
                        $skip += 1;
                    }                    
                    $skip += 1;
                    $content .= $data[$i];
                    $record = [];
                    $record['type'] = old.Token::TYPE_METHOD;
                    $record['value'] = $name . $content;
                    $record['method'] = [];
                    $record['method']['name'] = rtrim($name);
                    $record['method']['attribute'] = $content;
                    
                    $token[] = $record;                    
                }                
            }
            elseif(
                $is_quote_single === false &&
                $is_quote_double === false && 
                $char == '\''                
            ){
                $is_quote_single = true;
                $quote_single = $char;
            }
            elseif(
                $is_quote_single === true
            ){
                if(
                    $char == '\'' &&
                    $data[$previous_nr] != '\\'
                ){
                    $quote_single .= $char;
                    $record['type'] = old.Token::TYPE_QUOTE_SINGLE_STRING;
                    $record['value'] = $quote_single;
                    $token[] = $record;
                    $quote_single = '';
                    $is_quote_single = false;
                }
                $quote_single .= $char;
            }
            elseif(
                $is_quote_single === false &&
                $is_quote_double === false &&
                $char == '"'
                
                ){
                    $is_quote_double = true;
                    $quote_double = $char;
            }
            elseif(
                $is_quote_double === true
                ){
                    if(
                        $char == '"' && 
                        $data[$previous_nr] != '\\'
                    ){
                        $quote_double .= $char;
                        $record['type'] = old.Token::TYPE_QUOTE_DOUBLE_STRING;
                        $record['value'] = $quote_double;
                        $token[] = $record;
                        $quote_double = '';
                        $is_quote_single = false;
                    }
                    $quote_double .= $char;
            }
            $previous_nr = $nr;
        }
        return $token;
    }
    
    public static function assign($token=[]){
        $is_variable = false;
        $is_assign = false;
        $collect = [];
        foreach($token as $nr => $record){
            if($record['type'] == old.Token::TYPE_VARIABLE){
                $is_variable = $nr;
                continue;
            }
            if(
                $is_variable !== false &&
                $is_assign === false &&
                $record['type'] == old.Token::TYPE_OPERATOR
                ){                                       
                    $token[$is_variable]['variable']['is_assign'] = true;
                    $token[$is_variable]['variable']['assign']['operator'] = $record['value'];
                    $is_assign = true;
                    unset($token[$nr]);
            }
            elseif(
                $is_variable !== false &&
                $is_assign === false 
            ){
                break;
            } 
            elseif(
                $is_assign === true
            ) {
                $collect[] = $record;
                unset($token[$nr]);
            }
        }
        if(!empty($collect)){
            $token[$is_variable]['variable']['assign']['data'] = $collect;
        }                
        return $token;
    }
    
    private static function type($char=null){
        switch($char){
            case '.' :
                return old.Token::TYPE_DOT;
                break;
            case ',' :
                return old.Token::TYPE_COMMA;
                break;
            case '(' :
                return old.Token::TYPE_PARENTHESE_OPEN;
                break;
            case ')' :
                return old.Token::TYPE_PARENTHESE_CLOSE;
                break;
            case '[' :
                return old.Token::TYPE_BRACKET_SQUARE_OPEN;
                break;
            case ']' :
                return old.Token::TYPE_BRACKET_SQUARE_CLOSE;
                break;
            case '{' :
                return old.Token::TYPE_CURLY_OPEN;
                break;
            case '}' :
                return old.Token::TYPE_CURLY_CLOSE;
                break;
            case '$' :
                return old.Token::TYPE_VARIABLE;
                break;
            case '\'' :
                return old.Token::TYPE_QUOTE_SINGLE;
                break;
            case '"' :
                return old.Token::TYPE_QUOTE_DOUBLE;
                break;
            case '\\' :
                return old.Token::TYPE_BACKSLASH;
                break;
            case ';' :
                return old.Token::TYPE_SEMI_COLON;
                break;
            case '0' :
            case '1' :
            case '2' :
            case '3' :
            case '4' :
            case '5' :
            case '6' :
            case '7' :
            case '8' :
            case '9' :
                return old.Token::TYPE_NUMBER;
                break;
            case '>' :
            case '<' :
            case '=' :
            case '-' :
            case '+' :
            case '/' :
            case '*' :
            case '%' :
            case '^' :
            case '!' :
            case '?' :
            case '|' :
            case '&' :
            case ':' :
                return old.Token::TYPE_OPERATOR;
                break;
            case ' ' :
            case "\t" :
            case "\n" :
            case "\r" :
                return old.Token::TYPE_WHITESPACE;
                break;
            default:
                return old.Token::TYPE_STRING;
                break;
        }
    }        
}