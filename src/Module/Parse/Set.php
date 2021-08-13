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

class Set {

    public static function has($token=[]){
        foreach ($token as $nr => $record){
            if(
                !empty($record['depth']) &&
                $record['value'] == '('
            ){
                return true;
            }
        }
        return false;
    }

    public static function get($token=[]){
        $highest = Set::highest($token);
        $set = [];
        $is_collect = false;
        foreach($token as $nr => $record){
            if(
                $record['depth'] === $highest &&
                $record['value'] === '('
            ){
                $is_collect = true;
            }
            elseif(
                $record['depth'] === $highest &&
                $record['value'] === ')'
            ){
                $is_collect = false;
            }
            elseif($is_collect){
                $set[$nr] = $record;
            }
        }
        return $set;
    }

    public static function target($token=[]){
        $highest = Set::highest($token);
        foreach($token as $nr => $record){
            if(
                $record['depth'] == $highest &&
                (
                    $record['value'] === '(' ||
                    $record['type'] === Token::TYPE_CAST
                )
            ){
                return $nr;
            }
        }
    }

    public static function replace($token=[], $set=[], $target=0){
        $target += 0;
        $nr = 0;
        foreach($set as $record){
            $token[$target + $nr] = $record;
            $nr++;
        }
        return $token;
    }

    public static function pre_remove($token=[]){
        $highest = Set::highest($token);
        $is_collect = false;
        foreach($token as $nr => $record){
            if($record['depth'] == $highest && $record['value'] == '('){
                $is_collect = true;
            }
            elseif($record['depth'] == $highest && $record['value'] == ')'){
                $is_collect = false;
                unset($token[$nr]);
            }
            elseif($is_collect){
                $token[$nr] = null;
            }
        }
        return $token;
    }

    public static function remove($token=[]){
        foreach($token as $nr => $record){
            if($record === null){
                unset($token[$nr]);
            }
        }
        return $token;
    }

    public static function highest($token=[]){
        $depth = 0;
        foreach ($token as $nr => $record){
            if(
                !empty($record['depth']) &&
                $record['depth'] > $depth
            ){
                $depth = $record['depth'];
            }
        }
        return $depth;
    }
}