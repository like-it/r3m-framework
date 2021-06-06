<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-13
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_time(Parse $parse, Data $data){
    $attribute = func_get_args();
    array_shift($attribute);
    array_shift($attribute);

    $time= array_shift($attribute);

    if(empty($attribute) && is_null($time)){
        $result = time();
    } else {
        if(is_bool($time)){
            $result = microtime($time);
        } else {
            switch(count($attribute)){
                case 5:
                    $result = mktime($time,
                        array_shift($attribute),
                        array_shift($attribute),
                        array_shift($attribute),
                        array_shift($attribute),
                        array_shift($attribute)
                    );
                break;
                case 4:
                    $result = mktime($time,
                        array_shift($attribute),
                        array_shift($attribute),
                        array_shift($attribute),
                        array_shift($attribute)
                    );
                break;
                case 3:
                    $result = mktime($time,
                        array_shift($attribute),
                        array_shift($attribute),
                        array_shift($attribute)
                    );
                break;
                case 2:
                    $result = mktime($time,
                        array_shift($attribute),
                        array_shift($attribute)
                    );
                break;
                case 1:
                    $result = mktime($time,
                        array_shift($attribute)
                    );
                break;
                case 0:
                    $result = mktime($time);
                break;
            }
        }
    }
    return $result;
}
