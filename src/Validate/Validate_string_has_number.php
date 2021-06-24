<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-18
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse\Token;

function validate_string_has_number(R3m\Io\App $object, $field='', $argument=''){
    $string = $object->request($field);
    $split = str_split($string);
    $test = [];
    $nr = 0;
    foreach($split as $char){
        if(
            in_array(
                $char,
                [
                    '0' ,
                    '1',
                    '2',
                    '3',
                    '4',
                    '5',
                    '6',
                    '7',
                    '8',
                    '9'
                ],
                 true
            )
        ){
            if(array_key_exists($nr, $test) === false){
                $test[$nr] = $char;
            } else {
                $test[$nr] .= $char;
            }
        } else {
            $nr++;
        }
    }
    $length = count($test);
    $argument = Token::tree('{if($argument ' . $argument . ')}{/if}');
    $left = null;
    $equation = null;
    $right = null;
    foreach($argument[1]['method']['attribute'][0] as $nr => $record){
        if(empty($left)){
            $left = $record;
        }
        elseif(empty($equation)){
            $equation = $record['value'];            
        }
        elseif(empty($right)){
            $right = $record['execute'];
            break;
        }
    }
    $result = false;
    switch($equation){
        case '>' :
            $result = $length > $right;
        break;
        case '<' :
            $result = $length < $right;
        break;
        case '>=' :
            $result = $length >= $right;
        break;
        case '<=' :
            $result = $length <= $right;
        break;                
        case '==' :
            $result = $length == $right;
        break;
        case '!=' :
            $result = $length != $right;
        break;
        case '===' :
            $result = $length === $right;
            break;
        case '!==' :
            $result = $length !== $right;
            break;
        default:
            throw new Exception('Unknown equation');
    }
    return $result;    
}
