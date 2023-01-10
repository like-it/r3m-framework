<?php
/**
 * @author          Remco van der Velde
 * @since           18-12-2020
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module;

use stdClass;
use Exception;
use R3m\Io\App;
use R3m\Io\Config;

class Limit extends Data{
    const LIMIT = 2;
    const MAX = 1000;

    public static function list($list): Limit
    {
        return new Limit($list);
    }

    public function with($limit=[]): array
    {
        $list = $this->data();
        $start = 0;
        if(array_key_exists('start', $limit)){
            $start = (int) $limit['start'];
        }
        if(array_key_exists('limit', $limit)){
            $the_limit = (int) $limit['limit'];
        } else {
            $the_limit = Limit::LIMIT;
        }
        if(array_key_exists('page', $limit)){
            $start =  ((int) $limit['page'] * $the_limit) - $the_limit;
        }
        $nr = 0;
        $end = $start + $the_limit;
        $result = [];
        if(
            is_array($list) || 
            is_object($list)
        ){
            $is_collect = false;
            foreach($list as $record){
                if($nr === $start){
                    $is_collect = true;
                }
                if($nr < $end && $is_collect){
                    $result[] = $record;
                }
                elseif($is_collect) {
                    break;
                }
                $nr++;
            }
        }
        return $result;
    }
}
