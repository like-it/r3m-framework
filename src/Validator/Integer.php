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
use R3m\Io\App;
use R3m\Io\Module\Parse\Token;

/**
 * @throws Exception
 */
function validate_integer(App $object, $string='', $field='', $argument=''): bool
{
    $int = intval($string);
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
            $result = $int > $right;
        break;
        case '<' :
            $result = $int < $right;
        break;
        case '>=' :
            $result = $int >= $right;
        break;
        case '<=' :
            $result = $int <= $right;
        break;                
        case '==' :
            $result = $int == $right;
        break;
        case '!=' :
            $result = $int != $right;
        break;
        case '===' :
            $result = $int === $right;
            break;
        case '!==' :
            $result = $int !== $right;
            break;
        default:
            throw new Exception('Unknown equation');
    }
    return $result;    
}
