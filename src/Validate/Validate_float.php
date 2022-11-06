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

function validate_float(R3m\Io\App $object, $string='', $field='', $argument=''){
    $float = floatval($string);
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
            $result = $float > $right;
        break;
        case '<' :
            $result = $float < $right;
        break;
        case '>=' :
            $result = $float >= $right;
        break;
        case '<=' :
            $result = $float <= $right;
        break;                
        case '==' :
            $result = $float == $right;
        break;
        case '!=' :
            $result = $float != $right;
        break;
        case '===' :
            $result = $float === $right;
            break;
        case '!==' :
            $result = $float !== $right;
            break;
        default:
            throw new Exception('Unknown equation');
    }
    return $result;    
}
